<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../sql/bd.php';

// Obtener usuario actual
$id_usuario = $_SESSION['user_id'];

require __DIR__ . '/../includes/categorias_procesar.php';

// Obtener cuentas del usuario
$stmt = $pdo->prepare("SELECT id, nombre, saldo_actual FROM cuentas WHERE id_usuario = ?");
$stmt->execute([$id_usuario]);
$cuentas = $stmt->fetchAll();

require __DIR__ . '/../includes/ingreso.php';
require __DIR__ . '/../includes/cuentas_procesar.php';

// Obtener nombre del usuario
$stmt = $pdo->prepare("SELECT nombre FROM usuarios WHERE id = ?");
$stmt->execute([$id_usuario]);
$usuario = $stmt->fetch();

// Sumar saldo total de todas las cuentas
$stmt = $pdo->prepare("SELECT SUM(saldo_actual) AS total FROM cuentas WHERE id_usuario = ?");
$stmt->execute([$id_usuario]);
$saldo = $stmt->fetch()['total'] ?? 0;

// Calcular total de ingresos
$stmt = $pdo->prepare("SELECT SUM(monto) AS total FROM movimientos WHERE id_usuario = ? AND tipo = 'ingreso'");
$stmt->execute([$id_usuario]);
$totalIngresos = $stmt->fetch()['total'] ?? 0;

// Calcular total de egresos
$stmt = $pdo->prepare("SELECT SUM(monto) AS total FROM movimientos WHERE id_usuario = ? AND tipo = 'egreso'");
$stmt->execute([$id_usuario]);
$totalEgresos = $stmt->fetch()['total'] ?? 0;

// Calcular porcentaje gastado
$porcentajeGasto = $totalIngresos > 0 ? round(($totalEgresos / $totalIngresos) * 100, 2) : 0;

// Obtener categorías por tipo
$categorias = ['ingreso' => [], 'egreso' => []];
foreach (['ingreso', 'egreso'] as $tipo) {
    $stmt = $pdo->prepare("SELECT id, nombre FROM categorias WHERE id_usuario = ? AND tipo = ?");
    $stmt->execute([$id_usuario, $tipo]);
    $categorias[$tipo] = $stmt->fetchAll();
}

// Obtener últimos 5 movimientos
$stmt = $pdo->prepare("
    SELECT m.tipo, m.monto, m.descripcion, m.fecha, c.nombre AS cuenta
    FROM movimientos m
    JOIN cuentas c ON m.id_cuenta = c.id
    WHERE m.id_usuario = ?
    ORDER BY m.id DESC
    LIMIT 5
");
$stmt->execute([$id_usuario]);
$movimientos = $stmt->fetchAll();

// Procesar borrado de cuenta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['borrar_cuenta'])) {
    // Eliminar movimientos
    $pdo->prepare("DELETE FROM movimientos WHERE id_usuario = ?")->execute([$id_usuario]);
    // Eliminar cuentas
    $pdo->prepare("DELETE FROM cuentas WHERE id_usuario = ?")->execute([$id_usuario]);

    header("Location: dashboard.php");
    exit;
}

