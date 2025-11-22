<?php
// ===================================
// ARCHIVO DE CONEXIÓN A BASE DE DATOS
// ===================================

$host = 'localhost';
$dbnome = 'salon_de_belleza';
$user =  'root';
$pass = 'Avila2001';

try {
    // Intentar conectar con la BD
    $pdo = new PDO("mysql:host=$host;dbname=$dbnome; charset=utf8", $user, $pass);
    
    // Configurar modo de error para excepciones (buenas prácticas)
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e) {
    // Si falla, detener el script y mostrar el error
    die("No se pudo conectar a la base de datos: " . $e->getMessage());
}
?>