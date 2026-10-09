---
applyTo: "system/Boot.php,system/CodeIgniter.php,system/Commands/Worker/**,system/Config/BaseService.php,system/Config/Factories.php,system/Config/Factory.php,system/Config/Services.php,system/Database/Config.php,system/Database/BaseConnection.php,system/Database/ConnectionInterface.php,system/Database/*/Connection.php,system/Events/Events.php,system/Session/**,system/Cache/**,system/HTTP/**,system/Filters/**,system/Router/**,system/Security/**,system/Debug/Toolbar.php,system/Debug/Toolbar/**,app/Config/WorkerMode.php,tests/system/Commands/Worker/**,user_guide_src/source/installation/worker_mode.rst"
---

# FrankenPHP Worker Mode

Use `system/Commands/Worker/Views/frankenphp-worker.php.tpl` as the canonical
Worker Mode entry point. `worker:install` publishes this template to
`public/frankenphp-worker.php`.

When changing bootstrap or request-lifecycle code, trace all three Worker Mode
phases:

1. One-time process bootstrap through `Boot::bootWorker()`.
2. Per-request preparation: reconnect database and cache connections, reset
   the `CodeIgniter` instance, and replace all request superglobals before
   calling `$app->run()`.
3. Post-request cleanup: close the session, clean up unfinished database
   transactions, reset factories and non-persistent services, clean up event
   listeners and performance logs, and reset the debug toolbar.

- Preserve the phase ordering unless the change explicitly proves a different
  order is safe.
- Check every new mutable static property, singleton, shared service, event
  listener, connection, handler, or global for cross-request state leakage.
- Do not preserve a service across requests merely for performance. Persistent
  services must be safe to reuse and must not retain request, response, user,
  route, security token, locale, or error state.
- Ensure exceptions and early exits cannot skip cleanup needed before the next
  request.
- For state-isolation fixes, prefer a regression test that exercises two
  sequential requests or reset cycles in the same process and proves the
  second request cannot observe the first request's state.
- If the template contract changes, update focused assertions in
  `tests/system/Commands/Worker/WorkerCommandsTest.php`.
- If existing installations need the new generated entry point, update the
  changelog and upgrading guide and instruct users to run
  `php spark worker:install --force`.
