<?php
/**
 * Página de Prueba de Configuración de Email
 * Solo accesible para Administradores
 */

require_once 'verificar_login.php';
require_once 'conexion.php';
require_once 'email_notificaciones.php';

// Verificar que el usuario esté autenticado
verificarLogin();

// Solo administradores pueden acceder
requiereAdministrador();

$mensaje = '';
$tipo_mensaje = '';
$resultado_prueba = null;

// Procesar envío de prueba
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email_destino = trim($_POST['email_destino'] ?? '');

    if (empty($email_destino)) {
        $mensaje = 'Debe ingresar un email de destino';
        $tipo_mensaje = 'error';
    } elseif (!filter_var($email_destino, FILTER_VALIDATE_EMAIL)) {
        $mensaje = 'El email ingresado no es válido';
        $tipo_mensaje = 'error';
    } else {
        $resultado_prueba = probarConfiguracionEmail($email_destino);
        if ($resultado_prueba['success']) {
            $tipo_mensaje = 'success';
        } else {
            $tipo_mensaje = 'error';
        }
        $mensaje = $resultado_prueba['mensaje'];
    }
}

// Verificar estado de configuración
$config_valida = emailConfiguracionValida();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba de Configuración Email - Sistema de Gestión Documental</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }

        .navbar {
            background: linear-gradient(135deg, #027be3 0%, #2196f3 100%);
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .navbar h1 {
            font-size: 24px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-secondary {
            background: white;
            color: #027be3;
        }

        .btn-secondary:hover {
            background: #f0f0f0;
        }

        .btn-primary {
            background: #027be3;
            color: white;
        }

        .btn-primary:hover {
            background: #0056b3;
        }

        .container {
            max-width: 900px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .card {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .card h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 22px;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }

        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        .config-status {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .status-icon {
            font-size: 24px;
        }

        .config-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #e0e0e0;
        }

        .config-item:last-child {
            border-bottom: none;
        }

        .config-label {
            font-weight: 600;
            color: #333;
        }

        .config-value {
            color: #666;
            font-family: monospace;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        .form-control:focus {
            outline: none;
            border-color: #027be3;
        }

        .resultado-detalle {
            background: #f8f9fa;
            border-left: 4px solid #027be3;
            padding: 15px;
            margin-top: 15px;
        }

        .resultado-detalle ul {
            list-style: none;
            padding: 0;
        }

        .resultado-detalle li {
            padding: 5px 0;
        }

        .resultado-detalle li:before {
            content: "• ";
            color: #027be3;
            font-weight: bold;
        }

        .instructions {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            margin-bottom: 20px;
        }

        .instructions h3 {
            margin-bottom: 10px;
            color: #1976d2;
            font-size: 16px;
        }

        .instructions ol {
            margin-left: 20px;
        }

        .instructions li {
            margin: 8px 0;
            line-height: 1.6;
        }

        code {
            background: #f5f5f5;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>📧 Prueba de Configuración Email</h1>
        <a href="index.php" class="btn btn-secondary">← Volver al Inicio</a>
    </div>

    <div class="container">
        <!-- Mensaje de resultado -->
        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-<?php echo $tipo_mensaje; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
                <?php if ($resultado_prueba && !empty($resultado_prueba['detalles'])): ?>
                    <div class="resultado-detalle">
                        <strong>Detalles:</strong>
                        <ul>
                            <?php foreach ($resultado_prueba['detalles'] as $detalle): ?>
                                <li><?php echo htmlspecialchars($detalle); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Estado de configuración -->
        <div class="card">
            <h2>Estado de Configuración</h2>

            <div class="config-status">
                <span class="status-icon"><?php echo $config_valida ? '✅' : '❌'; ?></span>
                <div>
                    <strong><?php echo $config_valida ? 'Configuración Válida' : 'Configuración Incompleta'; ?></strong>
                    <p style="margin: 5px 0 0 0; font-size: 14px; color: #666;">
                        <?php echo $config_valida ? 'El sistema está listo para enviar emails' : 'Debes configurar config_email.php antes de enviar emails'; ?>
                    </p>
                </div>
            </div>

            <div class="config-item">
                <span class="config-label">Email habilitado:</span>
                <span class="config-value"><?php echo EMAIL_ENABLED ? 'Sí' : 'No'; ?></span>
            </div>

            <div class="config-item">
                <span class="config-label">Tipo de mailer:</span>
                <span class="config-value"><?php echo EMAIL_MAILER; ?></span>
            </div>

            <?php if (EMAIL_MAILER === 'smtp'): ?>
            <div class="config-item">
                <span class="config-label">Servidor SMTP:</span>
                <span class="config-value"><?php echo SMTP_HOST; ?>:<?php echo SMTP_PORT; ?></span>
            </div>

            <div class="config-item">
                <span class="config-label">Seguridad:</span>
                <span class="config-value"><?php echo SMTP_SECURE ?: 'Ninguna'; ?></span>
            </div>

            <div class="config-item">
                <span class="config-label">Usuario SMTP:</span>
                <span class="config-value"><?php echo SMTP_USERNAME; ?></span>
            </div>

            <div class="config-item">
                <span class="config-label">Contraseña configurada:</span>
                <span class="config-value"><?php echo !empty(SMTP_PASSWORD) ? 'Sí' : 'No'; ?></span>
            </div>
            <?php endif; ?>

            <div class="config-item">
                <span class="config-label">Email remitente:</span>
                <span class="config-value"><?php echo EMAIL_FROM_ADDRESS; ?></span>
            </div>

            <div class="config-item">
                <span class="config-label">Nombre remitente:</span>
                <span class="config-value"><?php echo EMAIL_FROM_NAME; ?></span>
            </div>

            <div class="config-item">
                <span class="config-label">Envío inmediato:</span>
                <span class="config-value"><?php echo EMAIL_SEND_IMMEDIATE ? 'Sí' : 'No'; ?></span>
            </div>
        </div>

        <!-- Instrucciones -->
        <div class="instructions">
            <h3>📋 Instrucciones de Configuración</h3>
            <ol>
                <li>Editar el archivo <code>config_email.php</code></li>
                <li>Configurar las credenciales SMTP de tu servidor de correo</li>
                <li>Para Gmail, generar una "Contraseña de Aplicación" en <a href="https://myaccount.google.com/apppasswords" target="_blank">https://myaccount.google.com/apppasswords</a></li>
                <li>Cambiar <code>EMAIL_ENABLED</code> a <code>true</code></li>
                <li>Guardar y volver a esta página para probar</li>
            </ol>
        </div>

        <!-- Formulario de prueba -->
        <div class="card">
            <h2>Enviar Email de Prueba</h2>

            <?php if (!$config_valida): ?>
                <div class="alert alert-warning">
                    ⚠️ La configuración de email no está completa. Configura <code>config_email.php</code> antes de continuar.
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="email_destino">Email de Destino:</label>
                    <input
                        type="email"
                        id="email_destino"
                        name="email_destino"
                        class="form-control"
                        placeholder="tu_email@ejemplo.com"
                        required
                        <?php echo !$config_valida ? 'disabled' : ''; ?>
                    >
                    <small style="color: #666; display: block; margin-top: 5px;">
                        El email de prueba se enviará a esta dirección
                    </small>
                </div>

                <button type="submit" class="btn btn-primary" <?php echo !$config_valida ? 'disabled' : ''; ?>>
                    📤 Enviar Email de Prueba
                </button>
            </form>
        </div>

        <!-- Información adicional -->
        <div class="card">
            <h2>ℹ️ Información Adicional</h2>

            <p style="margin-bottom: 15px; line-height: 1.6;">
                El sistema de notificaciones por email está integrado con el sistema de notificaciones web.
                Cuando se crea una notificación, automáticamente se enviará un email si:
            </p>

            <ul style="margin-left: 20px; line-height: 1.8;">
                <li><code>EMAIL_ENABLED</code> está en <code>true</code></li>
                <li><code>EMAIL_SEND_IMMEDIATE</code> está en <code>true</code></li>
                <li>El usuario tiene un email configurado</li>
                <li>El usuario tiene <code>recibir_emails</code> habilitado en su perfil</li>
            </ul>

            <p style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px; line-height: 1.6;">
                <strong>Nota:</strong> Asegúrate de ejecutar el script SQL <code>sql/agregar_campo_recibir_emails.sql</code>
                para agregar el campo de preferencias de email a la tabla de usuarios.
            </p>
        </div>
    </div>
</body>
</html>
