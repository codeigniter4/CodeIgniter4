<?php
/**
 * System messages translation for CodeIgniter(tm)
 *
 * @author	CodeIgniter community
 * @author	Iban Eguia
 * @copyright	Copyright (c) 2014 - 2015, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	http://codeigniter.com
 */
defined('BASEPATH') OR exit('No direct script access allowed');

$lang['db_invalid_connection_str'] = 'No ha sido posible determinar la configuración de la base de datos basada en la cadena de conexión introducida.';
$lang['db_unable_to_connect'] = 'No ha sido posible conectar al servidor de la base de datos haciendo uso de la configuración proporcionada.';
$lang['db_unable_to_select'] = 'No ha sido posible seleccionar la base de datos especificada: %s';
$lang['db_unable_to_create'] = 'No ha sido posible crear la base de datos especificada: %s';
$lang['db_invalid_query'] = 'La consulta introducida no es válida.';
$lang['db_must_set_table'] = 'Debes fijar la tabla de la base de datos que será usada con tu consulta.';
$lang['db_must_use_set'] = 'Debes usar el método "set" para actualizar una entrada.';
$lang['db_must_use_index'] = 'Debes especificar un índice para emparejar las actualizaciones masivas.';
$lang['db_batch_missing_index'] = 'A una o más filas introducidas para la actualización masiva le falta el índice especificado.';
$lang['db_must_use_where'] = 'Las actualizaciones no están permitidas a no ser que contengan la cláusula "where".';
$lang['db_del_must_use_where'] = 'Los borrados no están permitidos a no ser que contengan la cláusula "where" o "like".';
$lang['db_field_param_missing'] = 'Para obtener campos se requiere el nombre de la tabla como parámetro.';
$lang['db_unsupported_function'] = 'Esta característica no está disponible para la base de datos que estás usando.';
$lang['db_transaction_failure'] = 'Fallo de transacción: Rollback realizado.';
$lang['db_unable_to_drop'] = 'No ha sido posible borrar la base de datos especificada.';
$lang['db_unsupported_feature'] = 'Característica no soportada de la plataforma de bases de datos que estás usando.';
$lang['db_unsupported_compression'] = 'El formato de compresión elegido no está soportado por el servidor.';
$lang['db_filepath_error'] = 'No ha sido posible escribir los datos en la ruta de archivo especificada.';
$lang['db_invalid_cache_path'] = 'La ruta de la caché introducida no es válida o escribible.';
$lang['db_table_name_required'] = 'Se requiere un nombre de tabla para esa operación.';
$lang['db_column_name_required'] = 'Se requiere un nombre de columna para esa operación.';
$lang['db_column_definition_required'] = 'Se requiere una definición de columna para esa operación.';
$lang['db_unable_to_set_charset'] = 'No ha sido posible fijar en la conexión el conjunto de caracteres: %s';
$lang['db_error_heading'] = 'Ha ocurrido un error con la base de datos';
