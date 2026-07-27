<?php
session_start();
require_once 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario_ingresado = trim($_POST['usuario'] ?? '');
    $password_ingresada = trim($_POST['password'] ?? '');

    if (empty($usuario_ingresado) || empty($password_ingresada)) {
        header("Location: ../index.php?error=vacio");
        exit;
    }

    try {
        $sql = "SELECT id_usuario, usuario, password, rol FROM usuarios WHERE usuario = :usuario";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':usuario', $usuario_ingresado, PDO::PARAM_STR);
        $stmt->execute();

        $usuario_db = $stmt->fetch();

        if ($usuario_db && password_verify($password_ingresada, $usuario_db['password'])) {
            session_regenerate_id(true);
            $_SESSION['id_usuario'] = $usuario_db['id_usuario'];
            $_SESSION['usuario'] = $usuario_db['usuario'];
            $_SESSION['rol'] = $usuario_db['rol'];

            header("Location: ../panel_principal.php");
            exit;
        } else {
            header("Location: ../index.php?error=1");
            exit;
        }
    } catch (PDOException $e) {
        header("Location: ../index.php?error=db");
        exit;
    }
} else {
    header("Location: ../index.php");
    exit;
}
