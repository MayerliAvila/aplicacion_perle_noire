<?php
// =============================================
// CATÁLOGO Y AGENDAMIENTO DE SERVICIOS
// =============================================

session_start();
require 'config/db.php';

// 1. VALIDACIÓN DE SESIÓN: Si no hay usuario, redirigir
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

// 2. OBTENER DATOS PRINCIPALES
// Obtener detalles del cliente logueado
$email = $_SESSION['user'];
$stmtCliente = $pdo->prepare("SELECT * FROM clientes WHERE email = ?");
$stmtCliente->execute([$email]);
$cliente = $stmtCliente->fetch();

// Obtener lista de todos los servicios
$stmt = $pdo->query("SELECT * FROM servicios ORDER BY nombreServicio");
$servicios = $stmt->fetchAll();

// Variables para controlar el modal y mensajes
$mostrarModal = false;
$servicioSeleccionado = null;
$mensaje = '';
$error = '';

// 3. PROCESAMIENTO: MOSTRAR MODAL DE AGENDAMIENTO (GET)
if (isset($_GET['agendar']) && $_GET['agendar'] > 0) {
    
    $mostrarModal = true;
    $idServicio = intval($_GET['agendar']);
    
    // Obtener detalles del servicio
    $stmtServicio = $pdo->prepare("SELECT * FROM servicios WHERE idServicio = ?");
    $stmtServicio->execute([$idServicio]);
    $servicioSeleccionado = $stmtServicio->fetch();
    
    // Obtener personal disponible
    $stmtPersonal = $pdo->query("SELECT * FROM personal WHERE estadoDisponible = TRUE ORDER BY nombre");
    $personalDisponible = $stmtPersonal->fetchAll();
}

