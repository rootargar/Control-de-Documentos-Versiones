<!DOCTYPE html>
<html>
<head>
    <title>Diagnóstico de Fechas</title>
    <style>
        body { font-family: monospace; padding: 20px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>Diagnóstico de INSERT de Documentos</h1>

<?php
require_once 'conexion.php';

echo "<h2>1. Verificar conexión a base de datos</h2>";
if ($conn) {
    echo "<p class='success'>✓ Conexión exitosa</p>";
} else {
    echo "<p class='error'>✗ Error de conexión</p>";
    exit;
}

// Obtener un usuario de prueba
echo "<h2>2. Buscar usuario responsable</h2>";
$sqlUsuario = "SELECT TOP 1 id, nombre FROM Usuarios WHERE estado = 1 ORDER BY id";
$stmtUsuario = sqlsrv_query($conn, $sqlUsuario);
$usuario = sqlsrv_fetch_array($stmtUsuario, SQLSRV_FETCH_ASSOC);
echo "<p class='info'>Usuario de prueba: ID=" . $usuario['id'] . ", Nombre=" . $usuario['nombre'] . "</p>";

// Datos de prueba
$nombre = 'TEST - Procedimiento de prueba';
$codigo = 'TEST-' . time(); // Código único
$categoria = 'Procedimiento';
$area = 'Administracion';
$departamento = 'Crédito y Cobranza';
$responsable_id = $usuario['id'];
$descripcion = 'Prueba de diagnóstico';

echo "<h2>3. Pruebas de INSERT con diferentes formatos de fecha</h2>";

// PRUEBA 1: Con fecha actual en formato correcto
echo "<h3>Prueba 1: Fecha actual con formato YYYY-MM-DD HH:MM:SS</h3>";
$fecha_elab_1 = date('Y-m-d') . ' 00:00:00';
$fecha_venc_1 = date('Y-m-d', strtotime('+1 year')) . ' 00:00:00';

echo "<pre>";
echo "Código: $codigo-1\n";
echo "Fecha elaboración: $fecha_elab_1\n";
echo "Fecha vencimiento: $fecha_venc_1\n";
echo "</pre>";

$sql = "INSERT INTO Documentos (nombre, codigo, categoria, area, departamento, responsable_id,
        descripcion, fecha_creacion, fecha_vencimiento, estado, activo)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pendiente', 1)";

$params = array($nombre, $codigo . '-1', $categoria, $area, $departamento, $responsable_id,
               $descripcion, $fecha_elab_1, $fecha_venc_1);

$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt) {
    echo "<p class='success'>✓ INSERT exitoso</p>";
} else {
    echo "<p class='error'>✗ ERROR:</p>";
    echo "<pre>";
    print_r(sqlsrv_errors());
    echo "</pre>";
}

// PRUEBA 2: Con las fechas que el usuario está intentando
echo "<h3>Prueba 2: Con fechas específicas 2025-11-25 y 2026-11-26</h3>";
$fecha_elab_2 = '2025-11-25 00:00:00';
$fecha_venc_2 = '2026-11-26 00:00:00';

echo "<pre>";
echo "Código: $codigo-2\n";
echo "Fecha elaboración: $fecha_elab_2\n";
echo "Fecha vencimiento: $fecha_venc_2\n";
echo "</pre>";

$params2 = array($nombre, $codigo . '-2', $categoria, $area, $departamento, $responsable_id,
                $descripcion, $fecha_elab_2, $fecha_venc_2);

$stmt2 = sqlsrv_query($conn, $sql, $params2);

if ($stmt2) {
    echo "<p class='success'>✓ INSERT exitoso</p>";
} else {
    echo "<p class='error'>✗ ERROR:</p>";
    echo "<pre>";
    print_r(sqlsrv_errors());
    echo "</pre>";
}

// PRUEBA 3: Sin formato de hora (solo YYYY-MM-DD)
echo "<h3>Prueba 3: Sin hora (solo YYYY-MM-DD)</h3>";
$fecha_elab_3 = '2025-11-25';
$fecha_venc_3 = '2026-11-26';

echo "<pre>";
echo "Código: $codigo-3\n";
echo "Fecha elaboración: $fecha_elab_3\n";
echo "Fecha vencimiento: $fecha_venc_3\n";
echo "</pre>";

$params3 = array($nombre, $codigo . '-3', $categoria, $area, $departamento, $responsable_id,
                $descripcion, $fecha_elab_3, $fecha_venc_3);

