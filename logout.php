<?php
// ========================
// CIERRE DE SESIÓN (LOGOUT)
// ========================

// 1. Iniciar la sesión para poder acceder a ella
session_start();

// 2. Destruir la sesión actual (elimina todos los datos de sesión)
session_destroy();

// 3. Redirigir al usuario de vuelta a la página de login (index.php)
header('Location: index.php');

// 4. Detener la ejecución del script
exit;
?>