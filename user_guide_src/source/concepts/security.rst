###################
Security Guidelines
###################

We take security seriously.
CodeIgniter incorporates a number of features and techniques to either enforce
good security practices, or to enable you to do so easily.

We respect the `Open Web Application Security Project (OWASP) <https://www.owasp.org>`_
and follow their recommendations as much as possible.

The following comes from
`OWASP Top Ten Cheat Sheet <https://www.owasp.org/index.php/OWASP_Top_Ten_Cheat_Sheet>`_,
identifying the top vulnerabilities for web applications.
For each, we provide a brief description, the OWASP recommendations, and then
the CodeIgniter provisions to address the problem.

************
A1 Injection
************

An injection is the inappropriate insertion of partial or complete data via
the input data from the client to the application. Attack vectors include SQL,
XML, ORM, code & buffer overflows.

OWASP recommendations
---------------------

- Presentation: set correct content type, character set & locale
- Submission: validate fields and provide feedback
- Controller: sanitize input; positive input validation using correct character set
- Model: parameterized queries

CodeIgniter provisions
----------------------

- `HTTP library <../incoming/incomingrequest.html>`_ provides for input field filtering & content metadata
- Form validation library

*********************************************
A2 Weak authentication and session management
*********************************************

Inadequate authentication or improper session management can lead to a user
getting more privileges than they are entitled to.

OWASP recommendations
---------------------

- Presentation: validate authentication & role; send CSRF token with forms
- Design: only use built-in session management
- Controller: validate user, role, CSRF token
- Model: validate role
- Tip: consider the use of a request governor

CodeIgniter provisions
----------------------

- `Session <../libraries/sessions.html>`_ library
- `HTTP library <../incoming/incomingrequest.html>`_ provides for CSRF validation
- Easy to add third party authentication

*****************************
A3 Cross Site Scripting (XSS)
*****************************

Insufficient input validation where one user can add content to a web site
that can be malicious when viewed by other users to the web site.

OWASP recommendations
---------------------

- Presentation: output encode all user data as per output context; set input constraints
- Controller: positive input validation
- Tips: only process trustworthy data; do not store data HTML encoded in DB

CodeIgniter provisions
----------------------

- esc function
- Form validation library

***********************************
A4 Insecure Direct Object Reference
***********************************

Insecure Direct Object References occur when an application provides direct
access to objects based on user-supplied input. As a result of this vulnerability
attackers can bypass authorization and access resources in the system directly,
for example database records or files.

OWASP recommendations
---------------------

- Presentation: don't expose internal data; use random reference maps
- Controller: obtain data from trusted sources or random reference maps
- Model: validate user roles before updating data

CodeIgniter provisions
----------------------

- Form validation library
- Easy to add third party authentication

****************************
A5 Security Misconfiguration
****************************

Improper configuration of an application architecture can lead to mistakes
that might compromise the security of the whole architecture.

OWASP recommendations
---------------------

- Presentation: harden web and application servers; use HTTP strict transport security
- Controller: harden web and application servers; protect your XML stack
- Model: harden database servers

CodeIgniter provisions
----------------------

- Sanity checks during bootstrap

**************************
A6 Sensitive Data Exposure
**************************

Sensitive data must be protected when it is transmitted through the network.
Such data can include user credentials and credit cards. As a rule of thumb,
if data must be protected when it is stored, it must be protected also during
transmission.

OWASP recommendations
---------------------

- Presentation: use TLS1.2; use strong ciphers and hashes; do not send keys or hashes to browser
- Controller: use strong ciphers and hashes
- Model: mandate strong encrypted communications with servers

CodeIgniter provisions
----------------------

- Session keys stored encrypted

****************************************
A7 Missing Function Level Access Control
****************************************

Sensitive data must be protected when it is transmitted through the network.
Such data can include user credentials and credit cards. As a rule of thumb,
if data must be protected when it is stored, it must be protected also during
transmission.

OWASP recommendations
---------------------

- Presentation: ensure that non-web data is outside the web root; validate users and roles; send CSRF tokens
- Controller: validate users and roles; validate CSRF tokens
- Model: validate roles

CodeIgniter provisions
----------------------

- Public folder, with application and system outside
- `HTTP library <../incoming/incomingrequest.html>`_ provides for CSRF validation

************************************
A8 Cross Site Request Forgery (CSRF)
************************************

CSRF is an attack that forces an end user to execute unwanted actions on a web
application in which he/she is currently authenticated.

OWASP recommendations
---------------------

- Presentation: validate users and roles; send CSRF tokens
- Controller: validate users and roles; validate CSRF tokens
- Model: validate roles

CodeIgniter provisions
----------------------

- `HTTP library <../incoming/incomingrequest.html>`_ provides for CSRF validation

**********************************************
A9 Using Components with Known Vulnerabilities
**********************************************

Many applications have known vulnerabilities and known attack strategies that
can be exploited in order to gain remote control or to exploit data.

OWASP recommendations
---------------------

- Don't use any of these

CodeIgniter provisions
----------------------

- Third party libraries incorporated must be vetted

**************************************
A10 Unvalidated Redirects and Forwards
**************************************

Faulty business logic or injected actionable code could redirect the user
inappropriately.

OWASP recommendations
---------------------

- Presentation: don't use URL redirection; use random indirect references
- Controller: don't use URL redirection; use random indirect references
- Model: validate roles

CodeIgniter provisions
----------------------

- `HTTP library <../incoming/incomingrequest.html>`_ provides for ...
- `Session <../libraries/sessions.html>`_ library provides flashdata
