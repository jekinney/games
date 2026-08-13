/*
 * Plays each installed game for real, through the shell, and checks the score
 * lands on the board. Black-box throughout: the tests read the canvas rather
 * than reaching into game internals, so a game only has to honour the plugin
 * contract to pass.
 *
 * node tests/games.e2e.mjs
 */
import { openPage, check, checkTruthy, report, sleep, BASE } from './cdp.mjs';

const MIN_PLAY_MS = 2200;   // clear the shell's too-fast guard

/** Type initials into the shell's modal and submit. */
async function enterInitials(page, initials) {
  await page.until("!document.getElementById('initials-modal').hidden", 8000, 'initials modal');
  await page.evaluate(`(function(){
    var i = document.getElementById('initials-input');
    i.value = ${JSON.stringify(initials)};
    i.dispatchEvent(new Event('input', {bubbles:true}));
    i.dispatchEvent(new KeyboardEvent('keydown', {key:'Enter', bubbles:true}));
    return true;
  })()`);
  await page.until("document.getElementById('initials-modal').hidden", 8000, 'modal closes');
}

const topRow = "(function(){var r=document.querySelector('#board-body tr');return {who:r.querySelector('td.who').textContent, score:r.querySelector('td.score').textContent};})()";

/* ============================ SNAKE ============================ */

async function testSnake() {
  console.log('\n=== SNAKE ===');
  const page = await openPage('#snake');
  await page.waitForGame();

  check('title overlay is up', await page.evaluate(
    "document.querySelector('#stage iframe').contentDocument.getElementById('overlay').hidden"), false);
  checkTruthy('record line rendered', await page.evaluate(
    "document.querySelector('#stage iframe').contentDocument.getElementById('record').textContent.length > 1"));

  await sleep(MIN_PLAY_MS);

  await page.gameKey(' ');
  await page.until(
    "document.querySelector('#stage iframe').contentDocument.getElementById('overlay').hidden",
    4000, 'game starts');
  check('overlay cleared on start', await page.evaluate(
    "document.querySelector('#stage iframe').contentDocument.getElementById('overlay').hidden"), true);

  // Turn up and hold that line into the top wall.
  await page.gameKey('ArrowUp');
  await page.until(
    "!document.querySelector('#stage iframe').contentDocument.getElementById('overlay').hidden",
    8000, 'snake dies on the wall');
  checkTruthy('game over shown', (await page.evaluate(
    "document.querySelector('#stage iframe').contentDocument.querySelector('#overlay h1').textContent")).includes('GAME OVER'));

  await enterInitials(page, 'SNK');
  await page.until("document.querySelector('#board-body tr td.who').textContent !== '---'", 8000, 'board updates');
  const row = await page.evaluate(topRow);
  check('snake score recorded under SNK', row.who, 'SNK');
  checkTruthy('shell reported the placement', (await page.evaluate(
    "document.getElementById('submit-note').textContent")).includes('You placed'));

  page.close();
}

/* =========================== BREAKOUT =========================== */

/** Read the paddle's centre straight off the canvas. */
const paddleCentre = `(function(){
  var c = document.querySelector('#stage iframe').contentDocument.getElementById('board');
  var d = c.getContext('2d').getImageData(0, 340, 480, 1).data;
  var min = -1, max = -1;
  for (var x = 0; x < 480; x++) {
    var i = x * 4;
    if (d[i] > 240 && d[i+1] > 240 && d[i+2] > 240) { if (min < 0) min = x; max = x; }
  }
  return min < 0 ? null : (min + max) / 2;
})()`;

async function testBreakout() {
  console.log('\n=== BREAKOUT ===');
  const page = await openPage('#breakout');
  await page.waitForGame();
  await sleep(MIN_PLAY_MS);

  await page.gameKey(' ');           // start
  await sleep(200);
  check('paddle starts centred', Math.round(await page.evaluate(paddleCentre)), 240);

  // The iframe-relative mouse maths is the thing worth proving here.
  const expected = await page.evaluate(`(function(){
    var f = document.querySelector('#stage iframe');
    var w = f.contentWindow, c = f.contentDocument.getElementById('board');
    var box = c.getBoundingClientRect();
    c.dispatchEvent(new w.MouseEvent('mousemove', {
      clientX: box.left + box.width * 0.75,
      clientY: box.top + box.height / 2,
      bubbles: true
    }));
    return 480 * 0.75;
  })()`);
  await sleep(120);
  const actual = await page.evaluate(paddleCentre);
  checkTruthy('mouse moves the paddle to the right place (within 6px)', Math.abs(actual - expected) <= 6);

  await page.gameKey(' ');           // launch
  await page.gameKey('ArrowLeft');   // park the paddle left so the ball is lost

  await page.until("!document.getElementById('live-score').hidden && parseInt(document.getElementById('live-score-value').textContent.replace(/,/g,''),10) > 0",
    30000, 'ball breaks a brick and the live score updates');
  const live = await page.evaluate("parseInt(document.getElementById('live-score-value').textContent.replace(/,/g,''),10)");
  checkTruthy('live score above zero after a brick', live > 0);

  // Lose all three balls with the paddle parked in the corner. Each new ball
  // sits on the paddle until it's launched, so keep tapping SPACE - a press
  // while the ball is already in play is ignored by the game.
  const deadline = Date.now() + 120000;
  let ended = false;
  while (Date.now() < deadline) {
    if (await page.evaluate("!document.getElementById('initials-modal').hidden")) { ended = true; break; }
    await page.gameKey(' ');
    await sleep(400);
  }
  checkTruthy('all three balls lost, modal opened', ended);

  await enterInitials(page, 'BRK');
  await page.until("document.querySelector('#board-body tr td.who').textContent !== '---'", 8000, 'board updates');
  check('breakout score recorded under BRK', (await page.evaluate(topRow)).who, 'BRK');

  page.close();
}

