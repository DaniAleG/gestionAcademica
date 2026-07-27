<?php

$paginaActual = basename($_SERVER['SCRIPT_NAME']);
?>
<button id="sidebar-toggle" class="sidebar-toggle-btn" type="button" aria-label="Abrir menu" aria-expanded="false">
    &#9776;
</button>

<div id="sidebar-hover-zone" aria-hidden="true"></div>
<div id="sidebar-backdrop" aria-hidden="true"></div>

<nav id="sidebar" class="d-flex flex-column p-3 text-white">
    <div class="text-center mb-4 mt-2">
        <h3 class="fw-bold m-0 text-light">🎓 GESTIÓN</h3>
        <small style="color: var(--azul-claro);">ACADÉMICA</small>
    </div>
    <hr>

    <ul class="nav nav-pills flex-column mb-auto mt-2">
        <li class="nav-item mb-2">
            <a href="panel_principal.php" class="nav-link menu-item <?php echo $paginaActual === 'panel_principal.php' ? 'activo' : ''; ?>">
                🏠 Inicio
            </a>
        </li>
        <li class="nav-item mb-2">
            <a href="alumnos.php" class="nav-link menu-item <?php echo $paginaActual === 'alumnos.php' ? 'activo' : ''; ?>">
                🧑‍🎓 Alumnos
            </a>
        </li>
        <li class="nav-item mb-2">
            <a href="titulaciones.php" class="nav-link menu-item <?php echo $paginaActual === 'titulaciones.php' ? 'activo' : ''; ?>">
                🎓 Titulaciones
            </a>
        </li>
        <li class="nav-item mb-2">
            <a href="asignaturas.php" class="nav-link menu-item <?php echo $paginaActual === 'asignaturas.php' ? 'activo' : ''; ?>">
                📚 Asignaturas
            </a>
        </li>
        <li class="nav-item mb-2">
            <a href="matriculas.php" class="nav-link menu-item <?php echo $paginaActual === 'matriculas.php' ? 'activo' : ''; ?>">
                📝 Matrículas
            </a>
        </li>
        <li class="nav-item mb-2">
            <a href="convocatorias.php" class="nav-link menu-item <?php echo $paginaActual === 'convocatorias.php' ? 'activo' : ''; ?>">
                📅 Convocatorias
            </a>
        </li>
    </ul>

    <hr>
    <div class="text-center pb-2">
        <small class="text-white-50">Versión 1.0</small>
    </div>
</nav>

<script src="frontend/js/sidebar.js" defer></script>
