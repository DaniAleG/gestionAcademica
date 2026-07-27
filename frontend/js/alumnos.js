const API_ALUMNOS = 'backend/api_alumnos.php';
let modalAlumno;

document.addEventListener('DOMContentLoaded', function () {
    modalAlumno = new bootstrap.Modal(document.getElementById('modalAlumno'));
    cargarAlumnos();

    document.getElementById('form-alumno').addEventListener('submit', guardarAlumno);

    ['cedula', 'nombre', 'apellido', 'correo', 'fecha_nacimiento'].forEach(function (id) {
        const campo = document.getElementById(id);
        if (campo) {
            campo.addEventListener('input', () => mostrarErrorCampo(id + '-error', ''));
        }
    });

    let temporizadorBusqueda;
    document.getElementById('buscador').addEventListener('input', function () {
        clearTimeout(temporizadorBusqueda);
        temporizadorBusqueda = setTimeout(() => cargarAlumnos(this.value), 300);
    });
});

async function cargarAlumnos(busqueda = '') {
    const tbody = document.getElementById('tabla-body');
    try {
        const url = API_ALUMNOS + (busqueda ? '?q=' + encodeURIComponent(busqueda) : '');
        const alumnos = await apiGet(url);
        if (alumnos.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-secondary py-4">No hay alumnos registrados.</td></tr>';
            return;
        }
        tbody.innerHTML = alumnos.map(a => `
            <tr>
                <td>${a.id_alumno}</td>
                <td>${escapeHtml(a.cedula)}</td>
                <td>${escapeHtml(a.nombre)}</td>
                <td>${escapeHtml(a.apellido)}</td>
                <td>${escapeHtml(a.correo)}</td>
                <td>${escapeHtml(a.fecha_nacimiento)}</td>
                <td class="text-center acciones">
                    <a href="#" class="editar" onclick="abrirModalEditar(${a.id_alumno}, ${JSON.stringify(a).replace(/"/g, '&quot;')}); return false;">Editar</a>
                    <a href="#" class="eliminar" onclick="eliminarAlumno(${a.id_alumno}); return false;">Eliminar</a>
                </td>
            </tr>
        `).join('');
    } catch (err) {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center text-danger py-4">${err.message}</td></tr>`;
    }
}

const CAMPOS_ERROR_ALUMNO = ['cedula-error', 'nombre-error', 'apellido-error', 'correo-error', 'fecha_nacimiento-error'];

function abrirModalNuevo() {
    document.getElementById('form-alumno').reset();
    document.getElementById('id_alumno').value = '';
    document.getElementById('modalTitulo').textContent = 'Nuevo Alumno';
    limpiarErroresCampos(CAMPOS_ERROR_ALUMNO);
    modalAlumno.show();
}

function abrirModalEditar(id, alumno) {
    document.getElementById('id_alumno').value = id;
    document.getElementById('cedula').value = alumno.cedula;
    document.getElementById('nombre').value = alumno.nombre;
    document.getElementById('apellido').value = alumno.apellido;
    document.getElementById('correo').value = alumno.correo;
    document.getElementById('fecha_nacimiento').value = alumno.fecha_nacimiento;
    document.getElementById('modalTitulo').textContent = 'Editar Alumno';
    limpiarErroresCampos(CAMPOS_ERROR_ALUMNO);
    modalAlumno.show();
}

async function guardarAlumno(evento) {
    evento.preventDefault();

    const id = document.getElementById('id_alumno').value;
    const cedula = normalizarTexto(document.getElementById('cedula').value);
    const nombre = normalizarTexto(document.getElementById('nombre').value);
    const apellido = normalizarTexto(document.getElementById('apellido').value);
    const correo = normalizarTexto(document.getElementById('correo').value);
    const fecha_nacimiento = document.getElementById('fecha_nacimiento').value;

    limpiarErroresCampos(CAMPOS_ERROR_ALUMNO);

    // --- Validaciones en el cliente (complemento de las del backend) ---
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
        id_alumno: id || undefined,
        cedula,
        nombre,
        apellido,
        correo,
        fecha_nacimiento,
    };

    try {
        const respuesta = await apiEnviar(API_ALUMNOS, id ? 'PUT' : 'POST', cuerpo);
        modalAlumno.hide();
        mostrarMensaje(respuesta.mensaje);
        cargarAlumnos(document.getElementById('buscador').value);
    } catch (err) {
        // Si el backend rechaza por cédula/correo duplicados, se muestra junto al campo correspondiente.
        if (/cédula|cedula/i.test(err.message)) {
            mostrarErrorCampo('cedula-error', err.message);
        } else if (/correo/i.test(err.message)) {
            mostrarErrorCampo('correo-error', err.message);
        } else {
            mostrarMensaje(err.message, 'danger');
        }
    }
}

async function eliminarAlumno(id) {
    if (!confirm('¿Estás seguro de eliminar este alumno?')) return;
    try {
        const respuesta = await apiEliminar(API_ALUMNOS + '?id=' + id);
        mostrarMensaje(respuesta.mensaje);
        cargarAlumnos(document.getElementById('buscador').value);
    } catch (err) {
        mostrarMensaje(err.message, 'danger');
    }
}

function escapeHtml(texto) {
    const div = document.createElement('div');
    div.textContent = texto ?? '';
    return div.innerHTML;
}
