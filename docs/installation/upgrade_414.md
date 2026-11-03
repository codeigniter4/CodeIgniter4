# Upgrading from 4.1.3 to 4.1.4

Please refer to the upgrade instructions corresponding to your installation method.

- [Composer Installation App Starter Upgrading](installing_composer.md#upgrading)
- [Composer Installation Adding CodeIgniter4 to an Existing Project Upgrading](installing_composer.md#upgrading_1)
- [Manual Installation Upgrading](installing_manual.md#upgrading)

This release focuses on code style. All changes (except those noted below) are cosmetic to bring the code in line with the new [CodeIgniter Coding Standard](https://github.com/CodeIgniter/coding-standard) (based on PSR-12).

## Breaking Changes

### Method Scope

The following methods were changed from `public` to `protected` to match their parent class methods and better align with their uses. If you relied on any of these methods being public (highly unlikely), adjust your code accordingly:

- `CodeIgniter\Database\MySQLi\Connection::execute()`
- `CodeIgniter\Database\MySQLi\Connection::_fieldData()`
- `CodeIgniter\Database\MySQLi\Connection::_indexData()`
- `CodeIgniter\Database\MySQLi\Connection::_foreignKeyData()`
- `CodeIgniter\Database\Postgre\Builder::_like_statement()`
- `CodeIgniter\Database\Postgre\Connection::execute()`
- `CodeIgniter\Database\Postgre\Connection::_fieldData()`
- `CodeIgniter\Database\Postgre\Connection::_indexData()`
- `CodeIgniter\Database\Postgre\Connection::_foreignKeyData()`
- `CodeIgniter\Database\SQLSRV\Connection::execute()`
- `CodeIgniter\Database\SQLSRV\Connection::_fieldData()`
- `CodeIgniter\Database\SQLSRV\Connection::_indexData()`
- `CodeIgniter\Database\SQLSRV\Connection::_foreignKeyData()`
- `CodeIgniter\Database\SQLite3\Connection::execute()`
- `CodeIgniter\Database\SQLite3\Connection::_fieldData()`
- `CodeIgniter\Database\SQLite3\Connection::_indexData()`
- `CodeIgniter\Database\SQLite3\Connection::_foreignKeyData()`
- `CodeIgniter\Images\Handlers\GDHandler::_flatten()`
- `CodeIgniter\Images\Handlers\GDHandler::_flip()`
- `CodeIgniter\Images\Handlers\ImageMagickHandler::_flatten()`
- `CodeIgniter\Images\Handlers\ImageMagickHandler::_flip()`
- `CodeIgniter\Test\Mock\MockIncomingRequest::detectURI()`
- `CodeIgniter\Test\Mock\MockSecurity::sendCookie()`

## Project Files

All files in the project space were reformatted with the new coding style. This will not affect existing code but you may want to apply the updated coding style to your own projects to keep them in line with the framework's version of these files.
