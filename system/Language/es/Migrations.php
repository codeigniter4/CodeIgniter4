<?php

/**
 * Migration language strings.
 *
 * @package      CodeIgniter
 * @author       Fernán Castro Asensio
 * @license      https://opensource.org/licenses/MIT	MIT License
 * @link         https://codeigniter.com
 * @since        Version 4.0.0
 * @filesource
 * 
 * @codeCoverageIgnore
 */
return [
	// Migration Runner
	'missingTable'    => 'Debe establecer la tabla de migrado.',
	'invalidType'     => 'Numeración de tipo de migrado inválida: {0}',
	'disabled'        => 'Las migracines se han cargado pero están deshabilitadas o configuradas incorrectamente.',
	'notFound'        => 'Archivo de migración no encontrado: ',
	'empty'           => 'No se han encontrado archivos de migración',
	'gap'             => 'Hay un hueco en la secuencia de migración cerca del número de versión: ',
	'classNotFound'   => 'La clase de migración "%s" no se ha encontrado.',
	'missingMethod'   => 'La clase de migración no ha encontrado el método "%s".',

	// Migration Command
	'migHelpLatest'   => "\t\tMigra la base de datos a la última versión disponible.",
	'migHelpCurrent'  => "\t\tMigra la base de datos a la versión marcado como 'current' en configuración.",
	'migHelpVersion'  => "\tMigra la base de datos a la versión {v}.",
	'migHelpRollback' => "\tEjecuta todas las migraciones 'down' a la versión 0.",
	'migHelpRefresh'  => "\t\tDesinstala y reejecuta todas las migraciones para refrescar la base de datos.",
	'migHelpSeed'     => "\tEjecuta la semilla de nombre [name].",
	'migCreate'       => "\tCrea una nueva migración llamada [name]",
	'nameMigration'   => "Nombre el archivo de migración",
	'badCreateName'   => 'Debe establecer un nombre al archivo de migración.',
	'writeError'      => 'Error al intentar crear el archivo.',

	'toLatest'          => 'Migrando a la última versión...',
	'migInvalidVersion' => 'Número de versión incorrecta.',
	'toVersionPH'       => 'Migrando a la versión %s...',
	'toVersion'         => 'Migrando a la versión actual...',
	'rollingBack'       => "Deshaciendo todas las migraciones...",
	'noneFound'         => 'No se han encontrado migraciones.',
	'on'                => 'Migrado en: ',
	'migSeeder'         => 'Nombre de semilla',
	'migMissingSeeder'  => 'Debe indicar un nombre de semilla.',
	'removed'           => 'Desinstalando: ',
	'added'             => 'Ejecutando: ',

	'version'           => 'Versión',
	'filename'          => 'Nombre de archivo',
];
