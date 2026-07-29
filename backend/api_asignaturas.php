<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/includes/api_helpers.php';
require_once __DIR__ . '/conexion.php';
requerirSesionApi();
if (in_array($metodo, ['POST', 'PUT', 'DELETE'], true)) {
    requerirRolApi(['admin']);
}
$metodo = $_SERVER['REQUEST_METHOD'];

function validarAsignatura(array $d, PDO $pdo, ?int $idExcluir = null): array
{
    $nombre = normalizarTexto((string)($d['nombre'] ?? ''));
    $creditos = $d['creditos'] ?? '';
    $id_titulacion = $d['id_titulacion'] ?? '';

    if ($nombre === '' || $creditos === '' || $id_titulacion === '') {
        responderError('Todos los campos son obligatorios.', 422);
    }
    if (mb_strlen($nombre) < 3) {
        responderError('El nombre de la asignatura debe tener al menos 3 caracteres.', 422);
    }
    if (!ctype_digit((string)$creditos) || (int)$creditos < 1 || (int)$creditos > 20) {
        responderError('Los créditos deben ser un número entre 1 y 20.', 422);
    }
    if (!ctype_digit((string)$id_titulacion)) {
        responderError('La titulación seleccionada no es válida.', 422);
    }

    $id_titulacion = (int)$id_titulacion;

    // Evita asignaturas duplicadas dentro de la misma titulación.
    $sql = 'SELECT id_asignatura FROM asignatura WHERE LOWER(nombre) = LOWER(:nombre) AND id_titulacion = :id_titulacion';
    if ($idExcluir !== null) {
        $sql .= ' AND id_asignatura <> :id';
    }
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':nombre', $nombre);
    $stmt->bindValue(':id_titulacion', $id_titulacion, PDO::PARAM_INT);
    if ($idExcluir !== null) {
        $stmt->bindValue(':id', $idExcluir, PDO::PARAM_INT);
    }
    $stmt->execute();
    if ($stmt->fetch()) {
        responderError('Ya existe una asignatura con ese nombre en la titulación seleccionada.', 409);
    }

    return ['nombre' => $nombre, 'creditos' => (int)$creditos, 'id_titulacion' => $id_titulacion];
}

try {
    if ($metodo === 'GET') {
        $stmt = $pdo->query("SELECT a.id_asignatura, a.nombre, a.creditos, a.id_titulacion, t.nombre AS nombre_titulacion
                              FROM asignatura a INNER JOIN titulacion t ON a.id_titulacion = t.id_titulacion
                              ORDER BY a.id_asignatura DESC");
        responderExito($stmt->fetchAll());
    }

    if ($metodo === 'POST') {
        $d = validarAsignatura(leerJsonBody(), $pdo);
        $stmt = $pdo->prepare('INSERT INTO asignatura (nombre, creditos, id_titulacion)
                                VALUES (:nombre, :creditos, :id_titulacion) RETURNING id_asignatura');
        $stmt->execute($d);
        responderExito(['id_asignatura' => $stmt->fetchColumn()], 'Asignatura registrada correctamente.');
    }

    if ($metodo === 'PUT') {
        $body = leerJsonBody();
        $id = (int)($body['id_asignatura'] ?? 0);
        if ($id <= 0) {
            responderError('ID de asignatura inválido.', 400);
        }
        $d = validarAsignatura($body, $pdo, $id);
        $d['id'] = $id;
        $stmt = $pdo->prepare('UPDATE asignatura SET nombre = :nombre, creditos = :creditos, id_titulacion = :id_titulacion WHERE id_asignatura = :id');
        $stmt->execute($d);
        responderExito([], 'Asignatura actualizada correctamente.');
    }

    if ($metodo === 'DELETE') {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            responderError('ID de asignatura inválido.', 400);
        }
        $stmt = $pdo->prepare('DELETE FROM asignatura WHERE id_asignatura = :id');
        $stmt->execute([':id' => $id]);
        responderExito([], 'Asignatura eliminada correctamente.');
    }

    responderError('Método no permitido.', 405);
} catch (PDOException $e) {
    responderError('No se pudo completar la operación (revisa que la titulación exista).', 500);
}
