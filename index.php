<?php
// =============================================
// INICIO DE SESIÓN (LOGIN)
// =============================================

session_start();
require 'config/db.php';

$error = '';

// Si el formulario fue enviado (método POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Recoger y limpiar los datos
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $pass = isset($_POST['password']) ? $_POST['password'] : '';

    // 1. Validar que los campos no estén vacíos
    if ($email && $pass) {
        
        // 2. Buscar el usuario en la BD por email y contraseña
        $stmt = $pdo->prepare("SELECT * FROM clientes WHERE email = ? AND password = ?");
        $stmt->execute([$email, $pass]);
        $user = $stmt->fetch();

        // 3. Verificar si se encontró el usuario
        if ($user) {
            // Login exitoso: Guardar email e ID en la sesión
            $_SESSION['user'] = $user['email'];
            $_SESSION['idCliente'] = $user['idCliente'];
            
            // Redirigir al panel de servicios
            header('Location: servicios.php');
            exit;
        } else {
            // Credenciales incorrectas
            $error = 'Usuario o contraseña incorrectos';
        }
    } else {
        // Campos faltantes
        $error = 'Por favor complete todos los campos';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Perle Noire</title>
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
    <div class="login-wrapper">
        
        <div class="brand-panel">
            <div class="brand-icon">✨</div>
            <h1 class="brand-title">Perle Noire System</h1>
            <p class="brand-subtitle">Optimización y Belleza en un solo lugar</p>
            <p class="brand-description">Accede a nuestro sistema de agendamiento y facturación exclusivo para clientes.</p>
        </div>
        
        <div class="form-panel">
            <h2 class="form-title">¡Bienvenido de vuelta!</h2>
            <p class="form-subtitle">Ingresa tus credenciales para continuar</p>
            
            <?php if ($error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form action="index.php" method="POST">
                
                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <input type="email" id="email" name="email" placeholder="tu@email.com" required>
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                </div>

                <button type="submit" class="btn-login">Iniciar Sesión</button>

                <div class="register-link">
                    ¿No tienes cuenta? <a href="register.php">Regístrate aquí</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>