<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../sql/bd.php';

$id_usuario = $_SESSION['user_id'];
$anio = $_POST['anio'] ?? null;
$mes = $_POST['mes'] ?? null;

if (!$anio || !$mes) {
    die("Faltan parámetros.");
}

header('Content-Type: text/csv; charset=UTF-8');
header("Content-Disposition: attachment; filename=movimientos_{$anio}_{$mes}.csv");
header('Pragma: no-cache');
header('Expires: 0');

$delimiter = ';';

$output = fopen('php://output', 'w');

fwrite($output, "\xEF\xBB\xBF");

// Encabezados
fputcsv($output, ['Fecha', 'Tipo', 'Cuenta', 'Monto', 'Descripción'], $delimiter);

// Consulta
$stmt = $pdo->prepare("
    SELECT m.fecha, m.tipo, c.nombre AS cuenta, m.monto, m.descripcion
    FROM movimientos m
    JOIN cuentas c ON m.id_cuenta = c.id
    WHERE m.id_usuario = ? AND YEAR(m.fecha) = ? AND MONTH(m.fecha) = ?
    ORDER BY m.fecha DESC
");
$stmt->execute([$id_usuario, $anio, $mes]);

while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $fila['fecha'] = date('d/m/Y', strtotime($fila['fecha']));
    fputcsv($output, $fila, $delimiter);
}

fclose($output);
exit;