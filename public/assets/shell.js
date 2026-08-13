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
  // A score arriving this soon after the frame mounted is a console one-liner,
  // not a game. Kept short on purpose: a genuinely fast death in Snake can
  // happen a couple of seconds in, and refusing a real score is worse than
  // letting a determined cheat through (see docs/plan/03-scores-api.md).
  var MIN_PLAY_MS = 2000;
  var COUNTDOWN_SECONDS = 10;  // arcade auto-submit

  var state = {
    games: [],
    byId: {},
    current: null,   // the game object being played
    frame: null,     // the live iframe element
    mountedAt: 0,
    modal: null      // { resolve, timer, deadline } while initials entry is open
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
    boardBody: document.getElementById('board-body'),
    liveScore: document.getElementById('live-score'),
    liveScoreValue: document.getElementById('live-score-value'),
    gameSearch: document.getElementById('game-search'),
    submitNote: document.getElementById('submit-note'),
    modal: document.getElementById('initials-modal'),
    modalBox: document.getElementById('initials-box'),
    modalHeading: document.getElementById('initials-heading'),
    modalRank: document.getElementById('initials-rank'),
    modalInput: document.getElementById('initials-input'),
    modalCountdown: document.getElementById('initials-countdown'),
    slots: Array.prototype.slice.call(document.querySelectorAll('#initials-slots .slot'))
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
    el.gameSearch.addEventListener('input', function () {
      renderSidebar();
      renderGrid();
      markCurrent(currentGameId());
    });
  }

  /* ---------- sidebar + grid ---------- */

  function searchQuery() {
    return el.gameSearch.value.trim().toLowerCase();
  }

  function filteredGames() {
    var q = searchQuery();
    if (!q) return state.games;
    return state.games.filter(function (g) {
      return g.title.toLowerCase().indexOf(q) !== -1;
    });
  }

  function renderSidebar() {
    var games = filteredGames();
    el.gameList.innerHTML = '';

    if (!state.games.length) {
      var empty = document.createElement('li');
      empty.className = 'muted';
      text(empty, 'no games installed yet');
      el.gameList.appendChild(empty);
      text(el.gameCount, '');
      return;
    }

    if (!games.length) {
      var none = document.createElement('li');
      none.className = 'muted';
      text(none, 'no matches');
      el.gameList.appendChild(none);
    } else {
      games.forEach(function (game) {
        var li = document.createElement('li');
        li.dataset.id = game.id;
        var a = document.createElement('a');
        a.href = '#' + game.id;
        text(a, game.title);
        li.appendChild(a);
        el.gameList.appendChild(li);
      });
    }

    var q = searchQuery();
    var total = state.games.length;
    if (q && games.length !== total) {
      text(el.gameCount, games.length + ' of ' + total + ' games');
    } else {
      text(el.gameCount, total + (total === 1 ? ' game' : ' games'));
    }
  }

  function markCurrent(id) {
    Array.prototype.forEach.call(el.gameList.children, function (li) {
      li.classList.toggle('current', li.dataset.id === id);
    });
  }

  function renderGrid() {
    el.gameGrid.innerHTML = '';

    filteredGames().forEach(function (game) {
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
    show(el.liveScore, false);
    text(el.liveScoreValue, '0');
    show(el.submitNote, false);
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
    fetchScores(gameId).then(function (scores) {
      // Ignore a response that arrived after the player switched games.
      if (!state.current || state.current.id !== gameId) return;
      renderBoard(scores);
    });
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

      case 'set-score':
        text(el.liveScoreValue, Number(msg.value || 0).toLocaleString());
        show(el.liveScore, true);
        break;

      case 'score':
        handleScore(msg);
        break;

      case 'get-scores':
        fetchScores(state.current.id).then(function (scores) {
          replyToGame(msg.requestId, { scores: scores });
        });
        break;

      default:
        console.debug('[arcade] unhandled message from game:', msg.type);
    }
  }

  function replyToGame(requestId, payload) {
    if (!state.frame || !requestId) return;
    var msg = { source: 'arcade-shell', requestId: requestId };
    for (var k in payload) {
      if (Object.prototype.hasOwnProperty.call(payload, k)) msg[k] = payload[k];
    }
    state.frame.contentWindow.postMessage(msg, window.location.origin);
  }

  /* ---------- game over ---------- */

  function handleScore(msg) {
    var game = state.current;
    if (!game) return;

    var score = Math.floor(Number(msg.value));
    if (!isFinite(score) || score < 0) {
      console.warn('[arcade] ignoring invalid score:', msg.value);
      replyToGame(msg.requestId, { accepted: false, rank: null, scores: [] });
      return;
    }

    // Nobody plays a game in under three seconds. This is the console-cheat guard.
    if (Date.now() - state.mountedAt < MIN_PLAY_MS) {
      console.warn('[arcade] score ignored: game had not been running long enough');
      note('That game ended too fast to record a score.', true);
      replyToGame(msg.requestId, { accepted: false, rank: null, scores: [] });
      return;
    }

    // Prompt optimistically: a round-trip before the modal would stall the
    // most dramatic moment. If it turns out not to place, we say so after.
    askForInitials(score).then(function (initials) {
      return postScore(game.id, score, initials);
    }).then(function (result) {
      if (!result) {
        note('Could not save that score. Try again later.', true);
        replyToGame(msg.requestId, { accepted: false, rank: null, scores: [] });
        return;
      }

      renderBoard(result.scores || [], result.rank);

      if (result.accepted) {
        note('You placed #' + result.rank + ' as ' + result.initials + '.', false);
      } else {
        note('Score ' + score.toLocaleString() + ' — not quite enough for the top ' + BOARD_SIZE + '.', false);
      }

      replyToGame(msg.requestId, {
        accepted: result.accepted,
        rank: result.rank,
        scores: result.scores || []
      });
    });
  }

  function note(message, isError) {
    text(el.submitNote, message);
    el.submitNote.classList.toggle('error', !!isError);
    show(el.submitNote, true);
  }

  function fetchScores(gameId) {
    return fetch('/api/scores.php?game=' + encodeURIComponent(gameId), { headers: { Accept: 'application/json' } })
      .then(function (r) { return r.ok ? r.json() : { scores: [] }; })
      .then(function (data) { return data.scores || []; })
      .catch(function () { return []; });
  }

  function postScore(gameId, score, initials) {
    return fetch('/api/scores.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify({ game: gameId, score: score, initials: initials })
    })
      .then(function (r) { return r.ok ? r.json() : null; })
      .catch(function (err) {
        console.error('[arcade] score post failed:', err);
        return null;
      });
  }

  /* ---------- initials entry ---------- */

  function askForInitials(score) {
    return new Promise(function (resolve) {
      var value = '';

      text(el.modalHeading, 'New High Score!');
      text(el.modalRank, score.toLocaleString() + ' points — enter your initials');
      el.modalInput.value = '';
      paintSlots('');
      show(el.modal, true);

      // Focus has to come back from the iframe or we get no keystrokes.
      window.focus();
      el.modalInput.focus();

      var remaining = COUNTDOWN_SECONDS;
      tick();

      var timer = setInterval(function () {
        remaining -= 1;
        tick();
        if (remaining <= 0) finish();
      }, 1000);

      function tick() {
        text(el.modalCountdown, 'auto-submits in ' + Math.max(remaining, 0) + 's');
      }

      function paintSlots(current) {
        el.slots.forEach(function (slot, i) {
          var ch = current.charAt(i);
          text(slot, ch);
          slot.classList.toggle('filled', ch !== '');
          slot.classList.toggle('active', i === current.length);
        });
      }

      function onInput() {
        // Strip anything that isn't A-Z0-9 and keep the input in sync with the slots.
        value = el.modalInput.value.toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 3);
        el.modalInput.value = value;
        paintSlots(value);
      }

      function onKeyDown(event) {
        if (event.key === 'Enter') {
          event.preventDefault();
          finish();
        } else if (event.key === 'Escape') {
          event.preventDefault();
          finish();
        }
      }

      function finish() {
        clearInterval(timer);
        el.modalInput.removeEventListener('input', onInput);
        el.modalInput.removeEventListener('keydown', onKeyDown);
        el.modal.removeEventListener('mousedown', keepFocus);
        show(el.modal, false);
        state.modal = null;

        // Fewer than three characters pads with A, same as the cabinets.
        var initials = value === '' ? 'AAA' : (value + 'AAA').slice(0, 3);
        resolve(initials);
      }

      function keepFocus(event) {
        // Clicking the backdrop shouldn't steal focus from the input.
        event.preventDefault();
        el.modalInput.focus();
      }

      el.modalInput.addEventListener('input', onInput);
      el.modalInput.addEventListener('keydown', onKeyDown);
      el.modal.addEventListener('mousedown', keepFocus);

      state.modal = { resolve: resolve, cancel: finish };
    });
  }

  boot();
})();
