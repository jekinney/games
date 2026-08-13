/*
 * Minimal Chrome DevTools Protocol client. No dependencies — Node 22+ ships a
 * global WebSocket.
 *
 * Start Chrome first:
 *   chrome --headless=new --remote-debugging-port=9222 --user-data-dir=<tmp> about:blank
 */

export const BASE = process.env.ARCADE_BASE || 'http://127.0.0.1:8123';
const CDP = process.env.CDP_URL || 'http://127.0.0.1:9222';

export const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

let failures = 0;

export function check(label, actual, expected) {
  const ok = JSON.stringify(actual) === JSON.stringify(expected);
  if (!ok) failures++;
  console.log(`${ok ? 'PASS' : 'FAIL'} ${label}${ok ? '' : `  got=${JSON.stringify(actual)} want=${JSON.stringify(expected)}`}`);
}

export function checkTruthy(label, actual) {
  const ok = !!actual;
  if (!ok) failures++;
  console.log(`${ok ? 'PASS' : 'FAIL'} ${label}${ok ? '' : `  got=${JSON.stringify(actual)}`}`);
}

export function report() {
  console.log(`\n${failures === 0 ? 'ALL PASSED' : failures + ' FAILURE(S)'}`);
  return failures;
}

/** Open a page and return helpers bound to it. */
export async function openPage(path = '') {
  const target = await (await fetch(`${CDP}/json/new?${encodeURIComponent(BASE + '/' + path)}`, { method: 'PUT' })).json();
  const ws = new WebSocket(target.webSocketDebuggerUrl);
  await new Promise((res, rej) => { ws.onopen = res; ws.onerror = rej; });

  let msgId = 0;
  const waiting = new Map();
  ws.onmessage = (e) => {
    const m = JSON.parse(e.data);
    if (m.id && waiting.has(m.id)) {
      const w = waiting.get(m.id);
      waiting.delete(m.id);
      m.error ? w.reject(new Error(JSON.stringify(m.error))) : w.resolve(m.result);
    }
  };

  const send = (method, params = {}) => {
    const id = ++msgId;
    return new Promise((resolve, reject) => {
      waiting.set(id, { resolve, reject });
      ws.send(JSON.stringify({ id, method, params }));
    });
  };

  async function evaluate(expression, awaitPromise = false) {
    const r = await send('Runtime.evaluate', { expression, awaitPromise, returnByValue: true });
    if (r.exceptionDetails) {
      throw new Error('page threw: ' + (r.exceptionDetails.exception?.description || r.exceptionDetails.text));
    }
    return r.result.value;
  }

  async function until(expression, timeoutMs = 8000, label = expression) {
    const deadline = Date.now() + timeoutMs;
    while (Date.now() < deadline) {
      if (await evaluate(expression)) return true;
      await sleep(120);
    }
    throw new Error(`timed out waiting for: ${label}`);
  }

  /** Send a key to the game inside the iframe, as a real player would. */
  async function gameKey(key, type = 'keydown') {
    return evaluate(`(function(){
      var f = document.querySelector('#stage iframe');
      var w = f.contentWindow;
      w.dispatchEvent(new w.KeyboardEvent('${type}', { key: ${JSON.stringify(key)}, bubbles: true, cancelable: true }));
      return true;
    })()`);
  }

  /** Wait until the game's iframe has loaded and its SDK is live. */
  async function waitForGame(timeoutMs = 10000) {
    await until(
      "(function(){var f=document.querySelector('#stage iframe');return !!(f&&f.contentWindow&&f.contentWindow.Arcade&&f.contentDocument&&f.contentDocument.readyState==='complete');})()",
      timeoutMs,
      'game iframe + SDK'
    );
  }

  await send('Runtime.enable');
  await send('Page.enable');

  return { send, evaluate, until, gameKey, waitForGame, close: () => ws.close() };
}
