<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/includes/api_helpers.php';
require_once __DIR__ . '/conexion.php';
requerirSesionApi();

$metodo = $_SERVER['REQUEST_METHOD'];
$tiposValidos = ['Parcial', 'Final', 'Supletorio'];

function validarConvocatoria(array $d, array $tiposValidos): array
{
    $id_asignatura = $d['id_asignatura'] ?? '';
    $fecha_examen = trim((string)($d['fecha_examen'] ?? ''));
    $tipo = trim((string)($d['tipo'] ?? ''));

    if ($id_asignatura === '' || $fecha_examen === '' || $tipo === '') {
        responderError('Todos los campos son obligatorios.', 422);
    }
    if (!ctype_digit((string)$id_asignatura)) {
        responderError('La asignatura seleccionada no es válida.', 422);
    }
    if (!in_array($tipo, $tiposValidos, true)) {
        responderError('El tipo de evaluación no es válido.', 422);
    }
    $fechaExamenValida = DateTime::createFromFormat('Y-m-d', $fecha_examen);
    if (!$fechaExamenValida) {
        responderError('La fecha de examen no es válida.', 422);
    }
    if ($fechaExamenValida < new DateTime('today')) {
        responderError('La fecha de examen no puede ser una fecha pasada.', 422);
    }

    return ['id_asignatura' => (int)$id_asignatura, 'fecha_examen' => $fecha_examen, 'tipo' => $tipo];

    return ['id_asignatura' => (int)$id_asignatura, 'fecha_examen' => $fecha_examen, 'tipo' => $tipo];
}

try {
    if ($metodo === 'GET') {
        $stmt = $pdo->query("SELECT c.id_convocatoria, c.id_asignatura, c.fecha_examen, c.tipo, a.nombre AS nombre_asignatura
                              FROM convocatoria c INNER JOIN asignatura a ON c.id_asignatura = a.id_asignatura
                              ORDER BY c.id_convocatoria DESC");
        responderExito($stmt->fetchAll());
    }

    if ($metodo === 'POST') {
        $d = validarConvocatoria(leerJsonBody(), $tiposValidos);
        $stmt = $pdo->prepare('INSERT INTO convocatoria (id_asignatura, fecha_examen, tipo)
                                VALUES (:id_asignatura, :fecha_examen, :tipo) RETURNING id_convocatoria');
        $stmt->execute($d);
        responderExito(['id_convocatoria' => $stmt->fetchColumn()], 'Convocatoria registrada correctamente.');
    }

    if ($metodo === 'PUT') {
        $body = leerJsonBody();
        $id = (int)($body['id_convocatoria'] ?? 0);
        if ($id <= 0) {
            responderError('ID de convocatoria inválido.', 400);
        }
        $d = validarConvocatoria($body, $tiposValidos);
        $d['id'] = $id;
        $stmt = $pdo->prepare('UPDATE convocatoria SET id_asignatura = :id_asignatura, fecha_examen = :fecha_examen,
                                tipo = :tipo WHERE id_convocatoria = :id');
        $stmt->execute($d);
        responderExito([], 'Convocatoria actualizada correctamente.');
    }

    if ($metodo === 'DELETE') {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            responderError('ID de convocatoria inválido.', 400);
        }
        $stmt = $pdo->prepare('DELETE FROM convocatoria WHERE id_convocatoria = :id');
        $stmt->execute([':id' => $id]);
        responderExito([], 'Convocatoria eliminada correctamente.');
    }

    responderError('Método no permitido.', 405);
} catch (PDOException $e) {
    responderError('No se pudo completar la operación.', 500);
}
