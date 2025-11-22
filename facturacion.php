<?php
session_start();
require 'config/db.php';

// 1. Validación de sesión
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

// 2. Obtener datos del cliente
$email = $_SESSION['user'];
$stmtCliente = $pdo->prepare("SELECT * FROM clientes WHERE email = ?");
$stmtCliente->execute([$email]);
$cliente = $stmtCliente->fetch();

// Iniciales
function obtenerIniciales($n, $a=''){
    return strtoupper($n[0] . $a[0]);
}
$iniciales = obtenerIniciales($cliente['nombre'], $cliente['apellido']);

$pagoExitoso = false;

// 4. Actualizar estado de pago
if (isset($_POST['pagar']) && isset($_POST['idFactura'])) {

    $idFactura = $_POST['idFactura'];

    $updateFactura = $pdo->prepare("
        UPDATE factura 
        SET FK_estadoCita = 1 
        WHERE idFacturas = ?
    ");
    $updateFactura->execute([$idFactura]);

    $pagoExitoso = true;
}

// 5. Consulta de citas y facturas SOLO DEL CLIENTE LOGUEADO
$idCliente = $cliente['idCliente'];

$sql = "
    SELECT 
        c.fechaCita,
        s.nombreServicio,
        s.precio,
        f.idFacturas,
        f.FK_estadoCita
    FROM cita c
    INNER JOIN servicios s ON c.FK_servicio = s.idServicio
    LEFT JOIN factura f ON f.FK_cita = c.idCita
    WHERE c.FK_cliente = ?
    AND f.idFacturas IS NOT NULL
    ORDER BY c.fechaCita DESC
";


$stmt = $pdo->prepare($sql);
$stmt->execute([$idCliente]);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facturación - Perle Noire</title>
    <link rel="stylesheet" href="assets/css/factura.css">
</head>
<body>

<nav class="navbar">
    <div class="logo"> Perle Noire </div>
    <div class="user-section">
        <div class="user-info">
                <div class="user-avatar">👤</div>
                <span><?php echo htmlspecialchars($cliente['nombre'] ?? 'Usuario'); ?></span>
            </div>
        <a href="servicios.php" class="btn-header">💅 Servicios</a>
        <a href="logout.php" class="btn-header">Cerrar Sesión</a>
    </div>
</nav>

<div class="main-content">
    <h1 class="page-title">Historial de Citas y Facturación</h1>

    <div class="facturacion-card">
        <h2 class="card-title">Mis Citas 📝</h2>

        <?php if (count($result) > 0): ?>

        <div class="table-responsive">
            <table class="facturacion-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Servicio</th>
                        <th>Precio</th>
                        <th>Factura #</th>
                        <th>Pago</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($result as $cita): ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($cita['fechaCita'])) ?></td>
                        <td><?= htmlspecialchars($cita['nombreServicio']) ?></td>
                        <td>$<?= number_format($cita['precio'], 0, '.', ',') ?></td>
                        <td><?= $cita['idFacturas'] ?: 'N/A' ?></td>

                        <td>
                            <?php if ($cita['idFacturas']): ?>

                                <?php if ($cita['FK_estadoCita'] == 2): ?>
                                    <form method="POST">
                                        <input type="hidden" name="idFactura" value="<?= $cita['idFacturas'] ?>">
                                        <button type="submit" name="pagar" class="btn-pagar">Pagar</button>
                                    </form>
                                <?php else: ?>
                                    <span class="badge-pagado">✔ Pagado</span>
                                <?php endif; ?>

                            <?php else: ?>
                                <span class="badge-pendiente">Sin factura</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php else: ?>
            <div class="no-citas">No tienes citas registradas aún 😔</div>
        <?php endif; ?>
    </div>
</div>

<!-- MODAL -->
<?php if ($pagoExitoso): ?>
<div id="modalPago" style="
    display:flex;
    position:fixed;
    top:0; left:0;
    width:100%; height:100%;
    background:rgba(0,0,0,0.6);
    justify-content:center;
    align-items:center;
">
    <div class="modal-box">
        <h2>✅ Pago Exitoso</h2>
        <p>Tu pago ha sido procesado correctamente.</p>
        <button onclick="window.location='facturacion.php'">Cerrar</button>
    </div>
</div>
<?php endif; ?>

</body>
</html>
