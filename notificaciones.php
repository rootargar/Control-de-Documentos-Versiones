<?php
/**
 * Sistema de Gestión de Notificaciones
 * Funciones para crear, leer y gestionar notificaciones del sistema
 */

require_once 'conexion.php';

// Cargar sistema de emails si está disponible
if (file_exists(__DIR__ . '/email_notificaciones.php')) {
    require_once 'email_notificaciones.php';
}

/**
 * Crea una nueva notificación en el sistema
 *
 * @param int $documento_id ID del documento relacionado
 * @param string $tipo_evento Tipo de evento (Creacion, Cambio Estado, Nueva Version, etc.)
 * @param string $mensaje Mensaje de la notificación
 * @param int|array $usuarios_destino ID de usuario(s) que recibirá(n) la notificación
 * @param bool $enviar_email Si true, también envía notificación por email
 * @return bool True si se creó correctamente, False en caso contrario
 */
function crearNotificacion($documento_id, $tipo_evento, $mensaje, $usuarios_destino, $enviar_email = true) {
    global $conn;

    // Si $usuarios_destino es un solo ID, convertirlo a array
    if (!is_array($usuarios_destino)) {
        $usuarios_destino = array($usuarios_destino);
    }

    $exito = true;

    // Obtener datos del documento para el email
    $datos_documento = array();
    if ($enviar_email && $documento_id) {
        $sql_doc = "SELECT d.*, u.nombre as responsable
                    FROM Documentos d
                    LEFT JOIN Usuarios u ON d.responsable_id = u.id
                    WHERE d.id = ?";
        $stmt_doc = sqlsrv_query($conn, $sql_doc, array($documento_id));
        if ($stmt_doc) {
            $datos_documento = sqlsrv_fetch_array($stmt_doc, SQLSRV_FETCH_ASSOC);
            if ($datos_documento) {
                $datos_documento['id'] = $documento_id;
            }
        }
    }

    foreach ($usuarios_destino as $usuario_id) {
        // Insertar notificación en base de datos
        $sql = "INSERT INTO Notificaciones
                (documento_id, usuario_id, tipo_evento, fecha_programada, leida, mensaje)
                VALUES (?, ?, ?, GETDATE(), 0, ?)";

        $params = array($documento_id, $usuario_id, $tipo_evento, $mensaje);
        $stmt = sqlsrv_query($conn, $sql, $params);

        if (!$stmt) {
            error_log("Error al crear notificación: " . print_r(sqlsrv_errors(), true));
            $exito = false;
            continue;
        }

        // Enviar email si está habilitado y la función existe
        if ($enviar_email && function_exists('enviarNotificacionEmail') && EMAIL_SEND_IMMEDIATE) {
            enviarNotificacionEmail($usuario_id, $tipo_evento, $mensaje, $datos_documento);
        }
    }

    return $exito;
}

/**
 * Obtiene las notificaciones de un usuario
 *
 * @param int $usuario_id ID del usuario
 * @param bool $solo_no_leidas Si true, solo devuelve notificaciones no leídas
 * @param int $limite Número máximo de notificaciones a devolver
 * @return array Array de notificaciones
 */
function obtenerNotificaciones($usuario_id, $solo_no_leidas = false, $limite = 50) {
    global $conn;

    $sql = "SELECT TOP ($limite)
                n.id,
                n.documento_id,
                n.tipo_evento,
                n.mensaje,
                n.fecha_programada,
                n.leida,
                d.nombre as documento_nombre,
                d.codigo as documento_codigo
            FROM Notificaciones n
            LEFT JOIN Documentos d ON n.documento_id = d.id
            WHERE n.usuario_id = ?";

    if ($solo_no_leidas) {
        $sql .= " AND n.leida = 0";
    }

    $sql .= " ORDER BY n.fecha_programada DESC";

    $params = array($usuario_id);
    $stmt = sqlsrv_query($conn, $sql, $params);

    if (!$stmt) {
        error_log("Error al obtener notificaciones: " . print_r(sqlsrv_errors(), true));
        return array();
    }

    $notificaciones = array();
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        // Formatear la fecha
        if ($row['fecha_programada'] instanceof DateTime) {
            $row['fecha_formateada'] = $row['fecha_programada']->format('d/m/Y H:i');
            $row['fecha_relativa'] = obtenerTiempoRelativo($row['fecha_programada']);
        }
        $notificaciones[] = $row;
    }

    return $notificaciones;
}

