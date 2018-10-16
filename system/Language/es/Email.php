<?php

/**
 * Email language strings.
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
	'mustBeArray'          => 'El método de validación del email debe ser pasado en array.',
	'invalidAddress'       => 'Dirección de email inválida: {0}',
	'attachmentMissing'    => 'No se ha podido localizar el adjunto: {0}',
	'attachmentUnreadable' => 'No se ha podido abrir el adjunto: {0}',
	'noFrom'               => 'No se pude enviar un email sin cabecera "Para".',
	'noRecipients'         => 'Debe incluir destinatarios: Para, Cc, or Bcc',
	'sendFailurePHPMail'   => 'Incapaz de enviar email usando PHP mail(). Su servidor puede no estar configurado para enviar correos usando este método.',
	'sendFailureSendmail'  => 'Incapaz de enviar email usando PHP Sendmail. Su servidor puede no estar configurado para enviar correos usando este método.',
	'sendFailureSmtp'      => 'Incapaz de enviar email usando PHP SMTP. Su servidor puede no estar configurado para enviar correos usando este método.',
	'sent'                 => 'Su mensaje ha sido enviado correctamente utilizando el siguiente protocolo: {0, string}',
	'noSocket'             => 'Incapaz de abrir un socket a Sendmail. Compruebe la configuración.',
	'noHostname'           => 'No ha especificado un nombre de host SMTP.',
	'SMTPError'            => 'Se han encontrado los siguientes errores SMTP: {0}',
	'noSMTPAuth'           => 'Error: Debe especificar un usuario y contraseña SMTP.',
	'failedSMTPLogin'      => 'Ha fallado el envío del comando AUTH LOGIN. Error: {0}',
	'SMTPAuthUsername'     => 'Ha fallado la autentificación del usuario. Error: {0}',
	'SMTPAuthPassword'     => 'Ha fallado la autentificación de la contraseña. Error: {0}',
	'SMTPDataFailure'      => 'Incapaz de enviar datos: {0}',
	'exitStatus'           => 'Código de estado de salida: {0}',
];
