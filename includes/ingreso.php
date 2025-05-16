<?php
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['registrar_movimiento'])) {
    $cuenta = $_POST['cuenta'] ?? '';
    $tipo = $_POST['tipo'] ?? '';
    $categoria = $_POST['categoria'] ?? null;
    $monto = $_POST['monto'] ?? 0;
    $descripcion = $_POST['descripcion'] ?? '';

    if (!is_numeric($monto) || $monto <= 0) {
        $mensaje = "El monto debe ser un número válido.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO movimientos (id_usuario, id_cuenta, id_categoria, tipo, monto, descripcion, fecha) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$id_usuario, $cuenta, $categoria ?: null, $tipo, $monto, $descripcion]);

        $signo = $tipo === 'ingreso' ? '+' : '-';
        $stmt = $pdo->prepare("UPDATE cuentas SET saldo_actual = saldo_actual $signo ? WHERE id = ? AND id_usuario = ?");
        $stmt->execute([$monto, $cuenta, $id_usuario]);

        // Redireccionar para evitar reenvío de formulario al refrescar
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();

        $mensaje = "✅ Movimiento registrado correctamente.";
    }
}
?>