/**
 * Marca una notificación como leída
 *
 * @param int $notificacion_id ID de la notificación
 * @param int $usuario_id ID del usuario (para verificar que sea su notificación)
 * @return bool True si se marcó correctamente, False en caso contrario
 */
function marcarNotificacionLeida($notificacion_id, $usuario_id) {
    global $conn;

    $sql = "UPDATE Notificaciones
            SET leida = 1
            WHERE id = ? AND usuario_id = ?";

    $params = array($notificacion_id, $usuario_id);
    $stmt = sqlsrv_query($conn, $sql, $params);

    if (!$stmt) {
        error_log("Error al marcar notificación como leída: " . print_r(sqlsrv_errors(), true));
        return false;
    }

    return true;
}

/**
 * Marca todas las notificaciones de un usuario como leídas
 *
 * @param int $usuario_id ID del usuario
 * @return bool True si se marcaron correctamente, False en caso contrario
 */
function marcarTodasLeidas($usuario_id) {
    global $conn;

    $sql = "UPDATE Notificaciones
            SET leida = 1
            WHERE usuario_id = ? AND leida = 0";

    $params = array($usuario_id);
    $stmt = sqlsrv_query($conn, $sql, $params);

    if (!$stmt) {
        error_log("Error al marcar todas las notificaciones como leídas: " . print_r(sqlsrv_errors(), true));
        return false;
    }

    return true;
}

/**
 * Cuenta las notificaciones no leídas de un usuario
 *
 * @param int $usuario_id ID del usuario
 * @return int Número de notificaciones no leídas
 */
function contarNotificacionesNoLeidas($usuario_id) {
    global $conn;

    $sql = "SELECT COUNT(*) as total
            FROM Notificaciones
            WHERE usuario_id = ? AND leida = 0";

    $params = array($usuario_id);
    $stmt = sqlsrv_query($conn, $sql, $params);

    if (!$stmt) {
        error_log("Error al contar notificaciones: " . print_r(sqlsrv_errors(), true));
        return 0;
    }

    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    return $row['total'];
}

/**
 * Elimina una notificación
 *
 * @param int $notificacion_id ID de la notificación
 * @param int $usuario_id ID del usuario (para verificar que sea su notificación)
 * @return bool True si se eliminó correctamente, False en caso contrario
 */
function eliminarNotificacion($notificacion_id, $usuario_id) {
    global $conn;

    $sql = "DELETE FROM Notificaciones
            WHERE id = ? AND usuario_id = ?";

    $params = array($notificacion_id, $usuario_id);
    $stmt = sqlsrv_query($conn, $sql, $params);

    if (!$stmt) {
        error_log("Error al eliminar notificación: " . print_r(sqlsrv_errors(), true));
        return false;
    }

    return true;
}

/**
 * Notifica a los usuarios relevantes sobre eventos de documentos
 *
 * @param int $documento_id ID del documento
 * @param string $tipo_evento Tipo de evento
 * @param string $mensaje Mensaje personalizado
 * @param int|null $excluir_usuario_id Usuario a excluir de las notificaciones (generalmente quien realizó la acción)
 */
