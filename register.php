<?php
// =============================================
// REGISTRO DE NUEVOS CLIENTES
// =============================================

require 'db.php'; // Incluir la conexión a la BD

$error = '';
$success = '';

// Si el formulario fue enviado (método POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Recoger y limpiar datos
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $apellido = isset($_POST['apellido']) ? trim($_POST['apellido']) : '';
    $telefono = isset($_POST['telefono']) ? trim($_POST['telefono']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    // =================================
    // 1. INICIO DE VALIDACIONES
    // =================================
    if (empty($nombre) || empty($apellido) || empty($telefono) || empty($email) || empty($password) || empty($confirmPassword)) {
        $error = 'Todos los campos son obligatorios';
    } elseif ($password !== $confirmPassword) {
        $error = 'Las contraseñas no coinciden';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres';
    }
    // =================================
    // 2. FIN DE VALIDACIONES
    // =================================
    
    else {
        try {
            // 3. Verificar si el email ya existe en la BD
            $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM clientes WHERE email = ?");
            $stmtCheck->execute([$email]);
            $emailExists = $stmtCheck->fetchColumn();

            if ($emailExists > 0) {
                $error = 'Este correo electrónico ya está registrado';
            } else {
                // 4. Insertar nuevo cliente (Contraseña en texto plano ⚠️)
                $stmtInsert = $pdo->prepare("INSERT INTO clientes (nombre, apellido, telefono, email, password, fechaRegistro) VALUES (?, ?, ?, ?, ?, NOW())");
                $stmtInsert->execute([$nombre, $apellido, $telefono, $email, $password]);

                // Registro exitoso, redirigir automáticamente al login después de 2 segundos
                $success = '¡Registro exitoso! Redirigiendo al inicio de sesión...';
                header("refresh:2;url=index.php");
            }
        } catch(PDOException $e) {
            // Manejo de error de base de datos
            $error = 'Error al registrar el usuario: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Perle Noire</title>
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
    <div class="login-wrapper">
        
        <div class="brand-panel">
            <div class="brand-icon">📝</div>
            <h1 class="brand-title">Crea tu Cuenta</h1>
            <p class="brand-subtitle">Únete a la experiencia Perle Noire</p>
        </div>
        
        <div class="form-panel">
            <h2 class="form-title">Registro de Clientes</h2>
            <p class="form-subtitle">Completa todos los campos obligatorios (*)</p>
            
            <?php if ($error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <form action="register.php" method="POST">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="nombre">Nombre *</label>
                        <input type="text" id="nombre" name="nombre" placeholder="Tu nombre" required value="<?php echo htmlspecialchars($nombre ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="apellido">Apellido *</label>
                        <input type="text" id="apellido" name="apellido" placeholder="Tu apellido" required value="<?php echo htmlspecialchars($apellido ?? ''); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="telefono">Teléfono *</label>
                    <input type="tel" id="telefono" name="telefono" placeholder="Ej: 555-1234567" required value="<?php echo htmlspecialchars($telefono ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="email">Correo Electrónico *</label>
                    <input type="email" id="email" name="email" placeholder="tu@email.com" required value="<?php echo htmlspecialchars($email ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="password">Contraseña *</label>
                    <input type="password" id="password" name="password" placeholder="Mínimo 6 caracteres" required>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirmar Contraseña *</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Repite tu contraseña" required>
                </div>

                <button type="submit" class="btn-register">Crear Cuenta</button>

                <div class="login-link">
                    ¿Ya tienes cuenta? <a href="index.php">Inicia sesión aquí</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>