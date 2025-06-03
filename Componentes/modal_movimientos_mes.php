<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../sql/bd.php';

$id_usuario = $_SESSION['user_id'];

// Obtener años disponibles
$stmt = $pdo->prepare("
    SELECT DISTINCT YEAR(fecha) AS anio
    FROM movimientos
    WHERE id_usuario = ?
    ORDER BY anio DESC
");
$stmt->execute([$id_usuario]);
$anios_disponibles = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Variables para año y mes
$anio_seleccionado = $_GET['anio'] ?? null;
$mes_seleccionado = $_GET['mes'] ?? null;

$meses_nombre = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
];

$movimientos_filtrados = [];

// Si hay año y mes seleccionados, traer movimientos
if ($anio_seleccionado && $mes_seleccionado) {
    $stmt = $pdo->prepare("
        SELECT m.tipo, m.monto, m.descripcion, m.fecha, c.nombre AS cuenta
        FROM movimientos m
        JOIN cuentas c ON m.id_cuenta = c.id
        WHERE m.id_usuario = ? AND YEAR(m.fecha) = ? AND MONTH(m.fecha) = ?
        ORDER BY m.id DESC
    ");
    $stmt->execute([$id_usuario, $anio_seleccionado, $mes_seleccionado]);
    $movimientos_filtrados = $stmt->fetchAll();
}

// Obtener meses disponibles para año seleccionado
$meses_disponibles = [];
if ($anio_seleccionado) {
    $stmt = $pdo->prepare("
        SELECT DISTINCT MONTH(fecha) AS mes
        FROM movimientos
        WHERE id_usuario = ? AND YEAR(fecha) = ?
        ORDER BY mes ASC
    ");
    $stmt->execute([$id_usuario, $anio_seleccionado]);
    $meses_disponibles = $stmt->fetchAll(PDO::FETCH_COLUMN);
}
?>

<style>
.modal {
    display: none;
    position: fixed;
    z-index: 10000;
    left: 0; top: 0;
    width: 100%; height: 100%;
    background-color: rgba(0,0,0,0.5);
    justify-content: center;
    align-items: center;
}
.modal-content {
    background-color: white;
    padding: 30px;
    border-radius: 12px;
    max-width: 600px;
    width: 90%;
    max-height: 90%;
    overflow-y: auto;
}
</style>

<div id="modalMovimientos" class="modal">
    <div class="modal-content">
        <h3>Movimientos por mes y año</h3>

        <form method="GET" action="" style="margin-bottom: 15px;">
            <label for="anio">Año:</label>
            <select name="anio" id="anio" onchange="this.form.submit()">
                <option value="">Selecciona un año</option>
                <?php foreach ($anios_disponibles as $anio): ?>
                    <option value="<?= $anio ?>" <?= ($anio == $anio_seleccionado) ? 'selected' : '' ?>>
                        <?= $anio ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <?php if ($anio_seleccionado): ?>
                <label for="mes">Mes:</label>
                <select name="mes" id="mes" onchange="this.form.submit()">
                    <option value="">Selecciona un mes</option>
                    <?php foreach ($meses_disponibles as $mes): ?>
                        <option value="<?= $mes ?>" <?= ($mes == $mes_seleccionado) ? 'selected' : '' ?>>
                            <?= $meses_nombre[$mes] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>
        </form>

        <?php if ($anio_seleccionado && $mes_seleccionado): ?>
            <?php if (count($movimientos_filtrados) > 0): ?>
                <?php foreach ($movimientos_filtrados as $mov): ?>
                    <?php
                        $fechaFormateada = date('d/m/Y', strtotime($mov['fecha']));
                        $descripcion = trim($mov['descripcion']);
                        $tipoClase = $mov['tipo'] === 'ingreso' ? 'mov-ingreso' : 'mov-egreso';
                    ?>
                    <div class="movimiento-card <?= $tipoClase ?>" style="margin-bottom: 10px;">
                        <div><strong><?= $fechaFormateada ?></strong></div>
                        <div><?= ucfirst($mov['tipo']) ?> en <strong><?= htmlspecialchars($mov['cuenta']) ?></strong></div>
                        <div style="color: <?= $mov['tipo'] === 'ingreso' ? '#27ae60' : '#c0392b' ?>;">
                            $<?= number_format($mov['monto'], 2, ',', '.') ?>
                        </div>
                        <?php if ($descripcion !== ''): ?>
                            <div style="font-style: italic;">(<?= htmlspecialchars($descripcion) ?>)</div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No hay movimientos para este mes y año.</p>
            <?php endif; ?>
        <?php endif; ?>

        <form method="POST" action="../Componentes/exportar_excel.php" target="_blank">
            <input type="hidden" name="anio" value="<?= htmlspecialchars($anio_seleccionado) ?>">
            <input type="hidden" name="mes" value="<?= htmlspecialchars($mes_seleccionado) ?>">
            <button type="submit" class="btn btn-success">Descargar informe</button>
        </form>

        <button onclick="cerrarModal()" class="btn btn-cancel" style="margin-top: 15px;">Cerrar</button>
    </div>
</div>

<?php if ($anio_seleccionado || $mes_seleccionado): ?>
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            document.getElementById('modalMovimientos').style.display = 'flex';
        });
    </script>
<?php endif; ?>

<script>
    function cerrarModal() {
        const urlLimpia = window.location.pathname;
        window.location.href = urlLimpia;
    }
</script>