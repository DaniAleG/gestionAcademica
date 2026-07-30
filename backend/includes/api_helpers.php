<?php
/**
 * Funciones comunes para todos los endpoints backend/api_*.php.
 * Cada api_*.php es el único archivo de backend que necesita cada
 * módulo (antes eran 4: insertar/actualizar/eliminar/editar).
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

/**
 * Corta la ejecución con un JSON de error si no hay sesión iniciada.
 * Se usa en vez de requerirSesion() (que redirige) porque estos
 * archivos devuelven JSON, no HTML.
 */
function requerirSesionApi(): void
{
    if (!isset($_SESSION['id_usuario'])) {
        responderError('Debes iniciar sesión.', 401);
    }
}
/** Corta la ejecución con 403 si el rol de la sesión no está en la lista permitida. */

/** Corta la ejecución con 403 si el rol de la sesión no está en la lista permitida. */
function requerirRolApi(array $rolesPermitidos): void
{
    $rol = $_SESSION['rol'] ?? '';
    if (!in_array($rol, $rolesPermitidos, true)) {
        responderError('No tienes permisos para realizar esta acción.', 403);
    }
}

/** Devuelve un JSON de éxito y termina la ejecución. */
function responderExito($datos = [], string $mensaje = 'Operación realizada correctamente'): void
{
    echo json_encode(['estado' => 'exito', 'mensaje' => $mensaje, 'datos' => $datos], JSON_UNESCAPED_UNICODE);
    exit;
}

/** Devuelve un JSON de error con el código HTTP indicado y termina la ejecución. */
function responderError(string $mensaje, int $codigoHttp = 400): void
{
    http_response_code($codigoHttp);
    echo json_encode(['estado' => 'error', 'mensaje' => $mensaje], JSON_UNESCAPED_UNICODE);
    exit;
}

/** Lee y decodifica el body JSON de una petición POST/PUT. */
function leerJsonBody(): array
{
    $crudo = file_get_contents('php://input');
    if ($crudo === false || trim($crudo) === '') {
        return [];
    }
    $datos = json_decode($crudo, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($datos)) {
        responderError('El cuerpo de la petición no es un JSON válido.', 400);
    }
    return $datos;
}

/**
 * Valida el dígito verificador de una cédula ecuatoriana (algoritmo módulo 10).
 * Requiere exactamente 10 dígitos numéricos.
 */
function validarDigitoVerificadorCedula(string $numero): bool
{
    if (!preg_match('/^\d{10}$/', $numero)) {
        return false;
    }

    $provincia = (int)substr($numero, 0, 2);
    if ($provincia < 1 || $provincia > 24) {
        return false;
    }

    $digitos = array_map('intval', str_split($numero));
    $sumaPares = 0;
    $sumaImpares = 0;

    for ($i = 0; $i < 9; $i += 2) {
        $mul = $digitos[$i] * 2;
        if ($mul > 9) {
            $mul -= 9;
        }
        $sumaPares += $mul;
    }

    for ($i = 1; $i < 8; $i += 2) {
        $sumaImpares += $digitos[$i];
    }

    $sumaTotal = $sumaPares + $sumaImpares;
    $residuo = $sumaTotal % 10;
    $digitoVerificador = $residuo === 0 ? 0 : 10 - $residuo;

    return $digitoVerificador === $digitos[9];
}

/** Deja solo letras (con tildes/ñ) y espacios simples; colapsa espacios repetidos. */
function normalizarNombre(string $valor): string
{
    $texto = preg_replace('/[^\p{L}\s]/u', '', $valor) ?? '';
    $texto = preg_replace('/\s+/u', ' ', trim($texto)) ?? '';
    return $texto;
}

/** Recorta espacios sobrantes y colapsa espacios repetidos (para textos libres, no solo letras). */
function normalizarTexto(string $valor): string
{
    return preg_replace('/\s+/u', ' ', trim($valor)) ?? '';
}

/**
 * Valida una fecha de nacimiento (formato Y-m-d) y que la edad resultante
 * esté dentro de un rango razonable. Corta la ejecución con 422 si falla.
 * Devuelve el objeto DateTime ya validado.
 */
function validarFechaNacimiento(string $fecha, int $edadMinima, int $edadMaxima): DateTime
{
    $fechaValida = DateTime::createFromFormat('Y-m-d', $fecha);
    if (!$fechaValida || $fechaValida->format('Y-m-d') !== $fecha || $fechaValida > new DateTime()) {
        responderError('La fecha de nacimiento no es válida.', 422);
    }
    $edad = $fechaValida->diff(new DateTime())->y;
    if ($edad < $edadMinima || $edad > $edadMaxima) {
        responderError("La edad debe estar entre $edadMinima y $edadMaxima años.", 422);
    }
    return $fechaValida;
}
