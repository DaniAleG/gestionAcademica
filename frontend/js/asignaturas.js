const API_ASIGNATURAS = 'backend/api_asignaturas.php';
const API_TITULACIONES_LISTA = 'backend/api_titulaciones.php';
let modalAsignatura;
let titulacionesCache = [];

document.addEventListener('DOMContentLoaded', function () {
    modalAsignatura = new bootstrap.Modal(document.getElementById('modalAsignatura'));
    cargarTitulacionesEnSelect();
    cargarAsignaturas();
    document.getElementById('form-asignatura').addEventListener('submit', guardarAsignatura);
});

async function cargarTitulacionesEnSelect() {
    try {
        titulacionesCache = await apiGet(API_TITULACIONES_LISTA);
        const select = document.getElementById('id_titulacion');
        select.innerHTML = '<option value="">-- Seleccione --</option>' +
            titulacionesCache.map(t => `<option value="${t.id_titulacion}">${escapeHtml(t.nombre)}</option>`).join('');
    } catch (err) {
        mostrarMensaje('No se pudieron cargar las titulaciones: ' + err.message, 'danger');
    }
}

async function cargarAsignaturas() {
    const tbody = document.getElementById('tabla-body');
    try {
        const asignaturas = await apiGet(API_ASIGNATURAS);
        if (asignaturas.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-secondary py-4">No hay asignaturas registradas.</td></tr>';
            return;
        }
        tbody.innerHTML = asignaturas.map(a => `
            <tr>
                <td>${a.id_asignatura}</td>
                <td>${escapeHtml(a.nombre)}</td>
                <td>${a.creditos}</td>
                <td>${escapeHtml(a.nombre_titulacion)}</td>
                <td class="text-center acciones">
                    <a href="#" class="editar" onclick='abrirModalEditar(${a.id_asignatura}, ${JSON.stringify(a)}); return false;'>Editar</a>
                    <a href="#" class="eliminar" onclick="eliminarAsignatura(${a.id_asignatura}); return false;">Eliminar</a>
                </td>
            </tr>
        `).join('');
    } catch (err) {
        tbody.innerHTML = `<tr><td colspan="5" class="text-center text-danger py-4">${err.message}</td></tr>`;
    }
}

function abrirModalNuevo() {
    if (titulacionesCache.length === 0) {
        mostrarMensaje('Debes registrar al menos una titulación antes de crear asignaturas.', 'danger');
        return;
    }
    document.getElementById('form-asignatura').reset();
    document.getElementById('id_asignatura').value = '';
    document.getElementById('modalTitulo').textContent = 'Nueva Asignatura';
    modalAsignatura.show();
}

function abrirModalEditar(id, a) {
    document.getElementById('id_asignatura').value = id;
    document.getElementById('nombre').value = a.nombre;
    document.getElementById('creditos').value = a.creditos;
    document.getElementById('id_titulacion').value = a.id_titulacion;
    document.getElementById('modalTitulo').textContent = 'Editar Asignatura';
    modalAsignatura.show();
}

async function guardarAsignatura(evento) {
    evento.preventDefault();
    const id = document.getElementById('id_asignatura').value;
    const cuerpo = {
        id_asignatura: id || undefined,
        nombre: document.getElementById('nombre').value.trim(),
        creditos: document.getElementById('creditos').value,
        id_titulacion: document.getElementById('id_titulacion').value,
    };
    try {
        const respuesta = await apiEnviar(API_ASIGNATURAS, id ? 'PUT' : 'POST', cuerpo);
        modalAsignatura.hide();
        mostrarMensaje(respuesta.mensaje);
        cargarAsignaturas();
    } catch (err) {
        mostrarMensaje(err.message, 'danger');
    }
}

async function eliminarAsignatura(id) {
    if (!confirm('¿Eliminar esta asignatura?')) return;
    try {
        const respuesta = await apiEliminar(API_ASIGNATURAS + '?id=' + id);
        mostrarMensaje(respuesta.mensaje);
        cargarAsignaturas();
    } catch (err) {
        mostrarMensaje(err.message, 'danger');
    }
}

function escapeHtml(texto) {
    const div = document.createElement('div');
    div.textContent = texto ?? '';
    return div.innerHTML;
}
