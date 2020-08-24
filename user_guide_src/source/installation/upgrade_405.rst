#############################
Upgrading from 4.0.4 to 4.0.5
#############################

**Cookie SameSite support**

CodeIgniter 4.0.5 introduces a setting for the cookie SameSite attribute. Prior versions did not set this
attribute at all. The default setting for cookies is now `Lax`. This will affect how cookies are handled in
cross-domain contexts and you may need to adjust this setting in your projects. Separate settings in `config/App.php`
exists for Response cookies and for CSRF cookies.

For additional information, see `MDN Web Docs <https://developer.mozilla.org/pl/docs/Web/HTTP/Headers/Set-Cookie/SameSite>`_.
The SameSite specifications are described in `RFC 6265 <https://tools.ietf.org/html/rfc6265>`_
and the `RFC 6265bis revision <https://datatracker.ietf.org/doc/draft-ietf-httpbis-rfc6265bis/?include_text=1>`_.
