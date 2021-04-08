<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
		bootstrap="system/Test/bootstrap.php"
		backupGlobals="false"
		colors="true"
		convertErrorsToExceptions="true"
		convertNoticesToExceptions="true"
		convertWarningsToExceptions="true"
		stopOnError="false"
		stopOnFailure="false"
		stopOnIncomplete="false"
		stopOnSkipped="false"
		xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/9.3/phpunit.xsd">
	<coverage includeUncoveredFiles="true" processUncoveredFiles="true">
		<include>
			<directory suffix=".php">./app</directory>
		</include>
		<exclude>
			<directory suffix=".php">./app/Views</directory>
			<file>./app/Config/Routes.php</file>
		</exclude>
		<report>
			<clover outputFile="build/logs/clover.xml"/>
			<html outputDirectory="build/logs/html"/>
			<php outputFile="build/logs/coverage.serialized"/>
			<text outputFile="php://stdout" showUncoveredFiles="false"/>
		</report>
	</coverage>
	<testsuites>
		<testsuite name="App">
			<directory>./tests</directory>
		</testsuite>
	</testsuites>
	<logging>
		<testdoxHtml outputFile="build/logs/testdox.html"/>
		<testdoxText outputFile="build/logs/testdox.txt"/>
		<junit outputFile="build/logs/logfile.xml"/>
	</logging>
	<php>
		<server name="app.baseURL" value="http://example.com/"/>
		<!-- Directory containing phpunit.xml -->
		<const name="HOMEPATH" value="./"/>
		<!-- Directory containing the Paths config file -->
		<const name="CONFIGPATH" value="./app/Config/"/>
		<!-- Directory containing the front controller (index.php) -->
		<const name="PUBLICPATH" value="./public/"/>
		<!-- Database configuration -->
		<!-- Uncomment to provide your own database for testing
		<env name="database.tests.hostname" value="localhost"/>
		<env name="database.tests.database" value="tests"/>
		<env name="database.tests.username" value="tests_user"/>
		<env name="database.tests.password" value=""/>
		<env name="database.tests.DBDriver" value="MySQLi"/>
		<env name="database.tests.DBPrefix" value="tests_"/>
		-->
	</php>
</phpunit>
