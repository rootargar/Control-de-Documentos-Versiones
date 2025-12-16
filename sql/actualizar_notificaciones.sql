-- Script para actualizar la tabla Notificaciones
-- Agrega campos necesarios para el sistema de notificaciones mejorado
-- Base de datos: CPP

USE CPP;
GO

-- Verificar si la columna usuario_id existe, si no existe, agregarla
IF NOT EXISTS (
    SELECT * FROM sys.columns
    WHERE object_id = OBJECT_ID('Notificaciones')
    AND name = 'usuario_id'
)
BEGIN
    ALTER TABLE Notificaciones
    ADD usuario_id INT NULL;

    -- Agregar clave foránea para usuario_id
    ALTER TABLE Notificaciones
    ADD CONSTRAINT FK_Notificaciones_Usuarios
    FOREIGN KEY (usuario_id) REFERENCES Usuarios(id);

    PRINT 'Columna usuario_id agregada con éxito';
END
ELSE
BEGIN
    PRINT 'La columna usuario_id ya existe';
END
GO

-- Verificar si la columna leida existe, si no existe, agregarla
IF NOT EXISTS (
    SELECT * FROM sys.columns
    WHERE object_id = OBJECT_ID('Notificaciones')
    AND name = 'leida'
)
BEGIN
    ALTER TABLE Notificaciones
    ADD leida BIT NOT NULL DEFAULT 0;

    PRINT 'Columna leida agregada con éxito';
END
ELSE
BEGIN
    PRINT 'La columna leida ya existe';
END
GO

-- Verificar si la columna enviado existe (del sistema antiguo)
-- Si existe, migrar los datos a leida y luego eliminarla
IF EXISTS (
    SELECT * FROM sys.columns
    WHERE object_id = OBJECT_ID('Notificaciones')
    AND name = 'enviado'
)
BEGIN
    -- Si leida existe, copiar valores de enviado a leida
    IF EXISTS (
        SELECT * FROM sys.columns
        WHERE object_id = OBJECT_ID('Notificaciones')
        AND name = 'leida'
    )
    BEGIN
        UPDATE Notificaciones
        SET leida = enviado
        WHERE leida = 0;

        PRINT 'Datos migrados de enviado a leida';
    END

    -- Nota: No eliminamos la columna enviado para mantener compatibilidad con código existente
    -- Si desea eliminarla, descomente la siguiente línea:
    -- ALTER TABLE Notificaciones DROP COLUMN enviado;
END
GO

-- Crear índices para mejorar el rendimiento
IF NOT EXISTS (
    SELECT * FROM sys.indexes
    WHERE name = 'IX_Notificaciones_Usuario'
    AND object_id = OBJECT_ID('Notificaciones')
)
BEGIN
    CREATE INDEX IX_Notificaciones_Usuario
    ON Notificaciones(usuario_id, leida, fecha_programada DESC);

    PRINT 'Índice IX_Notificaciones_Usuario creado con éxito';
END
ELSE
BEGIN
    PRINT 'El índice IX_Notificaciones_Usuario ya existe';
END
GO

IF NOT EXISTS (
    SELECT * FROM sys.indexes
    WHERE name = 'IX_Notificaciones_Documento'
    AND object_id = OBJECT_ID('Notificaciones')
)
BEGIN
    CREATE INDEX IX_Notificaciones_Documento
    ON Notificaciones(documento_id, fecha_programada DESC);

    PRINT 'Índice IX_Notificaciones_Documento creado con éxito';
END
ELSE
BEGIN
    PRINT 'El índice IX_Notificaciones_Documento ya existe';
END
GO

-- Migrar notificaciones existentes sin usuario_id
-- Asignar a administradores si no tienen usuario asignado
IF EXISTS (
    SELECT * FROM Notificaciones WHERE usuario_id IS NULL
)
BEGIN
    -- Obtener el ID del primer administrador
    DECLARE @admin_id INT;
    SELECT TOP 1 @admin_id = id FROM Usuarios WHERE rol_id = 1 AND estado = 'Activo';

    IF @admin_id IS NOT NULL
    BEGIN
        UPDATE Notificaciones
        SET usuario_id = @admin_id
        WHERE usuario_id IS NULL;

        PRINT 'Notificaciones sin usuario asignadas al administrador';
    END
END
GO

PRINT 'Script de actualización completado con éxito';
GO
