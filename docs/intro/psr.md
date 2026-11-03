# PSR Compliance

The [PHP-FIG](https://www.php-fig.org/) was created in 2009 to help make code more interoperable between frameworks by ratifying interfaces, style guides, and more that members were free to implement or not. While CodeIgniter is not a member of the FIG, we are compatible with a number of their proposals. This guide is meant to list the status of our compliance with the various accepted, and some draft, proposals.

**PSR-1: Basic Coding Standard**

This recommendation covers basic class, method, and file-naming standards. Our [style guide](https://github.com/codeigniter4/CodeIgniter4/blob/develop/contributing/styleguide.md) meets PSR-1 and adds its own requirements on top of it.

**PSR-12: Extended Coding Style**

Our [style guide](https://github.com/codeigniter4/CodeIgniter4/blob/develop/contributing/styleguide.md) follows the recommendation plus a set of our own styling conventions.

**PSR-3: Logger Interface**

CodeIgniter's [Logger](../general/logging.md) implements all of the interfaces provided by this PSR.

**PSR-4: Autoloading Standard**

This PSR provides a method for organizing files and namespaces to allow for a standard method of autoloading classes. Our [Autoloader](../concepts/autoloader.md) meets the PSR-4 recommendations.

**PSR-6: Caching Interfaces**

**PSR-16: SimpleCache Interface**

While the framework Cache components do not adhere to PSR-6 or PSR-16, a separate set of adapters is available from the CodeIgniter4 organization as a supplemental module. It is recommended that projects use the native Cache drivers directly, as the adapters are only intended for compatibility with third-party libraries. For more information, visit the [CodeIgniter4 Cache repo](https://github.com/codeigniter4/cache).

**PSR-7: HTTP Message Interface**

This PSR standardizes a way of representing the HTTP interactions. While many of the concepts became part of our HTTP layer, CodeIgniter does not strive for compatibility with this recommendation.

---

If you find any places where we claim to meet a PSR but have failed to implement it correctly, please let us know and we will get it fixed, or submit a pull request with the required changes.
