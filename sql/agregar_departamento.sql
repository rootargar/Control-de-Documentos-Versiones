-- Script para agregar la columna 'departamento' a la tabla Documentos
-- Fecha: 2025-11-25
-- Descripción: Agrega un nuevo campo VARCHAR para almacenar el departamento del documento

USE CPP;
GO

-- Verificar si la columna ya existe antes de agregarla
IF NOT EXISTS (
    SELECT * FROM sys.columns
    WHERE object_id = OBJECT_ID(N'dbo.Documentos')
    AND name = 'departamento'
)
BEGIN
    ALTER TABLE dbo.Documentos
    ADD departamento VARCHAR(100) NULL;

    PRINT 'Columna departamento agregada exitosamente a la tabla Documentos';
END
ELSE
BEGIN
    PRINT 'La columna departamento ya existe en la tabla Documentos';
END
GO
