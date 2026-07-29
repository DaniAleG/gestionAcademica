const API_TITULACIONES = 'backend/api_titulaciones.php';
let modalTitulacion;

document.addEventListener('DOMContentLoaded', function () {
    modalTitulacion = new bootstrap.Modal(document.getElementById('modalTitulacion'));
    cargarTitulaciones();
    if (ROL_USUARIO !== 'admin') {
        document.getElementById('btn-nuevo-titulacion').style.display = 'none';
    }
    document.getElementById('form-titulacion').addEventListener('submit', guardarTitulacion);
});

async function cargarTitulaciones() {
    const tbody = document.getElementById('tabla-body');
    try {
        const titulaciones = await apiGet(API_TITULACIONES);
        if (titulaciones.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-secondary py-4">No hay titulaciones registradas.</td></tr>';
            return;
        }
        tbody.innerHTML = titulaciones.map(t => `
            <tr>
                <td>${t.id_titulacion}</td>
                <td>${escapeHtml(t.nombre)}</td>
                <td>${escapeHtml(t.descripcion || 'N/A')}</td>
                <td class="text-center acciones">
                    <a href="#" class="editar" onclick='abrirModalEditar(${t.id_titulacion}, ${JSON.stringify(t)}); return false;'>Editar</a>
                    <a href="#" class="eliminar" onclick="eliminarTitulacion(${t.id_titulacion}); return false;">Eliminar</a>
                </td>
            </tr>
        `).join('');
    } catch (err) {
        tbody.innerHTML = `<tr><td colspan="4" class="text-center text-danger py-4">${err.message}</td></tr>`;
    }
}

function abrirModalNuevo() {
    document.getElementById('form-titulacion').reset();
    document.getElementById('id_titulacion').value = '';
    document.getElementById('modalTitulo').textContent = 'Nueva Titulación';
    modalTitulacion.show();
}

function abrirModalEditar(id, t) {
    document.getElementById('id_titulacion').value = id;
    document.getElementById('nombre').value = t.nombre;
    document.getElementById('descripcion').value = t.descripcion || '';
    document.getElementById('modalTitulo').textContent = 'Editar Titulación';
    modalTitulacion.show();
}

async function guardarTitulacion(evento) {
    evento.preventDefault();
    const id = document.getElementById('id_titulacion').value;
    const cuerpo = {
        id_titulacion: id || undefined,
        nombre: document.getElementById('nombre').value.trim(),
        descripcion: document.getElementById('descripcion').value.trim(),
    };
    try {
        const respuesta = await apiEnviar(API_TITULACIONES, id ? 'PUT' : 'POST', cuerpo);
        modalTitulacion.hide();
        mostrarMensaje(respuesta.mensaje);
        cargarTitulaciones();
    } catch (err) {
        mostrarMensaje(err.message, 'danger');
    }
}

async function eliminarTitulacion(id) {
    if (!confirm('¿Eliminar esta titulación? Esto eliminará también las asignaturas asociadas.')) return;
    try {
        const respuesta = await apiEliminar(API_TITULACIONES + '?id=' + id);
        mostrarMensaje(respuesta.mensaje);
        cargarTitulaciones();
    } catch (err) {
        mostrarMensaje(err.message, 'danger');
    }
}

function escapeHtml(texto) {
    const div = document.createElement('div');
    div.textContent = texto ?? '';
    return div.innerHTML;
}
