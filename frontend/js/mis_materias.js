document.addEventListener('DOMContentLoaded', function () {
    cargarMisMaterias();
});

async function cargarMisMaterias() {
    const tbody = document.getElementById('tabla-body');
    try {
        // api_notas.php ya filtra automáticamente por el alumno de la sesión.
        const registros = await apiGet('backend/api_notas.php');
        if (registros.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-secondary py-4">Todavía no estás matriculado en ninguna materia.</td></tr>';
            return;
        }
        tbody.innerHTML = registros.map(r => `
            <tr>
                <td>${escapeHtml(r.nombre_asignatura)}</td>
                <td class="text-center">${formatearNota(r.notas.Parcial)}</td>
                <td class="text-center">${formatearNota(r.notas.Final)}</td>
                <td class="text-center">${formatearNota(r.notas.Supletorio)}</td>
                <td class="text-center">${badgeEstado(r.estado_academico)}</td>
            </tr>
        `).join('');
    } catch (err) {
        tbody.innerHTML = `<tr><td colspan="5" class="text-center text-danger py-4">${err.message}</td></tr>`;
    }
}

function formatearNota(valor) {
    return (valor === undefined || valor === null) ? '<span class="text-muted">—</span>' : valor.toFixed(2);
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

function escapeHtml(texto) {
    const div = document.createElement('div');
    div.textContent = texto ?? '';
    return div.innerHTML;
}
