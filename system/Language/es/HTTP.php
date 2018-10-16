<?php

/**
 * HTTP language strings.
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
	// CurlRequest
	'missingCurl'                => 'Debe habilitar CURL para usar la clase CURLRequest.',
	'invalidSSLKey'              => 'No se puede establecer la clave SSL. {0} no es un archivo válido.',
	'sslCertNotFound'            => 'No se ha encontrata el certificado SSL en: {0}',
	'curlError'                  => '{0} : {1}',

	// IncomingRequest
	'invalidNegotiationType'     => '{0} no es un tipo válido de negaciación. Debe ser uno de los siguientes: media, charset, encoding, language.',

	// Message
	'invalidHTTPProtocol'        => 'Versión de Protocolo HTNL inválida. Debe ser una de las siguientes: {0}',

	// Negotiate
	'emptySupportedNegotiations' => 'Debe proporcionar un array de valores soportados por todas las negociaciones.',

	// RedirectResponse
	'invalidRoute'               => '{0, string} no es una ruta válida.',

	// DownloadResponse
	'cannotSetBinary'            => 'Cuando se establece la ruta del archivo no puede ser binario.',
	'cannotSetFilepath'          => 'Cuando se establece binario no puede establecerse la ruta del archivo: {0}',
	'notFoundDownloadSource'     => 'No se ha encontrado el cuerpo de descarga de origen.',
	'cannotSetCache'             => 'No se soporta la cache para descarga.',
	'cannotSetStatusCode'        => 'No se soporta el cambio de código de estado para descarga. código: {0}, motivo: {1}',

	// Response
	'missingResponseStatus'      => 'Falta el código de estado en la respuesta HTTP',
	'invalidStatusCode'          => '{0, string} no es un código de estado HTTP válido',
	'unknownStatusCode'          => 'Código de estado HTTP desconocido sin mensaje: {0}',

	// URI
	'cannotParseURI'             => 'Incapazar de parsear URI: {0}',
	'segmentOutOfRange'          => 'Segmento de petición URI fuera del rango: {0}',
	'invalidPort'                => 'Los puertos deben estar entre 0 y 65535. Establecido: {0}',
	'malformedQueryString'       => 'Las cadenas de consulta no deben incluir fragmentos URI.',

	// Page Not Found
	'pageNotFound'               => 'Página no encontrada',
	'emptyController'            => 'No se ha especificado el Controlador.',
	'controllerNotFound'         => 'No se encuentra el Controlador o su método: {0}::{1}',
	'methodNotFound'             => 'No se ha encontrado el método del Controlador: {0}',

	// CSRF
	'disallowedAction'           => 'La acción solicitado no está permitida.',

	// Uploaded file moving
	'alreadyMoved'				 => 'El archivo subido ya ha sido movido.',
	'invalidFile'				 => 'El archivo original no es un archivo válido.',
	'moveFailed'				 => 'No se ha podido mover el archivo {0} a {1} ({2})',
];
