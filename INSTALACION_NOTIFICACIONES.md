# Guía de Instalación - Sistema de Notificaciones por Email

Esta guía te ayudará a configurar el sistema de notificaciones por email en 5 pasos simples.

## 📋 Requisitos Previos

- PHP 7.4 o superior
- SQL Server
- Acceso a servidor SMTP (Gmail, Outlook, etc.)
- Composer instalado (ya ejecutado)

## 🚀 Instalación Rápida

### Paso 1: Actualizar Base de Datos

Ejecutar los siguientes scripts SQL en orden:

```bash
# Script 1: Actualizar tabla de notificaciones
sqlcmd -S tu_servidor -d CPP -i sql/actualizar_notificaciones.sql

# Script 2: Agregar campo recibir_emails a usuarios
sqlcmd -S tu_servidor -d CPP -i sql/agregar_campo_recibir_emails.sql
```

O desde SQL Server Management Studio, abrir y ejecutar manualmente ambos archivos.

### Paso 2: Configurar Servidor SMTP

Editar el archivo `config_email.php`:

```php
// ACTIVAR el sistema de emails
define('EMAIL_ENABLED', true);
define('EMAIL_SEND_IMMEDIATE', true);

// CONFIGURAR tu servidor SMTP
define('SMTP_HOST', 'smtp.gmail.com');        // Tu servidor
define('SMTP_PORT', 587);                      // Puerto
define('SMTP_SECURE', 'tls');                  // Seguridad
define('SMTP_USERNAME', 'tu_email@gmail.com'); // Tu email
define('SMTP_PASSWORD', 'xxxx xxxx xxxx xxxx');// App Password

// CONFIGURAR remitente
define('EMAIL_FROM_ADDRESS', 'noreply@tusistema.com');
define('EMAIL_FROM_NAME', 'Sistema Gestión Documental');

// URL de tu sistema (para enlaces en emails)
define('SYSTEM_BASE_URL', 'http://localhost/Control-de-Documentos-Versiones');
```

### Paso 3: Obtener Credenciales SMTP

#### Para Gmail:

1. Ve a https://myaccount.google.com/apppasswords
2. Crea una "Contraseña de aplicación"
3. Usa esa contraseña en `SMTP_PASSWORD`

#### Para Outlook:

```php
define('SMTP_HOST', 'smtp.office365.com');
define('SMTP_USERNAME', 'tu_email@outlook.com');
define('SMTP_PASSWORD', 'tu_contraseña_normal');
```

#### Para otros proveedores:

Ver ejemplos en `config_email.php` (líneas 85-120)

### Paso 4: Probar Configuración

1. Acceder como **Administrador** al sistema
2. Ir a `probar_email.php`
3. Ingresar tu email de prueba
4. Click en "Enviar Email de Prueba"
5. Verificar que llegue el email

### Paso 5: Configurar Usuarios

Los emails se envían automáticamente a usuarios que tengan:

- ✅ Email configurado en su perfil
- ✅ Campo `recibir_emails = 1` (activado por defecto)
- ✅ Estado activo

Para editar usuarios, ir a `usuarios.php` y asegurarse de que tengan email.

## 🎨 Características

Los emails incluyen:

- ✅ Diseño HTML profesional y responsivo
- ✅ Plantillas personalizadas por tipo de evento
- ✅ Colores e iconos distintivos
- ✅ Información completa del documento
- ✅ Botón de acción para ver el documento
- ✅ Enlaces directos al sistema
- ✅ Versión texto plano para compatibilidad

## 📧 Tipos de Notificaciones por Email

El sistema envía emails automáticamente cuando:

| Evento | Quién recibe el email |
|--------|----------------------|
| 📄 **Documento creado** | Administradores y Aprobadores |
| ✅ **Documento aprobado/rechazado** | Responsable del documento |
| 🔄 **Nueva versión subida** | Responsable y Aprobadores |
| ⏰ **Documento próximo a vencer** | Responsable y Administradores |

## ⚙️ Configuración Avanzada

### Deshabilitar emails temporalmente

```php
// En config_email.php
define('EMAIL_ENABLED', false);
```

Esto mantiene las notificaciones web activas pero deshabilita los emails.

### Activar modo debug

```php
// En config_email.php
define('SMTP_DEBUG', 2); // Muestra mensajes detallados
```

### Cambiar URL base del sistema

```php
// En config_email.php
define('SYSTEM_BASE_URL', 'https://midominio.com/sistema');
```

## 🔧 Solución de Problemas

### Los emails no se envían

1. **Verificar configuración:**
   - Ir a `probar_email.php`
   - Revisar el estado de configuración
   - Debe mostrar "✅ Configuración Válida"

2. **Verificar credenciales SMTP:**
   - Usuario y contraseña correctos
   - Para Gmail, usar "Contraseña de aplicación"
   - Puerto correcto (587 para TLS, 465 para SSL)

3. **Revisar logs de error:**
   ```bash
   tail -f /var/log/php_errors.log
   ```

4. **Verificar firewall:**
   - Puerto 587 debe estar abierto
   - Permitir conexiones salientes a servidor SMTP

### Gmail bloquea el acceso

- Activar "Verificación en 2 pasos"
- Generar "Contraseña de aplicación"
- NO usar contraseña normal de Gmail

### El usuario no recibe emails

1. Verificar que el usuario tenga email en su perfil
2. Verificar campo `recibir_emails = 1` en base de datos
3. Verificar que el usuario esté activo
4. Revisar carpeta de spam/correo no deseado

## 📚 Documentación Completa

Para más detalles, ver:
- `NOTIFICACIONES_README.md` - Documentación completa del sistema
- `config_email.php` - Ejemplos de configuración para diferentes proveedores
- `email_notificaciones.php` - Código fuente con documentación

## 🎯 Resumen de Archivos

- ✅ `config_email.php` - **Configurar este archivo primero**
- ✅ `email_notificaciones.php` - Funciones de envío (no modificar)
- ✅ `probar_email.php` - Página de prueba (acceso admin)
- ✅ `sql/actualizar_notificaciones.sql` - Ejecutar en BD
- ✅ `sql/agregar_campo_recibir_emails.sql` - Ejecutar en BD
- ✅ `vendor/` - Librería PHPMailer (no modificar)

## ✅ Checklist de Instalación

- [ ] Ejecutar script `sql/actualizar_notificaciones.sql`
- [ ] Ejecutar script `sql/agregar_campo_recibir_emails.sql`
- [ ] Editar `config_email.php` con credenciales SMTP
- [ ] Cambiar `EMAIL_ENABLED` a `true`
- [ ] Configurar `SYSTEM_BASE_URL` con tu dominio
- [ ] Probar envío con `probar_email.php`
- [ ] Verificar que usuarios tengan email configurado
- [ ] Crear un documento de prueba para verificar notificaciones

## 🆘 Soporte

Si tienes problemas:
1. Revisar `probar_email.php` para diagnosticar
2. Activar `SMTP_DEBUG = 2` en `config_email.php`
3. Revisar logs de error de PHP
4. Consultar documentación completa en `NOTIFICACIONES_README.md`

---

**¡Listo!** El sistema de notificaciones por email está completamente funcional. 🎉
