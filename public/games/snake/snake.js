/* Snake — 20x20 grid, fixed timestep that speeds up as you grow. */
(function () {
  'use strict';

  var COLS = 20, ROWS = 20, CELL = 20;
  var START_MS = 140, MIN_MS = 60, SPEEDUP_MS = 4;

  var canvas = document.getElementById('board');
  var ctx = canvas.getContext('2d');
  var overlay = document.getElementById('overlay');
  var recordEl = document.getElementById('record');

  var snake, dir, queued, food, score, tickMs, timer;
  var phase = 'title';   // title | playing | paused | dead | submitted

  /* ---------- setup ---------- */

  function reset() {
    snake = [{ x: 10, y: 10 }, { x: 9, y: 10 }, { x: 8, y: 10 }];
    dir = { x: 1, y: 0 };
    queued = [];
    score = 0;
    tickMs = START_MS;
    placeFood();
    Arcade.setScore(0);
  }

  function placeFood() {
    var open = [];
    for (var y = 0; y < ROWS; y++) {
      for (var x = 0; x < COLS; x++) {
        if (!occupied(x, y)) open.push({ x: x, y: y });
      }
    }
    food = open.length ? open[Math.floor(Math.random() * open.length)] : null;
  }

  function occupied(x, y) {
    return snake.some(function (part) { return part.x === x && part.y === y; });
  }

  /* ---------- loop ---------- */

  function start() {
    reset();
    phase = 'playing';
    overlay.hidden = true;
    schedule();
    draw();
  }

  function schedule() {
    clearTimeout(timer);
    if (phase !== 'playing') return;
    timer = setTimeout(step, tickMs);
  }

  function step() {
    if (queued.length) dir = queued.shift();

    var head = { x: snake[0].x + dir.x, y: snake[0].y + dir.y };

    // Walls kill. No wrapping — this is the 1976 rule.
    if (head.x < 0 || head.y < 0 || head.x >= COLS || head.y >= ROWS) return die();

    // Biting yourself kills, except the tail tip which is about to move away.
    for (var i = 0; i < snake.length - 1; i++) {
      if (snake[i].x === head.x && snake[i].y === head.y) return die();
    }

    snake.unshift(head);

    if (food && head.x === food.x && head.y === food.y) {
      score += 10;
      Arcade.setScore(score);
      tickMs = Math.max(MIN_MS, tickMs - SPEEDUP_MS);
      placeFood();
      if (!food) return win();
    } else {
      snake.pop();
    }

    draw();
    schedule();
  }

  function die() {
    phase = 'dead';
    clearTimeout(timer);
    draw();
    showOverlay('GAME OVER', score, 'submitting…');
    submit();
  }

  function win() {
    phase = 'dead';
    clearTimeout(timer);
    draw();
    showOverlay('PERFECT!', score, 'submitting…');
    submit();
  }

  function submit() {
    Arcade.submitScore(score).then(function (result) {
      phase = 'submitted';
      var line = !result ? 'score not saved'
        : result.accepted ? 'YOU PLACED #' + result.rank + '!'
        : 'no top-ten spot this time';
      showOverlay(snake.length === COLS * ROWS ? 'PERFECT!' : 'GAME OVER', score, line);
    });
  }

  function showOverlay(heading, points, note) {
    overlay.innerHTML = '';

    var h = document.createElement('h1');
    h.textContent = heading;
    overlay.appendChild(h);

    var s = document.createElement('p');
    s.className = 'big';
    s.textContent = String(points);
    overlay.appendChild(s);

    var n = document.createElement('p');
    n.className = 'dim';
    n.textContent = note;
    overlay.appendChild(n);

    if (phase === 'submitted') {
      var again = document.createElement('p');
      again.className = 'blink';
      again.textContent = 'PRESS SPACE TO PLAY AGAIN';
      overlay.appendChild(again);
    }

    overlay.hidden = false;
  }

  /* ---------- drawing ---------- */

  function draw() {
    ctx.fillStyle = '#000';
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    // Faint grid so the play field reads as a grid, like the cabinet did.
    ctx.strokeStyle = '#0a1a0a';
    ctx.lineWidth = 1;
    for (var i = 1; i < COLS; i++) {
      ctx.beginPath();
      ctx.moveTo(i * CELL + .5, 0);
      ctx.lineTo(i * CELL + .5, canvas.height);
      ctx.moveTo(0, i * CELL + .5);
      ctx.lineTo(canvas.width, i * CELL + .5);
      ctx.stroke();
    }

    if (food) {
      ctx.fillStyle = '#e33';
      ctx.fillRect(food.x * CELL + 4, food.y * CELL + 4, CELL - 8, CELL - 8);
    }

    snake.forEach(function (part, index) {
      ctx.fillStyle = index === 0 ? '#cfc' : '#0c0';
      ctx.fillRect(part.x * CELL + 1, part.y * CELL + 1, CELL - 2, CELL - 2);
    });
  }

  /* ---------- input ---------- */

  var DIRECTIONS = {
    ArrowUp: { x: 0, y: -1 }, w: { x: 0, y: -1 },
    ArrowDown: { x: 0, y: 1 }, s: { x: 0, y: 1 },
    ArrowLeft: { x: -1, y: 0 }, a: { x: -1, y: 0 },
    ArrowRight: { x: 1, y: 0 }, d: { x: 1, y: 0 }
  };

  window.addEventListener('keydown', function (event) {
    var key = event.key.length === 1 ? event.key.toLowerCase() : event.key;
    var next = DIRECTIONS[key];

    if (next) {
      event.preventDefault();   // stop the arrow keys scrolling the page
      turn(next);
      return;
    }

    if (key === ' ' || key === 'Enter') {
      event.preventDefault();
      if (phase === 'title' || phase === 'submitted') start();
      return;
    }

    if (key === 'p' && (phase === 'playing' || phase === 'paused')) {
      event.preventDefault();
      togglePause();
    }
  });

  // Clicking the canvas starts a game too — not everyone reads the overlay.
  canvas.addEventListener('mousedown', function () {
    if (phase === 'title' || phase === 'submitted') start();
    else if (phase === 'paused') togglePause();
  });

  function turn(next) {
    // Compare against the last queued turn, so two fast presses can't reverse
    // the snake into itself.
    var last = queued.length ? queued[queued.length - 1] : dir;
    if (next.x === -last.x && next.y === -last.y) return;
    if (next.x === last.x && next.y === last.y) return;
    if (queued.length < 2) queued.push(next);
  }

  function togglePause() {
    if (phase === 'playing') {
      phase = 'paused';
      clearTimeout(timer);
      showOverlay('PAUSED', score, 'press P to resume');
    } else if (phase === 'paused') {
      phase = 'playing';
      overlay.hidden = true;
      schedule();
    }
  }

  // Don't burn CPU in a background tab.
  document.addEventListener('visibilitychange', function () {
    if (document.hidden && phase === 'playing') togglePause();
  });

  /* ---------- boot ---------- */

  snake = [];
  food = null;
  score = 0;
  draw();

  Arcade.getHighScores().then(function (scores) {
    recordEl.textContent = scores.length
      ? 'RECORD  ' + scores[0].initials + '  ' + scores[0].score
      : 'NO SCORES YET';
  });

  Arcade.ready();
})();
