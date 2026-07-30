const API_ASIGNATURAS_MIAS = 'backend/api_asignaturas.php?mias=1';
const API_NOTAS = 'backend/api_notas.php';

let cursoActualId = null;
let cursoActualNombre = '';

document.addEventListener('DOMContentLoaded', function () {
    cargarMisCursos();
});

async function cargarMisCursos() {
    const tbody = document.getElementById('tabla-cursos');
    try {
        const cursos = await apiGet(API_ASIGNATURAS_MIAS);
        if (cursos.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-secondary py-4">Todavía no tienes asignaturas asignadas. Habla con el administrador.</td></tr>';
            return;
        }
        tbody.innerHTML = cursos.map(c => `
            <tr>
                <td>${escapeHtml(c.nombre)}</td>
                <td>${c.creditos}</td>
                <td>${escapeHtml(c.nombre_titulacion)}</td>
                <td class="text-center">
                    <button type="button" class="btn btn-guardar text-white btn-sm" onclick="verCurso(${c.id_asignatura}, '${escapeHtml(c.nombre).replace(/'/g, "\\'")}')">Ver alumnos</button>
                </td>
            </tr>
        `).join('');
    } catch (err) {
        tbody.innerHTML = `<tr><td colspan="4" class="text-center text-danger py-4">${err.message}</td></tr>`;
    }
}

async function verCurso(idAsignatura, nombreAsignatura) {
    cursoActualId = idAsignatura;
    cursoActualNombre = nombreAsignatura;
    document.getElementById('titulo-detalle').textContent = 'Alumnos de: ' + nombreAsignatura;
    document.getElementById('vista-lista').classList.add('d-none');
    document.getElementById('vista-detalle').classList.remove('d-none');
    await cargarNotasDelCurso();
}

function volverALista() {
    document.getElementById('vista-detalle').classList.add('d-none');
    document.getElementById('vista-lista').classList.remove('d-none');
    cursoActualId = null;
}

async function cargarNotasDelCurso() {
    const tbody = document.getElementById('tabla-notas');
    tbody.innerHTML = '<tr><td colspan="6" class="text-center text-secondary py-4">Cargando...</td></tr>';
    try {
        const registros = await apiGet(API_NOTAS + '?id_asignatura=' + cursoActualId);
        if (registros.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-secondary py-4">Todavía no hay alumnos matriculados en este curso.</td></tr>';
            return;
        }
        tbody.innerHTML = registros.map(r => `
            <tr>
                <td>${escapeHtml(r.nombre_alumno)}</td>
                <td>${escapeHtml(r.cedula)}</td>
                ${celdaNota(r.id_matricula, 'Parcial', r.notas.Parcial)}
                ${celdaNota(r.id_matricula, 'Final', r.notas.Final)}
                ${celdaNota(r.id_matricula, 'Supletorio', r.notas.Supletorio)}
                <td class="text-center">${badgeEstado(r.estado_academico)}</td>
            </tr>
        `).join('');

        // Enlaza el evento de guardado a cada input de nota recién creado.
        document.querySelectorAll('.input-nota').forEach(input => {
            input.addEventListener('change', onCambiarNota);
        });
    } catch (err) {
        tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger py-4">${err.message}</td></tr>`;
    }
}

function celdaNota(idMatricula, tipo, valor) {
    const val = (valor === undefined || valor === null) ? '' : valor;
    return `
        <td class="text-center">
            <input type="number" class="form-control form-control-sm text-center input-nota"
                   style="max-width: 90px; display: inline-block;"
                   min="0" max="10" step="0.01"
                   data-id-matricula="${idMatricula}" data-tipo="${tipo}"
                   value="${val}">
        </td>
    `;
}

function badgeEstado(estado) {
    const clases = {
        'Aprobado': 'bg-success',
        'Reprobado': 'bg-danger',
        'En curso': 'bg-secondary',
    };
    const clase = clases[estado] || 'bg-secondary';
    return `<span class="badge ${clase}">${estado}</span>`;
}

async function onCambiarNota(evento) {
    const input = evento.target;
    const idMatricula = input.dataset.idMatricula;
    const tipo = input.dataset.tipo;
    const valor = input.value.trim();

    if (valor === '') return; // no borra notas desde aquí, solo guarda valores nuevos

    const numero = parseFloat(valor);
    if (isNaN(numero) || numero < 0 || numero > 10) {
        mostrarMensaje('La nota debe ser un número entre 0 y 10.', 'danger');
        input.classList.add('is-invalid');
        return;
    }
    input.classList.remove('is-invalid');

    try {
        await apiEnviar(API_NOTAS, 'POST', { id_matricula: idMatricula, tipo, nota: numero });
        mostrarMensaje('Nota guardada.');
        cargarNotasDelCurso();
    } catch (err) {
        mostrarMensaje(err.message, 'danger');
    }
}

function escapeHtml(texto) {
    const div = document.createElement('div');
    div.textContent = texto ?? '';
    return div.innerHTML;
}
