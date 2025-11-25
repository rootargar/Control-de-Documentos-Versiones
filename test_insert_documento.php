<?php
require_once 'conexion.php';

echo "=== PRUEBA DE INSERT DIRECTO EN DOCUMENTOS ===\n\n";

// Primero, obtener el ID de Carmen Urquidez
$sqlUsuario = "SELECT id FROM Usuarios WHERE nombre LIKE '%Carmen%Urquidez%'";
$stmtUsuario = sqlsrv_query($conn, $sqlUsuario);

if ($stmtUsuario === false) {
    echo "Error al buscar usuario:\n";
    print_r(sqlsrv_errors());
    exit;
}

$usuario = sqlsrv_fetch_array($stmtUsuario, SQLSRV_FETCH_ASSOC);
if (!$usuario) {
    echo "Usuario Carmen Urquidez no encontrado. Buscando todos los usuarios...\n";
    $sqlTodos = "SELECT id, nombre FROM Usuarios WHERE estado = 1";
    $stmtTodos = sqlsrv_query($conn, $sqlTodos);
    while ($u = sqlsrv_fetch_array($stmtTodos, SQLSRV_FETCH_ASSOC)) {
        echo "ID: " . $u['id'] . " - Nombre: " . $u['nombre'] . "\n";
    }
    exit;
}

$responsable_id = $usuario['id'];
echo "Usuario encontrado - ID: $responsable_id\n\n";

// Datos a insertar
$nombre = 'Procedimiento para otorgar crédito de post venta';
$codigo = 'PE-CC-01-02';
$categoria = 'Procedimiento';
$area = 'Administracion';
$departamento = 'Crédito y Cobranza';
$descripcion = 'Actualizado 2025';

// Probar diferentes formatos de fecha
echo "=== PRUEBA 1: Fecha con formato YYYY-MM-DD HH:MM:SS ===\n";
$fecha_elaboracion = '2025-11-25 00:00:00';
$fecha_vencimiento = '2026-11-26 00:00:00';

echo "Fecha elaboración: $fecha_elaboracion\n";
echo "Fecha vencimiento: $fecha_vencimiento\n\n";

$sql = "INSERT INTO Documentos (nombre, codigo, categoria, area, departamento, responsable_id,
        descripcion, fecha_creacion, fecha_vencimiento, estado, activo)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pendiente', 1)";

$params = array($nombre, $codigo, $categoria, $area, $departamento, $responsable_id,
               $descripcion, $fecha_elaboracion, $fecha_vencimiento);

echo "Intentando INSERT...\n";
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt) {
    echo "✓ INSERT exitoso con formato YYYY-MM-DD HH:MM:SS\n";

    // Obtener el ID insertado
    $lastIdSql = "SELECT @@IDENTITY as id";
    $stmtId = sqlsrv_query($conn, $lastIdSql);
    $resultId = sqlsrv_fetch_array($stmtId, SQLSRV_FETCH_ASSOC);
    echo "ID del documento creado: " . $resultId['id'] . "\n\n";
} else {
    echo "✗ ERROR en INSERT:\n";
    $errors = sqlsrv_errors();
    foreach ($errors as $error) {
        echo "SQLSTATE: " . $error['SQLSTATE'] . "\n";
        echo "Código: " . $error['code'] . "\n";
        echo "Mensaje: " . $error['message'] . "\n\n";
    }
}

// Probar con formato de fecha solo YYYY-MM-DD
echo "\n=== PRUEBA 2: Fecha con formato YYYY-MM-DD (sin hora) ===\n";
$codigo2 = 'PE-CC-01-02-TEST2';
$fecha_elaboracion2 = '2025-11-25';
$fecha_vencimiento2 = '2026-11-26';

echo "Fecha elaboración: $fecha_elaboracion2\n";
echo "Fecha vencimiento: $fecha_vencimiento2\n\n";

$params2 = array($nombre, $codigo2, $categoria, $area, $departamento, $responsable_id,
                $descripcion, $fecha_elaboracion2, $fecha_vencimiento2);

echo "Intentando INSERT...\n";
$stmt2 = sqlsrv_query($conn, $sql, $params2);

if ($stmt2) {
    echo "✓ INSERT exitoso con formato YYYY-MM-DD\n";

    $stmtId2 = sqlsrv_query($conn, $lastIdSql);
    $resultId2 = sqlsrv_fetch_array($stmtId2, SQLSRV_FETCH_ASSOC);
    echo "ID del documento creado: " . $resultId2['id'] . "\n\n";
} else {
    echo "✗ ERROR en INSERT:\n";
    $errors = sqlsrv_errors();
    foreach ($errors as $error) {
        echo "SQLSTATE: " . $error['SQLSTATE'] . "\n";
        echo "Código: " . $error['code'] . "\n";
        echo "Mensaje: " . $error['message'] . "\n\n";
    }
}

// Probar con formato incorrecto (como podría estar llegando)
echo "\n=== PRUEBA 3: Fecha con formato DD/MM/YYYY (formato incorrecto) ===\n";
$codigo3 = 'PE-CC-01-02-TEST3';
$fecha_elaboracion3 = '25/11/2025';
$fecha_vencimiento3 = '26/11/2026';

echo "Fecha elaboración: $fecha_elaboracion3\n";
echo "Fecha vencimiento: $fecha_vencimiento3\n\n";

$params3 = array($nombre, $codigo3, $categoria, $area, $departamento, $responsable_id,
                $descripcion, $fecha_elaboracion3, $fecha_vencimiento3);

echo "Intentando INSERT...\n";
$stmt3 = sqlsrv_query($conn, $sql, $params3);

if ($stmt3) {
    echo "✓ INSERT exitoso con formato DD/MM/YYYY\n";

    $stmtId3 = sqlsrv_query($conn, $lastIdSql);
    $resultId3 = sqlsrv_fetch_array($stmtId3, SQLSRV_FETCH_ASSOC);
    echo "ID del documento creado: " . $resultId3['id'] . "\n\n";
} else {
    echo "✗ ERROR en INSERT (esto es esperado):\n";
    $errors = sqlsrv_errors();
    foreach ($errors as $error) {
        echo "SQLSTATE: " . $error['SQLSTATE'] . "\n";
        echo "Código: " . $error['code'] . "\n";
        echo "Mensaje: " . $error['message'] . "\n\n";
    }
}

echo "\n=== FIN DE PRUEBAS ===\n";
?>
