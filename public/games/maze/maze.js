/*
 * Time Attack Maze — a fresh maze each run, scored in centiseconds.
 *
 * This game is "scoring": "low" in game.json: 1240 (12.40s) beats 3000 (30.00s).
 */
(function () {
  'use strict';

  var SIZE = 13;                 // cells per side
  var CANVAS = 420;
  var CELL = Math.floor((CANVAS - 20) / SIZE);
  var PAD = Math.floor((CANVAS - CELL * SIZE) / 2);

  var canvas = document.getElementById('board');
  var ctx = canvas.getContext('2d');
  var overlay = document.getElementById('overlay');
  var recordEl = document.getElementById('record');

  var grid, player, startedAt, elapsedCs, phase, frame, trail;
  phase = 'title';   // title | playing | done | submitted

  /* ---------- maze generation (recursive backtracker) ---------- */

  function generate() {
    grid = [];
    for (var y = 0; y < SIZE; y++) {
      grid[y] = [];
      for (var x = 0; x < SIZE; x++) {
        // Walls are per-cell: north/east/south/west, all closed to begin with.
        grid[y][x] = { n: true, e: true, s: true, w: true, seen: false };
      }
    }

    var stack = [{ x: 0, y: 0 }];
    grid[0][0].seen = true;

    while (stack.length) {
      var cell = stack[stack.length - 1];
      var options = neighbours(cell.x, cell.y).filter(function (n) {
        return !grid[n.y][n.x].seen;
      });

      if (!options.length) {
        stack.pop();
        continue;
      }

      var next = options[Math.floor(Math.random() * options.length)];
      knockDown(cell, next);
      grid[next.y][next.x].seen = true;
      stack.push({ x: next.x, y: next.y });
    }
  }

  function neighbours(x, y) {
    var out = [];
    if (y > 0) out.push({ x: x, y: y - 1, dir: 'n' });
    if (x < SIZE - 1) out.push({ x: x + 1, y: y, dir: 'e' });
    if (y < SIZE - 1) out.push({ x: x, y: y + 1, dir: 's' });
    if (x > 0) out.push({ x: x - 1, y: y, dir: 'w' });
    return out;
  }

  var OPPOSITE = { n: 's', s: 'n', e: 'w', w: 'e' };

  function knockDown(from, to) {
    grid[from.y][from.x][to.dir] = false;
    grid[to.y][to.x][OPPOSITE[to.dir]] = false;
  }

  /* ---------- game flow ---------- */

  function start() {
    generate();
    player = { x: 0, y: 0 };
    trail = [{ x: 0, y: 0 }];
    elapsedCs = 0;
    startedAt = performance.now();
    phase = 'playing';
    overlay.hidden = true;
    if (!frame) frame = requestAnimationFrame(tick);
  }

  function tick() {
    frame = requestAnimationFrame(tick);
    if (phase === 'playing') {
      elapsedCs = Math.floor((performance.now() - startedAt) / 10);
      Arcade.setScore(elapsedCs);
    }
    draw();
  }

  function finish() {
    phase = 'done';
    elapsedCs = Math.floor((performance.now() - startedAt) / 10);
    draw();
    showOverlay('ESCAPED!', formatTime(elapsedCs), 'submitting…');

    Arcade.submitScore(elapsedCs).then(function (result) {
      phase = 'submitted';
      var line = !result ? 'time not saved'
        : result.accepted ? 'YOU PLACED #' + result.rank + '!'
        : 'not fast enough for the top ten';
      showOverlay('ESCAPED!', formatTime(elapsedCs), line);
    });
  }

  function formatTime(centiseconds) {
    var seconds = Math.floor(centiseconds / 100);
    var cs = centiseconds % 100;
    return seconds + '.' + (cs < 10 ? '0' + cs : cs) + 's';
  }

  function showOverlay(heading, time, note) {
    overlay.innerHTML = '';

    var h = document.createElement('h1');
    h.textContent = heading;
    overlay.appendChild(h);

    var t = document.createElement('p');
    t.className = 'big';
    t.textContent = time;
    overlay.appendChild(t);

    var n = document.createElement('p');
    n.className = 'dim';
    n.textContent = note;
    overlay.appendChild(n);

    if (phase === 'submitted') {
      var again = document.createElement('p');
      again.className = 'blink';
      again.textContent = 'PRESS SPACE FOR A NEW MAZE';
      overlay.appendChild(again);
    }

    overlay.hidden = false;
  }

  /* ---------- drawing ---------- */

  function draw() {
    ctx.fillStyle = '#000';
    ctx.fillRect(0, 0, CANVAS, CANVAS);

    if (!grid) return;

    // Goal square
    ctx.fillStyle = '#0a4';
    ctx.fillRect(PAD + (SIZE - 1) * CELL + 3, PAD + (SIZE - 1) * CELL + 3, CELL - 6, CELL - 6);

    // Where you've been, so backtracking is visible
    ctx.fillStyle = '#123';
    trail.forEach(function (cell) {
      ctx.fillRect(PAD + cell.x * CELL + 3, PAD + cell.y * CELL + 3, CELL - 6, CELL - 6);
    });

    // Walls
    ctx.strokeStyle = '#6cf';
    ctx.lineWidth = 2;
    ctx.beginPath();
    for (var y = 0; y < SIZE; y++) {
      for (var x = 0; x < SIZE; x++) {
        var cell = grid[y][x];
        var left = PAD + x * CELL;
        var top = PAD + y * CELL;
        if (cell.n) { ctx.moveTo(left, top); ctx.lineTo(left + CELL, top); }
        if (cell.w) { ctx.moveTo(left, top); ctx.lineTo(left, top + CELL); }
        if (cell.e) { ctx.moveTo(left + CELL, top); ctx.lineTo(left + CELL, top + CELL); }
        if (cell.s) { ctx.moveTo(left, top + CELL); ctx.lineTo(left + CELL, top + CELL); }
      }
    }
    ctx.stroke();

    // Player
    ctx.fillStyle = '#fff';
    ctx.beginPath();
    ctx.arc(PAD + player.x * CELL + CELL / 2, PAD + player.y * CELL + CELL / 2, CELL / 3.2, 0, Math.PI * 2);
    ctx.fill();

    // Clock
    ctx.font = 'bold 15px "Courier New", monospace';
    ctx.fillStyle = '#fff';
    ctx.textAlign = 'center';
    ctx.fillText(formatTime(elapsedCs || 0), CANVAS / 2, PAD - 4);
  }

  /* ---------- input ---------- */

  var MOVES = {
    ArrowUp: 'n', w: 'n',
    ArrowRight: 'e', d: 'e',
    ArrowDown: 's', s: 's',
    ArrowLeft: 'w', a: 'w'
  };

  var STEP = { n: { x: 0, y: -1 }, s: { x: 0, y: 1 }, e: { x: 1, y: 0 }, w: { x: -1, y: 0 } };

  window.addEventListener('keydown', function (event) {
    var key = event.key.length === 1 ? event.key.toLowerCase() : event.key;

    if (key === ' ' || key === 'Enter') {
      event.preventDefault();
      if (phase === 'title' || phase === 'submitted') start();
      return;
    }

    var dir = MOVES[key];
    if (!dir) return;
    event.preventDefault();
    if (phase !== 'playing') return;

    move(dir);
  });

  function move(dir) {
    if (grid[player.y][player.x][dir]) return;   // wall in the way

    player = { x: player.x + STEP[dir].x, y: player.y + STEP[dir].y };

    var visited = trail.some(function (c) { return c.x === player.x && c.y === player.y; });
    if (!visited) trail.push({ x: player.x, y: player.y });

    if (player.x === SIZE - 1 && player.y === SIZE - 1) finish();
  }

  canvas.addEventListener('mousedown', function () {
    if (phase === 'title' || phase === 'submitted') start();
  });

  // A hidden tab shouldn't quietly run up the clock.
  document.addEventListener('visibilitychange', function () {
    if (!document.hidden || phase !== 'playing') return;
    phase = 'done';
    showOverlay('PAUSED', formatTime(elapsedCs), 'run abandoned — press SPACE for a new maze');
    phase = 'submitted';
  });

  /* ---------- boot ---------- */

  generate();
  player = { x: 0, y: 0 };
  trail = [{ x: 0, y: 0 }];
  elapsedCs = 0;
  draw();

  Arcade.getHighScores().then(function (scores) {
    recordEl.textContent = scores.length
      ? 'RECORD  ' + scores[0].initials + '  ' + formatTime(scores[0].score)
      : 'NO TIMES YET';
  });

  Arcade.ready();
})();
