<?php
declare(strict_types=1);

/**
 * Conexión a PostgreSQL.
 *
 * Funciona en dos escenarios sin tocar código:
 *  1) Local (XAMPP/localhost): usa los valores por defecto de abajo,
 *     o defínelos como variables de entorno si prefieres.
 *  2) Render: Render inyecta automáticamente la variable de entorno
 *     DATABASE_URL con la cadena de conexión de la base de datos Postgres
 *     que crees en el dashboard. Si existe, se usa esa.
 */

$databaseUrl = getenv('DATABASE_URL') ?: null;

if ($databaseUrl) {
    // --- Conexión usando la URL que entrega Render ---
    $partes = parse_url($databaseUrl);

    $host     = $partes['host'] ?? 'localhost';
    $port     = (string)($partes['port'] ?? '5432');
    $user     = $partes['user'] ?? 'postgres';
    $password = $partes['pass'] ?? '';
    $database = ltrim($partes['path'] ?? '', '/');
    // Render exige SSL para conectarse a su Postgres desde fuera de su red interna
    $sslmode  = 'require';
} else {
    // --- Conexión local (localhost / pgAdmin / PostgreSQL instalado en tu PC) ---
    $host     = getenv('DB_HOST') ?: 'localhost';
    $port     = getenv('DB_PORT') ?: '5432';
    $user     = getenv('DB_USER') ?: 'postgres';
    $password = getenv('DB_PASSWORD') ?: '1234'; // Cambia esto por tu contraseña local
    $database = getenv('DB_NAME') ?: 'gestion_academica';
    $sslmode  = 'prefer';
}

$dsn = "pgsql:host=$host;port=$port;dbname=$database;sslmode=$sslmode";

$opciones = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $password, $opciones);
} catch (PDOException $e) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'estado'  => 'error',
        'mensaje' => 'Error de conexión a la base de datos', 
    ]);
    exit;
}
