# Sistema de Notificaciones

## Descripción General

El sistema de notificaciones permite a los usuarios recibir alertas sobre eventos importantes relacionados con los documentos del sistema, como creaciones, aprobaciones, rechazos, nuevas versiones y más.

## Características Principales

### 1. **Notificaciones en Tiempo Real**
- Badge en el navbar que muestra el número de notificaciones no leídas
- Actualización automática cada 30 segundos
- Animación visual cuando llegan nuevas notificaciones

### 2. **Tipos de Eventos**
El sistema notifica sobre los siguientes eventos:

| Evento | Descripción | Icono | Quién recibe la notificación |
|--------|-------------|-------|------------------------------|
| **Creación** | Documento nuevo creado | 📄 | Administradores y Aprobadores |
| **Cambio Estado** | Documento aprobado/rechazado | ✅ | Responsable del documento |
| **Nueva Versión** | Nueva versión subida | 🔄 | Responsable y Aprobadores |
| **Próximo Vencimiento** | Documento cercano a vencer | ⏰ | Responsable y Administradores |
| **Asignación** | Documento asignado | 👤 | Usuario asignado |
| **Comentario** | Nuevo comentario agregado | 💬 | Usuarios relacionados |

### 3. **Interfaz de Usuario**

#### Panel Desplegable (Navbar)
- Click en el icono de campana (🔔) para abrir
- Muestra las últimas 10 notificaciones
- Indica notificaciones no leídas con fondo azul claro
- Botón para marcar todas como leídas
- Link al centro de notificaciones completo

#### Centro de Notificaciones (`ver_notificaciones.php`)
- Vista completa de todas las notificaciones
- Filtros: "Todas" y "No leídas"
- Acciones individuales: Ver documento, Eliminar
- Acción global: Marcar todas como leídas
- Contador de notificaciones no leídas
- Click en notificación para marcarla como leída

## Arquitectura Técnica

### Archivos del Sistema

#### Backend PHP

1. **`notificaciones.php`** - Funciones principales
   ```php
   - crearNotificacion($documento_id, $tipo_evento, $mensaje, $usuarios_destino)
   - obtenerNotificaciones($usuario_id, $solo_no_leidas, $limite)
   - marcarNotificacionLeida($notificacion_id, $usuario_id)
   - marcarTodasLeidas($usuario_id)
   - contarNotificacionesNoLeidas($usuario_id)
   - eliminarNotificacion($notificacion_id, $usuario_id)
   - notificarEventoDocumento($documento_id, $tipo_evento, $mensaje, $excluir_usuario_id)
   ```

2. **`api_notificaciones.php`** - Endpoint API REST
   - GET `?accion=obtener` - Obtener notificaciones
   - GET `?accion=contar` - Contar no leídas
   - POST `?accion=marcar_leida` - Marcar una como leída
   - POST `?accion=marcar_todas_leidas` - Marcar todas como leídas
   - POST `?accion=eliminar` - Eliminar notificación
   - GET `?accion=obtener_recientes` - Últimas 10 notificaciones

3. **`componente_notificaciones.php`** - Widget para incluir en páginas
   - Icono de campana con badge
   - Panel desplegable
   - JavaScript para actualización automática

4. **`ver_notificaciones.php`** - Página completa de gestión

#### Base de Datos

**Tabla: Notificaciones**
```sql
- id (INT, PK, IDENTITY)
- documento_id (INT, FK → Documentos)
- usuario_id (INT, FK → Usuarios)
- tipo_evento (VARCHAR)
- mensaje (VARCHAR)
- fecha_programada (DATETIME)
- leida (BIT) - 0: no leída, 1: leída
- enviado (BIT) - campo legacy, mantiene compatibilidad
```

**Índices:**
- `IX_Notificaciones_Usuario` en (usuario_id, leida, fecha_programada DESC)
- `IX_Notificaciones_Documento` en (documento_id, fecha_programada DESC)

### Migración de Base de Datos

Para actualizar la base de datos, ejecutar:

```bash
# Desde SQL Server Management Studio o línea de comandos:
sqlcmd -S servidor -d CPP -i sql/actualizar_notificaciones.sql
```

O ejecutar manualmente el script `sql/actualizar_notificaciones.sql`

## Integración en Páginas

### Agregar Widget de Notificaciones

En cualquier página con navbar, agregar:

```php
<?php include 'componente_notificaciones.php'; ?>
```

