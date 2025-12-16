<?php
/**
 * Sistema de Envío de Emails para Notificaciones
 * Utiliza PHPMailer para enviar notificaciones por correo electrónico
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once 'config_email.php';
require_once 'conexion.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Envía un email de notificación
 *
 * @param string $destinatario_email Email del destinatario
 * @param string $destinatario_nombre Nombre del destinatario
 * @param string $asunto Asunto del email
 * @param string $cuerpo_html Cuerpo del email en HTML
 * @param string $cuerpo_texto Cuerpo del email en texto plano (opcional)
 * @return bool True si se envió correctamente, False en caso contrario
 */
function enviarEmail($destinatario_email, $destinatario_nombre, $asunto, $cuerpo_html, $cuerpo_texto = '') {
    // Verificar si el envío de emails está habilitado
    if (!EMAIL_ENABLED || !emailConfiguracionValida()) {
        error_log("Envío de emails deshabilitado o configuración inválida");
        return false;
    }

    // Validar email del destinatario
    if (empty($destinatario_email) || !filter_var($destinatario_email, FILTER_VALIDATE_EMAIL)) {
        error_log("Email inválido: $destinatario_email");
        return false;
    }

    try {
        $mail = new PHPMailer(true);

        // Configuración del servidor
        if (EMAIL_MAILER === 'smtp') {
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = SMTP_AUTH;
            $mail->Username   = SMTP_USERNAME;
            $mail->Password   = SMTP_PASSWORD;
            $mail->SMTPSecure = SMTP_SECURE;
            $mail->Port       = SMTP_PORT;
            $mail->Timeout    = SMTP_TIMEOUT;

            // Configuración de depuración
            $mail->SMTPDebug = SMTP_DEBUG;

            // Permitir certificados autofirmados (solo desarrollo)
            if (SMTP_ALLOW_SELF_SIGNED) {
                $mail->SMTPOptions = array(
                    'ssl' => array(
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    )
                );
            }
        } elseif (EMAIL_MAILER === 'sendmail') {
            $mail->isSendmail();
        } else {
            $mail->isMail();
        }

        // Configuración del remitente
        $mail->setFrom(EMAIL_FROM_ADDRESS, EMAIL_FROM_NAME);
        $mail->addAddress($destinatario_email, $destinatario_nombre);
        $mail->addReplyTo(EMAIL_FROM_ADDRESS, EMAIL_FROM_NAME);

        // Contenido del email
        $mail->isHTML(true);
        $mail->CharSet = EMAIL_CHARSET;
        $mail->Subject = $asunto;
        $mail->Body    = $cuerpo_html;
        $mail->AltBody = !empty($cuerpo_texto) ? $cuerpo_texto : strip_tags($cuerpo_html);

        // Enviar
        $resultado = $mail->send();

        if ($resultado) {
            error_log("Email enviado exitosamente a: $destinatario_email");
        }

        return $resultado;

    } catch (Exception $e) {
        error_log("Error al enviar email: {$mail->ErrorInfo}");
        return false;
    }
}

/**
 * Envía notificación por email a un usuario
 *
 * @param int $usuario_id ID del usuario destinatario
 * @param string $tipo_evento Tipo de evento
 * @param string $mensaje Mensaje de la notificación
 * @param array $datos_documento Datos del documento (nombre, codigo, etc.)
 * @return bool True si se envió correctamente
 */
function enviarNotificacionEmail($usuario_id, $tipo_evento, $mensaje, $datos_documento = array()) {
    global $conn;

    // Obtener información del usuario
    $sql = "SELECT nombre, email, recibir_emails FROM Usuarios WHERE id = ? AND estado = 'Activo'";
    $stmt = sqlsrv_query($conn, $sql, array($usuario_id));

    if (!$stmt) {
        error_log("Error al obtener usuario para email: " . print_r(sqlsrv_errors(), true));
        return false;
    }

    $usuario = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

    if (!$usuario) {
        error_log("Usuario no encontrado o inactivo: $usuario_id");
        return false;
    }

    // Verificar si el usuario quiere recibir emails
    if (isset($usuario['recibir_emails']) && !$usuario['recibir_emails']) {
        error_log("Usuario $usuario_id no desea recibir emails");
        return false;
    }

    // Verificar que el usuario tenga email
    if (empty($usuario['email'])) {
        error_log("Usuario $usuario_id no tiene email configurado");
        return false;
    }

    // Preparar variables para el asunto y contenido
    $variables = array(
        'documento' => $datos_documento['nombre'] ?? 'Documento',
        'codigo' => $datos_documento['codigo'] ?? '',
        'estado' => $datos_documento['estado'] ?? '',
        'usuario' => $usuario['nombre']
    );

    // Generar asunto
    $asunto = obtenerAsuntoEmail($tipo_evento, $variables);

    // Generar cuerpo HTML
    $cuerpo_html = generarEmailHTML($tipo_evento, $mensaje, $datos_documento, $usuario['nombre']);

    // Generar cuerpo de texto plano
    $cuerpo_texto = generarEmailTexto($tipo_evento, $mensaje, $datos_documento, $usuario['nombre']);

    // Enviar email
    return enviarEmail($usuario['email'], $usuario['nombre'], $asunto, $cuerpo_html, $cuerpo_texto);
}

