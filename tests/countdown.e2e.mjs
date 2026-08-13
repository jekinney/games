/* Verifies the arcade auto-submit: leave the modal alone and it submits itself. */
const BASE = 'http://127.0.0.1:8123';
const CDP = 'http://127.0.0.1:9222';
const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

let failures = 0;
function check(label, actual, expected) {
  const ok = JSON.stringify(actual) === JSON.stringify(expected);
  if (!ok) failures++;
  console.log(`${ok ? 'PASS' : 'FAIL'} ${label}${ok ? '' : `  got=${JSON.stringify(actual)} want=${JSON.stringify(expected)}`}`);
}

const target = await (await fetch(`${CDP}/json/new?${encodeURIComponent(BASE + '/#hello')}`, { method: 'PUT' })).json();
const ws = new WebSocket(target.webSocketDebuggerUrl);
await new Promise((res, rej) => { ws.onopen = res; ws.onerror = rej; });

let msgId = 0; const waiting = new Map();
ws.onmessage = (e) => {
  const m = JSON.parse(e.data);
  if (m.id && waiting.has(m.id)) { const w = waiting.get(m.id); waiting.delete(m.id); m.error ? w.reject(new Error(JSON.stringify(m.error))) : w.resolve(m.result); }
};
const send = (method, params = {}) => { const id = ++msgId; return new Promise((resolve, reject) => { waiting.set(id, { resolve, reject }); ws.send(JSON.stringify({ id, method, params })); }); };
async function evaluate(expression, awaitPromise = true) {
  const r = await send('Runtime.evaluate', { expression, awaitPromise, returnByValue: true });
  if (r.exceptionDetails) throw new Error('page threw: ' + (r.exceptionDetails.exception?.description || r.exceptionDetails.text));
  return r.result.value;
}
async function until(expr, timeoutMs, label) {
  const deadline = Date.now() + timeoutMs;
  while (Date.now() < deadline) { if (await evaluate(expr, false)) return true; await sleep(150); }
  throw new Error('timed out waiting for: ' + label);
}
await send('Runtime.enable');

await until("(function(){var f=document.querySelector('#stage iframe');return f&&f.contentWindow&&f.contentWindow.Arcade;})()", 10000, 'sdk');
await sleep(3300); // clear the minimum-play-duration guard

await evaluate("(function(){var d=document.querySelector('#stage iframe').contentDocument;d.getElementById('tap').click();d.getElementById('over').click();return true;})()", false);
await until("!document.getElementById('initials-modal').hidden", 5000, 'modal opens');

console.log('modal open — typing one letter, then walking away');
await evaluate("(function(){var i=document.getElementById('initials-input');i.value='z';i.dispatchEvent(new Event('input',{bubbles:true}));return true;})()", false);

const t0 = Date.now();
const first = await evaluate("document.getElementById('initials-countdown').textContent");
console.log('  countdown reads:', JSON.stringify(first));

await sleep(3000);
const mid = await evaluate("document.getElementById('initials-countdown').textContent");
console.log('  3s later:       ', JSON.stringify(mid));
check('countdown is ticking down', mid !== first, true);

// Wait out the rest of the 10 seconds without touching anything.
await until("document.getElementById('initials-modal').hidden", 12000, 'auto-submit closes the modal');
const elapsed = Math.round((Date.now() - t0) / 1000);
console.log('  modal auto-closed after ~' + elapsed + 's');
check('closed on its own near the 10s mark', elapsed >= 8 && elapsed <= 12, true);

await until("Array.from(document.querySelectorAll('#board-body tr td.who')).some(function(td){return td.textContent!=='---';})", 6000, 'board update');
check('single typed letter padded to ZAA', await evaluate("Array.from(document.querySelectorAll('#board-body tr td.who')).some(function(td){return td.textContent==='ZAA';})"), true);

console.log(`\n${failures === 0 ? 'ALL PASSED' : failures + ' FAILURE(S)'}`);
ws.close();
process.exit(failures === 0 ? 0 : 1);
