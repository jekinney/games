/* Asteroids — vector graphics, requestAnimationFrame, wrap-around field. */
(function () {
  'use strict';

  var W = 480, H = 360;

  var SHIP_R       = 10;
  var ROTATE_SPD   = Math.PI * 1.8;   // rad/s
  var THRUST_ACCEL = 210;             // px/s²
  var MAX_SPEED    = 320;             // px/s
  var DRAG         = 0.986;           // applied per-tick: pow(DRAG, dt*60)
  var BULLET_SPD   = 440;             // px/s
  var BULLET_LIFE  = 0.68;            // seconds
  var MAX_BULLETS  = 5;
  var FIRE_COOLDOWN = 0.22;           // seconds between shots
  var INVINCIBLE_S = 2.5;
  var START_LIVES  = 3;

  // size 0=large 1=medium 2=small
  var SIZES  = [34, 19, 10];
  var POINTS = [20, 50, 100];
  var SPEEDS = [52, 88, 135];

  var canvas = document.getElementById('board');
  var ctx    = canvas.getContext('2d');
  var overlay   = document.getElementById('overlay');
  var recordEl  = document.getElementById('record');
  var ovSub     = document.getElementById('ov-sub');
  var ovHint    = document.getElementById('ov-hint');

  var ship, bullets, rocks, sparks;
  var score, lives, level, phase, lastTs, invTimer, fireCd;
  phase = 'title';
  rocks = [];

  /* ---- math helpers ---- */

  function wrap(v, max) { return ((v % max) + max) % max; }
  function d2(ax, ay, bx, by) { var dx = ax - bx, dy = ay - by; return dx*dx + dy*dy; }

  /* ---- asteroid shape ---- */

  function makeVerts(r) {
    var n = 9 + Math.floor(Math.random() * 5);   // 9–13 sides
    var v = [];
    for (var i = 0; i < n; i++) {
      var a = (i / n) * Math.PI * 2;
      var len = r * (0.6 + Math.random() * 0.55);
      v.push([Math.cos(a) * len, Math.sin(a) * len]);
    }
    return v;
  }

  function spawnRock(size, x, y) {
    var r   = SIZES[size];
    var spd = SPEEDS[size] * (0.65 + Math.random() * 0.7);
    var dir = Math.random() * Math.PI * 2;
    return {
      x: x != null ? x : Math.random() * W,
      y: y != null ? y : Math.random() * H,
      vx: Math.cos(dir) * spd, vy: Math.sin(dir) * spd,
      size: size, r: r,
      angle: 0, spin: (Math.random() - 0.5) * 2.6,
      verts: makeVerts(r),
    };
  }

  function spawnWave(n) {
    var list = [];
    for (var i = 0; i < n; i++) {
      var x, y;
      do { x = Math.random() * W; y = Math.random() * H; }
      while (d2(x, y, W/2, H/2) < 120*120);
      list.push(spawnRock(0, x, y));
    }
    return list;
  }

  /* ---- sparks ---- */

  function explode(x, y, n) {
    for (var i = 0; i < n; i++) {
      var a = Math.random() * Math.PI * 2;
      var s = 50 + Math.random() * 110;
      sparks.push({ x: x, y: y, vx: Math.cos(a)*s, vy: Math.sin(a)*s,
                    life: 0.35 + Math.random() * 0.45 });
    }
  }

  /* ---- game state ---- */

  function newShip() {
    return { x: W/2, y: H/2, vx: 0, vy: 0, angle: -Math.PI/2, thrusting: false };
  }

  function startGame() {
    score = 0; lives = START_LIVES; level = 1;
    bullets = []; sparks = [];
    ship = newShip(); invTimer = INVINCIBLE_S; fireCd = 0;
    rocks = spawnWave(4);
    phase = 'playing';
    overlay.hidden = true;
    Arcade.setScore(0);
  }

  function loseLife() {
    explode(ship.x, ship.y, 14);
    lives--;
    if (lives <= 0) {
      phase = 'over';
      overlay.hidden = false;
      ovSub.textContent  = 'SCORE ' + score;
      ovHint.textContent = 'SPACE to submit';
      ovHint.classList.add('blink');
    } else {
      ship = newShip();
      bullets = [];
      invTimer = INVINCIBLE_S;
    }
  }

  /* ---- input ---- */

  var keys = {};

  document.addEventListener('keydown', function (e) {
    keys[e.code] = true;
    if (e.code === 'Space') e.preventDefault();

    if (phase === 'title' && (e.code === 'Space' || e.code === 'Enter')) {
      startGame();
    } else if (phase === 'over' && e.code === 'Space') {
      submitGameOver();
    } else if (phase === 'done' && e.code === 'Space') {
      ovSub.textContent = '\u00a0';
      ovHint.textContent = 'PRESS SPACE TO START';
      phase = 'title';
    }
  });

  document.addEventListener('keyup', function (e) { keys[e.code] = false; });

  overlay.addEventListener('click', function () {
    if (phase === 'title') { startGame(); }
    else if (phase === 'over') { submitGameOver(); }
    else if (phase === 'done') { phase = 'title'; ovSub.textContent = '\u00a0'; ovHint.textContent = 'PRESS SPACE TO START'; }
  });

  function submitGameOver() {
    if (phase !== 'over') return;
    phase = 'submitted';
    ovHint.textContent = '';
    Arcade.submitScore(score).then(function (r) {
      if (!r) return;
      ovSub.textContent = r.accepted ? 'RANK ' + r.rank + '!' : 'NOT ON THE BOARD';
      ovHint.textContent = 'SPACE or click to play again';
      ovHint.classList.add('blink');
      phase = 'done';
    });
  }

  function tryFire() {
    if (fireCd > 0 || bullets.length >= MAX_BULLETS) return;
    fireCd = FIRE_COOLDOWN;
    bullets.push({
      x:  ship.x + Math.cos(ship.angle) * (SHIP_R + 3),
      y:  ship.y + Math.sin(ship.angle) * (SHIP_R + 3),
      vx: ship.vx + Math.cos(ship.angle) * BULLET_SPD,
      vy: ship.vy + Math.sin(ship.angle) * BULLET_SPD,
      life: BULLET_LIFE,
    });
  }

  /* ---- update ---- */

  function update(dt) {
    if (fireCd > 0) fireCd -= dt;
    updateSparks(dt);

    if (phase !== 'playing') return;

    // Rotate
    var rot = (keys['ArrowLeft']  || keys['KeyA'] ? -1 : 0)
            + (keys['ArrowRight'] || keys['KeyD'] ?  1 : 0);
    ship.angle += rot * ROTATE_SPD * dt;

    // Thrust
    ship.thrusting = !!(keys['ArrowUp'] || keys['KeyW']);
    if (ship.thrusting) {
      ship.vx += Math.cos(ship.angle) * THRUST_ACCEL * dt;
      ship.vy += Math.sin(ship.angle) * THRUST_ACCEL * dt;
      var spd = Math.hypot(ship.vx, ship.vy);
      if (spd > MAX_SPEED) { ship.vx = ship.vx/spd*MAX_SPEED; ship.vy = ship.vy/spd*MAX_SPEED; }
    }

    // Drag
    var drag = Math.pow(DRAG, dt * 60);
    ship.vx *= drag; ship.vy *= drag;

    ship.x = wrap(ship.x + ship.vx * dt, W);
    ship.y = wrap(ship.y + ship.vy * dt, H);

    if (invTimer > 0) invTimer -= dt;

    if (keys['Space']) tryFire();

    // Bullets
    bullets = bullets.filter(function (b) {
      b.x = wrap(b.x + b.vx * dt, W);
      b.y = wrap(b.y + b.vy * dt, H);
      b.life -= dt;
      return b.life > 0;
    });

    // Rocks
    rocks.forEach(function (a) {
      a.x    = wrap(a.x + a.vx * dt, W);
      a.y    = wrap(a.y + a.vy * dt, H);
      a.angle += a.spin * dt;
    });

    // Bullet × rock collisions
    var hitBullets = new Set();
    var newRocks   = [];

    rocks = rocks.filter(function (a) {
      for (var i = 0; i < bullets.length; i++) {
        if (hitBullets.has(i)) continue;
        var b = bullets[i];
        if (d2(b.x, b.y, a.x, a.y) < (a.r + 3) * (a.r + 3)) {
          hitBullets.add(i);
          score += POINTS[a.size];
          Arcade.setScore(score);
          explode(a.x, a.y, a.size === 0 ? 9 : a.size === 1 ? 6 : 3);
          if (a.size < 2) {
            newRocks.push(spawnRock(a.size + 1, a.x, a.y));
            newRocks.push(spawnRock(a.size + 1, a.x, a.y));
          }
          return false;
        }
      }
      return true;
    });

    bullets = bullets.filter(function (_, i) { return !hitBullets.has(i); });
    rocks   = rocks.concat(newRocks);

    // Ship × rock collision
    if (invTimer <= 0) {
      for (var i = 0; i < rocks.length; i++) {
        var a = rocks[i];
        if (d2(ship.x, ship.y, a.x, a.y) < (SHIP_R + a.r * 0.72) * (SHIP_R + a.r * 0.72)) {
          loseLife();
          return;
        }
      }
    }

    // Wave cleared
    if (rocks.length === 0) {
      phase = 'clear';
      setTimeout(function () {
        level++;
        bullets = []; sparks = [];
        ship = newShip(); invTimer = INVINCIBLE_S;
        rocks = spawnWave(3 + level);
        phase = 'playing';
      }, 1400);
    }
  }

  function updateSparks(dt) {
    sparks = sparks.filter(function (s) {
      s.x = wrap(s.x + s.vx * dt, W);
      s.y = wrap(s.y + s.vy * dt, H);
      s.life -= dt;
      return s.life > 0;
    });
  }

  /* ---- draw ---- */

  function drawRock(a) {
    ctx.save();
    ctx.translate(a.x, a.y);
    ctx.rotate(a.angle);
    ctx.beginPath();
    ctx.moveTo(a.verts[0][0], a.verts[0][1]);
    for (var i = 1; i < a.verts.length; i++) ctx.lineTo(a.verts[i][0], a.verts[i][1]);
    ctx.closePath();
    ctx.stroke();
    ctx.restore();
  }

  function drawShip() {
    ctx.save();
    ctx.translate(ship.x, ship.y);
    ctx.rotate(ship.angle);
    ctx.strokeStyle = '#fff';
    ctx.lineWidth = 1.5;
    ctx.beginPath();
    ctx.moveTo(SHIP_R * 2, 0);
    ctx.lineTo(-SHIP_R, SHIP_R * 1.15);
    ctx.lineTo(-SHIP_R * 0.45, 0);      // tail notch
    ctx.lineTo(-SHIP_R, -SHIP_R * 1.15);
    ctx.closePath();
    ctx.stroke();
    if (ship.thrusting && Math.random() > 0.35) {
      ctx.strokeStyle = '#f80';
      ctx.lineWidth = 1.5;
      ctx.beginPath();
      ctx.moveTo(-SHIP_R, SHIP_R * 0.55);
      ctx.lineTo(-SHIP_R * 2 - Math.random() * SHIP_R * 1.2, 0);
      ctx.lineTo(-SHIP_R, -SHIP_R * 0.55);
      ctx.stroke();
    }
    ctx.restore();
  }

  function draw() {
    ctx.fillStyle = '#000';
    ctx.fillRect(0, 0, W, H);

    // Rocks
    ctx.strokeStyle = '#fff';
    ctx.lineWidth = 1.5;
    rocks.forEach(drawRock);

    // Bullets
    ctx.fillStyle = '#fff';
    bullets.forEach(function (b) {
      ctx.beginPath();
      ctx.arc(b.x, b.y, 2.5, 0, Math.PI * 2);
      ctx.fill();
    });

    // Sparks
    sparks.forEach(function (s) {
      ctx.globalAlpha = Math.min(1, s.life * 2.5);
      ctx.fillStyle = s.life > 0.3 ? '#fc6' : '#f40';
      ctx.fillRect(s.x - 1.5, s.y - 1.5, 3, 3);
    });
    ctx.globalAlpha = 1;

    // Ship — blink while invincible
    if (phase === 'playing' || phase === 'clear') {
      if (invTimer <= 0 || Math.floor(invTimer * 9) % 2 === 0) drawShip();
    }

    // HUD
    ctx.fillStyle = '#0f0';
    ctx.font = '13px "Courier New"';
    ctx.textAlign = 'left';
    ctx.fillText(String(score).padStart(6, '0'), 8, 18);
    ctx.textAlign = 'right';
    ctx.fillText('LV ' + level, W - 8, 18);

    // Lives as mini ships
    for (var i = 0; i < lives; i++) {
      ctx.save();
      ctx.translate(12 + i * 18, H - 14);
      ctx.rotate(-Math.PI / 2);
      ctx.strokeStyle = '#0f0';
      ctx.lineWidth = 1.5;
      ctx.beginPath();
      ctx.moveTo(7, 0); ctx.lineTo(-4, 4); ctx.lineTo(-2, 0); ctx.lineTo(-4, -4);
      ctx.closePath();
      ctx.stroke();
      ctx.restore();
    }
  }

  /* ---- title drift ---- */

  function driftTitle(dt) {
    ctx.fillStyle = '#000';
    ctx.fillRect(0, 0, W, H);
    ctx.strokeStyle = '#444';
    ctx.lineWidth = 1.5;
    rocks.forEach(function (a) {
      a.x = wrap(a.x + a.vx * dt, W);
      a.y = wrap(a.y + a.vy * dt, H);
      a.angle += a.spin * dt;
      drawRock(a);
    });
  }

  /* ---- main loop ---- */

  function loop(ts) {
    var dt = Math.min((ts - (lastTs || ts)) / 1000, 0.05);
    lastTs = ts;

    if (phase === 'title' || phase === 'done') {
      driftTitle(dt);
    } else {
      update(dt);
      draw();
    }

    requestAnimationFrame(loop);
  }

  /* ---- boot ---- */

  rocks = spawnWave(5);   // title background

  Arcade.ready();
  canvas.focus();   // grab keyboard focus so Space-to-start works immediately
  Arcade.getHighScores().then(function (hs) {
    if (hs && hs.length && hs[0] && hs[0].score) {
      recordEl.textContent = 'RECORD ' + hs[0].score.toLocaleString();
    }
  });

  requestAnimationFrame(loop);

}());
