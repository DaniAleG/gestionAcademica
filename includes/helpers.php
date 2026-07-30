<?php
/**
 * Función de apoyo reutilizada en todas las páginas.
 */

/**
 * Corta la ejecución y manda al login si no hay sesión activa.
 * Debe llamarse DESPUÉS de session_start().
 */
function requerirSesion(): void
{
    if (!isset($_SESSION['id_usuario'])) {
        header("Location: index.php");
        exit;
    }
}

/**
 * Corta la ejecución y manda al panel principal si el rol de la sesión
 * no está en la lista permitida. Debe llamarse DESPUÉS de requerirSesion().
 */
function requerirRol(array $rolesPermitidos): void
{
    if (!in_array($_SESSION['rol'] ?? '', $rolesPermitidos, true)) {
        header("Location: panel_principal.php");
        exit;
    }
}