/**
 * Genera el cuerpo HTML del email
 *
 * @param string $tipo_evento Tipo de evento
 * @param string $mensaje Mensaje de la notificación
 * @param array $datos_documento Datos del documento
 * @param string $nombre_usuario Nombre del usuario destinatario
 * @return string HTML del email
 */
function generarEmailHTML($tipo_evento, $mensaje, $datos_documento, $nombre_usuario) {
    // Obtener icono y color del evento
    require_once 'notificaciones.php';
    $icono = obtenerIconoEvento($tipo_evento);
    $color = obtenerColorEvento($tipo_evento);

    // Preparar datos del documento
    $doc_nombre = $datos_documento['nombre'] ?? 'Sin nombre';
    $doc_codigo = $datos_documento['codigo'] ?? '';
    $doc_id = $datos_documento['id'] ?? null;

    // URL del documento
    $url_documento = '';
    if ($doc_id) {
        $url_documento = SYSTEM_BASE_URL . '/ver_documento.php?id=' . $doc_id;
    }

    // Generar HTML
    $html = '
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificación - Sistema de Gestión Documental</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
            background-color: #f5f5f5;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .email-header {
            background: linear-gradient(135deg, #027be3 0%, #2196f3 100%);
            color: #ffffff;
            padding: 30px 20px;
            text-align: center;
        }
        .email-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .email-body {
            padding: 30px 20px;
        }
        .notification-card {
            background-color: #f8f9fa;
            border-left: 4px solid ' . $color . ';
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .notification-icon {
            font-size: 32px;
            margin-bottom: 10px;
        }
        .notification-type {
            background-color: ' . $color . ';
            color: #ffffff;
            padding: 5px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        .notification-message {
            font-size: 16px;
            color: #333333;
            line-height: 1.6;
            margin: 15px 0;
        }
        .document-info {
            background-color: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            padding: 15px;
            margin: 20px 0;
        }
        .document-info-item {
            margin: 8px 0;
            font-size: 14px;
            color: #555555;
        }
        .document-info-label {
            font-weight: 600;
            color: #333333;
        }
        .btn-primary {
            display: inline-block;
            background-color: #027be3;
            color: #ffffff;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            margin: 20px 0;
        }
        .email-footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666666;
            border-top: 1px solid #e0e0e0;
        }
        .email-footer p {
            margin: 5px 0;
        }
        .greeting {
            font-size: 16px;
            color: #333333;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>🔔 Sistema de Gestión Documental</h1>
        </div>

        <div class="email-body">
            <p class="greeting">Hola <strong>' . htmlspecialchars($nombre_usuario) . '</strong>,</p>

            <div class="notification-card">
                <div class="notification-icon">' . $icono . '</div>
                <span class="notification-type">' . htmlspecialchars($tipo_evento) . '</span>
                <div class="notification-message">
                    ' . htmlspecialchars($mensaje) . '
                </div>
            </div>';

    // Información del documento
    if (!empty($doc_nombre)) {
        $html .= '
            <div class="document-info">
                <div class="document-info-item">
                    <span class="document-info-label">📄 Documento:</span>
                    ' . htmlspecialchars($doc_nombre) . '
                </div>';

        if (!empty($doc_codigo)) {
            $html .= '
                <div class="document-info-item">
                    <span class="document-info-label">🔖 Código:</span>
                    ' . htmlspecialchars($doc_codigo) . '
                </div>';
        }

        if (isset($datos_documento['estado'])) {
            $html .= '
                <div class="document-info-item">
                    <span class="document-info-label">📊 Estado:</span>
                    ' . htmlspecialchars($datos_documento['estado']) . '
                </div>';
        }

        if (isset($datos_documento['responsable'])) {
            $html .= '
                <div class="document-info-item">
                    <span class="document-info-label">👤 Responsable:</span>
                    ' . htmlspecialchars($datos_documento['responsable']) . '
                </div>';
        }

        $html .= '
            </div>';
    }

    // Botón para ver documento
    if (!empty($url_documento)) {
        $html .= '
            <center>
                <a href="' . htmlspecialchars($url_documento) . '" class="btn-primary">
                    Ver Documento
                </a>
            </center>';
    }

    $html .= '
            <p style="margin-top: 30px; font-size: 14px; color: #666666;">
                Esta es una notificación automática del Sistema de Gestión Documental.
            </p>
        </div>

        <div class="email-footer">
            <p><strong>Sistema de Gestión Documental</strong></p>
            <p>Este correo fue enviado automáticamente. Por favor, no responder.</p>
            <p style="margin-top: 10px;">
                <a href="' . SYSTEM_BASE_URL . '/ver_notificaciones.php" style="color: #027be3; text-decoration: none;">
                    Ver todas mis notificaciones
                </a>
            </p>
        </div>
    </div>
</body>
</html>';

    return $html;
}

/**
 * Genera el cuerpo de texto plano del email
 *
 * @param string $tipo_evento Tipo de evento
 * @param string $mensaje Mensaje de la notificación
 * @param array $datos_documento Datos del documento
 * @param string $nombre_usuario Nombre del usuario destinatario
 * @return string Texto plano del email
 */
function generarEmailTexto($tipo_evento, $mensaje, $datos_documento, $nombre_usuario) {
    $texto = "SISTEMA DE GESTIÓN DOCUMENTAL\n";
    $texto .= "==============================\n\n";
    $texto .= "Hola $nombre_usuario,\n\n";
    $texto .= "TIPO DE NOTIFICACIÓN: $tipo_evento\n\n";
    $texto .= "$mensaje\n\n";

    if (!empty($datos_documento['nombre'])) {
        $texto .= "INFORMACIÓN DEL DOCUMENTO:\n";
        $texto .= "- Documento: " . $datos_documento['nombre'] . "\n";

        if (!empty($datos_documento['codigo'])) {
            $texto .= "- Código: " . $datos_documento['codigo'] . "\n";
        }

        if (isset($datos_documento['estado'])) {
            $texto .= "- Estado: " . $datos_documento['estado'] . "\n";
        }

        if (isset($datos_documento['responsable'])) {
            $texto .= "- Responsable: " . $datos_documento['responsable'] . "\n";
        }

        $texto .= "\n";
    }

    if (isset($datos_documento['id'])) {
        $url_documento = SYSTEM_BASE_URL . '/ver_documento.php?id=' . $datos_documento['id'];
        $texto .= "Ver documento: $url_documento\n\n";
    }

    $texto .= "Esta es una notificación automática del Sistema de Gestión Documental.\n";
    $texto .= "Por favor, no responder a este correo.\n\n";
    $texto .= "Ver todas mis notificaciones: " . SYSTEM_BASE_URL . "/ver_notificaciones.php\n";

    return $texto;
}

/**
 * Envía emails de prueba para verificar configuración
 *
 * @param string $email_destino Email de prueba
 * @return array Resultado de la prueba
 */
function probarConfiguracionEmail($email_destino) {
    $resultado = array(
        'success' => false,
        'mensaje' => '',
        'detalles' => array()
    );

    // Verificar configuración
    if (!emailConfiguracionValida()) {
        $resultado['mensaje'] = 'Configuración de email inválida o incompleta';
        $resultado['detalles'][] = 'Revisar config_email.php';
        return $resultado;
    }

    $resultado['detalles'][] = 'Configuración válida';

    // Preparar email de prueba
    $asunto = '[SGD] Email de Prueba - Sistema de Gestión Documental';
    $cuerpo_html = generarEmailHTML(
        'Creacion',
        'Este es un email de prueba para verificar la configuración del servidor SMTP.',
        array(
            'nombre' => 'Documento de Prueba',
            'codigo' => 'TEST-001',
            'estado' => 'Prueba'
        ),
        'Usuario de Prueba'
    );

    // Intentar enviar
    $enviado = enviarEmail($email_destino, 'Usuario de Prueba', $asunto, $cuerpo_html);

    if ($enviado) {
        $resultado['success'] = true;
        $resultado['mensaje'] = 'Email de prueba enviado correctamente a ' . $email_destino;
        $resultado['detalles'][] = 'Verificar la bandeja de entrada';
    } else {
        $resultado['mensaje'] = 'Error al enviar email de prueba';
        $resultado['detalles'][] = 'Revisar logs de error para más detalles';
        $resultado['detalles'][] = 'Verificar credenciales SMTP';
        $resultado['detalles'][] = 'Verificar firewall y puertos';
    }

    return $resultado;
}
?>
