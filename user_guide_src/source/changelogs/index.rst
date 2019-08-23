###########
Change Logs
###########

Version |version|
====================================================

Release Date: Not Released


:doc:`See all the changes. </changelogs/next>`

Version 4.0.0-beta.3
====================================================

Release Date: May 06, 2019

Highlights:

- Fixed a number of model, database, validation & debug toolbar issues
- Type hinting added throughout & typos corrected (see API docs)

New messages:

- Database.FieldNotExists
- Validation.equals, not_equals

App changes:

- Removed $salt config item in app/Config/App
- Enabled migrations by default in app/Config/Migrations
- Simplified public/.htaccess

:doc:`See all the changes. </changelogs/v4.0.0-beta.3>`

Version 4.0.0-beta.2
====================================================

Release Date: April 04, 2019

Highlights:

- A number of fixes & improvements, importantly for the Model and testing classes
- Models now require a primary key
- Generated API docs accessible at https://codeigniter4.github.io/api/
- Validation rules have been enhanced
- .htaccess beefed up

New messages:

- Database.noPrimaryKey, forFindColumnHaveMultipleColumns, Database.forEmptyInputGiven

App changes:

- updated app/Config/Events 
- added app/Controllers/BaseController 
- added tests/ folder for unit testing
- added phpunit.xml.dist for unit testing configuration

:doc:`See all the changes. </changelogs/v4.0.0-beta.2>`

Version 4.0.0-beta.1
====================================================

Release Date: Unreleased

Highlights:

- New View Layouts provide simple way to create site site view templates.
- Fixed user guide CSS for proper wide table display
- Converted UploadedFile to use system messages
- Numerous database, migration & model bugs fixed
- Refactored unit testing for appstarter & framework distributions

New messages:

- Database.tableNotFound
- HTTP.uploadErr...

App changes:

- app/Config/Cache has new setting: database
- app/Views/welcome_message has logo tinted
- composer.json has a case correction
- env adds CI_ENVIRONMENT suggestion

:doc:`See all the changes. </changelogs/v4.0.0-beta.1>`

Version 4.0.0-alpha.5
====================================================

Release Date: January 30, 2019

**Alpha 5**

Highlights:

- updated PHP dependency to 7.2
- new feature branches have been created for the email and queue modules, 
    so they don't impact the release of 4.0.0
- dropped several language messages that were unused (eg Migrations.missingTable) 
    and added some new (eg Migrations.invalidType)
- lots of bug fixes
- code coverage is up to 78%

:doc:`See all the changes. </changelogs/v4.0.0-alpha.5>`

Version 4.0.0-alpha.4
====================================================

Release Date: December 15, 2018

**Next release of CodeIgniter4**

Highlights:

- Refactor for consistency: folder application renamed to app;
    constant BASEPATH renamed to SYSTEMPATH
- Debug toolbar gets its own config, history collector
- Numerous corrections and enhancements


:doc:`See all the changes. </changelogs/v4.0.0-alpha.4>`

Version 4.0.0-alpha.3
====================================================

Release Date: November 30, 2018

**Next alpha release of CodeIgniter4**

- Numerous bug fixes, across the framework
- Many missing features implemented, across the framework
- Code coverage is up to 72%
- CodeIgniter4 has been promoted to its own github organization.
  That is reflected in docs and comments.
- We have integrated a git pre-commit hook, which will apply the
  CI4 code sniffer rules, and attempt to fix them.
  We have run all the source files through it, and any "funny"
  code formatting is temporary until the rules are updated.
- We welcome Natan Felles, from Brazil, to the code developer team.
  He has proven to be passionate, dedicated and thorough :)

:doc:`See all the changes. </changelogs/v4.0.0-alpha.3>`


Version 4.0.0-alpha.2
=================================

Release Date: Oct 26, 2018

**Second alpha release of CodeIgniter4**

- bug fixes
- features implemented
- tutorial revised

:doc:`See all the changes. </changelogs/v4.0.0-alpha.2>`

Version 4.0.0-alpha.1
=================================

Release Date: September 28, 2018

**Rewrite of the CodeIgniter framework**

Non-code changes:
    - User Guide adapted or rewritten
    - `System message translations repository <https://github.com/bcit-ci/CodeIgniter4-translations>`_
    - `Roadmap subforum  <https://forum.codeigniter.com/forum-33.html>`_ for more transparent planning

New core classes:
    - CodeIgniter (bootstrap)
    - Common (shared functions)
    - ComposerScripts (integrate third party tools)
    - Controller (base controller)
    - Model (base model)
    - Entity (entity encapsulation)

Some new, some old & some borrowed packages, all namespaced.

:doc:`See all the changes. </changelogs/v4.0.0-alpha.1>`

.. toctree::
    :hidden:
    :titlesonly:

    next
    v4.0.0-alpha.5
    v4.0.0-alpha.4
    v4.0.0-alpha.3
    v4.0.0-alpha.2
    v4.0.0-alpha.1