if (isset($_GET['eliminar_cuenta'])) {
    $id_cuenta = intval($_GET['eliminar_cuenta']);
    $id_usuario = $_SESSION['user_id'];

    // Verificar si hay movimientos asociados a esta cuenta
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM movimientos WHERE id_cuenta = ? AND id_usuario = ?");
    $stmt->execute([$id_cuenta, $id_usuario]);
    $movimientosCount = $stmt->fetchColumn();

    if ($movimientosCount == 0) {
        // No tiene movimientos, borrar cuenta físicamente
        $stmt = $pdo->prepare("DELETE FROM cuentas WHERE id = ? AND id_usuario = ?");
        $stmt->execute([$id_cuenta, $id_usuario]);
        $mensaje = "Cuenta eliminada correctamente.";
    } else {
        // Tiene movimientos, solo desactivar la cuenta
        $stmt = $pdo->prepare("UPDATE cuentas SET activo = 0 WHERE id = ? AND id_usuario = ?");
        $stmt->execute([$id_cuenta, $id_usuario]);
        $mensaje = "Cuenta desactivada porque tiene movimientos asociados.";
    }

    header("Location: dashboard.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - FinTrack</title>
    <link rel="icon" href="../assets//img/Logo icono.svg" type="image/x-icon">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
</head>
<body>
    <header>
        <div class="logo">
            <img src="../assets/img/logo.png" alt="FinTrack">
        </div>
        <h2>Bienvenido, <?= htmlspecialchars($usuario['nombre']) ?></h2>
        <div class="card cerrar-sesion">
            <div class="avatar-menu">
                <video src="../assets/video/Avatar.mp4" class="avatar" autoplay loop muted playsinline></video>
                <div class="menu-logout">
                <a href="logout.php" class="logout-btn">Cerrar Sesión</a>

                <form method="POST" style="display:inline;">
                    <button type="submit" name="borrar_cuenta" class="btn btn-cancel" onclick="return confirm('¿Estás seguro de borrar los datos de tu cuenta? Esta acción no se puede deshacer y perderas todos tus datos.')">
                        Borrar Datos
                    </button>
                </form>
                </div> 
            </div>
        </div>
    </header>

    <div class="container">
        <!-- Tarjeta resumen financiero -->
        <div class="card stats">
            <h3>Resumen Financiero</h3>
            <p><strong>Saldo disponible:</strong> $<?= number_format($saldo, 2, ',', '.') ?></p>
            <p><strong>Total ingresos:</strong> $<?= number_format($totalIngresos, 2, ',', '.') ?></p>
            <p><strong>Total egresos:</strong> $<?= number_format($totalEgresos, 2, ',', '.') ?></p>
            <p><strong>Porcentaje Gastado:</strong> <?= $porcentajeGasto ?>%</p>
            <?php include __DIR__ . '/../componentes/formulario_categoria.php'; ?>

            <?php
                $exePath = realpath(__DIR__ . '/../C++/mensajes.exe');
                $mensaje = shell_exec("\"$exePath\" 2>&1");
                echo '<p style="text-align:center; margin-top:10px;"><strong>' . htmlspecialchars($mensaje) . '</strong></p>';
            ?>
        </div>

        <!-- Tarjeta de cuentas -->
        <div class="card">
            <h4>Tus cuentas:</h4><br>
            <div class="cuentas-container">
                <?php foreach ($cuentas as $cuenta): ?>
                    <div class="cuenta-card">
                        <h5><?= htmlspecialchars($cuenta['nombre']) ?></h5>
                        <p>$<?= number_format($cuenta['saldo_actual'], 2, ',', '.') ?></p>
                        <a href="?eliminar_cuenta=<?= $cuenta['id'] ?>"
                            class="btn-eliminar"
                            onclick="return confirm('¿Eliminar esta cuenta? Esta acción no se puede deshacer.')"
                            title="Eliminar cuenta">&minus;</a>
                    </div>
                <?php endforeach; ?>
            </div><br>
            <?php include __DIR__ . '/../componentes/formulario_cuenta.php'; ?>
        </div>

        <!-- Tarjeta de movimientos -->
        <div class="card">
            <h4>Últimos movimientos:</h4><br>
            <div class="movimientos-container">
                <?php foreach ($movimientos as $mov): ?>
                    <?php
                        $fechaFormateada = date('d/m/Y', strtotime($mov['fecha']));
                        $descripcion = trim($mov['descripcion']);
                        switch ($mov['tipo']) {
                            case 'ingreso':
                                $tipoClase = 'mov-ingreso';
                                break;
                            case 'egreso':
                                $tipoClase = 'mov-egreso';
                                break;
                            case 'ajuste':
                                $tipoClase = 'mov-ajuste';
                                break;
                            case 'transferencia':
                                $tipoClase = 'mov-transferencia';
                                break;
                            default:
                                $tipoClase = '';
                        }
                    ?>
                    <div class="movimiento-card <?= $tipoClase ?>">
                        <div class="mov-fecha"><?= $fechaFormateada ?></div>
                        <div class="mov-detalle">
                            <span class="mov-tipo"><?= ucfirst($mov['tipo']) ?></span> en <span class="mov-cuenta"><?= htmlspecialchars($mov['cuenta']) ?></span>
                        </div>
                        <div class="mov-monto">$<?= number_format($mov['monto'], 2, ',', '.') ?></div>
                        <?php if ($descripcion !== ''): ?>
                            <div class="mov-desc"><?= htmlspecialchars($descripcion) ?></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php include __DIR__ . '/../componentes/formulario_movimiento.php'; ?>
            <button onclick="document.getElementById('modalMovimientos').style.display='flex'" class="btn" style="margin-top: 10px;">
                📅 Ver más movimientos
            </button>

            <?php include __DIR__ . '/../componentes/modal_movimientos_mes.php'; ?>
        </div>
    </div>
</body>
</html>