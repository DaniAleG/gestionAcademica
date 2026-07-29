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
$estadosValidos = ['Activa', 'Inactiva', 'Retirada'];

function validarMatricula(array $d, array $estadosValidos): array
{
    $id_alumno = $d['id_alumno'] ?? '';
    $id_asignatura = $d['id_asignatura'] ?? '';
    $fecha_matricula = trim((string)($d['fecha_matricula'] ?? ''));
    $estado = trim((string)($d['estado'] ?? ''));

    if ($id_alumno === '' || $id_asignatura === '' || $fecha_matricula === '' || $estado === '') {
        responderError('Todos los campos son obligatorios.', 422);
    }
    if (!ctype_digit((string)$id_alumno) || !ctype_digit((string)$id_asignatura)) {
        responderError('Alumno o asignatura no válidos.', 422);
    }
    if (!in_array($estado, $estadosValidos, true)) {
        responderError('El estado no es válido.', 422);
    }

    return [
        'id_alumno' => (int)$id_alumno,
        'id_asignatura' => (int)$id_asignatura,
        'fecha_matricula' => $fecha_matricula,
        'estado' => $estado,
    ];
}

try {
    if ($metodo === 'GET') {
        $stmt = $pdo->query("SELECT m.id_matricula, m.id_alumno, m.id_asignatura, m.fecha_matricula, m.estado,
                                     CONCAT(al.nombre, ' ', al.apellido) AS nombre_alumno,
                                     asg.nombre AS nombre_asignatura
                              FROM matricula m
                              INNER JOIN alumno al ON m.id_alumno = al.id_alumno
                              INNER JOIN asignatura asg ON m.id_asignatura = asg.id_asignatura
                              ORDER BY m.id_matricula DESC");
        responderExito($stmt->fetchAll());
    }

    if ($metodo === 'POST') {
        $d = validarMatricula(leerJsonBody(), $estadosValidos);
        $stmt = $pdo->prepare('INSERT INTO matricula (id_alumno, id_asignatura, fecha_matricula, estado)
                                VALUES (:id_alumno, :id_asignatura, :fecha_matricula, :estado) RETURNING id_matricula');
        $stmt->execute($d);
        responderExito(['id_matricula' => $stmt->fetchColumn()], 'Matrícula registrada correctamente.');
    }

    if ($metodo === 'PUT') {
        $body = leerJsonBody();
        $id = (int)($body['id_matricula'] ?? 0);
        if ($id <= 0) {
            responderError('ID de matrícula inválido.', 400);
        }
        $d = validarMatricula($body, $estadosValidos);
        $d['id'] = $id;
        $stmt = $pdo->prepare('UPDATE matricula SET id_alumno = :id_alumno, id_asignatura = :id_asignatura,
                                fecha_matricula = :fecha_matricula, estado = :estado WHERE id_matricula = :id');
        $stmt->execute($d);
        responderExito([], 'Matrícula actualizada correctamente.');
    }

    if ($metodo === 'DELETE') {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            responderError('ID de matrícula inválido.', 400);
        }
        $stmt = $pdo->prepare('DELETE FROM matricula WHERE id_matricula = :id');
        $stmt->execute([':id' => $id]);
        responderExito([], 'Matrícula eliminada correctamente.');
    }

    responderError('Método no permitido.', 405);
} catch (PDOException $e) {
    responderError('No se pudo completar la operación (ese alumno ya podría estar matriculado en esa asignatura).', 500);
}
