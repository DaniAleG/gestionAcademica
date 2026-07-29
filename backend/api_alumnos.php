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

function validarAlumno(array $d, PDO $pdo, ?int $idExcluir = null): array
{
    $cedula = preg_replace('/\D+/', '', (string)($d['cedula'] ?? '')) ?? '';
    $nombre = normalizarNombre((string)($d['nombre'] ?? ''));
    $apellido = normalizarNombre((string)($d['apellido'] ?? ''));
    $correo = trim((string)($d['correo'] ?? ''));
    $fecha_nacimiento = trim((string)($d['fecha_nacimiento'] ?? ''));

    if ($cedula === '' || $nombre === '' || $apellido === '' || $correo === '' || $fecha_nacimiento === '') {
        responderError('Todos los campos son obligatorios.', 422);
    }
    if (mb_strlen($nombre) < 2) {
        responderError('El nombre debe tener al menos 2 caracteres.', 422);
    }
    if (mb_strlen($apellido) < 2) {
        responderError('El apellido debe tener al menos 2 caracteres.', 422);
    }
    if (!preg_match('/^\d{10}$/', $cedula)) {
        responderError('La cédula debe tener exactamente 10 dígitos.', 422);
    }
    if (!validarDigitoVerificadorCedula($cedula)) {
        responderError('La cédula ingresada no es válida.', 422);
    }
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        responderError('El correo electrónico no es válido.', 422);
    }
    $fechaValida = DateTime::createFromFormat('Y-m-d', $fecha_nacimiento);
    if (!$fechaValida || $fechaValida > new DateTime()) {
        responderError('La fecha de nacimiento no es válida.', 422);
    }
    $edad = $fechaValida->diff(new DateTime())->y;
    if ($edad < 16 || $edad > 100) {
        responderError('La edad del alumno debe estar entre 16 y 100 años.', 422);
    }

    $sql = 'SELECT id_alumno FROM alumno WHERE (cedula = :cedula OR correo = :correo)';
    if ($idExcluir !== null) {
        $sql .= ' AND id_alumno <> :id';
    }
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':cedula', $cedula);
    $stmt->bindValue(':correo', $correo);
    if ($idExcluir !== null) {
        $stmt->bindValue(':id', $idExcluir, PDO::PARAM_INT);
    }
    $stmt->execute();
    if ($stmt->fetch()) {
        responderError('Ya existe un alumno con esa cédula o correo.', 409);
    }

    return compact('cedula', 'nombre', 'apellido', 'correo', 'fecha_nacimiento');
}

try {
    if ($metodo === 'GET') {
        $busqueda = trim((string)($_GET['q'] ?? ''));
        if ($busqueda === '') {
            $stmt = $pdo->query('SELECT id_alumno, cedula, nombre, apellido, correo, fecha_nacimiento FROM alumno ORDER BY id_alumno DESC');
        } else {
            $stmt = $pdo->prepare("SELECT id_alumno, cedula, nombre, apellido, correo, fecha_nacimiento FROM alumno
                                    WHERE cedula ILIKE :b OR nombre ILIKE :b OR apellido ILIKE :b OR correo ILIKE :b
                                    ORDER BY id_alumno DESC");
            $stmt->execute([':b' => '%' . $busqueda . '%']);
        }
        responderExito($stmt->fetchAll());
    }

    if ($metodo === 'POST') {
        $d = validarAlumno(leerJsonBody(), $pdo);
        $stmt = $pdo->prepare('INSERT INTO alumno (cedula, nombre, apellido, correo, fecha_nacimiento)
                                VALUES (:cedula, :nombre, :apellido, :correo, :fecha_nacimiento) RETURNING id_alumno');
        $stmt->execute($d);
        responderExito(['id_alumno' => $stmt->fetchColumn()], 'Alumno registrado correctamente.');
    }

    if ($metodo === 'PUT') {
        $body = leerJsonBody();
        $id = (int)($body['id_alumno'] ?? 0);
        if ($id <= 0) {
            responderError('ID de alumno inválido.', 400);
        }
        $d = validarAlumno($body, $pdo, $id);
        $d['id'] = $id;
        $stmt = $pdo->prepare('UPDATE alumno SET cedula = :cedula, nombre = :nombre, apellido = :apellido,
                                correo = :correo, fecha_nacimiento = :fecha_nacimiento WHERE id_alumno = :id');
        $stmt->execute($d);
        responderExito([], 'Alumno actualizado correctamente.');
    }

    if ($metodo === 'DELETE') {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            responderError('ID de alumno inválido.', 400);
        }
        $stmt = $pdo->prepare('DELETE FROM alumno WHERE id_alumno = :id');
        $stmt->execute([':id' => $id]);
        responderExito([], 'Alumno eliminado correctamente.');
    }

    responderError('Método no permitido.', 405);
} catch (PDOException $e) {
    responderError('No se pudo completar la operación (verifica que el registro no esté en uso).', 500);
}
