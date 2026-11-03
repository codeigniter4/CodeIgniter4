# Upgrading from 4.0.x to 4.0.4

Please refer to the upgrade instructions corresponding to your installation method.

- [Composer Installation App Starter Upgrading](installing_composer.md#upgrading)
- [Composer Installation Adding CodeIgniter4 to an Existing Project Upgrading](installing_composer.md#upgrading_1)
- [Manual Installation Upgrading](installing_manual.md#upgrading)

CodeIgniter 4.0.4 fixes a bug in the implementation of [Controller Filters](../incoming/filters.md), breaking code implementing the `FilterInterface`.

## Breaking Changes

### Update FilterInterface Declarations

The method signatures for `after()` and `before()` must be updated to include `$arguments`. The function definitions should be changed from:

```
public function before(RequestInterface $request)
public function after(RequestInterface $request, ResponseInterface $response)
```

to:

```
public function before(RequestInterface $request, $arguments = null)
public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
```