/* ============================= MAZE ============================= */

/*
 * Find the player's cell by locating the white dot on the canvas.
 *
 * The game applies a move on keydown but only repaints on the next animation
 * frame, so this waits for a frame before reading - otherwise a successful
 * move reads as a wall and the solver desyncs from the real player.
 */
const playerCell = `new Promise(function(resolve){
  var f = document.querySelector('#stage iframe');
  f.contentWindow.requestAnimationFrame(function(){
    var c = f.contentDocument.getElementById('board');
    var SIZE = 13, CELL = Math.floor((420 - 20) / SIZE), PAD = Math.floor((420 - CELL * SIZE) / 2);
    var w = CELL * SIZE;
    var d = c.getContext('2d').getImageData(PAD, PAD, w, w).data;
    var sx = 0, sy = 0, n = 0;
    for (var i = 0; i < d.length; i += 4) {
      if (d[i] > 240 && d[i+1] > 240 && d[i+2] > 240) {
        var p = i / 4;
        sx += p % w; sy += Math.floor(p / w); n++;
      }
    }
    resolve(n ? { x: Math.floor((sx / n) / CELL), y: Math.floor((sy / n) / CELL) } : null);
  });
})`;

/** Read the player's cell, waiting for a repaint first. */
const readCell = (page) => page.evaluate(playerCell, true);

async function testMaze() {
  console.log('\n=== TIME ATTACK MAZE ===');
  const page = await openPage('#maze');
  await page.waitForGame();

  check('controls line mentions the clock', await page.evaluate(
    "document.getElementById('game-title').textContent"), 'Time Attack Maze (1981)');

  await sleep(MIN_PLAY_MS);
  await page.gameKey(' ');
  await sleep(200);

  const start = await readCell(page);
  check('player starts in the top-left cell', start, { x: 0, y: 0 });

  // Depth-first search, driven entirely through the keyboard. A move that
  // doesn't change the player's cell means a wall was in the way.
  const DIRS = [['ArrowRight', 1, 0], ['ArrowDown', 0, 1], ['ArrowLeft', -1, 0], ['ArrowUp', 0, -1]];
  const GOAL = 12;
  const visited = new Set(['0,0']);
  const path = [{ x: 0, y: 0 }];
  let steps = 0;

  while (true) {
    const cur = path[path.length - 1];
    if (cur.x === GOAL && cur.y === GOAL) break;
    if (++steps > 4000) throw new Error('maze solver gave up');

    let moved = false;
    for (const [key, dx, dy] of DIRS) {
      const nx = cur.x + dx, ny = cur.y + dy;
      if (nx < 0 || ny < 0 || nx > GOAL || ny > GOAL) continue;
      if (visited.has(nx + ',' + ny)) continue;

      await page.gameKey(key);
      const now = await readCell(page);
      if (now && now.x === nx && now.y === ny) {
        visited.add(nx + ',' + ny);
        path.push(now);
        moved = true;
        break;
      }
    }

    if (!moved) {
      path.pop();
      if (!path.length) throw new Error('maze had no route to the goal');
      const back = path[path.length - 1];
      const key = back.x > cur.x ? 'ArrowRight' : back.x < cur.x ? 'ArrowLeft'
                : back.y > cur.y ? 'ArrowDown' : 'ArrowUp';
      await page.gameKey(key);
      const now = await readCell(page);
      if (!now || now.x !== back.x || now.y !== back.y) throw new Error('backtrack failed');
    }
  }

  console.log(`  solved in ${steps} search steps, ${visited.size} cells visited`);
  check('reached the goal cell', await readCell(page), { x: 12, y: 12 });
  checkTruthy('escape overlay shown', (await page.evaluate(
    "document.querySelector('#stage iframe').contentDocument.querySelector('#overlay h1').textContent")).includes('ESCAPED'));

  await enterInitials(page, 'MZE');
  await page.until("document.querySelector('#board-body tr td.who').textContent !== '---'", 8000, 'board updates');

  const mine = await page.evaluate(topRow);
  check('maze time recorded under MZE', mine.who, 'MZE');
  const myTime = parseInt(mine.score.replace(/,/g, ''), 10);
  checkTruthy('time is above the 2s minimum the manifest sets', myTime >= 200);

  // scoring:"low" - a faster time must outrank the one just set.
  await fetch(BASE + '/api/scores.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ game: 'maze', score: Math.max(200, myTime - 100), initials: 'FST' })
  });
  await page.evaluate("location.reload(); true");
  await page.waitForGame();
  await page.until("document.querySelector('#board-body tr td.who').textContent !== '---'", 8000, 'board reloads');

  const best = await page.evaluate(topRow);
  check('faster time takes rank 1', best.who, 'FST');
  checkTruthy('rank 1 is lower than rank 2 for a low-scoring game', await page.evaluate(`(function(){
    var rows = document.querySelectorAll('#board-body tr');
    var a = parseInt(rows[0].querySelector('td.score').textContent.replace(/,/g,''), 10);
    var b = parseInt(rows[1].querySelector('td.score').textContent.replace(/,/g,''), 10);
    return a < b;
  })()`));

  page.close();
}

/* ============================== run ============================== */

await testSnake();
await testBreakout();
await testMaze();
process.exit(report() === 0 ? 0 : 1);
