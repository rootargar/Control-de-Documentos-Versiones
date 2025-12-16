<?php
require_once 'verificar_login.php';
require_once 'conexion.php';
require_once 'notificaciones.php';

// Verificar que el usuario esté autenticado
verificarLogin();

$usuario_id = $_SESSION['usuario_id'];
$filtro = $_GET['filtro'] ?? 'todas';

// Obtener notificaciones según el filtro
if ($filtro === 'no_leidas') {
    $notificaciones = obtenerNotificaciones($usuario_id, true, 100);
} else {
    $notificaciones = obtenerNotificaciones($usuario_id, false, 100);
}

$total_no_leidas = contarNotificacionesNoLeidas($usuario_id);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Notificaciones - Sistema de Gestión Documental</title>
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

        .navbar .actions {
            display: flex;
            gap: 15px;
            align-items: center;
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

        .container {
            max-width: 1000px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .page-header {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-header h2 {
            color: #333;
            font-size: 24px;
        }

        .stats {
            display: flex;
            gap: 20px;
            font-size: 14px;
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .stat-badge {
            background: #f44336;
            color: white;
            padding: 4px 12px;
            border-radius: 12px;
            font-weight: bold;
        }

        .filtros {
            background: white;
            padding: 20px 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .filtros-tabs {
            display: flex;
            gap: 10px;
        }

        .filtro-tab {
            padding: 10px 20px;
            border: 1px solid #ddd;
            background: white;
            cursor: pointer;
            border-radius: 5px;
            transition: all 0.3s;
            text-decoration: none;
            color: #333;
            font-size: 14px;
        }

        .filtro-tab:hover {
            background: #f8f9fa;
        }

        .filtro-tab.activo {
            background: #027be3;
            color: white;
            border-color: #027be3;
        }

        .notificaciones-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .notificacion-card {
            padding: 20px 25px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            gap: 15px;
            align-items: flex-start;
            transition: background-color 0.2s;
            cursor: pointer;
            position: relative;
        }

        .notificacion-card:hover {
            background-color: #f8f9fa;
        }

        .notificacion-card:last-child {
            border-bottom: none;
        }

        .notificacion-card.no-leida {
            background-color: #e3f2fd;
        }

        .notificacion-card.no-leida:hover {
            background-color: #bbdefb;
        }

        .notificacion-icono {
            font-size: 32px;
            flex-shrink: 0;
        }

        .notificacion-contenido {
            flex: 1;
            min-width: 0;
        }

        .notificacion-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 8px;
        }

        .notificacion-tipo {
            background: #027be3;
            color: white;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .notificacion-mensaje {
            color: #333;
            font-size: 15px;
            line-height: 1.5;
            margin-bottom: 10px;
        }

        .notificacion-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .notificacion-meta {
            display: flex;
            gap: 15px;
            font-size: 13px;
            color: #666;
        }

        .notificacion-meta-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .notificacion-actions {
            display: flex;
            gap: 10px;
        }

        .btn-small {
            padding: 5px 12px;
            font-size: 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-ver {
            background: #027be3;
            color: white;
        }

        .btn-ver:hover {
            background: #0056b3;
        }

        .btn-eliminar {
            background: #f44336;
            color: white;
        }

        .btn-eliminar:hover {
            background: #d32f2f;
        }

        .notificacion-indicador {
            width: 10px;
            height: 10px;
            background: #2196f3;
            border-radius: 50%;
            position: absolute;
            top: 25px;
            right: 25px;
        }

        .vacio {
            padding: 60px 20px;
            text-align: center;
            color: #999;
        }

        .vacio-icono {
            font-size: 64px;
            margin-bottom: 15px;
        }

        .vacio-texto {
            font-size: 16px;
        }

        .loading {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        .toast {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: #333;
            color: white;
            padding: 15px 25px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            display: none;
            z-index: 1000;
            animation: slideIn 0.3s ease-out;
        }

        .toast.success {
            background: #4caf50;
        }

        .toast.error {
            background: #f44336;
        }

        .toast.show {
            display: block;
        }

        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <div class="navbar">
        <h1>📧 Mis Notificaciones</h1>
        <div class="actions">
            <a href="index.php" class="btn btn-secondary">← Volver al Inicio</a>
        </div>
    </div>

    <!-- Contenedor principal -->
    <div class="container">
        <!-- Cabecera de página -->
        <div class="page-header">
            <div>
                <h2>Centro de Notificaciones</h2>
            </div>
            <div class="stats">
                <div class="stat-item">
                    <span>No leídas:</span>
                    <span class="stat-badge" id="total-no-leidas"><?php echo $total_no_leidas; ?></span>
                </div>
                <div class="stat-item">
                    <span>Total:</span>
                    <strong><?php echo count($notificaciones); ?></strong>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="filtros">
            <div class="filtros-tabs">
                <a href="?filtro=todas" class="filtro-tab <?php echo $filtro === 'todas' ? 'activo' : ''; ?>">
                    Todas
                </a>
                <a href="?filtro=no_leidas" class="filtro-tab <?php echo $filtro === 'no_leidas' ? 'activo' : ''; ?>">
                    No leídas
                </a>
            </div>
            <?php if ($total_no_leidas > 0): ?>
            <button class="btn btn-secondary" onclick="marcarTodasLeidas()">
                ✓ Marcar todas como leídas
            </button>
            <?php endif; ?>
        </div>

        <!-- Lista de notificaciones -->
        <div class="notificaciones-container">
            <?php if (empty($notificaciones)): ?>
                <div class="vacio">
                    <div class="vacio-icono">📭</div>
                    <div class="vacio-texto">
                        <?php echo $filtro === 'no_leidas' ? 'No tienes notificaciones sin leer' : 'No tienes notificaciones'; ?>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($notificaciones as $notif): ?>
                <div class="notificacion-card <?php echo !$notif['leida'] ? 'no-leida' : ''; ?>"
                     data-id="<?php echo $notif['id']; ?>"
                     data-documento="<?php echo $notif['documento_id']; ?>"
                     data-leida="<?php echo $notif['leida']; ?>">

                    <div class="notificacion-icono">
                        <?php echo obtenerIconoEvento($notif['tipo_evento']); ?>
                    </div>

                    <div class="notificacion-contenido">
                        <div class="notificacion-header">
                            <span class="notificacion-tipo"
                                  style="background: <?php echo obtenerColorEvento($notif['tipo_evento']); ?>">
                                <?php echo $notif['tipo_evento']; ?>
                            </span>
                        </div>

                        <div class="notificacion-mensaje">
                            <?php echo htmlspecialchars($notif['mensaje']); ?>
                        </div>

                        <div class="notificacion-footer">
                            <div class="notificacion-meta">
                                <div class="notificacion-meta-item">
                                    🕒 Hace <?php echo $notif['fecha_relativa']; ?>
                                </div>
                                <?php if ($notif['documento_nombre']): ?>
                                <div class="notificacion-meta-item">
                                    📄 <?php echo htmlspecialchars($notif['documento_nombre']); ?>
                                </div>
                                <?php endif; ?>
                            </div>

                            <div class="notificacion-actions">
                                <?php if ($notif['documento_id']): ?>
                                <button class="btn-small btn-ver"
                                        onclick="verDocumento(<?php echo $notif['id']; ?>, <?php echo $notif['documento_id']; ?>)">
                                    Ver documento
                                </button>
                                <?php endif; ?>
                                <button class="btn-small btn-eliminar"
                                        onclick="eliminarNotificacion(event, <?php echo $notif['id']; ?>)">
                                    Eliminar
                                </button>
                            </div>
                        </div>
                    </div>

                    <?php if (!$notif['leida']): ?>
                    <div class="notificacion-indicador"></div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Toast de notificación -->
    <div id="toast" class="toast"></div>

    <script>
        // Marcar todas como leídas
        function marcarTodasLeidas() {
            if (!confirm('¿Deseas marcar todas las notificaciones como leídas?')) {
                return;
            }

            fetch('api_notificaciones.php?accion=marcar_todas_leidas', {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Todas las notificaciones marcadas como leídas', 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showToast('Error al marcar notificaciones', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error de conexión', 'error');
            });
        }

        // Ver documento
        function verDocumento(notifId, documentoId) {
            // Marcar como leída
            fetch('api_notificaciones.php?accion=marcar_leida', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'notificacion_id=' + notifId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Redirigir al documento
                    window.location.href = 'ver_documento.php?id=' + documentoId;
                }
            })
            .catch(error => console.error('Error:', error));
        }

        // Eliminar notificación
        function eliminarNotificacion(event, notifId) {
            event.stopPropagation();

            if (!confirm('¿Deseas eliminar esta notificación?')) {
                return;
            }

            fetch('api_notificaciones.php?accion=eliminar', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'notificacion_id=' + notifId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Notificación eliminada', 'success');
                    // Remover elemento del DOM
                    const card = document.querySelector(`[data-id="${notifId}"]`);
                    if (card) {
                        card.style.animation = 'slideOut 0.3s ease-out';
                        setTimeout(() => {
                            card.remove();
                            // Si no quedan notificaciones, recargar página
                            if (document.querySelectorAll('.notificacion-card').length === 0) {
                                window.location.reload();
                            }
                        }, 300);
                    }
                } else {
                    showToast('Error al eliminar notificación', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error de conexión', 'error');
            });
        }

        // Mostrar toast
        function showToast(mensaje, tipo = 'success') {
            const toast = document.getElementById('toast');
            toast.textContent = mensaje;
            toast.className = 'toast ' + tipo + ' show';

            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }

        // Click en tarjeta para marcar como leída
        document.querySelectorAll('.notificacion-card').forEach(card => {
            card.addEventListener('click', function(e) {
                // No hacer nada si se hizo click en un botón
                if (e.target.tagName === 'BUTTON') {
                    return;
                }

                const notifId = this.dataset.id;
                const leida = this.dataset.leida;

                // Si ya está leída, no hacer nada
                if (leida === '1') {
                    return;
                }

                // Marcar como leída
                fetch('api_notificaciones.php?accion=marcar_leida', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'notificacion_id=' + notifId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Actualizar UI
                        this.classList.remove('no-leida');
                        const indicador = this.querySelector('.notificacion-indicador');
                        if (indicador) {
                            indicador.remove();
                        }
                        this.dataset.leida = '1';

                        // Actualizar contador
                        const badge = document.getElementById('total-no-leidas');
                        const total = parseInt(badge.textContent);
                        if (total > 0) {
                            badge.textContent = total - 1;
                        }
                    }
                })
                .catch(error => console.error('Error:', error));
            });
        });
    </script>
</body>
</html>
