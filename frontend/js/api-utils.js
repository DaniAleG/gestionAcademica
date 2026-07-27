// Funciones compartidas por todos los módulos (alumnos.js, titulaciones.js, etc.)
// para no repetir el mismo código de fetch/mensajes en cada archivo.

async function apiGet(url) {
    const resp = await fetch(url);
    const data = await resp.json().catch(() => ({ estado: 'error', mensaje: 'Respuesta inválida del servidor.' }));
    if (!resp.ok || data.estado !== 'exito') {
        throw new Error(data.mensaje || 'No se pudo obtener la información.');
    }
    return data.datos;
}

async function apiEnviar(url, metodo, cuerpo) {
    const resp = await fetch(url, {
        method: metodo,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(cuerpo),
    });
    const data = await resp.json().catch(() => ({ estado: 'error', mensaje: 'Respuesta inválida del servidor.' }));
    if (!resp.ok || data.estado !== 'exito') {
        throw new Error(data.mensaje || 'Ocurrió un error al procesar la solicitud.');
    }
    return data;
}

async function apiEliminar(url) {
    const resp = await fetch(url, { method: 'DELETE' });
    const data = await resp.json().catch(() => ({ estado: 'error', mensaje: 'Respuesta inválida del servidor.' }));
    if (!resp.ok || data.estado !== 'exito') {
        throw new Error(data.mensaje || 'No se pudo eliminar el registro.');
    }
    return data;
}

/** Muestra un mensaje de éxito/error dentro de #zona-mensajes (debe existir en la página). */
function mostrarMensaje(texto, tipo = 'success') {
    const zona = document.getElementById('zona-mensajes');
    if (!zona) return;
    zona.innerHTML = `<div class="alert alert-${tipo} shadow-sm">${tipo === 'success' ? '✔' : '⚠'} ${texto}</div>`;
    if (tipo === 'success') {
        setTimeout(() => { zona.innerHTML = ''; }, 4000);
    }
}
