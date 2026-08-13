# Tests

No test framework — plain PHP and plain Node. Both print `PASS`/`FAIL` lines and
exit non-zero on failure.

## Score storage (fast, no server needed)

```
php tests/score-store.test.php
```

Covers top-10 truncation, rank calculation, tie rules (older entry keeps the
higher spot), `scoring: "low"` time-attack ordering, initials coercion,
`remove()`, and corrupt-file recovery.

## Browser end-to-end

These drive real Chrome over the DevTools Protocol. No dependencies — Node 22+
ships a global `WebSocket`.

Start the two processes first:

```
php -S 127.0.0.1:8123 -t public
"C:\Program Files\Google\Chrome\Application\chrome.exe" --headless=new ^
  --remote-debugging-port=9222 --user-data-dir=%TEMP%\arcade-chrome --no-first-run about:blank
```

Then:

```
node tests/shell.e2e.mjs        # the full play-through, ~10s
node tests/countdown.e2e.mjs    # the 10-second auto-submit, ~20s
```

`shell.e2e.mjs` covers: game discovery, sidebar and grid rendering, iframe mount,
the SDK handshake, `setScore` live readout, rejection of spoofed messages from
outside the game frame, the too-fast-to-be-real guard, the initials modal
(focus, slots, input sanitising, padding), score submission, board update, and
hash routing including the unknown-game case.

Both e2e scripts write to the real `data/scores/hello.json`. Clear it first for
predictable results:

```
php scripts/arcade.php clear hello
```

## Concurrency

The parallel-write check isn't scripted here; it was run as 20 processes ×
15 submissions against one board, confirming the file stayed valid JSON with
exactly 10 correctly-sorted entries. Re-run it after any change to
`ScoreStore::submit()`.
