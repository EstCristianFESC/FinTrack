<?php
session_start();
require __DIR__ . '/../sql/bd.php';
require __DIR__ . '/../includes/helpers.php';

$mensaje = "";

// Mostrar mensaje de éxito si viene desde el registro
if (isset($_GET['registro']) && $_GET['registro'] === 'ok') {
    $mensaje = "Usuario registrado exitosamente. Ahora puedes iniciar sesión.";
}

// Procesar formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Login
    if (isset($_POST['login'])) {
        $email = $_POST['email'] ?? '';
        $pass = $_POST['password'] ?? '';

        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($pass, $usuario['password'])) {
            $_SESSION['user_id'] = $usuario['id'];
            $_SESSION['nombre'] = $usuario['nombre'];
            header("Location: dashboard.php");
            exit;
        } else {
            $mensaje = "Correo o contraseña incorrectos.";
        }
    }

    // Registro
    if (isset($_POST['registro'])) {
        $nombre = $_POST['nombre'] ?? '';
        $email = $_POST['email'] ?? '';
        $pass = $_POST['password'] ?? '';
        $confirm = $_POST['confirmar'] ?? '';

        if ($pass !== $confirm) {
            $mensaje = "Las contraseñas no coinciden.";
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            try {
                $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)");
                $stmt->execute([$nombre, $email, $hash]);

                $id_usuario = $pdo->lastInsertId();

                crearCategoriasPorDefecto($pdo, $id_usuario);

                // Redirigir al login con mensaje de éxito
                header("Location: index.php?registro=ok");
                exit;
            } catch (PDOException $e) {
                $mensaje = $e->getCode() == 23000 ? "El correo ya está registrado." : "Error: " . $e->getMessage();
            }
        }
    }

}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>FinTrack - Login y Registro</title>
    <link rel="icon" href="../assets//img/Logo icono.svg" type="image/x-icon">
    <link rel="stylesheet" href="../assets/css/login.css">
</head>
<body>
    <div class="outer-wrapper">

        <?php if ($mensaje): ?>
            <div style="display: flex; justify-content: center;">
                <p style="color: <?= str_contains($mensaje, 'exitosamente') ? 'green' : 'red' ?>; font-weight: bold; text-align: center;">
                    <?= $mensaje ?>
                </p><br><br>
            </div>
        <?php endif; ?>

        <!-- Formulario de login -->
        <div id="loginForm" class="container">
        <div class="logo-wrapper">
            <img src="../assets/img/logo.png" alt="FinTrack" class="logo-fintrack">
        </div>
        <h3>Iniciar sesión</h3>
        <form method="POST" action="">
            <input type="email" name="email" id="emailLogin" placeholder="Correo electrónico" required>
            <input type="password" name="password" id="passwordLogin" placeholder="Contraseña" required>
            <button type="submit" name="login">Ingresar</button>
        </form>
        <p>¿No tienes cuenta? <a href="#" id="goToRegister">Regístrate</a></p>
        </div>

        <!-- Formulario de registro -->
        <div id="registerForm" class="container" style="display: none;">
        <div class="logo-wrapper">
            <img src="../assets/img/logo.png" alt="FinTrack" class="logo-fintrack">
        </div>
        <h3>Registro de usuario</h3>
        <form method="POST" action="">
            <input type="text" name="nombre" id="nombre" placeholder="Nombre" required>
            <input type="email" name="email" id="emailRegister" placeholder="Correo electrónico" required>
            <input type="password" name="password" id="passwordRegister" placeholder="Contraseña" required>
            <input type="password" name="confirmar" id="confirmar" placeholder="Confirmar contraseña" required>
            <button type="submit" name="registro">Registrarse</button>
        </form>
        <p>¿Ya tienes cuenta? <a href="#" id="goToLogin">Inicia sesión</a></p>
        </div>
    </div>

    <script>
        // Alternar formularios
        document.getElementById('goToRegister').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('loginForm').style.display = 'none';
            document.getElementById('registerForm').style.display = 'block';
        });

        document.getElementById('goToLogin').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('registerForm').style.display = 'none';
            document.getElementById('loginForm').style.display = 'block';
        });

        // Mostrar automáticamente el login si venimos de registro
        const params = new URLSearchParams(window.location.search);
        if (params.get('registro') === 'ok') {
            document.getElementById('registerForm').style.display = 'none';
            document.getElementById('loginForm').style.display = 'block';
        }
    </script>
</body>
</html>