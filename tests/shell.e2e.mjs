/*
 * End-to-end test of the arcade shell, driven over the Chrome DevTools Protocol.
 * No dependencies — Node 22 ships a global WebSocket.
 *
 * Because the game iframe is same-origin, the parent page can reach into it,
 * which is exactly how a real player's clicks would land.
 */

const BASE = process.env.ARCADE_BASE || 'http://127.0.0.1:8123';
const CDP = process.env.CDP_URL || 'http://127.0.0.1:9222';

let failures = 0;
function check(label, actual, expected) {
  const ok = JSON.stringify(actual) === JSON.stringify(expected);
  if (!ok) failures++;
  console.log(`${ok ? 'PASS' : 'FAIL'} ${label}${ok ? '' : `  got=${JSON.stringify(actual)} want=${JSON.stringify(expected)}`}`);
}
function checkTruthy(label, actual) {
  const ok = !!actual;
  if (!ok) failures++;
  console.log(`${ok ? 'PASS' : 'FAIL'} ${label}${ok ? '' : `  got=${JSON.stringify(actual)}`}`);
}

const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

// --- minimal CDP client ---
const target = await (await fetch(`${CDP}/json/new?${encodeURIComponent(BASE + '/#hello')}`, { method: 'PUT' })).json();
const ws = new WebSocket(target.webSocketDebuggerUrl);
await new Promise((res, rej) => { ws.onopen = res; ws.onerror = rej; });

let msgId = 0;
const waiting = new Map();
ws.onmessage = (e) => {
  const m = JSON.parse(e.data);
  if (m.id && waiting.has(m.id)) {
    const { resolve, reject } = waiting.get(m.id);
    waiting.delete(m.id);
    m.error ? reject(new Error(JSON.stringify(m.error))) : resolve(m.result);
  }
};
function send(method, params = {}) {
  const id = ++msgId;
  return new Promise((resolve, reject) => {
    waiting.set(id, { resolve, reject });
    ws.send(JSON.stringify({ id, method, params }));
  });
}

/** Evaluate an expression in the page and return its value. */
async function evaluate(expression, awaitPromise = true) {
  const r = await send('Runtime.evaluate', {
    expression, awaitPromise, returnByValue: true, allowUnsafeEvalBlockedByCSP: true,
  });
  if (r.exceptionDetails) {
    throw new Error('page threw: ' + (r.exceptionDetails.exception?.description || r.exceptionDetails.text));
  }
  return r.result.value;
}

/** Poll until the expression is truthy, or give up. */
async function until(expression, timeoutMs = 8000, label = expression) {
  const deadline = Date.now() + timeoutMs;
  while (Date.now() < deadline) {
    if (await evaluate(expression, false)) return true;
    await sleep(120);
  }
  throw new Error(`timed out waiting for: ${label}`);
}

await send('Runtime.enable');
await send('Page.enable');

console.log('--- shell loads and finds the game ---');
await until("!!document.querySelector('#game-list li[data-id=\"hello\"]')", 8000, 'sidebar entry');
check('sidebar lists Hello', await evaluate("document.querySelector('#game-list li[data-id=\"hello\"] a').textContent"), 'Hello');
check('current game is marked', await evaluate("document.querySelector('#game-list li[data-id=\"hello\"]').classList.contains('current')"), true);
check('player panel visible', await evaluate("!document.getElementById('player').hidden"), true);
check('home panel hidden', await evaluate("document.getElementById('home').hidden"), true);
check('title shows year', await evaluate("document.getElementById('game-title').textContent"), 'Hello (2026)');

console.log('\n--- iframe mounts and the SDK connects ---');
await until("(function(){var f=document.querySelector('#stage iframe');return f && f.contentDocument && f.contentDocument.readyState==='complete';})()", 8000, 'iframe load');
check('stage status hidden after ready()', await evaluate("document.getElementById('stage-status').hidden"), true);
check('empty board renders 10 rows', await evaluate("document.querySelectorAll('#board-body tr').length"), 10);
check('empty rows show dashes', await evaluate("document.querySelector('#board-body tr td.who').textContent"), '---');
check('getHighScores() reached the game', await evaluate("document.querySelector('#stage iframe').contentDocument.getElementById('best').textContent"), 'no scores yet — the board is yours');

console.log('\n--- the shell ignores messages that are not from the game frame ---');
// Same shape as a real score message, but sent by the top window.
await evaluate("window.postMessage({source:'arcade-game',type:'score',value:424242}, location.origin); true", false);
await sleep(400);
check('spoofed message ignored', await evaluate("document.getElementById('initials-modal').hidden"), true);
check('no note from spoofed message', await evaluate("document.getElementById('submit-note').hidden"), true);

