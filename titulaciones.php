<?php
session_start();
require_once 'includes/helpers.php';
requerirSesion();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Titulaciones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="frontend/css/style.css">
</head>
<body>
    <div class="d-flex">
        <?php include 'includes/sidebar.php'; ?>

        <div id="content">
            <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4 p-3">
                <div class="container-fluid">
                    <span class="navbar-brand mb-0 h4 text-secondary">Gestión de Titulaciones</span>
                    <a href="panel_principal.php" class="btn btn-outline-secondary btn-sm">&larr; Panel Principal</a>
                </div>
            </nav>

            <div class="container-fluid px-4 pb-4">
                <div id="zona-mensajes"></div>

                <div class="card shadow-sm">
                    <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
                        <span>Titulaciones Registradas</span>
                        <button type="button" class="btn btn-guardar text-white btn-sm" onclick="abrirModalNuevo()">+ Nueva Titulación</button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-azul">
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Descripción</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tabla-body">
                                    <tr><td colspan="4" class="text-center text-secondary py-4">Cargando...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalTitulacion" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="form-titulacion">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitulo">Nueva Titulación</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="id_titulacion">
                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" id="nombre" class="form-control" required maxlength="150" placeholder="Ej. Ingeniería en Tecnologías de la Información">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Descripción (Opcional)</label>
                            <textarea id="descripcion" class="form-control" rows="3" maxlength="500"></textarea>
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
<script src="frontend/js/titulaciones.js"></script>
</body>
</html>
