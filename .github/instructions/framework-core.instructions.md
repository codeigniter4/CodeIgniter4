---
applyTo: "system/**/*.php"
---

# Framework core

- Assume public and protected symbols are extension points used by third-party
  applications unless they are clearly marked internal.
- Apply the target-branch compatibility policy from the root `AGENTS.md`
  before changing any extension point.
- Before changing a signature or behavior, inspect parent classes, implemented
  interfaces, traits, service factories, configuration, and corresponding
  tests.
- Keep file paths, class names, and namespaces aligned under `CodeIgniter\`.
- Preserve existing extension and dependency-injection points.
- Consider long-running worker environments. Do not retain request-specific
  state in static properties, globals, or shared services after a request
  finishes.
- Avoid introducing a framework-wide abstraction to solve a single local
  problem.
- On `develop`, keep exceptions and observable side effects compatible. On a
  `4.*` target, change them only as an explicit, narrow, documented breaking
  enhancement with a migration path.
