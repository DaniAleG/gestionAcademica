const API_MATRICULAS = 'backend/api_matriculas.php';
const API_ALUMNOS_LISTA = 'backend/api_alumnos.php';
const API_ASIGNATURAS_LISTA = 'backend/api_asignaturas.php';
let modalMatricula;
let alumnosCache = [];
let asignaturasCache = [];

document.addEventListener('DOMContentLoaded', function () {
    modalMatricula = new bootstrap.Modal(document.getElementById('modalMatricula'));
    cargarSelects();
    cargarMatriculas();
    if (ROL_USUARIO !== 'admin') {
        document.getElementById('btn-nuevo-matriculas').style.display = 'none';
    }
    document.getElementById('form-matricula').addEventListener('submit', guardarMatricula);
});

async function cargarSelects() {
    try {
        [alumnosCache, asignaturasCache] = await Promise.all([
            apiGet(API_ALUMNOS_LISTA),
            apiGet(API_ASIGNATURAS_LISTA),
        ]);
        document.getElementById('id_alumno').innerHTML = '<option value="">-- Seleccione --</option>' +
            alumnosCache.map(a => `<option value="${a.id_alumno}">${escapeHtml(a.nombre + ' ' + a.apellido)}</option>`).join('');
        document.getElementById('id_asignatura').innerHTML = '<option value="">-- Seleccione --</option>' +
            asignaturasCache.map(a => `<option value="${a.id_asignatura}">${escapeHtml(a.nombre)}</option>`).join('');
    } catch (err) {
        mostrarMensaje('No se pudieron cargar alumnos/asignaturas: ' + err.message, 'danger');
    }
}

const BADGE_ESTADO = { Activa: 'bg-success', Retirada: 'bg-danger', Inactiva: 'bg-secondary' };

async function cargarMatriculas() {
    const tbody = document.getElementById('tabla-body');
    try {
        const matriculas = await apiGet(API_MATRICULAS);
        if (matriculas.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-secondary py-4">No hay matrículas registradas.</td></tr>';
            return;
        }
        tbody.innerHTML = matriculas.map(m => `
            <tr>
                <td>${m.id_matricula}</td>
                <td>${escapeHtml(m.nombre_alumno)}</td>
                <td>${escapeHtml(m.nombre_asignatura)}</td>
                <td>${escapeHtml(m.fecha_matricula)}</td>
                <td><span class="badge ${BADGE_ESTADO[m.estado] || 'bg-secondary'}">${escapeHtml(m.estado)}</span></td>
                <td class="text-center acciones">
                    <a href="#" class="editar" onclick='abrirModalEditar(${m.id_matricula}, ${JSON.stringify(m)}); return false;'>Editar</a>
                    <a href="#" class="eliminar" onclick="eliminarMatricula(${m.id_matricula}); return false;">Eliminar</a>
                </td>
            </tr>
        `).join('');
    } catch (err) {
        tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger py-4">${err.message}</td></tr>`;
    }
}

function abrirModalNuevo() {
    if (alumnosCache.length === 0 || asignaturasCache.length === 0) {
        mostrarMensaje('Necesitas al menos un alumno y una asignatura registrados antes de crear matrículas.', 'danger');
        return;
    }
    document.getElementById('form-matricula').reset();
    document.getElementById('id_matricula').value = '';
    document.getElementById('modalTitulo').textContent = 'Nueva Matrícula';
    modalMatricula.show();
}

function abrirModalEditar(id, m) {
    document.getElementById('id_matricula').value = id;
    document.getElementById('id_alumno').value = m.id_alumno;
    document.getElementById('id_asignatura').value = m.id_asignatura;
    document.getElementById('fecha_matricula').value = m.fecha_matricula;
    document.getElementById('estado').value = m.estado;
    document.getElementById('modalTitulo').textContent = 'Editar Matrícula';
    modalMatricula.show();
}

async function guardarMatricula(evento) {
    evento.preventDefault();
    const id = document.getElementById('id_matricula').value;
    const cuerpo = {
        id_matricula: id || undefined,
        id_alumno: document.getElementById('id_alumno').value,
        id_asignatura: document.getElementById('id_asignatura').value,
        fecha_matricula: document.getElementById('fecha_matricula').value,
        estado: document.getElementById('estado').value,
    };
    try {
        const respuesta = await apiEnviar(API_MATRICULAS, id ? 'PUT' : 'POST', cuerpo);
        modalMatricula.hide();
        mostrarMensaje(respuesta.mensaje);
        cargarMatriculas();
    } catch (err) {
        mostrarMensaje(err.message, 'danger');
    }
}

async function eliminarMatricula(id) {
    if (!confirm('¿Eliminar esta matrícula?')) return;
    try {
        const respuesta = await apiEliminar(API_MATRICULAS + '?id=' + id);
        mostrarMensaje(respuesta.mensaje);
        cargarMatriculas();
    } catch (err) {
        mostrarMensaje(err.message, 'danger');
    }
}

function escapeHtml(texto) {
    const div = document.createElement('div');
    div.textContent = texto ?? '';
    return div.innerHTML;
}
