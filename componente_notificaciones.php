<?php
/**
 * Componente de Notificaciones
 * Widget de notificaciones para incluir en el navbar
 */

// Este archivo debe ser incluido después de verificar_login.php
// y debe tener acceso a $_SESSION['usuario_id']

if (!isset($_SESSION['usuario_id'])) {
    return;
}
?>

<!-- Estilos del componente de notificaciones -->
<style>
    .notificaciones-container {
        position: relative;
        display: inline-block;
        margin-right: 20px;
    }

    .notificaciones-bell {
        position: relative;
        cursor: pointer;
        font-size: 24px;
        color: white;
        background: none;
        border: none;
        padding: 8px;
        border-radius: 50%;
        transition: background-color 0.3s;
    }

    .notificaciones-bell:hover {
        background-color: rgba(255, 255, 255, 0.1);
    }

    .notificaciones-badge {
        position: absolute;
        top: 2px;
        right: 2px;
        background: #f44336;
        color: white;
        border-radius: 10px;
        padding: 2px 6px;
        font-size: 11px;
        font-weight: bold;
        min-width: 18px;
        text-align: center;
    }

    .notificaciones-dropdown {
        position: absolute;
        top: 100%;
        right: 0;
        margin-top: 10px;
        width: 380px;
        max-height: 500px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        display: none;
        z-index: 1000;
        overflow: hidden;
    }

    .notificaciones-dropdown.activo {
        display: block;
    }

    .notificaciones-header {
        padding: 15px 20px;
        background: linear-gradient(135deg, #027be3 0%, #2196f3 100%);
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .notificaciones-header h3 {
        margin: 0;
        font-size: 16px;
    }

    .marcar-todas-btn {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        border: none;
        padding: 5px 12px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
        transition: background-color 0.3s;
    }

    .marcar-todas-btn:hover {
        background: rgba(255, 255, 255, 0.3);
    }

    .notificaciones-lista {
        max-height: 400px;
        overflow-y: auto;
    }

    .notificacion-item {
        padding: 15px 20px;
        border-bottom: 1px solid #f0f0f0;
        cursor: pointer;
        transition: background-color 0.2s;
        display: flex;
        gap: 12px;
        align-items: flex-start;
    }

    .notificacion-item:hover {
        background-color: #f8f9fa;
    }

    .notificacion-item.no-leida {
        background-color: #e3f2fd;
    }

    .notificacion-item.no-leida:hover {
        background-color: #bbdefb;
    }

    .notificacion-icono {
        font-size: 24px;
        flex-shrink: 0;
    }

    .notificacion-contenido {
        flex: 1;
        min-width: 0;
    }

    .notificacion-mensaje {
        color: #333;
        font-size: 14px;
        margin-bottom: 5px;
        line-height: 1.4;
    }

    .notificacion-tiempo {
        color: #666;
        font-size: 12px;
    }

    .notificacion-indicador {
        width: 8px;
        height: 8px;
        background: #2196f3;
        border-radius: 50%;
        flex-shrink: 0;
        margin-top: 5px;
    }

    .notificaciones-footer {
        padding: 12px 20px;
        text-align: center;
        border-top: 1px solid #f0f0f0;
    }

    .ver-todas-btn {
        color: #027be3;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        transition: color 0.3s;
    }

    .ver-todas-btn:hover {
        color: #0056b3;
        text-decoration: underline;
    }

    .notificaciones-vacio {
        padding: 40px 20px;
        text-align: center;
        color: #999;
    }

    .notificaciones-vacio-icono {
        font-size: 48px;
        margin-bottom: 10px;
    }

    .notificaciones-vacio-texto {
        font-size: 14px;
    }

    /* Animación de actualización */
    @keyframes shake {
        0%, 100% { transform: rotate(0deg); }
        25% { transform: rotate(-15deg); }
        75% { transform: rotate(15deg); }
    }

    .notificaciones-bell.nueva-notificacion {
        animation: shake 0.5s ease-in-out;
    }

    /* Scrollbar personalizado */
    .notificaciones-lista::-webkit-scrollbar {
        width: 6px;
    }

    .notificaciones-lista::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .notificaciones-lista::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 3px;
    }

    .notificaciones-lista::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
</style>

<!-- HTML del componente de notificaciones -->
<div class="notificaciones-container">
    <button class="notificaciones-bell" id="notificaciones-bell" title="Notificaciones">
        🔔
        <span class="notificaciones-badge" id="notificaciones-badge" style="display: none;">0</span>
    </button>

    <div class="notificaciones-dropdown" id="notificaciones-dropdown">
        <div class="notificaciones-header">
            <h3>Notificaciones</h3>
            <button class="marcar-todas-btn" id="marcar-todas-btn" title="Marcar todas como leídas">
                Marcar todas
            </button>
        </div>

        <div class="notificaciones-lista" id="notificaciones-lista">
            <!-- Las notificaciones se cargarán aquí dinámicamente -->
            <div class="notificaciones-vacio">
                <div class="notificaciones-vacio-icono">📭</div>
                <div class="notificaciones-vacio-texto">No tienes notificaciones</div>
            </div>
        </div>

        <div class="notificaciones-footer">
            <a href="ver_notificaciones.php" class="ver-todas-btn">Ver todas las notificaciones</a>
        </div>
    </div>
</div>

<!-- JavaScript del componente de notificaciones -->
<script>
(function() {
    const bell = document.getElementById('notificaciones-bell');
    const badge = document.getElementById('notificaciones-badge');
    const dropdown = document.getElementById('notificaciones-dropdown');
    const lista = document.getElementById('notificaciones-lista');
    const marcarTodasBtn = document.getElementById('marcar-todas-btn');

    let dropdownAbierto = false;
    let ultimoTotal = 0;

    // Toggle del dropdown
    bell.addEventListener('click', function(e) {
        e.stopPropagation();
        dropdownAbierto = !dropdownAbierto;

        if (dropdownAbierto) {
            dropdown.classList.add('activo');
            cargarNotificaciones();
        } else {
            dropdown.classList.remove('activo');
        }
    });

    // Cerrar dropdown al hacer click fuera
    document.addEventListener('click', function(e) {
        if (!dropdown.contains(e.target) && e.target !== bell) {
            dropdown.classList.remove('activo');
            dropdownAbierto = false;
        }
    });

    // Marcar todas como leídas
    marcarTodasBtn.addEventListener('click', function() {
        fetch('api_notificaciones.php?accion=marcar_todas_leidas', {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                cargarNotificaciones();
                actualizarContador();
            }
        })
        .catch(error => console.error('Error:', error));
    });

    // Cargar notificaciones
    function cargarNotificaciones() {
        fetch('api_notificaciones.php?accion=obtener_recientes')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarNotificaciones(data.notificaciones);
                }
            })
            .catch(error => console.error('Error:', error));
    }

    // Mostrar notificaciones en el dropdown
    function mostrarNotificaciones(notificaciones) {
        if (notificaciones.length === 0) {
            lista.innerHTML = `
                <div class="notificaciones-vacio">
                    <div class="notificaciones-vacio-icono">📭</div>
                    <div class="notificaciones-vacio-texto">No tienes notificaciones</div>
                </div>
            `;
            return;
        }

        let html = '';
        notificaciones.forEach(notif => {
            html += `
                <div class="notificacion-item ${notif.clase}" data-id="${notif.id}" data-documento="${notif.documento_id}">
                    <div class="notificacion-icono">${notif.icono}</div>
                    <div class="notificacion-contenido">
                        <div class="notificacion-mensaje">${notif.mensaje}</div>
                        <div class="notificacion-tiempo">Hace ${notif.tiempo}</div>
                    </div>
                    ${!notif.leida ? '<div class="notificacion-indicador"></div>' : ''}
                </div>
            `;
        });

        lista.innerHTML = html;

        // Agregar event listeners a las notificaciones
        document.querySelectorAll('.notificacion-item').forEach(item => {
            item.addEventListener('click', function() {
                const notifId = this.dataset.id;
                const documentoId = this.dataset.documento;

                // Marcar como leída
                fetch('api_notificaciones.php?accion=marcar_leida', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'notificacion_id=' + notifId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Redirigir al documento si está disponible
                        if (documentoId) {
                            window.location.href = 'ver_documento.php?id=' + documentoId;
                        }
                    }
                });
            });
        });
    }

    // Actualizar contador de notificaciones
    function actualizarContador() {
        fetch('api_notificaciones.php?accion=contar')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const total = data.total;

                    if (total > 0) {
                        badge.textContent = total > 99 ? '99+' : total;
                        badge.style.display = 'block';

                        // Animación si hay nuevas notificaciones
                        if (total > ultimoTotal) {
                            bell.classList.add('nueva-notificacion');
                            setTimeout(() => {
                                bell.classList.remove('nueva-notificacion');
                            }, 500);
                        }
                    } else {
                        badge.style.display = 'none';
                    }

                    ultimoTotal = total;
                }
            })
            .catch(error => console.error('Error:', error));
    }

    // Actualizar contador cada 30 segundos
    actualizarContador();
    setInterval(actualizarContador, 30000);

    // Actualizar notificaciones si el dropdown está abierto
    setInterval(function() {
        if (dropdownAbierto) {
            cargarNotificaciones();
        }
    }, 30000);
})();
</script>
