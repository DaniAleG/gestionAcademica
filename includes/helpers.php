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
