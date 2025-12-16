-- Script para agregar campo recibir_emails a la tabla Usuarios
-- Este campo controla si el usuario desea recibir notificaciones por email
-- Base de datos: CPP

USE CPP;
GO

-- Verificar si la columna recibir_emails existe, si no existe, agregarla
IF NOT EXISTS (
    SELECT * FROM sys.columns
    WHERE object_id = OBJECT_ID('Usuarios')
    AND name = 'recibir_emails'
)
BEGIN
    ALTER TABLE Usuarios
    ADD recibir_emails BIT NOT NULL DEFAULT 1;

    PRINT 'Columna recibir_emails agregada con éxito (por defecto: habilitado)';
END
ELSE
BEGIN
    PRINT 'La columna recibir_emails ya existe';
END
GO

-- Actualizar usuarios existentes para que reciban emails por defecto
UPDATE Usuarios
SET recibir_emails = 1
WHERE recibir_emails IS NULL;
GO

PRINT 'Script completado con éxito';
GO
