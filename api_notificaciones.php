<?php
/**
 * API de Notificaciones
 * Endpoint para operaciones AJAX con notificaciones
 */

session_start();
require_once 'conexion.php';
require_once 'verificar_login.php';
require_once 'notificaciones.php';

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(array('error' => 'No autenticado'));
    exit();
}

// Establecer el tipo de contenido a JSON
header('Content-Type: application/json');

$accion = isset($_GET['accion']) ? $_GET['accion'] : '';
$usuario_id = $_SESSION['usuario_id'];

try {
    switch ($accion) {
        case 'obtener':
            // Obtener notificaciones del usuario
            $solo_no_leidas = isset($_GET['no_leidas']) && $_GET['no_leidas'] == '1';
            $limite = isset($_GET['limite']) ? intval($_GET['limite']) : 50;

            $notificaciones = obtenerNotificaciones($usuario_id, $solo_no_leidas, $limite);

            echo json_encode(array(
                'success' => true,
                'notificaciones' => $notificaciones
            ));
            break;

        case 'contar':
            // Contar notificaciones no leídas
            $total = contarNotificacionesNoLeidas($usuario_id);

            echo json_encode(array(
                'success' => true,
                'total' => $total
            ));
            break;

        case 'marcar_leida':
            // Marcar una notificación como leída
            if (!isset($_POST['notificacion_id'])) {
                throw new Exception('ID de notificación no proporcionado');
            }

            $notificacion_id = intval($_POST['notificacion_id']);
            $resultado = marcarNotificacionLeida($notificacion_id, $usuario_id);

            echo json_encode(array(
                'success' => $resultado,
                'mensaje' => $resultado ? 'Notificación marcada como leída' : 'Error al marcar notificación'
            ));
            break;

        case 'marcar_todas_leidas':
            // Marcar todas las notificaciones como leídas
            $resultado = marcarTodasLeidas($usuario_id);

            echo json_encode(array(
                'success' => $resultado,
                'mensaje' => $resultado ? 'Todas las notificaciones marcadas como leídas' : 'Error al marcar notificaciones'
            ));
            break;

        case 'eliminar':
            // Eliminar una notificación
            if (!isset($_POST['notificacion_id'])) {
                throw new Exception('ID de notificación no proporcionado');
            }

            $notificacion_id = intval($_POST['notificacion_id']);
            $resultado = eliminarNotificacion($notificacion_id, $usuario_id);

            echo json_encode(array(
                'success' => $resultado,
                'mensaje' => $resultado ? 'Notificación eliminada' : 'Error al eliminar notificación'
            ));
            break;

        case 'obtener_recientes':
            // Obtener las últimas 10 notificaciones para el dropdown
            $notificaciones = obtenerNotificaciones($usuario_id, false, 10);
            $total_no_leidas = contarNotificacionesNoLeidas($usuario_id);

            // Preparar las notificaciones con formato HTML
            $notificaciones_html = array();
            foreach ($notificaciones as $notif) {
                $icono = obtenerIconoEvento($notif['tipo_evento']);
                $color = obtenerColorEvento($notif['tipo_evento']);
                $clase_leida = $notif['leida'] ? 'leida' : 'no-leida';

                $notificaciones_html[] = array(
                    'id' => $notif['id'],
                    'icono' => $icono,
                    'color' => $color,
                    'mensaje' => $notif['mensaje'],
                    'tiempo' => $notif['fecha_relativa'],
                    'documento_id' => $notif['documento_id'],
                    'leida' => $notif['leida'],
                    'clase' => $clase_leida
                );
            }

            echo json_encode(array(
                'success' => true,
                'notificaciones' => $notificaciones_html,
                'total_no_leidas' => $total_no_leidas
            ));
            break;

        default:
            throw new Exception('Acción no válida');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(array(
        'success' => false,
        'error' => $e->getMessage()
    ));
}
?>
