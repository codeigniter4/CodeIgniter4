Version |version|
====================================================

Release Date: Not released

**RC.1 release of CodeIgniter4**

Enhancements:

- CI3 Email ported to CI4
- Encryption (basic) added
- Migrations refactored for more wholistic functionality (BC)
- added convert() to ImageHandlerInterface
- disabled debug toolbar for downloads

App changes:

- Config/Email and Encryption added
- Config/Migration modified, and has different settings
- Controllers/Home fixed, removing unnecessary model reference

The list of changed files follows, with PR numbers shown.

- admin/

- app/
	- Config/
		- Email #2092
		- Encryption #2135
		- Migrations #2065
	- Controllers/
		- BaseController #2046
		- Home #2145

- public/

- system/
	- API/
		- ResponseTrait #2131
	- Autoloader/
		- Autoloader #2149
		- FileLocator #2149
	- Cache/Handlders/
		- RedisHandler #2144
	- Commands/Database/
		- CreateMigration #2065
		- MigrationCurrent #2065
		- MigrationLatest #2065
		- MigrationRefresh #2065
		- MigrationRollback #2065
	- Config/
		- BaseConfig #2082
		- Services #2135, 2092
	- Database/
		- BaseBuilder #2127, 2090, 2142
		- MigrationRunner #2065
	- Debug/
		- Toolbar #2118
	- Email/
		- Email #2092
	- Encryption/
		- EncrypterInterface #2135
		- Encryption #2135
		- Exceptions/EncryptionException #2135
		- Handlers/
			- BaseHandler #2135
			- OpenSSLHandler #2135
	- Exceptions/
		- ConfigException #2065		
	- Filters/
		- DebugToolbar #2118
	- Helpers/
		- inflector_helper #2065
	- HTTP/
		- DownloadResponse #2129
		- Files/UploadedFile #2128
	- Images/
		- Handlers/BaseHandler #2113, 2150
		- BImageHandlerInterface #2113
	- Language/en/
		- Email #2092
		- Encryption #2135
		- Migrations #2065
	- Session/Handlers/
		- RedisHandler #2125
	- CodeIgniter #2126
	- Common #2109
	- Model #2090


- tests/system/
	- API/
		- ResponseTraitTest #2131
	- Database/
		- Builder/GetTest #2142
		- Live/ModelTest #2090
		- Migrations/MigrationRunnerTest #2065
	- Encryption/
		- EncryptionTest #2135
		- OpenSSLHandlerTest #2135
	- Helpers/
		- InflectorHelperTest #2065
	- HTTP/
		DownloadResponseTest #2129
	- Images/
		- GDHandlerTest #2113

- user_guide_src/
	- dbmgmt/
		- migrations #2065, 2132, 2136
	- helpers/
		- inflector_helper #2065
	- libraries/
		- email #2092
		- encryption #2135
		- images #2113
	- outgoing/
		- api_responses #2131
		- localization #2134
		- response #2129

PRs merged:
-----------

- #2150 New logic for Image->fit()
- #2149 listNamespaceFiles: Ensure trailing slash
- #2145 Remove UserModel reference from Home controller
- #2144 Update Redis legacy function
- #2142 Fixing BuilderBase resetting when getting the SQL
- #2136 Migrations user guide fixes
- #2135 Encryption
- #2134 Fix localization writeup
- #2132 Update migration User Guide
- #2131 Added No Content response to API\ResponseTrait
- #2129 Add setFileName() to DownloadResponse
- #2128 guessExtension fallback to clientExtension
- #2127 Update limit function since $offset is nullable
- #2126 Limit storePreviousURL to certain requests
- #2125 Updated redis session handler to support redis 5.0.x
- #2118 Disabled Toolbar on downloads
- #2113 Add Image->convert()
- #2109 Fix typo in checking if exists db_connect()
- #2092 Original email port
- #2090 Fix prevent soft delete all without conditions set
- #2082 Update BaseConfig.php
- #2065 Migration updates for more wholistic functionality
- #2046 clean base controller code
