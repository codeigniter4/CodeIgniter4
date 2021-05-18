#############################
Upgrading from 4.0.4 to 4.0.5
#############################

**Cookie SameSite support**

CodeIgniter 4.0.5 introduces a setting for the cookie SameSite attribute. Prior versions did not set this
attribute at all. The default setting for cookies is now `Lax`. This will affect how cookies are handled in
cross-domain contexts and you may need to adjust this setting in your projects. Separate settings in `app/Config/App.php`
exists for Response cookies and for CSRF cookies.

For additional information, see `MDN Web Docs <https://developer.mozilla.org/pl/docs/Web/HTTP/Headers/Set-Cookie/SameSite>`_.
The SameSite specifications are described in `RFC 6265 <https://tools.ietf.org/html/rfc6265>`_
and the `RFC 6265bis revision <https://datatracker.ietf.org/doc/draft-ietf-httpbis-rfc6265bis/?include_text=1>`_.

**Message::getHeader(s)**

The HTTP layer is moving towards `PSR-7 compliance <https://www.php-fig.org/psr/psr-7/>`_. Towards this end
``Message::getHeader()`` and ``Message::getHeaders()`` are deprecated and should be replaced
with ``Message::header()`` and ``Message::headers()`` respectively. Note that this pertains
to all classes that extend ``Message`` as well: ``Request``, ``Response`` and their subclasses.

Additional related deprecations from the HTTP layer:

* ``Message::isJSON``: Check the "Content-Type" header directly
* ``Request[Interface]::isValidIP``: Use the Validation class with ``valid_ip``
* ``Request[Interface]::getMethod()``: The ``$upper`` parameter will be removed, use str_to_upper()
* ``Request[Trait]::$ipAddress``: This property will become private
* ``Request::$proxyIPs``: This property will be removed; access ``config('App')->proxyIPs`` directly
* ``Request::__construct()``: The constructor will no longer take ``Config\App`` and has been made nullable to aid transition
* ``Response[Interface]::getReason()``: Use ``getReasonPhrase()`` instead
* ``Response[Interface]::getStatusCode()``: The explicit ``int`` return type will be removed (no action required)

**ResponseInterface**

This interface intends to include the necessary methods for any framework-compatible response class.
A number of methods expected by the framework were missing and have noe been added. If you use any
classes the implement ``ResponseInterface`` directly they will need to be compatible with the
updated requirements. These methods are as follows:

* ``setLastModified($date);``
* ``setLink(PagerInterface $pager);``
* ``setJSON($body, bool $unencoded = false);``
* ``getJSON();``
* ``setXML($body);``
* ``getXML();``
* ``send();``
* ``sendHeaders();``
* ``sendBody();``
* ``setCookie($name, $value = '', $expire = '', $domain = '', $path = '/', $prefix = '', $secure = false, $httponly = false, $samesite = null);``
* ``hasCookie(string $name, string $value = null, string $prefix = ''): bool;``
* ``getCookie(string $name = null, string $prefix = '');``
* ``deleteCookie(string $name = '', string $domain = '', string $path = '/', string $prefix = '');``
* ``getCookies();``
* ``redirect(string $uri, string $method = 'auto', int $code = null);``
* ``download(string $filename = '', $data = '', bool $setMime = false);``

To facilitate use of this interface these methods have been moved from the framework's ``Response`` into a ``ResponseTrait``
which you may use, and ``DownloadResponse`` now extends ``Response`` directly to ensure maximum compatibility.

**Config\Services**

Service discovery has been updated to allow third-party services (when enabled via Modules) to take precedence over core services. Update
**app/Config/Services.php** so the class extends ``CodeIgniter\Config\BaseService`` to allow proper discovery of third-party services.

Project Files
=============

Numerous files in the project space (root, app, public, writable) received updates. Due to
these files being outside of the system scope they will not be changed without your intervention.
There are some third-party CodeIgniter modules available to assist with merging changes to
the project space: `Explore on Packagist <https://packagist.org/explore/?query=codeigniter4%20updates>`_.

.. note:: Except in very rare cases for bug fixes, no changes made to files for the project space
    will break your application. All changes noted here are optional until the next major version,
    and any mandatory changes will be covered in the sections above.

Content Changes
---------------

The following files received significant changes (including deprecations or visual adjustments)
and it is recommended that you merge the updated versions with your application:

* ``app/Views/*``
* ``public/index.php``
* ``public/.htaccess``
* ``spark``
* ``phpunit.xml.dist``
* ``composer.json``

All Changes
-----------

This is a list of all files in the project space that received changes;
many will be simple comments or formatting that have no affect on the runtime:

* ``LICENSE``
* ``README.md``
* ``app/Config/App.php``
* ``app/Config/Autoload.php``
* ``app/Config/Boot/development.php``
* ``app/Config/Boot/production.php``
* ``app/Config/Boot/testing.php``
* ``app/Config/Cache.php``
* ``app/Config/Constants.php``
* ``app/Config/ContentSecurityPolicy.php``
* ``app/Config/Database.php``
* ``app/Config/DocTypes.php``
* ``app/Config/Email.php``
* ``app/Config/Encryption.php``
* ``app/Config/Events.php``
* ``app/Config/Exceptions.php``
* ``app/Config/Filters.php``
* ``app/Config/ForeignCharacters.php``
* ``app/Config/Format.php``
* ``app/Config/Generators.php``
* ``app/Config/Honeypot.php``
* ``app/Config/Images.php``
* ``app/Config/Kint.php``
* ``app/Config/Logger.php``
* ``app/Config/Migrations.php``
* ``app/Config/Mimes.php``
* ``app/Config/Modules.php``
* ``app/Config/Pager.php``
* ``app/Config/Paths.php``
* ``app/Config/Routes.php``
* ``app/Config/Security.php``
* ``app/Config/Services.php``
* ``app/Config/Toolbar.php``
* ``app/Config/UserAgents.php``
* ``app/Config/Validation.php``
* ``app/Config/View.php``
* ``app/Controllers/BaseController.php``
* ``app/Controllers/Home.php``
* ``app/Views/errors/cli/error_404.php``
* ``app/Views/errors/cli/error_exception.php``
* ``app/Views/errors/html/debug.css``
* ``app/Views/errors/html/debug.js``
* ``app/Views/errors/html/error_exception.php``
* ``composer.json``
* ``env``
* ``license.txt``
* ``phpunit.xml.dist``
* ``public/.htaccess``
* ``public/index.php``
* ``spark``
