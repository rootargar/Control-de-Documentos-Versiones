<?php
/**
 * Configuración de Email para Sistema de Notificaciones
 *
 * IMPORTANTE: Configurar estos valores según tu servidor SMTP
 */

// ========================================
// CONFIGURACIÓN SMTP
// ========================================

// Tipo de servidor de correo
// Opciones: 'smtp', 'sendmail', 'mail'
define('EMAIL_MAILER', 'smtp');

// Configuración SMTP
define('SMTP_HOST', 'smtp.gmail.com');              // Servidor SMTP (ej: smtp.gmail.com, smtp.office365.com)
define('SMTP_PORT', 587);                            // Puerto SMTP (587 para TLS, 465 para SSL, 25 para sin encriptación)
define('SMTP_SECURE', 'tls');                        // Encriptación: 'tls', 'ssl', o '' para ninguna
define('SMTP_AUTH', true);                           // Requiere autenticación

// Credenciales SMTP
define('SMTP_USERNAME', 'tu_email@gmail.com');       // Usuario SMTP (generalmente tu email)
define('SMTP_PASSWORD', 'tu_contraseña_o_app_password'); // Contraseña SMTP o App Password

// ========================================
// CONFIGURACIÓN DEL REMITENTE
// ========================================

define('EMAIL_FROM_ADDRESS', 'noreply@tusistema.com'); // Email del remitente
define('EMAIL_FROM_NAME', 'Sistema Gestión Documental'); // Nombre del remitente

// ========================================
// CONFIGURACIÓN GENERAL
// ========================================

// Activar/desactivar envío de emails
define('EMAIL_ENABLED', true);                       // Cambiar a false para deshabilitar emails

// Modo de depuración SMTP
// 0 = Sin debug, 1 = Mensajes del cliente, 2 = Mensajes cliente y servidor
define('SMTP_DEBUG', 0);

// Tiempo de espera para conexión SMTP (segundos)
define('SMTP_TIMEOUT', 30);

// Charset para los emails
define('EMAIL_CHARSET', 'UTF-8');

// ========================================
// CONFIGURACIÓN DE NOTIFICACIONES
// ========================================

// Enviar email inmediatamente o solo guardar en BD
define('EMAIL_SEND_IMMEDIATE', true);                // true = enviar inmediatamente, false = solo guardar

// Incluir logo en los emails (URL completa)
define('EMAIL_LOGO_URL', 'https://tusistema.com/logo.png');

// URL base del sistema (para enlaces en emails)
define('SYSTEM_BASE_URL', 'http://localhost/Control-de-Documentos-Versiones');

// ========================================
// PLANTILLAS DE ASUNTO POR TIPO DE EVENTO
// ========================================

$EMAIL_SUBJECTS = array(
    'Creacion' => '[SGD] Nuevo documento creado: {documento}',
    'Cambio Estado' => '[SGD] Documento {estado}: {documento}',
    'Nueva Version' => '[SGD] Nueva versión disponible: {documento}',
    'Proximo Vencimiento' => '[SGD] ⚠️ Documento próximo a vencer: {documento}',
    'Asignacion' => '[SGD] Documento asignado: {documento}',
    'Comentario' => '[SGD] Nuevo comentario en: {documento}'
);

// ========================================
// CONFIGURACIÓN AVANZADA
// ========================================

// Permitir certificados SSL autofirmados (solo para desarrollo)
define('SMTP_ALLOW_SELF_SIGNED', false);

// Usar cola de emails (para envíos masivos)
define('USE_EMAIL_QUEUE', false);                    // Requiere implementación de cola

// Máximo de emails por hora (0 = ilimitado)
define('MAX_EMAILS_PER_HOUR', 100);

// ========================================
// EJEMPLOS DE CONFIGURACIÓN PARA DIFERENTES PROVEEDORES
// ========================================

/*
// GMAIL
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
define('SMTP_USERNAME', 'tu_email@gmail.com');
define('SMTP_PASSWORD', 'tu_app_password'); // Generar en: https://myaccount.google.com/apppasswords

// OUTLOOK / Office 365
define('SMTP_HOST', 'smtp.office365.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
define('SMTP_USERNAME', 'tu_email@outlook.com');
define('SMTP_PASSWORD', 'tu_contraseña');

// YAHOO
define('SMTP_HOST', 'smtp.mail.yahoo.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
define('SMTP_USERNAME', 'tu_email@yahoo.com');
define('SMTP_PASSWORD', 'tu_app_password');

// SMTP LOCAL / cPanel
define('SMTP_HOST', 'mail.tudominio.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
define('SMTP_USERNAME', 'usuario@tudominio.com');
define('SMTP_PASSWORD', 'tu_contraseña');

// SendGrid
define('SMTP_HOST', 'smtp.sendgrid.net');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
define('SMTP_USERNAME', 'apikey');
define('SMTP_PASSWORD', 'tu_api_key');

// Amazon SES
define('SMTP_HOST', 'email-smtp.us-east-1.amazonaws.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
define('SMTP_USERNAME', 'tu_smtp_username');
define('SMTP_PASSWORD', 'tu_smtp_password');
*/

// ========================================
// VALIDACIÓN DE CONFIGURACIÓN
// ========================================

/**
 * Verifica si la configuración de email está completa
 * @return bool
 */
function emailConfiguracionValida() {
    if (!EMAIL_ENABLED) {
        return false;
    }

    if (EMAIL_MAILER === 'smtp') {
        if (empty(SMTP_HOST) || empty(SMTP_USERNAME) || empty(SMTP_PASSWORD)) {
            error_log("Configuración de email incompleta. Revisar config_email.php");
            return false;
        }
    }

    return true;
}

/**
 * Obtiene el asunto del email según el tipo de evento
 * @param string $tipo_evento Tipo de evento
 * @param array $variables Variables para reemplazar en el asunto
 * @return string
 */
function obtenerAsuntoEmail($tipo_evento, $variables = array()) {
    global $EMAIL_SUBJECTS;

    $asunto = isset($EMAIL_SUBJECTS[$tipo_evento])
              ? $EMAIL_SUBJECTS[$tipo_evento]
              : '[SGD] Notificación: {documento}';

    // Reemplazar variables
    foreach ($variables as $key => $value) {
        $asunto = str_replace('{' . $key . '}', $value, $asunto);
    }

    return $asunto;
}
?>
