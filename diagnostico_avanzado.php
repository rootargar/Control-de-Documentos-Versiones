<!DOCTYPE html>
<html>
<head>
    <title>Diagnóstico Avanzado - Triggers y Constraints</title>
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
    <h1>Diagnóstico Avanzado de la Tabla Documentos</h1>

<?php
require_once 'conexion.php';

if (!$conn) {
    echo "<p class='error'>✗ Error de conexión</p>";
    exit;
}

echo "<h2>1. Verificar TRIGGERS en la tabla Documentos</h2>";
$sqlTriggers = "SELECT
    t.name AS trigger_name,
    OBJECT_NAME(t.parent_id) AS table_name,
    t.is_disabled,
    t.type_desc,
    OBJECT_DEFINITION(t.object_id) AS trigger_definition
FROM sys.triggers t
WHERE OBJECT_NAME(t.parent_id) = 'Documentos'";

$stmtTriggers = sqlsrv_query($conn, $sqlTriggers);
$hayTriggers = false;

echo "<table>";
echo "<tr><th>Trigger</th><th>Tipo</th><th>Deshabilitado</th><th>Definición</th></tr>";

while ($trigger = sqlsrv_fetch_array($stmtTriggers, SQLSRV_FETCH_ASSOC)) {
    $hayTriggers = true;
    echo "<tr>";
    echo "<td>" . htmlspecialchars($trigger['trigger_name']) . "</td>";
    echo "<td>" . htmlspecialchars($trigger['type_desc']) . "</td>";
    echo "<td>" . ($trigger['is_disabled'] ? 'Sí' : 'No') . "</td>";
    echo "<td><pre>" . htmlspecialchars(substr($trigger['trigger_definition'], 0, 500)) . "...</pre></td>";
    echo "</tr>";
}

echo "</table>";

if ($hayTriggers) {
    echo "<p class='warning'>⚠ Se encontraron TRIGGERS activos - Podrían estar causando el problema</p>";
} else {
    echo "<p class='success'>✓ No hay triggers en la tabla</p>";
}

echo "<h2>2. Verificar CONSTRAINTS en la tabla Documentos</h2>";
$sqlConstraints = "SELECT
    CONSTRAINT_NAME,
    CONSTRAINT_TYPE,
    COLUMN_NAME
FROM INFORMATION_SCHEMA.CONSTRAINT_COLUMN_USAGE
WHERE TABLE_NAME = 'Documentos'
ORDER BY CONSTRAINT_TYPE, CONSTRAINT_NAME";

$stmtConstraints = sqlsrv_query($conn, $sqlConstraints);

echo "<table>";
echo "<tr><th>Constraint</th><th>Tipo</th><th>Columna</th></tr>";

while ($constraint = sqlsrv_fetch_array($stmtConstraints, SQLSRV_FETCH_ASSOC)) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($constraint['CONSTRAINT_NAME']) . "</td>";
    echo "<td>" . htmlspecialchars($constraint['CONSTRAINT_TYPE']) . "</td>";
    echo "<td>" . htmlspecialchars($constraint['COLUMN_NAME']) . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h2>3. Verificar CHECK CONSTRAINTS específicos</h2>";
$sqlChecks = "SELECT
    cc.name AS constraint_name,
    cc.definition,
    cc.is_disabled
FROM sys.check_constraints cc
JOIN sys.tables t ON cc.parent_object_id = t.object_id
WHERE t.name = 'Documentos'";

$stmtChecks = sqlsrv_query($conn, $sqlChecks);
$hayChecks = false;

echo "<table>";
echo "<tr><th>Check Constraint</th><th>Definición</th><th>Deshabilitado</th></tr>";

while ($check = sqlsrv_fetch_array($stmtChecks, SQLSRV_FETCH_ASSOC)) {
    $hayChecks = true;
    echo "<tr>";
    echo "<td>" . htmlspecialchars($check['constraint_name']) . "</td>";
    echo "<td><pre>" . htmlspecialchars($check['definition']) . "</pre></td>";
    echo "<td>" . ($check['is_disabled'] ? 'Sí' : 'No') . "</td>";
    echo "</tr>";
}

