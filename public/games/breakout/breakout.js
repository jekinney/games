/* Breakout — requestAnimationFrame loop, float positions, 3 balls. */
(function () {
  'use strict';

  var W = 480, H = 360;
  var PADDLE_W = 64, PADDLE_H = 8, PADDLE_Y = H - 24;
  var BALL_R = 4, BALL_SPEED = 3.4, MAX_SPEED = 6.5;
  var ROWS = 6, COLS = 12, BRICK_H = 14, BRICK_TOP = 40, BRICK_GAP = 2;
  var ROW_POINTS = [70, 70, 50, 50, 30, 30];
  var ROW_COLORS = ['#e33', '#e33', '#e83', '#e83', '#dd3', '#3c3'];
  var START_LIVES = 3;

  var canvas = document.getElementById('board');
  var ctx = canvas.getContext('2d');
  var overlay = document.getElementById('overlay');
  var recordEl = document.getElementById('record');

  var paddleX, ball, bricks, score, lives, phase, frame, keys;
  phase = 'title';   // title | serving | playing | dead | submitted
  keys = {};

  /* ---------- setup ---------- */

  function reset() {
    score = 0;
    lives = START_LIVES;
    paddleX = (W - PADDLE_W) / 2;
    buildBricks();
    serve();
    Arcade.setScore(0);
  }

  function buildBricks() {
    var brickW = (W - BRICK_GAP) / COLS - BRICK_GAP;
    bricks = [];
    for (var row = 0; row < ROWS; row++) {
      for (var col = 0; col < COLS; col++) {
        bricks.push({
          x: BRICK_GAP + col * (brickW + BRICK_GAP),
          y: BRICK_TOP + row * (BRICK_H + BRICK_GAP),
          w: brickW,
          h: BRICK_H,
          points: ROW_POINTS[row],
          color: ROW_COLORS[row],
          alive: true
        });
      }
    }
  }

  /** Park the ball on the paddle until the player launches it. */
  function serve() {
    phase = 'serving';
    ball = {
      x: paddleX + PADDLE_W / 2,
      y: PADDLE_Y - BALL_R - 1,
      dx: 0,
      dy: 0
    };
  }

  function launch() {
    if (phase !== 'serving') return;
    phase = 'playing';
    // Always upward, angled slightly one way or the other.
    var angle = (Math.random() * 0.6 - 0.3) + (-Math.PI / 2);
    ball.dx = Math.cos(angle) * BALL_SPEED;
    ball.dy = Math.sin(angle) * BALL_SPEED;
  }

  /* ---------- loop ---------- */

  function start() {
    reset();
    overlay.hidden = true;
    if (!frame) frame = requestAnimationFrame(tick);
  }

  function tick() {
    frame = requestAnimationFrame(tick);
    update();
    draw();
  }

  function update() {
    if (phase === 'title' || phase === 'dead' || phase === 'submitted') return;

    // Keyboard paddle movement; the mouse sets paddleX directly.
    if (keys.left) paddleX -= 6;
    if (keys.right) paddleX += 6;
    paddleX = clamp(paddleX, 0, W - PADDLE_W);

    if (phase === 'serving') {
      ball.x = paddleX + PADDLE_W / 2;
      ball.y = PADDLE_Y - BALL_R - 1;
      return;
    }

    ball.x += ball.dx;
    ball.y += ball.dy;

    // Walls
    if (ball.x - BALL_R < 0) { ball.x = BALL_R; ball.dx = Math.abs(ball.dx); }
    if (ball.x + BALL_R > W) { ball.x = W - BALL_R; ball.dx = -Math.abs(ball.dx); }
    if (ball.y - BALL_R < 0) { ball.y = BALL_R; ball.dy = Math.abs(ball.dy); }

    // Paddle: where it hits decides the angle, so the player has real control.
    if (ball.dy > 0 &&
        ball.y + BALL_R >= PADDLE_Y &&
        ball.y - BALL_R <= PADDLE_Y + PADDLE_H &&
        ball.x >= paddleX && ball.x <= paddleX + PADDLE_W) {
      var hit = (ball.x - (paddleX + PADDLE_W / 2)) / (PADDLE_W / 2); // -1..1
      var speed = Math.min(MAX_SPEED, Math.hypot(ball.dx, ball.dy) * 1.02);
      var bounce = hit * (Math.PI / 3);                                // up to 60°
      ball.dx = Math.sin(bounce) * speed;
      ball.dy = -Math.cos(bounce) * speed;
      ball.y = PADDLE_Y - BALL_R - 1;
    }

    hitBricks();

    // Lost the ball
    if (ball.y - BALL_R > H) {
      lives -= 1;
      if (lives <= 0) return die();
      serve();
    }
  }

  function hitBricks() {
    for (var i = 0; i < bricks.length; i++) {
      var b = bricks[i];
      if (!b.alive) continue;
      if (ball.x + BALL_R < b.x || ball.x - BALL_R > b.x + b.w) continue;
      if (ball.y + BALL_R < b.y || ball.y - BALL_R > b.y + b.h) continue;

      b.alive = false;
      score += b.points;
      Arcade.setScore(score);

      // Bounce off whichever side the ball came through.
      var fromSide = Math.abs(ball.x - (b.x + b.w / 2)) / b.w >
                     Math.abs(ball.y - (b.y + b.h / 2)) / b.h;
      if (fromSide) ball.dx = -ball.dx;
      else ball.dy = -ball.dy;

      if (!bricks.some(function (brick) { return brick.alive; })) return clear();
      return;   // one brick per frame keeps the bounce readable
    }
  }

  function clear() {
    phase = 'dead';
    showOverlay('CLEARED!', score, 'submitting…');
    submit();
  }

  function die() {
    phase = 'dead';
    showOverlay('GAME OVER', score, 'submitting…');
    submit();
  }

  function submit() {
    var heading = bricks.some(function (b) { return b.alive; }) ? 'GAME OVER' : 'CLEARED!';
    Arcade.submitScore(score).then(function (result) {
      phase = 'submitted';
      var line = !result ? 'score not saved'
        : result.accepted ? 'YOU PLACED #' + result.rank + '!'
        : 'no top-ten spot this time';
      showOverlay(heading, score, line);
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
    ctx.fillRect(0, 0, W, H);

    bricks.forEach(function (b) {
      if (!b.alive) return;
      ctx.fillStyle = b.color;
      ctx.fillRect(b.x, b.y, b.w, b.h);
    });

    ctx.fillStyle = '#fff';
    ctx.fillRect(paddleX, PADDLE_Y, PADDLE_W, PADDLE_H);

    if (phase === 'playing' || phase === 'serving') {
      ctx.beginPath();
      ctx.arc(ball.x, ball.y, BALL_R, 0, Math.PI * 2);
      ctx.fill();
    }

    ctx.font = '12px "Courier New", monospace';
    ctx.fillStyle = '#888';
    ctx.textAlign = 'left';
    ctx.fillText('SCORE ' + score, 8, 20);
    ctx.textAlign = 'right';
    ctx.fillText('BALLS ' + Math.max(lives, 0), W - 8, 20);
  }

  function clamp(value, min, max) {
    return value < min ? min : value > max ? max : value;
  }

  /* ---------- input ---------- */

  window.addEventListener('keydown', function (event) {
    var key = event.key;

    if (key === 'ArrowLeft' || key === 'a' || key === 'A') { keys.left = true; event.preventDefault(); }
    if (key === 'ArrowRight' || key === 'd' || key === 'D') { keys.right = true; event.preventDefault(); }

    if (key === ' ' || key === 'Enter') {
      event.preventDefault();
      if (phase === 'title' || phase === 'submitted') start();
      else if (phase === 'serving') launch();
    }
  });

  window.addEventListener('keyup', function (event) {
    var key = event.key;
    if (key === 'ArrowLeft' || key === 'a' || key === 'A') keys.left = false;
    if (key === 'ArrowRight' || key === 'd' || key === 'D') keys.right = false;
  });

  // Mouse control. clientX is relative to this frame, and the canvas is
  // CSS-scaled, so map through the bounding box rather than assuming 1:1.
  canvas.addEventListener('mousemove', function (event) {
    if (phase === 'title' || phase === 'dead' || phase === 'submitted') return;
    var box = canvas.getBoundingClientRect();
    var scale = W / box.width;
    paddleX = clamp((event.clientX - box.left) * scale - PADDLE_W / 2, 0, W - PADDLE_W);
  });

  canvas.addEventListener('mousedown', function (event) {
    event.preventDefault();
    if (phase === 'title' || phase === 'submitted') start();
    else if (phase === 'serving') launch();
  });

  document.addEventListener('visibilitychange', function () {
    // Losing the ball while the tab is hidden would be unfair; park it.
    if (document.hidden && phase === 'playing') {
      phase = 'serving';
      serve();
    }
  });

  /* ---------- boot ---------- */

  score = 0;
  lives = START_LIVES;
  paddleX = (W - PADDLE_W) / 2;
  buildBricks();
  ball = { x: W / 2, y: PADDLE_Y - BALL_R - 1, dx: 0, dy: 0 };
  draw();

  Arcade.getHighScores().then(function (scores) {
    recordEl.textContent = scores.length
      ? 'RECORD  ' + scores[0].initials + '  ' + scores[0].score
      : 'NO SCORES YET';
  });

  Arcade.ready();
})();
