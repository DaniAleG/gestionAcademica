const API_MAESTROS = 'backend/api_maestros.php';
let modalMaestro;

document.addEventListener('DOMContentLoaded', function () {
    modalMaestro = new bootstrap.Modal(document.getElementById('modalMaestro'));
    cargarMaestros();

    if (ROL_USUARIO !== 'admin') {
        document.getElementById('btn-nuevo-maestro').style.display = 'none';
    }

    document.getElementById('form-maestro').addEventListener('submit', guardarMaestro);

    ['cedula', 'nombre', 'apellido', 'correo', 'fecha_nacimiento'].forEach(function (id) {
        const campo = document.getElementById(id);
        if (campo) {
            campo.addEventListener('input', () => mostrarErrorCampo(id + '-error', ''));
        }
    });

    let temporizadorBusqueda;
    document.getElementById('buscador').addEventListener('input', function () {
        clearTimeout(temporizadorBusqueda);
        temporizadorBusqueda = setTimeout(() => cargarMaestros(this.value), 300);
    });
});

async function cargarMaestros(busqueda = '') {
    const tbody = document.getElementById('tabla-body');
    try {
        const url = API_MAESTROS + (busqueda ? '?q=' + encodeURIComponent(busqueda) : '');
        const maestros = await apiGet(url);
        if (maestros.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-secondary py-4">No hay maestros registrados.</td></tr>';
            return;
        }
        tbody.innerHTML = maestros.map(m => `
            <tr>
                <td>${m.id_maestro}</td>
                <td>${escapeHtml(m.cedula)}</td>
                <td>${escapeHtml(m.nombre)}</td>
                <td>${escapeHtml(m.apellido)}</td>
                <td>${escapeHtml(m.correo)}</td>
                <td>${escapeHtml(m.especialidad || '')}</td>
                ${ROL_USUARIO === 'admin' ? `
                <td class="text-center acciones">
                        <a href="#" class="editar" onclick="abrirModalEditar(${m.id_maestro}, ${JSON.stringify(m).replace(/"/g, '&quot;')}); return false;">Editar</a>
                        <a href="#" class="eliminar" onclick="eliminarMaestro(${m.id_maestro}); return false;">Eliminar</a>
                </td>
                ` : ''}
            </tr>
        `).join('');
    } catch (err) {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center text-danger py-4">${err.message}</td></tr>`;
    }
}

const CAMPOS_ERROR_MAESTRO = ['cedula-error', 'nombre-error', 'apellido-error', 'correo-error', 'fecha_nacimiento-error'];

function abrirModalNuevo() {
    document.getElementById('form-maestro').reset();
    document.getElementById('id_maestro').value = '';
    document.getElementById('modalTitulo').textContent = 'Nuevo Maestro';
    limpiarErroresCampos(CAMPOS_ERROR_MAESTRO);
    modalMaestro.show();
}

function abrirModalEditar(id, maestro) {
    document.getElementById('id_maestro').value = id;
    document.getElementById('cedula').value = maestro.cedula;
    document.getElementById('nombre').value = maestro.nombre;
    document.getElementById('apellido').value = maestro.apellido;
    document.getElementById('correo').value = maestro.correo;
    document.getElementById('fecha_nacimiento').value = maestro.fecha_nacimiento;
    document.getElementById('especialidad').value = maestro.especialidad || '';
    document.getElementById('modalTitulo').textContent = 'Editar Maestro';
    limpiarErroresCampos(CAMPOS_ERROR_MAESTRO);
    modalMaestro.show();
}

async function guardarMaestro(evento) {
    evento.preventDefault();

    const id = document.getElementById('id_maestro').value;
    const cedula = normalizarTexto(document.getElementById('cedula').value);
    const nombre = normalizarTexto(document.getElementById('nombre').value);
    const apellido = normalizarTexto(document.getElementById('apellido').value);
    const correo = normalizarTexto(document.getElementById('correo').value);
    const fecha_nacimiento = document.getElementById('fecha_nacimiento').value;
    const especialidad = normalizarTexto(document.getElementById('especialidad').value);

    limpiarErroresCampos(CAMPOS_ERROR_MAESTRO);

    let hayErrores = false;

    const errorCedula = validarCedula(cedula);
    if (errorCedula) { mostrarErrorCampo('cedula-error', errorCedula); hayErrores = true; }

    const errorNombre = validarNombrePropio(nombre, 'El nombre');
    if (errorNombre) { mostrarErrorCampo('nombre-error', errorNombre); hayErrores = true; }

    const errorApellido = validarNombrePropio(apellido, 'El apellido');
    if (errorApellido) { mostrarErrorCampo('apellido-error', errorApellido); hayErrores = true; }

    const errorCorreo = validarCorreo(correo);
    if (errorCorreo) { mostrarErrorCampo('correo-error', errorCorreo); hayErrores = true; }

    if (!fecha_nacimiento) {
        mostrarErrorCampo('fecha_nacimiento-error', 'La fecha de nacimiento es obligatoria.');
        hayErrores = true;
    } else if (fecha_nacimiento > new Date().toISOString().split('T')[0]) {
        mostrarErrorCampo('fecha_nacimiento-error', 'La fecha de nacimiento no puede ser futura.');
        hayErrores = true;
    }

    if (hayErrores) return;

    const cuerpo = {
        id_maestro: id || undefined,
        cedula,
        nombre,
        apellido,
        correo,
        fecha_nacimiento,
        especialidad,
    };

    try {
        const respuesta = await apiEnviar(API_MAESTROS, id ? 'PUT' : 'POST', cuerpo);
        modalMaestro.hide();
        mostrarMensaje(respuesta.mensaje);
        cargarMaestros(document.getElementById('buscador').value);
    } catch (err) {
        if (/cédula|cedula/i.test(err.message)) {
            mostrarErrorCampo('cedula-error', err.message);
        } else if (/correo/i.test(err.message)) {
            mostrarErrorCampo('correo-error', err.message);
        } else if (/edad/i.test(err.message)) {
            mostrarErrorCampo('fecha_nacimiento-error', err.message);
        } else {
            mostrarMensaje(err.message, 'danger');
        }
    }
}

async function eliminarMaestro(id) {
    if (!confirm('¿Estás seguro de eliminar este maestro? Las asignaturas que dicta quedarán sin maestro asignado.')) return;
    try {
        const respuesta = await apiEliminar(API_MAESTROS + '?id=' + id);
        mostrarMensaje(respuesta.mensaje);
        cargarMaestros(document.getElementById('buscador').value);
    } catch (err) {
        mostrarMensaje(err.message, 'danger');
    }
}

function escapeHtml(texto) {
    const div = document.createElement('div');
    div.textContent = texto ?? '';
    return div.innerHTML;
}
