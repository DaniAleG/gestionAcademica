const API_CONVOCATORIAS = 'backend/api_convocatorias.php';
const API_ASIGNATURAS_LISTA2 = 'backend/api_asignaturas.php';
let modalConvocatoria;
let asignaturasCache2 = [];

document.addEventListener('DOMContentLoaded', function () {
    modalConvocatoria = new bootstrap.Modal(document.getElementById('modalConvocatoria'));
    cargarAsignaturasEnSelect();
    cargarConvocatorias();
    document.getElementById('form-convocatoria').addEventListener('submit', guardarConvocatoria);
});

async function cargarAsignaturasEnSelect() {
    try {
        asignaturasCache2 = await apiGet(API_ASIGNATURAS_LISTA2);
        document.getElementById('id_asignatura').innerHTML = '<option value="">-- Seleccione --</option>' +
            asignaturasCache2.map(a => `<option value="${a.id_asignatura}">${escapeHtml(a.nombre)}</option>`).join('');
    } catch (err) {
        mostrarMensaje('No se pudieron cargar las asignaturas: ' + err.message, 'danger');
    }
}

async function cargarConvocatorias() {
    const tbody = document.getElementById('tabla-body');
    try {
        const convocatorias = await apiGet(API_CONVOCATORIAS);
        if (convocatorias.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-secondary py-4">No hay convocatorias registradas.</td></tr>';
            return;
        }
        tbody.innerHTML = convocatorias.map(c => `
            <tr>
                <td>${c.id_convocatoria}</td>
                <td>${escapeHtml(c.nombre_asignatura)}</td>
                <td>${escapeHtml(c.fecha_examen)}</td>
                <td>${escapeHtml(c.tipo)}</td>
                <td class="text-center acciones">
                    <a href="#" class="editar" onclick='abrirModalEditar(${c.id_convocatoria}, ${JSON.stringify(c)}); return false;'>Editar</a>
                    <a href="#" class="eliminar" onclick="eliminarConvocatoria(${c.id_convocatoria}); return false;">Eliminar</a>
                </td>
            </tr>
        `).join('');
    } catch (err) {
        tbody.innerHTML = `<tr><td colspan="5" class="text-center text-danger py-4">${err.message}</td></tr>`;
    }
}

function abrirModalNuevo() {
    if (asignaturasCache2.length === 0) {
        mostrarMensaje('Debes registrar al menos una asignatura antes de crear convocatorias.', 'danger');
        return;
    }
    document.getElementById('form-convocatoria').reset();
    document.getElementById('id_convocatoria').value = '';
    document.getElementById('modalTitulo').textContent = 'Nueva Convocatoria';
    modalConvocatoria.show();
}

function abrirModalEditar(id, c) {
    document.getElementById('id_convocatoria').value = id;
    document.getElementById('id_asignatura').value = c.id_asignatura;
    document.getElementById('fecha_examen').value = c.fecha_examen;
    document.getElementById('tipo').value = c.tipo;
    document.getElementById('modalTitulo').textContent = 'Editar Convocatoria';
    modalConvocatoria.show();
}

async function guardarConvocatoria(evento) {
    evento.preventDefault();
    const id = document.getElementById('id_convocatoria').value;
    const cuerpo = {
        id_convocatoria: id || undefined,
        id_asignatura: document.getElementById('id_asignatura').value,
        fecha_examen: document.getElementById('fecha_examen').value,
        tipo: document.getElementById('tipo').value,
    };
    try {
        const respuesta = await apiEnviar(API_CONVOCATORIAS, id ? 'PUT' : 'POST', cuerpo);
        modalConvocatoria.hide();
        mostrarMensaje(respuesta.mensaje);
        cargarConvocatorias();
    } catch (err) {
        mostrarMensaje(err.message, 'danger');
    }
}

async function eliminarConvocatoria(id) {
    if (!confirm('¿Eliminar esta convocatoria?')) return;
    try {
        const respuesta = await apiEliminar(API_CONVOCATORIAS + '?id=' + id);
        mostrarMensaje(respuesta.mensaje);
        cargarConvocatorias();
    } catch (err) {
        mostrarMensaje(err.message, 'danger');
    }
}

function escapeHtml(texto) {
    const div = document.createElement('div');
    div.textContent = texto ?? '';
    return div.innerHTML;
}
