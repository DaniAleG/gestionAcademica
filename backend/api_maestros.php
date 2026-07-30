<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/includes/api_helpers.php';
require_once __DIR__ . '/conexion.php';
requerirSesionApi();

$metodo = $_SERVER['REQUEST_METHOD'];

function validarMaestro(array $d, PDO $pdo, ?int $idExcluir = null): array
{
    $cedula = preg_replace('/\D+/', '', (string)($d['cedula'] ?? '')) ?? '';
    $nombre = normalizarNombre((string)($d['nombre'] ?? ''));
    $apellido = normalizarNombre((string)($d['apellido'] ?? ''));
    $correo = trim((string)($d['correo'] ?? ''));
    $fecha_nacimiento = trim((string)($d['fecha_nacimiento'] ?? ''));
    $especialidad = normalizarTexto((string)($d['especialidad'] ?? ''));

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
    // Un maestro necesita ser mayor de edad y estar en un rango laboral razonable.
    validarFechaNacimiento($fecha_nacimiento, 21, 75);

    $sql = 'SELECT id_maestro FROM maestro WHERE (cedula = :cedula OR correo = :correo)';
    if ($idExcluir !== null) {
        $sql .= ' AND id_maestro <> :id';
    }
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':cedula', $cedula);
    $stmt->bindValue(':correo', $correo);
    if ($idExcluir !== null) {
        $stmt->bindValue(':id', $idExcluir, PDO::PARAM_INT);
    }
    $stmt->execute();
    if ($stmt->fetch()) {
        responderError('Ya existe un maestro con esa cédula o correo.', 409);
    }

    return compact('cedula', 'nombre', 'apellido', 'correo', 'fecha_nacimiento', 'especialidad');
}

try {
    if ($metodo === 'GET') {
        // Cualquier rol logueado puede ver la lista de maestros (se usa, por ejemplo,
        // para el selector de "maestro asignado" al crear una asignatura).
        $busqueda = trim((string)($_GET['q'] ?? ''));
        if ($busqueda === '') {
            $stmt = $pdo->query('SELECT id_maestro, cedula, nombre, apellido, correo, fecha_nacimiento, especialidad FROM maestro ORDER BY id_maestro DESC');
        } else {
            $stmt = $pdo->prepare("SELECT id_maestro, cedula, nombre, apellido, correo, fecha_nacimiento, especialidad FROM maestro
                                    WHERE cedula ILIKE :b OR nombre ILIKE :b OR apellido ILIKE :b OR correo ILIKE :b
                                    ORDER BY id_maestro DESC");
            $stmt->execute([':b' => '%' . $busqueda . '%']);
        }
        responderExito($stmt->fetchAll());
    }

    // Crear, editar y borrar maestros: solo el administrador.
    requerirRolApi(['admin']);

    if ($metodo === 'POST') {
        $d = validarMaestro(leerJsonBody(), $pdo);
        $stmt = $pdo->prepare('INSERT INTO maestro (cedula, nombre, apellido, correo, fecha_nacimiento, especialidad)
                                VALUES (:cedula, :nombre, :apellido, :correo, :fecha_nacimiento, :especialidad) RETURNING id_maestro');
        $stmt->execute($d);
        responderExito(['id_maestro' => $stmt->fetchColumn()], 'Maestro registrado correctamente.');
    }

    if ($metodo === 'PUT') {
        $body = leerJsonBody();
        $id = (int)($body['id_maestro'] ?? 0);
        if ($id <= 0) {
            responderError('ID de maestro inválido.', 400);
        }
        $d = validarMaestro($body, $pdo, $id);
        $d['id'] = $id;
        $stmt = $pdo->prepare('UPDATE maestro SET cedula = :cedula, nombre = :nombre, apellido = :apellido,
                                correo = :correo, fecha_nacimiento = :fecha_nacimiento, especialidad = :especialidad WHERE id_maestro = :id');
        $stmt->execute($d);
        responderExito([], 'Maestro actualizado correctamente.');
    }

    if ($metodo === 'DELETE') {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            responderError('ID de maestro inválido.', 400);
        }
        $stmt = $pdo->prepare('DELETE FROM maestro WHERE id_maestro = :id');
        $stmt->execute([':id' => $id]);
        responderExito([], 'Maestro eliminado correctamente.');
    }

    responderError('Método no permitido.', 405);
} catch (PDOException $e) {
    responderError('No se pudo completar la operación (verifica que el registro no esté en uso).', 500);
}