function notificarEventoDocumento($documento_id, $tipo_evento, $mensaje, $excluir_usuario_id = null) {
    global $conn;

    // Obtener información del documento
    $sql = "SELECT responsable_id, nombre
            FROM Documentos
            WHERE id = ?";
    $stmt = sqlsrv_query($conn, $sql, array($documento_id));
    $documento = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

    if (!$documento) {
        return false;
    }

    $usuarios_notificar = array();

    // Dependiendo del tipo de evento, determinar quién debe ser notificado
    switch ($tipo_evento) {
        case 'Creacion':
            // Notificar a Administradores y Aprobadores
            $sql = "SELECT id FROM Usuarios WHERE rol_id IN (1, 4) AND estado = 'Activo'";
            break;

        case 'Cambio Estado':
            // Notificar al responsable del documento
            $usuarios_notificar[] = $documento['responsable_id'];
            break;

        case 'Nueva Version':
            // Notificar al responsable y a los aprobadores
            $usuarios_notificar[] = $documento['responsable_id'];
            $sql = "SELECT id FROM Usuarios WHERE rol_id = 4 AND estado = 'Activo'";
            break;

        case 'Proximo Vencimiento':
            // Notificar al responsable y administradores
            $usuarios_notificar[] = $documento['responsable_id'];
            $sql = "SELECT id FROM Usuarios WHERE rol_id = 1 AND estado = 'Activo'";
            break;

        default:
            // Para otros eventos, notificar solo al responsable
            $usuarios_notificar[] = $documento['responsable_id'];
            break;
    }

    // Si hay una consulta SQL para obtener más usuarios, ejecutarla
    if (isset($sql)) {
        $stmt = sqlsrv_query($conn, $sql);
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $usuarios_notificar[] = $row['id'];
        }
    }

    // Eliminar duplicados
    $usuarios_notificar = array_unique($usuarios_notificar);

    // Excluir el usuario especificado (quien realizó la acción)
    if ($excluir_usuario_id !== null) {
        $usuarios_notificar = array_filter($usuarios_notificar, function($id) use ($excluir_usuario_id) {
            return $id != $excluir_usuario_id;
        });
    }

    // Crear las notificaciones
    if (!empty($usuarios_notificar)) {
        return crearNotificacion($documento_id, $tipo_evento, $mensaje, $usuarios_notificar);
    }

    return true;
}

/**
 * Convierte una fecha en tiempo relativo (hace X minutos/horas/días)
 *
 * @param DateTime $fecha Fecha a convertir
 * @return string Tiempo relativo
 */
function obtenerTiempoRelativo($fecha) {
    $ahora = new DateTime();
    $diferencia = $ahora->diff($fecha);

    if ($diferencia->y > 0) {
        return $diferencia->y . ' año' . ($diferencia->y > 1 ? 's' : '');
    } elseif ($diferencia->m > 0) {
        return $diferencia->m . ' mes' . ($diferencia->m > 1 ? 'es' : '');
    } elseif ($diferencia->d > 0) {
        return $diferencia->d . ' día' . ($diferencia->d > 1 ? 's' : '');
    } elseif ($diferencia->h > 0) {
        return $diferencia->h . ' hora' . ($diferencia->h > 1 ? 's' : '');
    } elseif ($diferencia->i > 0) {
        return $diferencia->i . ' minuto' . ($diferencia->i > 1 ? 's' : '');
    } else {
        return 'Ahora mismo';
    }
}

/**
 * Obtiene el icono apropiado para cada tipo de evento
 *
 * @param string $tipo_evento Tipo de evento
 * @return string Emoji o icono para el evento
 */
function obtenerIconoEvento($tipo_evento) {
    $iconos = array(
        'Creacion' => '📄',
        'Cambio Estado' => '✅',
        'Nueva Version' => '🔄',
        'Proximo Vencimiento' => '⏰',
        'Asignacion' => '👤',
        'Comentario' => '💬'
    );

    return isset($iconos[$tipo_evento]) ? $iconos[$tipo_evento] : '📌';
}

/**
 * Obtiene el color apropiado para cada tipo de evento
 *
 * @param string $tipo_evento Tipo de evento
 * @return string Color hexadecimal
 */
function obtenerColorEvento($tipo_evento) {
    $colores = array(
        'Creacion' => '#2196f3',      // Azul
        'Cambio Estado' => '#4caf50', // Verde
        'Nueva Version' => '#ff9800', // Naranja
        'Proximo Vencimiento' => '#f44336', // Rojo
        'Asignacion' => '#9c27b0',    // Púrpura
        'Comentario' => '#607d8b'     // Gris azulado
    );

    return isset($colores[$tipo_evento]) ? $colores[$tipo_evento] : '#757575';
}
?>
