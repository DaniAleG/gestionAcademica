<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/includes/api_helpers.php';
require_once __DIR__ . '/conexion.php';
requerirSesionApi();

$metodo = $_SERVER['REQUEST_METHOD'];

// Ver titulaciones lo puede hacer cualquier rol logueado; crear/editar/borrar, solo el admin.
if (in_array($metodo, ['POST', 'PUT', 'DELETE'], true)) {
    requerirRolApi(['admin']);
}

function validarTitulacion(array $d, PDO $pdo, ?int $idExcluir = null): array
{
    $nombre = normalizarTexto((string)($d['nombre'] ?? ''));
    $descripcion = normalizarTexto((string)($d['descripcion'] ?? ''));

    if ($nombre === '') {
        responderError('El nombre de la titulación es obligatorio.', 422);
    }
    if (mb_strlen($nombre) < 3) {
        responderError('El nombre de la titulación debe tener al menos 3 caracteres.', 422);
    }

    $sql = 'SELECT id_titulacion FROM titulacion WHERE LOWER(nombre) = LOWER(:nombre)';
    if ($idExcluir !== null) {
        $sql .= ' AND id_titulacion <> :id';
    }
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':nombre', $nombre);
    if ($idExcluir !== null) {
        $stmt->bindValue(':id', $idExcluir, PDO::PARAM_INT);
    }
    $stmt->execute();
    if ($stmt->fetch()) {
        responderError('Ya existe una titulación con ese nombre.', 409);
    }

    return ['nombre' => $nombre, 'descripcion' => $descripcion];
}

try {
    if ($metodo === 'GET') {
        $stmt = $pdo->query('SELECT id_titulacion, nombre, descripcion FROM titulacion ORDER BY nombre');
        responderExito($stmt->fetchAll());
    }

    if ($metodo === 'POST') {
        $d = validarTitulacion(leerJsonBody(), $pdo);
        $stmt = $pdo->prepare('INSERT INTO titulacion (nombre, descripcion) VALUES (:nombre, :descripcion) RETURNING id_titulacion');
        $stmt->execute($d);
        responderExito(['id_titulacion' => $stmt->fetchColumn()], 'Titulación registrada correctamente.');
    }

    if ($metodo === 'PUT') {
        $body = leerJsonBody();
        $id = (int)($body['id_titulacion'] ?? 0);
        if ($id <= 0) {
            responderError('ID de titulación inválido.', 400);
        }
        $d = validarTitulacion($body, $pdo, $id);
        $d['id'] = $id;
        $stmt = $pdo->prepare('UPDATE titulacion SET nombre = :nombre, descripcion = :descripcion WHERE id_titulacion = :id');
        $stmt->execute($d);
        responderExito([], 'Titulación actualizada correctamente.');
    }

    if ($metodo === 'DELETE') {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            responderError('ID de titulación inválido.', 400);
        }
        $stmt = $pdo->prepare('DELETE FROM titulacion WHERE id_titulacion = :id');
        $stmt->execute([':id' => $id]);
        responderExito([], 'Titulación eliminada correctamente.');
    }

    responderError('Método no permitido.', 405);
} catch (PDOException $e) {
    responderError('No se pudo completar la operación.', 500);
}
