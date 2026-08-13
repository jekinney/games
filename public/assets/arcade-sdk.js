/*
 * arcade-sdk.js — everything a game needs to talk to the cabinet.
 *
 *   <script src="/assets/arcade-sdk.js"></script>
 *
 *   Arcade.ready();                       // optional: hides the loading text
 *   Arcade.setScore(340);                 // optional: live score in the shell
 *   Arcade.submitScore(1240);             // game over — the SHELL asks for initials
 *   await Arcade.getHighScores();         // optional: read the current top 10
 *
 * submitScore() resolves to { accepted, rank, scores }. You never draw the
 * initials prompt yourself — the shell owns it so every game behaves the same.
 */
(function (global) {
  'use strict';

  var FRAMED = global.parent && global.parent !== global;
  var pending = {};
  var nextId = 1;

  function post(type, payload) {
    if (!FRAMED) {
      console.warn('[arcade-sdk] not running inside the arcade shell; "' + type + '" ignored');
      return;
    }
    var msg = { source: 'arcade-game', type: type };
    for (var key in payload) {
      if (Object.prototype.hasOwnProperty.call(payload, key)) msg[key] = payload[key];
    }
    global.parent.postMessage(msg, global.location.origin);
  }

  /** Send a message and wait for the shell's reply. */
  function request(type, payload, timeoutMs) {
    if (!FRAMED) {
      return Promise.resolve(null);
    }

    var id = nextId++;
    var data = payload || {};
    data.requestId = id;

    return new Promise(function (resolve) {
      var timer = setTimeout(function () {
        delete pending[id];
        resolve(null);           // never leave a game hanging on the shell
      }, timeoutMs || 8000);

      pending[id] = function (reply) {
        clearTimeout(timer);
        delete pending[id];
        resolve(reply);
      };

      post(type, data);
    });
  }

  global.addEventListener('message', function (event) {
    if (event.origin !== global.location.origin) return;

    var msg = event.data;
    if (!msg || msg.source !== 'arcade-shell') return;

    var handler = pending[msg.requestId];
    if (handler) handler(msg);
  });

  function toScore(value, label) {
    var n = Number(value);
    if (!isFinite(n) || n < 0) {
      throw new TypeError('[arcade-sdk] ' + label + ' needs a non-negative number, got: ' + value);
    }
    return Math.floor(n);
  }

  var Arcade = {
    /** Tell the shell the game has loaded and is playable. */
    ready: function () {
      post('ready', {});
    },

    /** Update the live score shown by the shell while playing. */
    setScore: function (value) {
      post('set-score', { value: toScore(value, 'setScore') });
    },

    /**
     * Game over. Call this exactly once per play.
     * Resolves to { accepted, rank, scores } — or null if the shell never replied.
     */
    submitScore: function (value) {
      return request('score', { value: toScore(value, 'submitScore') }, 60000)
        .then(function (reply) {
          if (!reply) return null;
          return { accepted: !!reply.accepted, rank: reply.rank, scores: reply.scores || [] };
        });
    },

    /** Current top 10 for this game. Resolves to an array (empty on failure). */
    getHighScores: function () {
      return request('get-scores', {}).then(function (reply) {
        return reply && reply.scores ? reply.scores : [];
      });
    },

    /** True when running inside the arcade shell rather than standalone. */
    isFramed: function () {
      return !!FRAMED;
    }
  };

  global.Arcade = Arcade;
})(window);
