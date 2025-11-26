<!DOCTYPE html>
<html>
<head>
    <title>Diagnóstico Avanzado v2 - Triggers y Constraints</title>
    <style>
        body { font-family: monospace; padding: 20px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .info { color: blue; }
        .warning { color: orange; font-weight: bold; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }
        table { border-collapse: collapse; width: 100%; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f0f0f0; }
    </style>
</head>
<body>
    <h1>Diagnóstico Avanzado v2 - Tabla Documentos</h1>

<?php
require_once 'conexion.php';

if (!$conn) {
    echo "<p class='error'>✗ Error de conexión</p>";
    exit;
}

echo "<h2>✓ Información importante del diagnóstico anterior:</h2>";
echo "<p class='success'>No hay TRIGGERS en la tabla - Esto descarta triggers como causa del problema</p>";

echo "<h2>1. Prueba CRÍTICA: INSERT SIN fecha_creacion (como funcionaba antes)</h2>";

$sqlUsuario = "SELECT TOP 1 id, nombre FROM Usuarios WHERE estado = 1 ORDER BY id";
$stmtUsuario = sqlsrv_query($conn, $sqlUsuario);
$usuario = sqlsrv_fetch_array($stmtUsuario, SQLSRV_FETCH_ASSOC);

$nombre = 'TEST - Sin fecha creacion';
$codigo = 'TEST-SIN-FECHA-' . time();
$categoria = 'Procedimiento';
$area = 'Administracion';
$departamento = 'Crédito y Cobranza';
$responsable_id = $usuario['id'];
$descripcion = 'Prueba sin fecha_creacion';
$fecha_vencimiento = '2026-11-26';

echo "<pre>";
echo "INSERT sin incluir fecha_creacion en la query\n";
echo "Código: $codigo\n";
echo "Fecha vencimiento: $fecha_vencimiento\n";
echo "</pre>";

$sqlSinFecha = "INSERT INTO Documentos (nombre, codigo, categoria, area, departamento, responsable_id,
                descripcion, fecha_vencimiento, estado, activo)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pendiente', 1)";

$paramsSinFecha = array($nombre, $codigo, $categoria, $area, $departamento, $responsable_id,
                       $descripcion, $fecha_vencimiento);

$stmtSinFecha = sqlsrv_query($conn, $sqlSinFecha, $paramsSinFecha);

if ($stmtSinFecha) {
    echo "<p class='success'>✓ INSERT EXITOSO sin fecha_creacion</p>";
    echo "<p class='warning'>⚠ CONCLUSIÓN: El problema está ESPECÍFICAMENTE con la columna fecha_creacion</p>";
} else {
    echo "<p class='error'>✗ ERROR:</p>";
    echo "<pre>";
    print_r(sqlsrv_errors());
    echo "</pre>";
}

echo "<h2>2. Prueba: INSERT con fecha_creacion = GETDATE()</h2>";

$codigo2 = 'TEST-GETDATE-' . time();

echo "<pre>";
echo "INSERT usando GETDATE() para fecha_creacion\n";
echo "Código: $codigo2\n";
echo "</pre>";

$sqlGetDate = "INSERT INTO Documentos (nombre, codigo, categoria, area, departamento, responsable_id,
               descripcion, fecha_creacion, fecha_vencimiento, estado, activo)
               VALUES (?, ?, ?, ?, ?, ?, ?, GETDATE(), ?, 'Pendiente', 1)";

$paramsGetDate = array($nombre, $codigo2, $categoria, $area, $departamento, $responsable_id,
                      $descripcion, $fecha_vencimiento);

$stmtGetDate = sqlsrv_query($conn, $sqlGetDate, $paramsGetDate);

if ($stmtGetDate) {
    echo "<p class='success'>✓ INSERT EXITOSO con GETDATE()</p>";
    echo "<p class='info'>GETDATE() funciona - el problema es con el valor que enviamos</p>";
} else {
    echo "<p class='error'>✗ ERROR con GETDATE():</p>";
    echo "<pre>";
    print_r(sqlsrv_errors());
    echo "</pre>";
}

echo "<h2>3. Verificar DEFAULT CONSTRAINT en fecha_creacion</h2>";

$sqlDefaults = "SELECT
    c.name AS column_name,
    dc.name AS constraint_name,
    dc.definition AS default_value,
    dc.is_system_named
FROM sys.columns c
LEFT JOIN sys.default_constraints dc ON c.default_object_id = dc.object_id
JOIN sys.tables t ON c.object_id = t.object_id
WHERE t.name = 'Documentos'
AND c.name IN ('fecha_creacion', 'fecha_modificacion', 'fecha_vencimiento')";

$stmtDefaults = sqlsrv_query($conn, $sqlDefaults);