echo "</table>";

if (!$hayChecks) {
    echo "<p class='info'>No hay CHECK constraints en la tabla</p>";
}

echo "<h2>4. Prueba: INSERT SIN fecha_creacion (como funcionaba antes)</h2>";

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
    echo "<p class='info'>Esto confirma que el problema está específicamente con fecha_creacion</p>";
} else {
    echo "<p class='error'>✗ ERROR:</p>";
    echo "<pre>";
    print_r(sqlsrv_errors());
    echo "</pre>";
}

echo "<h2>5. Prueba: INSERT con CONVERT explícito en fecha_creacion</h2>";

$codigo2 = 'TEST-CONVERT-' . time();
$fecha_creacion_str = '2025-11-25 00:00:00';

echo "<pre>";
echo "INSERT usando CONVERT(datetime, ?, 120)\n";
echo "Código: $codigo2\n";
echo "Fecha creación: $fecha_creacion_str\n";
echo "</pre>";

$sqlConvert = "INSERT INTO Documentos (nombre, codigo, categoria, area, departamento, responsable_id,
               descripcion, fecha_creacion, fecha_vencimiento, estado, activo)
               VALUES (?, ?, ?, ?, ?, ?, ?, CONVERT(datetime, ?, 120), ?, 'Pendiente', 1)";

$paramsConvert = array($nombre, $codigo2, $categoria, $area, $departamento, $responsable_id,
                      $descripcion, $fecha_creacion_str, $fecha_vencimiento);

$stmtConvert = sqlsrv_query($conn, $sqlConvert, $paramsConvert);

if ($stmtConvert) {
    echo "<p class='success'>✓ INSERT EXITOSO con CONVERT</p>";
} else {
    echo "<p class='error'>✗ ERROR con CONVERT:</p>";
    echo "<pre>";
    print_r(sqlsrv_errors());
    echo "</pre>";
}

echo "<h2>6. Prueba: INSERT con fecha_creacion = GETDATE()</h2>";

$codigo3 = 'TEST-GETDATE-' . time();

echo "<pre>";
echo "INSERT usando GETDATE() para fecha_creacion\n";
echo "Código: $codigo3\n";
echo "</pre>";

$sqlGetDate = "INSERT INTO Documentos (nombre, codigo, categoria, area, departamento, responsable_id,
               descripcion, fecha_creacion, fecha_vencimiento, estado, activo)
               VALUES (?, ?, ?, ?, ?, ?, ?, GETDATE(), ?, 'Pendiente', 1)";

$paramsGetDate = array($nombre, $codigo3, $categoria, $area, $departamento, $responsable_id,
                      $descripcion, $fecha_vencimiento);

$stmtGetDate = sqlsrv_query($conn, $sqlGetDate, $paramsGetDate);

if ($stmtGetDate) {
    echo "<p class='success'>✓ INSERT EXITOSO con GETDATE()</p>";
} else {
    echo "<p class='error'>✗ ERROR con GETDATE():</p>";
    echo "<pre>";
    print_r(sqlsrv_errors());
    echo "</pre>";
}

echo "<h2>7. Verificar valor por defecto de fecha_creacion</h2>";

$sqlDefaults = "SELECT
    c.name AS column_name,
    dc.definition AS default_value
FROM sys.columns c
LEFT JOIN sys.default_constraints dc ON c.default_object_id = dc.object_id
JOIN sys.tables t ON c.object_id = t.object_id
WHERE t.name = 'Documentos'
AND c.name IN ('fecha_creacion', 'fecha_modificacion', 'fecha_vencimiento')";

$stmtDefaults = sqlsrv_query($conn, $sqlDefaults);

echo "<table>";
echo "<tr><th>Columna</th><th>Valor por Defecto</th></tr>";

while ($def = sqlsrv_fetch_array($stmtDefaults, SQLSRV_FETCH_ASSOC)) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($def['column_name']) . "</td>";
    echo "<td>" . htmlspecialchars($def['default_value'] ?? 'NULL') . "</td>";
    echo "</tr>";
}
echo "</table>";

?>

</body>
</html>
