/*
 * shell.js — the arcade cabinet.
 *
 * Owns the sidebar, hash routing, the game iframe, and the score board.
 * Games live in the iframe and never touch anything in here directly;
 * they talk to the shell through arcade-sdk.js over postMessage.
 */
(function () {
  'use strict';

  var BOARD_SIZE = 10;

  var state = {
    games: [],
    byId: {},
    current: null,   // the game object being played
    frame: null      // the live iframe element
  };

  var el = {
    gameList: document.getElementById('game-list'),
    gameCount: document.getElementById('game-count'),
    gameGrid: document.getElementById('game-grid'),
    problems: document.getElementById('problems'),
    problemList: document.getElementById('problem-list'),
    home: document.getElementById('home'),
    player: document.getElementById('player'),
    notFound: document.getElementById('not-found'),
    title: document.getElementById('game-title'),
    description: document.getElementById('game-description'),
    controls: document.getElementById('game-controls'),
    stage: document.getElementById('stage'),
    stageStatus: document.getElementById('stage-status'),
    boardTitle: document.getElementById('board-title'),
    boardBody: document.getElementById('board-body')
  };

  /* ---------- helpers ---------- */

  function text(node, value) {
    node.textContent = value == null ? '' : String(value);
  }

  function show(node, visible) {
    node.hidden = !visible;
  }

  function currentGameId() {
    return decodeURIComponent(window.location.hash.replace(/^#/, '')).trim();
  }

  /* ---------- boot ---------- */

  function boot() {
    fetch('/api/games.php', { headers: { Accept: 'application/json' } })
      .then(function (r) {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
      })
      .then(function (data) {
        state.games = data.games || [];
        state.byId = {};
        state.games.forEach(function (g) { state.byId[g.id] = g; });

        renderSidebar();
        renderGrid();
        reportProblems(data.problems || []);
        route();
      })
      .catch(function (err) {
        el.gameList.innerHTML = '';
        var li = document.createElement('li');
        li.className = 'muted';
        text(li, 'could not load the game list');
        el.gameList.appendChild(li);
        console.error('[arcade] game list failed:', err);
      });

    window.addEventListener('hashchange', route);
    window.addEventListener('message', onGameMessage);
  }

  /* ---------- sidebar + grid ---------- */

  function renderSidebar() {
    el.gameList.innerHTML = '';

    if (!state.games.length) {
      var empty = document.createElement('li');
      empty.className = 'muted';
      text(empty, 'no games installed yet');
      el.gameList.appendChild(empty);
      text(el.gameCount, '');
      return;
    }

    state.games.forEach(function (game) {
      var li = document.createElement('li');
      li.dataset.id = game.id;
      var a = document.createElement('a');
      a.href = '#' + game.id;
      text(a, game.title);
      li.appendChild(a);
      el.gameList.appendChild(li);
    });

    text(el.gameCount, state.games.length + (state.games.length === 1 ? ' game' : ' games'));
  }

  function markCurrent(id) {
    Array.prototype.forEach.call(el.gameList.children, function (li) {
      li.classList.toggle('current', li.dataset.id === id);
    });
  }

  function renderGrid() {
    el.gameGrid.innerHTML = '';

    state.games.forEach(function (game) {
      var li = document.createElement('li');
      var a = document.createElement('a');
      a.href = '#' + game.id;

      if (game.thumb) {
        var img = document.createElement('img');
        img.className = 'thumb';
        img.src = game.thumb;
        img.alt = '';
        img.loading = 'lazy';
        a.appendChild(img);
      } else {
        var tile = document.createElement('span');
        tile.className = 'thumb letter';
        text(tile, game.title.charAt(0).toUpperCase());
        a.appendChild(tile);
      }

      var name = document.createElement('span');
      name.className = 'name';
      text(name, game.title);
      a.appendChild(name);

      var meta = document.createElement('span');
      meta.className = 'meta';
      text(meta, game.year ? String(game.year) : (game.author || ''));
      a.appendChild(meta);

      li.appendChild(a);
      el.gameGrid.appendChild(li);
    });
  }

  function reportProblems(problems) {
    if (!problems.length) {
      show(el.problems, false);
      return;
    }

    problems.forEach(function (p) { console.warn('[arcade] skipped —', p); });

    // Only shown on the page with ?debug=1; otherwise it's console-only.
    if (!document.body.dataset.debug) return;

    el.problemList.innerHTML = '';
    problems.forEach(function (p) {
      var li = document.createElement('li');
      text(li, p);
      el.problemList.appendChild(li);
    });
    show(el.problems, true);
  }

  /* ---------- routing ---------- */

  function route() {
    var id = currentGameId();

    if (!id) {
      unmountGame();
      markCurrent(null);
      show(el.home, true);
      show(el.player, false);
      show(el.notFound, false);
      document.title = 'Retro Arcade';
      return;
    }

    var game = state.byId[id];
    if (!game) {
      unmountGame();
      markCurrent(null);
      show(el.home, false);
      show(el.player, false);
      show(el.notFound, true);
      document.title = 'Not found — Retro Arcade';
      return;
    }

    show(el.home, false);
    show(el.notFound, false);
    show(el.player, true);
    markCurrent(id);
    mountGame(game);
  }

  /* ---------- the stage ---------- */

  function mountGame(game) {
    if (state.current && state.current.id === game.id) return;

    unmountGame();
    state.current = game;

    document.title = game.title + ' — Retro Arcade';
    text(el.title, game.year ? game.title + ' (' + game.year + ')' : game.title);
    text(el.description, game.description || '');
    show(el.description, !!game.description);

    if (game.controls) {
      el.controls.innerHTML = '';
      var label = document.createElement('b');
      text(label, 'Controls: ');
      el.controls.appendChild(label);
      el.controls.appendChild(document.createTextNode(game.controls));
      show(el.controls, true);
    } else {
      show(el.controls, false);
    }

    text(el.boardTitle, 'Top ' + BOARD_SIZE + ' — ' + game.title);
    renderBoard([]);
    loadScores(game.id);

    text(el.stageStatus, 'loading ' + game.title + '…');
    show(el.stageStatus, true);

    var frame = document.createElement('iframe');
    frame.title = game.title;
    frame.src = game.entry;
    frame.setAttribute('allow', 'autoplay');
    frame.addEventListener('load', function () {
      show(el.stageStatus, false);
      // Games capture their own keys; hand them focus so play starts immediately.
      try { frame.contentWindow.focus(); } catch (e) { /* cross-origin, ignore */ }
    });

    state.frame = frame;
    state.mountedAt = Date.now();
    el.stage.appendChild(frame);
  }

  function unmountGame() {
    if (state.frame) {
      state.frame.remove();
      state.frame = null;
    }
    state.current = null;
    state.mountedAt = 0;
  }

  /* ---------- score board ---------- */

  function loadScores(gameId) {
    fetch('/api/scores.php?game=' + encodeURIComponent(gameId), { headers: { Accept: 'application/json' } })
      .then(function (r) { return r.ok ? r.json() : { scores: [] }; })
      .then(function (data) {
        // Ignore a response that arrived after the player switched games.
        if (!state.current || state.current.id !== gameId) return;
        renderBoard(data.scores || []);
      })
      .catch(function () { /* board stays empty; not worth an error message */ });
  }

  function renderBoard(scores, freshRank) {
    el.boardBody.innerHTML = '';

    for (var i = 0; i < BOARD_SIZE; i++) {
      var entry = scores[i];
      var tr = document.createElement('tr');

      if (!entry) tr.className = 'empty';
      else if (freshRank && entry.rank === freshRank) tr.className = 'fresh';

      tr.appendChild(cell('rank', String(i + 1)));
      tr.appendChild(cell('who', entry ? entry.initials : '---'));
      tr.appendChild(cell('score', entry ? Number(entry.score).toLocaleString() : '0'));
      tr.appendChild(cell('when', entry ? formatDate(entry.date) : '--'));

      el.boardBody.appendChild(tr);
    }
  }

  function cell(className, value) {
    var td = document.createElement('td');
    td.className = className;
    text(td, value);          // textContent, never innerHTML — initials are user input
    return td;
  }

  function formatDate(iso) {
    if (!iso) return '--';
    return String(iso).slice(0, 10);
  }

  /* ---------- messages from games ---------- */

  function onGameMessage(event) {
    // Only the game we mounted, only from this origin.
    if (event.origin !== window.location.origin) return;
    if (!state.frame || event.source !== state.frame.contentWindow) return;

    var msg = event.data;
    if (!msg || msg.source !== 'arcade-game') return;

    switch (msg.type) {
      case 'ready':
        show(el.stageStatus, false);
        break;
      // 'score', 'set-score' and 'get-scores' arrive in M4 with the SDK.
      default:
        console.debug('[arcade] unhandled message from game:', msg.type);
    }
  }

  boot();
})();