if ($stmtDefaults) {
    echo "<table>";
    echo "<tr><th>Columna</th><th>Constraint</th><th>Valor por Defecto</th><th>Sistema</th></tr>";

    while ($def = sqlsrv_fetch_array($stmtDefaults, SQLSRV_FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($def['column_name']) . "</td>";
        echo "<td>" . htmlspecialchars($def['constraint_name'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($def['default_value'] ?? 'NULL') . "</td>";
        echo "<td>" . ($def['is_system_named'] ? 'Sí' : 'No') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p class='error'>Error al consultar defaults:</p>";
    echo "<pre>";
    print_r(sqlsrv_errors());
    echo "</pre>";
}

echo "<h2>4. Información detallada de la columna fecha_creacion</h2>";

$sqlColumnInfo = "SELECT
    c.name AS column_name,
    t.name AS data_type,
    c.max_length,
    c.precision,
    c.scale,
    c.is_nullable,
    c.collation_name
FROM sys.columns c
JOIN sys.types t ON c.user_type_id = t.user_type_id
JOIN sys.tables tb ON c.object_id = tb.object_id
WHERE tb.name = 'Documentos'
AND c.name = 'fecha_creacion'";

$stmtColumnInfo = sqlsrv_query($conn, $sqlColumnInfo);

if ($stmtColumnInfo) {
    $colInfo = sqlsrv_fetch_array($stmtColumnInfo, SQLSRV_FETCH_ASSOC);

    echo "<table>";
    echo "<tr><th>Propiedad</th><th>Valor</th></tr>";
    echo "<tr><td>Nombre</td><td>" . $colInfo['column_name'] . "</td></tr>";
    echo "<tr><td>Tipo de Dato</td><td>" . $colInfo['data_type'] . "</td></tr>";
    echo "<tr><td>Max Length</td><td>" . $colInfo['max_length'] . "</td></tr>";
    echo "<tr><td>Precision</td><td>" . $colInfo['precision'] . "</td></tr>";
    echo "<tr><td>Scale</td><td>" . $colInfo['scale'] . "</td></tr>";
    echo "<tr><td>Permite NULL</td><td>" . ($colInfo['is_nullable'] ? 'Sí' : 'No') . "</td></tr>";
    echo "<tr><td>Collation</td><td>" . ($colInfo['collation_name'] ?? 'N/A') . "</td></tr>";
    echo "</table>";
}

echo "<h2>5. Prueba: INSERT usando parámetros con tipo explícito</h2>";

$codigo3 = 'TEST-TYPED-' . time();
$fecha_creacion = '2025-11-25 00:00:00';

echo "<pre>";
echo "INSERT usando sqlsrv con tipo de dato explícito\n";
echo "Código: $codigo3\n";
echo "Fecha: $fecha_creacion\n";
echo "</pre>";

$sqlTyped = "INSERT INTO Documentos (nombre, codigo, categoria, area, departamento, responsable_id,
             descripcion, fecha_creacion, fecha_vencimiento, estado, activo)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pendiente', 1)";

// Usar array con tipo explícito para fecha_creacion
$paramsTyped = array(
    $nombre,
    $codigo3,
    $categoria,
    $area,
    $departamento,
    $responsable_id,
    $descripcion,
    array($fecha_creacion, SQLSRV_PARAM_IN, SQLSRV_PHPTYPE_STRING('UTF-8'), SQLSRV_SQLTYPE_DATETIME),
    $fecha_vencimiento
);

$stmtTyped = sqlsrv_query($conn, $sqlTyped, $paramsTyped);

if ($stmtTyped) {
    echo "<p class='success'>✓ INSERT EXITOSO con tipo explícito</p>";
    echo "<p class='success'>🎯 SOLUCIÓN ENCONTRADA: Usar tipo explícito SQLSRV_SQLTYPE_DATETIME</p>";
} else {
    echo "<p class='error'>✗ ERROR con tipo explícito:</p>";
    echo "<pre>";
    print_r(sqlsrv_errors());
    echo "</pre>";
}

echo "<h2>6. Prueba: Usar objeto DateTime de PHP</h2>";

$codigo4 = 'TEST-DATETIME-' . time();
$fechaObj = new DateTime('2025-11-25');

echo "<pre>";
echo "INSERT usando objeto DateTime de PHP\n";
echo "Código: $codigo4\n";
echo "Fecha objeto: " . $fechaObj->format('Y-m-d H:i:s') . "\n";
echo "</pre>";

$paramsDateTime = array(
    $nombre,
    $codigo4,
    $categoria,
    $area,
    $departamento,
    $responsable_id,
    $descripcion,
    $fechaObj,
    $fecha_vencimiento
);

$sqlDateTime = "INSERT INTO Documentos (nombre, codigo, categoria, area, departamento, responsable_id,
                descripcion, fecha_creacion, fecha_vencimiento, estado, activo)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pendiente', 1)";

$stmtDateTime = sqlsrv_query($conn, $sqlDateTime, $paramsDateTime);

if ($stmtDateTime) {
    echo "<p class='success'>✓ INSERT EXITOSO con DateTime</p>";
    echo "<p class='success'>🎯 SOLUCIÓN ALTERNATIVA: Usar objeto DateTime de PHP</p>";
} else {
    echo "<p class='error'>✗ ERROR con DateTime:</p>";
    echo "<pre>";
    print_r(sqlsrv_errors());
    echo "</pre>";
}

echo "<h2>7. RESUMEN DE RESULTADOS</h2>";
echo "<p><strong>Si alguna de las pruebas 5 o 6 fue exitosa, tenemos la solución al problema.</strong></p>";
echo "<p>Comparte los resultados de este diagnóstico para implementar la corrección en documentos.php</p>";

?>

</body>
</html>