Ejemplo completo:
```php
<div class="navbar">
    <h1>Mi Página</h1>
    <div class="nav-actions">
        <?php include 'componente_notificaciones.php'; ?>
        <div class="user-info">
            <strong><?php echo obtenerNombreUsuario(); ?></strong>
        </div>
    </div>
</div>
```

### Crear Notificaciones en el Código

#### Método 1: Función Simple
```php
require_once 'notificaciones.php';

// Notificar a un usuario específico
crearNotificacion(
    $documento_id,
    'Creacion',
    'Se ha creado un nuevo documento',
    $usuario_id
);

// Notificar a múltiples usuarios
crearNotificacion(
    $documento_id,
    'Cambio Estado',
    'El documento ha sido aprobado',
    [$usuario1_id, $usuario2_id, $usuario3_id]
);
```

#### Método 2: Función Automática (Recomendada)
```php
require_once 'notificaciones.php';

// La función determina automáticamente quién debe ser notificado
// según el tipo de evento
notificarEventoDocumento(
    $documento_id,
    'Nueva Version',
    'Se ha subido la versión 2.0 del documento XYZ',
    $_SESSION['usuario_id'] // Usuario a excluir (quien realizó la acción)
);
```

## Reglas de Negocio

### Quién Recibe Notificaciones

1. **Evento: Creación**
   - Todos los Administradores activos
   - Todos los Aprobadores activos
   - Excluye quien creó el documento

2. **Evento: Cambio Estado**
   - Solo el responsable del documento
   - Excluye quien cambió el estado

3. **Evento: Nueva Versión**
   - Responsable del documento
   - Todos los Aprobadores activos
   - Excluye quien subió la versión

4. **Evento: Próximo Vencimiento**
   - Responsable del documento
   - Todos los Administradores activos

### Marcado de Leídas

Las notificaciones se marcan como leídas:
1. **Automáticamente:** Al hacer click en la notificación
2. **Manualmente:** Al hacer click en "Marcar como leída"
3. **Global:** Con el botón "Marcar todas como leídas"

## Personalización

### Agregar Nuevos Tipos de Eventos

1. **Actualizar función `obtenerIconoEvento()`** en `notificaciones.php`:
```php
$iconos = array(
    'Mi Nuevo Evento' => '🎯',
    // ... otros iconos
);
```

2. **Actualizar función `obtenerColorEvento()`**:
```php
$colores = array(
    'Mi Nuevo Evento' => '#ff5722',
    // ... otros colores
);
```

3. **Agregar lógica en `notificarEventoDocumento()`**:
```php
case 'Mi Nuevo Evento':
    // Determinar quién debe ser notificado
    $usuarios_notificar[] = $usuario_especifico;
    break;
```

### Configurar Intervalo de Actualización

En `componente_notificaciones.php`, línea ~290:
```javascript
// Cambiar de 30000 (30 segundos) a otro valor
setInterval(actualizarContador, 30000); // ms
```

## Solución de Problemas

### Las notificaciones no aparecen

1. **Verificar que la tabla esté actualizada:**
   ```sql
   SELECT * FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_NAME = 'Notificaciones'
   ```
   Debe tener columnas: `usuario_id` y `leida`

2. **Verificar permisos de usuario en la sesión:**
   ```php
   var_dump($_SESSION['usuario_id']);
   ```

3. **Revisar errores de JavaScript en la consola del navegador**

### El badge no se actualiza

1. Verificar que `api_notificaciones.php` sea accesible
2. Revisar la consola del navegador para errores AJAX
3. Verificar que la sesión esté activa

### Las notificaciones no se crean

1. **Verificar que se incluyó el archivo:**
   ```php
   require_once 'notificaciones.php';
   ```

2. **Verificar logs de error PHP:**
   ```bash
   tail -f /var/log/php_errors.log
   ```

3. **Probar creación manual:**
   ```php
   $resultado = crearNotificacion(1, 'Creacion', 'Test', 1);
   var_dump($resultado); // Debe ser true
   ```

## Próximas Mejoras

- [ ] Notificaciones por email
- [ ] Notificaciones push del navegador
- [ ] Preferencias de notificación por usuario
- [ ] Notificaciones de documentos próximos a vencer (tarea programada)
- [ ] Resumen diario/semanal de notificaciones
- [ ] Sonido al recibir notificación
- [ ] Webhooks para integraciones externas

## Soporte

Para problemas o preguntas sobre el sistema de notificaciones:
1. Revisar este documento primero
2. Consultar el código en `notificaciones.php` (bien documentado)
3. Revisar logs de error del servidor
4. Contactar al administrador del sistema
