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

$lang['email_must_be_array'] = 'Debes pasar un array al método de validación de email.';
$lang['email_invalid_address'] = 'Correo electrónico no válido: %s';
$lang['email_attachment_missing'] = 'No ha sido posible encontrar este adjunto: %s';
$lang['email_attachment_unreadable'] = 'No ha sido posible abrir este adjunto: %s';
$lang['email_no_from'] = 'No se puede enviar un email sin la cabecera "From".';
$lang['email_no_recipients'] = 'Debes incluir destinatarios: Para, Cc, o Cco';
$lang['email_send_failure_phpmail'] = 'No ha sido posible enviar el correo usando PHP mail(). Tu servidor podría no estar configurado para enviar emails con este método.';
$lang['email_send_failure_sendmail'] = 'No ha sido posible enviar el correo usando PHP Sendmail. Tu servidor podría no estar configurado para enviar emails con este método.';
$lang['email_send_failure_smtp'] = 'No ha sido posible enviar el correo usando PHP SMTP. Tu servidor podría no estar configurado para enviar emails con este método.';
$lang['email_sent'] = 'Tu mensaje ha sido enviado con éxito usando este protocolo: %s';
$lang['email_no_socket'] = 'No ha sido posible abrir un socket a Sendmail. Por favor, comprueba la configuración.';
$lang['email_no_hostname'] = 'No has especificado un servidor SMTP.';
$lang['email_smtp_error'] = 'Se ha encontrado este error SMTP: %s';
$lang['email_no_smtp_unpw'] = 'Error: Debes asignar un usuario y una contraseña SMTP.';
$lang['email_failed_smtp_login'] = 'Fallo al enviar el comando AUTH LOGIN. Error: %s';
$lang['email_smtp_auth_un'] = 'Fallo al autenticar el usuario. Error: %s';
$lang['email_smtp_auth_pw'] = 'Fallo al autenticar la contraseña. Error: %s';
$lang['email_smtp_data_failure'] = 'No ha sido posible enviar los datos: %s';
$lang['email_exit_status'] = 'Código estado al salir: %s';
