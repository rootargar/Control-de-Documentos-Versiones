# Manual de Usuario
## Sistema de Gestión Documental

**Versión:** 1.0
**Fecha:** Diciembre 2025

---

## Tabla de Contenidos

1. [Introducción](#1-introducción)
2. [Inicio de Sesión](#2-inicio-de-sesión)
3. [Página Principal](#3-página-principal)
4. [Consultar Documentos](#4-consultar-documentos)
5. [Gestión de Documentos](#5-gestión-de-documentos)
6. [Subir Versiones de Documentos](#6-subir-versiones-de-documentos)
7. [Aprobar Documentos](#7-aprobar-documentos)
8. [Gestión de Usuarios](#8-gestión-de-usuarios)
9. [Auditoría del Sistema](#9-auditoría-del-sistema)
10. [Preguntas Frecuentes](#10-preguntas-frecuentes)

---

## 1. Introducción

### ¿Qué es el Sistema de Gestión Documental?

El Sistema de Gestión Documental es una aplicación web diseñada para administrar, controlar y versionar documentos de la organización de manera eficiente y segura. Permite a los usuarios crear, aprobar, consultar y gestionar documentos con control de versiones.

### Características Principales

- **Control de versiones automático** de documentos
- **Gestión de permisos** por roles de usuario
- **Aprobación de documentos** con flujo de trabajo
- **Auditoría completa** de todas las acciones
- **Búsqueda y consulta** de documentos aprobados
- **Gestión de usuarios** y roles

### Roles de Usuario

El sistema cuenta con cuatro roles principales:

| Rol | Descripción | Permisos |
|-----|-------------|----------|
| **Administrador** | Acceso completo al sistema | Gestionar usuarios, documentos, aprobar, auditoría |
| **Editor** | Creación y edición de documentos | Crear, editar, subir versiones de documentos |
| **Aprobador** | Revisión y aprobación | Aprobar o rechazar documentos pendientes |
| **Consultor** | Solo lectura | Consultar documentos aprobados |

---

## 2. Inicio de Sesión

### Acceder al Sistema

1. Abra su navegador web e ingrese a la URL del sistema
2. Se mostrará la pantalla de inicio de sesión
3. Ingrese sus credenciales:
   - **Usuario:** Su nombre de usuario asignado
   - **Contraseña:** Su contraseña personal
4. Haga clic en el botón **"Iniciar Sesión"**

### Consideraciones de Seguridad

- No comparta sus credenciales con otras personas
- Cierre sesión cuando termine de usar el sistema
- Si olvida su contraseña, contacte al administrador del sistema

---

## 3. Página Principal

Al iniciar sesión exitosamente, será redirigido a la **Página Principal** (Dashboard) donde encontrará:

### Panel de Estadísticas

Visualización de métricas importantes del sistema:

- **Usuarios Activos:** Cantidad de usuarios registrados y activos
- **Documentos Totales:** Total de documentos en el sistema
- **Documentos Pendientes:** Documentos esperando aprobación
- **Documentos Aprobados:** Documentos aprobados y disponibles

### Menú de Navegación

Opciones disponibles según su rol:

- **Gestión de Usuarios** (solo Administradores)
- **Gestión de Documentos** (Administradores, Editores, Aprobadores)
- **Consultar Documentos** (todos los usuarios)
- **Aprobar Documentos** (Administradores, Editores, Aprobadores)
- **Auditoría** (solo Administradores)

### Documentos Recientemente Modificados

Tabla que muestra los últimos 5 documentos modificados con:
- Código del documento
- Nombre
- Responsable
- Estado actual
- Fecha de última modificación

---

## 4. Consultar Documentos

Esta función permite visualizar todos los documentos **aprobados** del sistema.

### Cómo Consultar Documentos

1. Desde la página principal, haga clic en **"Consultar Documentos"**
2. Se mostrará una tabla con todos los documentos aprobados
3. La tabla incluye:
   - Nombre del documento
   - Categoría (Proceso, Política, Procedimiento)
   - Fecha de creación
   - Fecha de modificación
   - Área
   - Estado

### Visualizar un Documento

Para ver un documento específico:

1. Localice el documento en la tabla
2. Haga clic en el botón **"👁️ Ver PDF"** para visualizar el archivo en el navegador
3. O haga clic en **"📄 Detalles"** para ver información completa y todas las versiones

### Vista de Detalles

Al hacer clic en "Detalles", podrá ver:
- Información general del documento
- Todas las versiones disponibles
- Comentarios de cada versión
- Historial de cambios
- Opción de descargar versiones específicas

---

## 5. Gestión de Documentos

**Disponible para:** Administradores, Editores y Aprobadores

Esta sección permite crear nuevos documentos y administrar los existentes.

### Crear un Nuevo Documento

1. Acceda a **"Gestión de Documentos"** desde el menú principal
2. Complete el formulario con la siguiente información:

   **Campos Obligatorios (*):**
   - **Nombre del Documento:** Título descriptivo del documento
   - **Código:** Código único de identificación (ej: POL-001, PROC-045)
   - **Responsable:** Usuario responsable del documento

   **Campos Opcionales:**
   - **Categoría:** Proceso, Política o Procedimiento
   - **Área:** Administración, Refacciones, Servicio, Unidades
   - **Departamento:** Departamento correspondiente
   - **Fecha de Elaboración:** Fecha de creación (por defecto, fecha actual)
   - **Fecha de Vencimiento:** Fecha de caducidad del documento
   - **Descripción:** Descripción breve del contenido

3. Haga clic en **"Crear Documento"**
4. El sistema confirmará la creación exitosa

**Nota:** El documento se crea en estado **"Pendiente"** hasta que se suba una versión y sea aprobado.

### Editar un Documento

1. En la lista de documentos, localice el documento que desea editar
2. Haga clic en el botón **"Editar"**
3. Modifique los campos necesarios
4. Los administradores pueden cambiar el estado del documento
5. Haga clic en **"Actualizar Documento"**

### Eliminar un Documento

1. Localice el documento en la lista
2. Haga clic en el botón **"Eliminar"**
3. Confirme la acción en el mensaje de confirmación
4. El documento se marcará como inactivo (no se elimina físicamente)

---

## 6. Subir Versiones de Documentos

**Disponible para:** Administradores, Editores y Aprobadores

El sistema maneja **control de versiones automático** de documentos.

### Proceso de Subida

1. En la lista de documentos, haga clic en el botón **"Subir"** del documento deseado
2. Se mostrará la información del documento actual
3. Complete el formulario de subida:
   - **Archivo:** Seleccione el archivo PDF desde su computadora
   - **Comentario:** (Opcional) Describa los cambios realizados en esta versión

4. Haga clic en **"Subir Nueva Versión"**

### Versionamiento Automático

El sistema incrementa automáticamente la versión:
- **Primera versión:** 1.0
- **Versiones subsecuentes:** Se incrementan en 0.1 (1.1, 1.2, 1.3, etc.)

### Requisitos de Archivos

- **Formato:** Solo archivos PDF
- **Tamaño máximo:** Según configuración del sistema
- **Nombre:** Se genera automáticamente un nombre único

### Visualizar Versiones

1. Haga clic en **"Ver"** o **"Detalles"** del documento
2. Se mostrará la lista de todas las versiones con:
   - Número de versión
   - Fecha de subida
   - Usuario que la subió
   - Comentario
   - Tamaño del archivo
3. Puede descargar o visualizar cualquier versión anterior

---

## 7. Aprobar Documentos

**Disponible para:** Administradores, Editores y Aprobadores

Esta función permite revisar y aprobar documentos que están en estado **"Pendiente"**.

### Proceso de Aprobación

1. Acceda a **"Aprobar Documentos"** desde el menú principal
2. Se mostrará la lista de documentos pendientes de aprobación
3. Para cada documento puede:
   - **Ver detalles:** Revisar información completa
   - **Visualizar PDF:** Ver el documento actual
   - **Aprobar:** Cambiar estado a "Aprobado"
   - **Rechazar:** Cambiar estado a "Rechazado"

### Aprobar un Documento

1. Revise cuidadosamente el documento
2. Haga clic en el botón **"Aprobar"**
3. Opcionalmente, agregue un comentario de aprobación
4. Confirme la acción
5. El documento cambiará a estado **"Aprobado"** y estará disponible para consulta

### Rechazar un Documento

1. Si el documento requiere correcciones
2. Haga clic en el botón **"Rechazar"**
3. **Es importante** agregar un comentario indicando el motivo del rechazo
4. Confirme la acción
5. El documento cambiará a estado **"Rechazado"**
6. El responsable deberá realizar las correcciones necesarias

### Estados de Documentos

- **Pendiente:** Documento creado, esperando aprobación
- **Aprobado:** Documento revisado y aprobado, disponible para consulta
- **Rechazado:** Documento que requiere correcciones

---

## 8. Gestión de Usuarios

**Disponible solo para:** Administradores

Esta sección permite administrar los usuarios del sistema.

### Crear un Nuevo Usuario

1. Acceda a **"Gestión de Usuarios"**
2. Complete el formulario con:
   - **Nombre completo**
   - **Usuario:** Nombre de usuario para login (único)
   - **Contraseña:** Contraseña inicial
   - **Rol:** Seleccione el rol apropiado
   - **Estado:** Activo o Inactivo

3. Haga clic en **"Crear Usuario"**

### Editar un Usuario

1. Localice el usuario en la lista
2. Haga clic en **"Editar"**
3. Modifique los campos necesarios
4. Puede cambiar:
   - Nombre
   - Contraseña
   - Rol
   - Estado (activar/desactivar)

5. Haga clic en **"Actualizar Usuario"**

### Desactivar un Usuario

1. Es preferible desactivar usuarios en lugar de eliminarlos
2. Edite el usuario
3. Cambie el estado a **"Inactivo"**
4. El usuario no podrá iniciar sesión pero se mantiene el historial

### Buenas Prácticas

- Asigne roles según las responsabilidades reales del usuario
- Revise periódicamente los usuarios activos
- Desactive usuarios que ya no requieren acceso
- Use contraseñas seguras

---

## 9. Auditoría del Sistema

**Disponible solo para:** Administradores

El módulo de auditoría registra todas las acciones importantes del sistema.

### Acceder a la Auditoría

1. Desde el menú principal, haga clic en **"Auditoría"**
2. Se mostrará el registro completo de actividades

### Información Registrada

El sistema registra automáticamente:

- **Usuario:** Quién realizó la acción
- **Acción:** Tipo de operación realizada
  - Login/Logout
  - Crear/Editar/Eliminar Documento
  - Subir Versión
  - Aprobar/Rechazar Documento
  - Crear/Editar/Eliminar Usuario
  - Cambio de Estado

- **Descripción:** Detalles de la acción
- **Fecha y Hora:** Momento exacto de la acción
- **Dirección IP:** IP desde donde se realizó la acción
- **Tabla Afectada:** Entidad del sistema modificada

### Filtrar Auditoría

Puede filtrar los registros por:
- Rango de fechas
- Usuario específico
- Tipo de acción
- Tabla afectada

### Casos de Uso

- **Seguimiento de cambios:** Verificar quién modificó un documento
- **Seguridad:** Detectar accesos no autorizados
- **Cumplimiento:** Mantener trazabilidad de operaciones
- **Resolución de problemas:** Investigar errores o inconsistencias

---

## 10. Preguntas Frecuentes

### ¿Qué hago si olvido mi contraseña?

Contacte al administrador del sistema para que restablezca su contraseña.

### ¿Puedo eliminar una versión de un documento?

No, el sistema mantiene todas las versiones para trazabilidad. Solo se pueden agregar nuevas versiones.

### ¿Qué formatos de archivo acepta el sistema?

Actualmente el sistema solo acepta archivos en formato **PDF**.

### ¿Puedo editar un documento aprobado?

Sí, puede editar la información del documento, pero debe subir una nueva versión y esta deberá ser aprobada nuevamente.

### ¿Cómo sé si un documento está próximo a vencer?

El sistema muestra la fecha de vencimiento en los detalles del documento. Los administradores pueden generar reportes de documentos próximos a vencer.

### ¿Puedo descargar documentos?

Sí, puede visualizar documentos en el navegador y descargarlos usando la opción de descarga del visor PDF.

### ¿Qué hago si aparece un error al subir un documento?

Verifique que:
- El archivo sea un PDF válido
- No exceda el tamaño máximo permitido
- Tenga conexión estable a internet
- Sus permisos sean correctos

Si el problema persiste, contacte al administrador del sistema.

### ¿Cuánto tiempo se mantienen los registros de auditoría?

Los registros de auditoría se mantienen indefinidamente para cumplimiento normativo. Consulte con su administrador sobre políticas de retención.

### ¿Puedo cambiar mi propia contraseña?

Actualmente debe solicitar al administrador que cambie su contraseña. Una futura actualización puede incluir cambio de contraseña por el usuario.

### ¿El sistema funciona en dispositivos móviles?

El sistema es accesible desde navegadores móviles, aunque está optimizado para uso en computadoras de escritorio.

---

## Soporte Técnico

Para asistencia técnica o consultas adicionales, contacte a:

- **Departamento de Sistemas**
- **Administrador del Sistema**

---

**Fin del Manual de Usuario**
