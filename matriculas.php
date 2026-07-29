<?php
session_start();
require_once 'includes/helpers.php';
requerirSesion();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Matrículas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="frontend/css/style.css">
</head>
<body>
    <div class="d-flex">
        <?php include 'includes/sidebar.php'; ?>

        <div id="content">
            <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4 p-3">
                <div class="container-fluid">
                    <span class="navbar-brand mb-0 h4 text-secondary">Gestión de Matrículas</span>
                    <a href="panel_principal.php" class="btn btn-outline-secondary btn-sm">&larr; Panel Principal</a>
                </div>
            </nav>

            <div class="container-fluid px-4 pb-4">
                <div id="zona-mensajes"></div>

                <div class="card shadow-sm">
                    <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
                        <span>Matrículas Registradas</span>
                        <button id="btn-nuevo-matriculas" type="button" class="btn btn-guardar text-white btn-sm" onclick="abrirModalNuevo()">+ Nueva Matrícula</button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-azul">
                                    <tr>
                                        <th>ID</th>
                                        <th>Alumno</th>
                                        <th>Asignatura</th>
                                        <th>Fecha</th>
                                        <th>Estado</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tabla-body">
                                    <tr><td colspan="6" class="text-center text-secondary py-4">Cargando...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalMatricula" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="form-matricula">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitulo">Nueva Matrícula</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="id_matricula">
                        <div class="mb-3">
                            <label class="form-label">Alumno</label>
                            <select id="id_alumno" class="form-select" required>
                                <option value="">-- Seleccione --</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Asignatura</label>
                            <select id="id_asignatura" class="form-select" required>
                                <option value="">-- Seleccione --</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Fecha de Matrícula</label>
                            <input type="date" id="fecha_matricula" class="form-control" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Estado</label>
                            <select id="estado" class="form-select" required>
                                <option value="Activa">Activa</option>
                                <option value="Inactiva">Inactiva</option>
                                <option value="Retirada">Retirada</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-guardar text-white">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
<script src="frontend/js/api-utils.js"></script>
<script src="frontend/js/validaciones.js"></script>
<script src="frontend/js/matriculas.js"></script>
<script>const ROL_USUARIO = "<?= htmlspecialchars($_SESSION['rol'] ?? '') ?>";</script>
</body>
</html>