console.log('\n--- the too-fast guard rejects instant scores ---');
// Sent from inside the iframe via the real SDK, moments after mount.
await evaluate("document.querySelector('#stage iframe').contentWindow.Arcade.submitScore(999999); true", false);
await sleep(500);
checkTruthy('too-fast score is refused', (await evaluate("document.getElementById('submit-note').textContent")).includes('too fast'));
check('modal did NOT open for a too-fast score', await evaluate("document.getElementById('initials-modal').hidden"), true);

console.log('\n--- setScore() drives the live readout ---');
await evaluate("(function(){var d=document.querySelector('#stage iframe').contentDocument;d.getElementById('tap').click();d.getElementById('tap').click();return true;})()", false);
await until("!document.getElementById('live-score').hidden", 4000, 'live score visible');
const liveScore = await evaluate("parseInt(document.getElementById('live-score-value').textContent.replace(/,/g,''),10)");
checkTruthy('live score is above zero', liveScore > 0);
check('live score matches the game', await evaluate("parseInt(document.querySelector('#stage iframe').contentDocument.getElementById('score').textContent,10)"), liveScore);

console.log('\n--- real game over opens the initials modal ---');
// Wait out the minimum play duration, then end the game for real.
await sleep(3200);
await evaluate("(function(){document.querySelector('#stage iframe').contentDocument.getElementById('over').click();return true;})()", false);
await until("!document.getElementById('initials-modal').hidden", 5000, 'modal opens');
check('modal heading', await evaluate("document.getElementById('initials-heading').textContent"), 'New High Score!');
check('three slots rendered', await evaluate("document.querySelectorAll('#initials-slots .slot').length"), 3);
check('first slot is active', await evaluate("document.querySelectorAll('#initials-slots .slot')[0].classList.contains('active')"), true);
check('input is focused', await evaluate("document.activeElement === document.getElementById('initials-input')"), true);
checkTruthy('countdown is running', (await evaluate("document.getElementById('initials-countdown').textContent")).includes('auto-submits in'));

console.log('\n--- typing initials ---');
await evaluate(`(function(){
  var input = document.getElementById('initials-input');
  input.value = 'j!k';                       // lowercase + junk on purpose
  input.dispatchEvent(new Event('input', {bubbles:true}));
  return true;
})()`, false);
check('junk stripped, uppercased', await evaluate("document.getElementById('initials-input').value"), 'JK');
check('slots mirror the input', await evaluate("Array.from(document.querySelectorAll('#initials-slots .slot')).map(s=>s.textContent).join('')"), 'JK');

console.log('\n--- ENTER submits and the board updates ---');
await evaluate(`(function(){
  var input = document.getElementById('initials-input');
  input.dispatchEvent(new KeyboardEvent('keydown', {key:'Enter', bubbles:true}));
  return true;
})()`, false);
await until("document.getElementById('initials-modal').hidden", 5000, 'modal closes');
await until("document.querySelector('#board-body tr td.who').textContent !== '---'", 6000, 'board updates');
check('two chars padded with A', await evaluate("document.querySelector('#board-body tr td.who').textContent"), 'JKA');
check('rank 1 highlighted as fresh', await evaluate("document.querySelector('#board-body tr').classList.contains('fresh')"), true);
check('board still shows 10 rows', await evaluate("document.querySelectorAll('#board-body tr').length"), 10);
checkTruthy('placement note shown', (await evaluate("document.getElementById('submit-note').textContent")).includes('You placed #1'));
check('game was told its rank', await evaluate("document.querySelector('#stage iframe').contentDocument.getElementById('out').textContent"), 'you placed #1!');
const boardScore = await evaluate("parseInt(document.querySelector('#board-body tr td.score').textContent.replace(/,/g,''),10)");
check('board score matches what was played', boardScore, liveScore);

console.log('\n--- navigating away and back ---');
await evaluate("location.hash=''; true", false);
await until("!document.getElementById('home').hidden", 4000, 'home visible');
check('iframe unmounted', await evaluate("!document.querySelector('#stage iframe')"), true);
checkTruthy('home grid lists the game', await evaluate("!!document.querySelector('#game-grid a[href=\"#hello\"]')"));
check('grid falls back to a letter tile', await evaluate("document.querySelector('#game-grid a[href=\"#hello\"] .letter')?.textContent"), 'H');

await evaluate("location.hash='#nope'; true", false);
await until("!document.getElementById('not-found').hidden", 4000, 'not-found visible');
check('unknown game shows not-found', await evaluate("document.getElementById('not-found').hidden"), false);

console.log(`\n${failures === 0 ? 'ALL PASSED' : failures + ' FAILURE(S)'}`);
ws.close();
process.exit(failures === 0 ? 0 : 1);
