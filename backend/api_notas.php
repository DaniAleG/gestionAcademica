<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/includes/api_helpers.php';
require_once __DIR__ . '/conexion.php';
requerirSesionApi();

$metodo = $_SERVER['REQUEST_METHOD'];
$tiposValidos = ['Parcial', 'Final', 'Supletorio'];
$NOTA_MINIMA_APROBACION = 7.0;

/** Arma el estado académico (Aprobado/Reprobado/En curso) a partir de las notas de una matrícula. */
function calcularEstadoAcademico(array $notasPorTipo, float $minimaAprobacion): string
{
    // El Supletorio, si existe, es el que define el resultado final.
    if (isset($notasPorTipo['Supletorio'])) {
        return $notasPorTipo['Supletorio'] >= $minimaAprobacion ? 'Aprobado' : 'Reprobado';
    }
    if (isset($notasPorTipo['Final'])) {
        return $notasPorTipo['Final'] >= $minimaAprobacion ? 'Aprobado' : 'Reprobado';
    }
    return 'En curso';
}

/** Verifica que la matrícula exista y (si es maestro) que la asignatura le pertenezca. Devuelve la fila de matricula+asignatura. */
function obtenerMatriculaOFallar(PDO $pdo, int $idMatricula): array
{
    $stmt = $pdo->prepare('SELECT m.id_matricula, m.id_alumno, asg.id_asignatura, asg.id_maestro
                            FROM matricula m INNER JOIN asignatura asg ON m.id_asignatura = asg.id_asignatura
                            WHERE m.id_matricula = :id');
    $stmt->execute([':id' => $idMatricula]);
    $fila = $stmt->fetch();
    if (!$fila) {
        responderError('La matrícula indicada no existe.', 404);
    }
    if (($_SESSION['rol'] ?? '') === 'maestro' && (int)$fila['id_maestro'] !== (int)($_SESSION['id_maestro'] ?? -1)) {
        responderError('No puedes calificar una asignatura que no dictas.', 403);
    }
    return $fila;
}

try {
    if ($metodo === 'GET') {
        $rol = $_SESSION['rol'] ?? '';
        $sql = "SELECT m.id_matricula, m.id_alumno, m.id_asignatura, m.estado AS estado_matricula,
                       CONCAT(al.nombre, ' ', al.apellido) AS nombre_alumno, al.cedula,
                       asg.nombre AS nombre_asignatura, asg.id_maestro,
                       n.tipo, n.nota
                FROM matricula m
                INNER JOIN alumno al ON m.id_alumno = al.id_alumno
                INNER JOIN asignatura asg ON m.id_asignatura = asg.id_asignatura
                LEFT JOIN nota n ON n.id_matricula = m.id_matricula";
        $condiciones = [];
        $params = [];

        if ($rol === 'alumno') {
            $condiciones[] = 'm.id_alumno = :id_alumno';
            $params[':id_alumno'] = (int)($_SESSION['id_alumno'] ?? 0);
        } elseif ($rol === 'maestro') {
            $condiciones[] = 'asg.id_maestro = :id_maestro';
            $params[':id_maestro'] = (int)($_SESSION['id_maestro'] ?? 0);
        }
        // admin: sin condición extra, ve todo (opcionalmente filtrado por asignatura abajo)

        if (ctype_digit((string)($_GET['id_asignatura'] ?? ''))) {
            $condiciones[] = 'm.id_asignatura = :id_asignatura';
            $params[':id_asignatura'] = (int)$_GET['id_asignatura'];
        }

        if ($condiciones) {
            $sql .= ' WHERE ' . implode(' AND ', $condiciones);
        }
        $sql .= ' ORDER BY m.id_matricula, n.tipo';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $filas = $stmt->fetchAll();

        // Agrupa las filas (una por cada nota) en un registro por matrícula.
        $resultado = [];
        foreach ($filas as $fila) {
            $id = (int)$fila['id_matricula'];
            if (!isset($resultado[$id])) {
                $resultado[$id] = [
                    'id_matricula' => $id,
                    'id_alumno' => (int)$fila['id_alumno'],
                    'id_asignatura' => (int)$fila['id_asignatura'],
                    'nombre_alumno' => $fila['nombre_alumno'],
                    'cedula' => $fila['cedula'],
                    'nombre_asignatura' => $fila['nombre_asignatura'],
                    'estado_matricula' => $fila['estado_matricula'],
                    'notas' => [],
                ];
            }
            if ($fila['tipo'] !== null) {
                $resultado[$id]['notas'][$fila['tipo']] = (float)$fila['nota'];
            }
        }
        foreach ($resultado as &$registro) {
            $registro['estado_academico'] = calcularEstadoAcademico($registro['notas'], $NOTA_MINIMA_APROBACION);
        }
        unset($registro);

        responderExito(array_values($resultado));
    }

    // Registrar/editar/borrar notas: admin o el maestro dueño de la asignatura (nunca el alumno).
    requerirRolApi(['admin', 'maestro']);

    if ($metodo === 'POST' || $metodo === 'PUT') {
        $body = leerJsonBody();
        $id_matricula = (int)($body['id_matricula'] ?? 0);
        $tipo = trim((string)($body['tipo'] ?? ''));
        $notaValor = $body['nota'] ?? '';

        if ($id_matricula <= 0) {
            responderError('Matrícula inválida.', 422);
        }
        if (!in_array($tipo, $tiposValidos, true)) {
            responderError('El tipo de evaluación no es válido.', 422);
        }
        if ($notaValor === '' || !is_numeric($notaValor) || (float)$notaValor < 0 || (float)$notaValor > 10) {
            responderError('La nota debe ser un número entre 0 y 10.', 422);
        }

        obtenerMatriculaOFallar($pdo, $id_matricula); // valida existencia + permiso del maestro

        $stmt = $pdo->prepare('INSERT INTO nota (id_matricula, tipo, nota, fecha_registro)
                                VALUES (:id_matricula, :tipo, :nota, CURRENT_DATE)
                                ON CONFLICT (id_matricula, tipo)
                                DO UPDATE SET nota = EXCLUDED.nota, fecha_registro = CURRENT_DATE');
        $stmt->execute([
            ':id_matricula' => $id_matricula,
            ':tipo' => $tipo,
            ':nota' => round((float)$notaValor, 2),
        ]);
        responderExito([], 'Nota guardada correctamente.');
    }

    if ($metodo === 'DELETE') {
        $id_matricula = (int)($_GET['id_matricula'] ?? 0);
        $tipo = trim((string)($_GET['tipo'] ?? ''));
        if ($id_matricula <= 0 || !in_array($tipo, $tiposValidos, true)) {
            responderError('Parámetros inválidos.', 422);
        }
        obtenerMatriculaOFallar($pdo, $id_matricula);

        $stmt = $pdo->prepare('DELETE FROM nota WHERE id_matricula = :id_matricula AND tipo = :tipo');
        $stmt->execute([':id_matricula' => $id_matricula, ':tipo' => $tipo]);
        responderExito([], 'Nota eliminada correctamente.');
    }

    responderError('Método no permitido.', 405);
} catch (PDOException $e) {
    responderError('No se pudo completar la operación.', 500);
}