// 4. PROCESAMIENTO: AGENDAR CITA (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agendar_cita'])) {

    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];
    $idPersonal = intval($_POST['personal']);
    $idServicioPost = intval($_POST['servicio_id']);

    $fechaCita = $fecha . ' ' . $hora;

    try {
        // ---- 1. Insertar la cita ----
        $stmtInsert = $pdo->prepare("
            INSERT INTO cita (FK_cliente, FK_personal, FK_servicio, fechaCita)
            VALUES (?, ?, ?, ?)
        ");
        $stmtInsert->execute([$cliente['idCliente'], $idPersonal, $idServicioPost, $fechaCita]);

        // Obtener id generado
        $idCita = $pdo->lastInsertId();

        // ---- 2. Obtener precio del servicio ----
        $stmtPrecio = $pdo->prepare("SELECT precio FROM servicios WHERE idServicio = ?");
        $stmtPrecio->execute([$idServicioPost]);
        $precioServicio = $stmtPrecio->fetchColumn();

        // ---- 3. Crear ID de factura ----
        $idFacturas = 'FAC-' . str_pad($idCita, 5, '0', STR_PAD_LEFT);

        // ---- 4. Insertar factura (estado por pagar = idEstado 2) ----
        $stmtFactura = $pdo->prepare("
            INSERT INTO factura (idFacturas, fechaGeneracion, montoTotal, FK_cita, FK_estadoCita)
            VALUES (?, CURDATE(), ?, ?, 2)
        ");
        $stmtFactura->execute([$idFacturas, $precioServicio, $idCita]);

        // ---- 5. Éxito ----
        $mensaje = 'Cita y factura creadas correctamente 🎉';
        header("refresh:2;url=servicios.php");

    } catch(PDOException $e) {
        $error = 'Error al agendar la cita: ' . $e->getMessage();
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuestros Servicios - Perle Noire</title>
    <link rel="stylesheet" href="assets/css/services.css">

</head>
<body>
    
    <nav class="navbar">
        <div class="logo"> Perle Noire </div>
        <div class="user-section">
            <div class="user-info">
                <div class="user-avatar">👤</div>
                <span><?php echo htmlspecialchars($cliente['nombre'] ?? 'Usuario'); ?></span>
            </div>
            <a href="facturacion.php" class="btn-header">📅 Facturación</a>
            <a href="logout.php" class="btn-header">Cerrar Sesión</a>
        </div>
    </nav>

    <div class="main-content">
        <h1 class="page-title">Catálogo de Servicios ✨</h1>
        
        <?php if ($mensaje): ?>
            <div class="alert success"><?php echo htmlspecialchars($mensaje); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="services-grid">
            <?php if (count($servicios) > 0): ?>
                <?php foreach ($servicios as $servicio): ?>
                    <div class="service-card" onclick="window.location.href='servicios.php?agendar=<?php echo $servicio['idServicio']; ?>'">
                        <div class="service-header">
                            <span class="service-icon">✂️</span>
                        </div>
                        <div class="service-body">
                            <h3 class="service-title"><?php echo htmlspecialchars($servicio['nombreServicio']); ?></h3>
                            <p class="service-description"><?php echo htmlspecialchars($servicio['descripcion']); ?></p>
                            <div class="service-meta">
                                <span class="price">$<?php echo number_format($servicio['precio'], 2); ?></span>
                                <span class="duration"><?php echo htmlspecialchars($servicio['duracionMinuto']); ?> min</span>
                            </div>
                        </div>
                        <div class="service-footer">
                            <button class="btn-agendar">Agendar ahora</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align: center; padding: 60px 20px; color: #999; font-size: 18px;"> 
                    No hay servicios disponibles en este momento 😔 
                </div> 
            <?php endif; ?>
        </div>
    </div>
    
    <div class="modal-overlay <?php echo $mostrarModal ? 'active' : ''; ?>" id="modalAgendar">
        <?php if ($mostrarModal && $servicioSeleccionado): ?>
        <div class="modal-container">
            <div class="modal-header">
                <h2 class="modal-title">Agendar Cita</h2>
                <button class="close-btn" onclick="window.location.href='servicios.php'">×</button>
            </div>
            <div class="modal-body">
                
                <form action="servicios.php" method="POST">
                    <input type="hidden" name="servicio_id" value="<?php echo htmlspecialchars($servicioSeleccionado['idServicio']); ?>">
                    
                    <div class="form-group">
                        <label for="servicio_nombre">Servicio Seleccionado</label>
                        <input type="text" id="servicio_nombre" value="<?php echo htmlspecialchars($servicioSeleccionado['nombreServicio'] . ' - $' . number_format($servicioSeleccionado['precio'], 2)); ?>" disabled>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="fecha">Fecha *</label>
                            <input type="date" id="fecha" name="fecha" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="form-group">
                    <label for="hora">Hora *</label>
                    <select id="hora" name="hora" required>
                        <option value="">Selecciona una hora</option>
                        
                        <option value="10:00:00">10:00 AM</option>
                        <option value="10:30:00">10:30 AM</option>
                        <option value="11:00:00">11:00 AM</option>
                        <option value="11:30:00">11:30 AM</option>
                        <option value="12:00:00">12:00 PM</option>
                        <option value="12:30:00">12:30 PM</option>
                        <option value="13:00:00">01:00 PM</option>
                        <option value="13:30:00">01:30 PM</option>
                        <option value="14:00:00">02:00 PM</option>
                        <option value="14:30:00">02:30 PM</option>
                        <option value="15:00:00">03:00 PM</option>
                        <option value="15:30:00">03:30 PM</option>
                        <option value="16:00:00">04:00 PM</option>
                        <option value="16:30:00">04:30 PM</option>
                        <option value="17:00:00">05:00 PM</option>
                    </select>
                </div>
                    </div>
                    
                    <div class="estilista-selection">
                        <label class="estilista-label">Selecciona tu Estilista Favorito *</label>
                        <div class="estilistas-grid">
                            <?php foreach ($personalDisponible as $personal): ?>
                                <div 
                                    class="estilista-card" 
                                    onclick="selectEstilista(<?php echo $personal['idPersonal']; ?>, this)"
                                    >
                                    <div class="estilista-avatar">💅</div>
                                    <span class="estilista-nombre"><?php echo htmlspecialchars($personal['nombre'] . ' ' . $personal['apellido']); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <input type="hidden" id="personal" name="personal" required>
                    </div>
                    
                    <button type="submit" name="agendar_cita" class="btn-confirmar">Confirmar Cita</button>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script>
        function selectEstilista(id, element) {
            // 1. Remover la clase 'selected' de todas las tarjetas
            document.querySelectorAll('.estilista-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // 2. Agregar 'selected' a la tarjeta actual
            element.classList.add('selected');
            
            // 3. Asignar el ID al campo oculto para que se envíe con el formulario
            document.getElementById('personal').value = id;
        }
    </script>
</body>
</html>