$stmt3 = sqlsrv_query($conn, $sql, $params3);

if ($stmt3) {
    echo "<p class='success'>✓ INSERT exitoso</p>";
} else {
    echo "<p class='error'>✗ ERROR:</p>";
    echo "<pre>";
    print_r(sqlsrv_errors());
    echo "</pre>";
}

// PRUEBA 4: Con fecha NULL en vencimiento
echo "<h3>Prueba 4: Con fecha de vencimiento NULL</h3>";
$fecha_elab_4 = '2025-11-25 00:00:00';
$fecha_venc_4 = null;

echo "<pre>";
echo "Código: $codigo-4\n";
echo "Fecha elaboración: $fecha_elab_4\n";
echo "Fecha vencimiento: NULL\n";
echo "</pre>";

$params4 = array($nombre, $codigo . '-4', $categoria, $area, $departamento, $responsable_id,
                $descripcion, $fecha_elab_4, $fecha_venc_4);

$stmt4 = sqlsrv_query($conn, $sql, $params4);

if ($stmt4) {
    echo "<p class='success'>✓ INSERT exitoso</p>";
} else {
    echo "<p class='error'>✗ ERROR:</p>";
    echo "<pre>";
    print_r(sqlsrv_errors());
    echo "</pre>";
}

// PRUEBA 5: Simular lo que hace el código actual
echo "<h3>Prueba 5: Simulando el código actual de documentos.php</h3>";
$_POST_simulado = array(
    'fecha_elaboracion' => '2025-11-25',
    'fecha_vencimiento' => '2026-11-26'
);

$fecha_elab_input = trim($_POST_simulado['fecha_elaboracion'] ?? '');
if (empty($fecha_elab_input)) {
    $fecha_elaboracion = date('Y-m-d H:i:s');
} else {
    $fecha_elaboracion = $fecha_elab_input . ' 00:00:00';
}

$fecha_vencimiento = trim($_POST_simulado['fecha_vencimiento'] ?? '');
$fecha_venc_formatted = null;
if (!empty($fecha_vencimiento) && $fecha_vencimiento !== '') {
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_vencimiento)) {
        $fecha_venc_formatted = $fecha_vencimiento . ' 00:00:00';
    }
}

echo "<pre>";
echo "Código: $codigo-5\n";
echo "POST fecha_elaboracion: " . $_POST_simulado['fecha_elaboracion'] . "\n";
echo "POST fecha_vencimiento: " . $_POST_simulado['fecha_vencimiento'] . "\n";
echo "Procesado fecha_elaboracion: $fecha_elaboracion\n";
echo "Procesado fecha_vencimiento: " . ($fecha_venc_formatted ?? 'NULL') . "\n";
echo "</pre>";

$params5 = array($nombre, $codigo . '-5', $categoria, $area, $departamento, $responsable_id,
                $descripcion, $fecha_elaboracion, $fecha_venc_formatted);

$stmt5 = sqlsrv_query($conn, $sql, $params5);

if ($stmt5) {
    echo "<p class='success'>✓ INSERT exitoso - El código actual debería funcionar</p>";
} else {
    echo "<p class='error'>✗ ERROR - Hay un problema con el código actual:</p>";
    echo "<pre>";
    print_r(sqlsrv_errors());
    echo "</pre>";
}

echo "<h2>4. Verificar estructura de la tabla Documentos</h2>";
$sqlColumns = "SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, CHARACTER_MAXIMUM_LENGTH
               FROM INFORMATION_SCHEMA.COLUMNS
               WHERE TABLE_NAME = 'Documentos'
               ORDER BY ORDINAL_POSITION";
$stmtColumns = sqlsrv_query($conn, $sqlColumns);

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Columna</th><th>Tipo</th><th>Permite NULL</th><th>Longitud</th></tr>";
while ($col = sqlsrv_fetch_array($stmtColumns, SQLSRV_FETCH_ASSOC)) {
    echo "<tr>";
    echo "<td>" . $col['COLUMN_NAME'] . "</td>";
    echo "<td>" . $col['DATA_TYPE'] . "</td>";
    echo "<td>" . $col['IS_NULLABLE'] . "</td>";
    echo "<td>" . ($col['CHARACTER_MAXIMUM_LENGTH'] ?? 'N/A') . "</td>";
    echo "</tr>";
}
echo "</table>";

?>

</body>
</html>
