<?php
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['registrar_movimiento'])) {
    $cuenta_origen = $_POST['cuenta'] ?? '';
    $tipo = $_POST['tipo'] ?? '';
    $categoria = ($tipo === 'ajuste' || $tipo === 'transferencia') ? null : ($_POST['categoria'] ?? null);
    $monto = $_POST['monto'] ?? 0;
    $descripcion = $_POST['descripcion'] ?? '';
    $cuenta_destino = $_POST['cuenta_destino'] ?? null;

    if ($tipo === 'transferencia' && count($cuentas) < 2) {
        $mensaje = "No puedes hacer transferencias porque solo tienes una cuenta registrada.";
    } elseif (!is_numeric($monto) || $monto <= 0) {
        $mensaje = "El monto debe ser un número válido y mayor a cero.";
    } elseif ($tipo === 'transferencia' && $cuenta_origen === $cuenta_destino) {
        $mensaje = "La cuenta origen y destino no pueden ser la misma.";
    } else {
        try {
            // Validar que saldo no quede negativo en transferencia
            if ($tipo === 'transferencia') {
                $stmt = $pdo->prepare("SELECT saldo_actual FROM cuentas WHERE id = ? AND id_usuario = ?");
                $stmt->execute([$cuenta_origen, $id_usuario]);
                $saldo_origen = $stmt->fetchColumn() ?? 0;

                if ($saldo_origen < $monto) {
                    $mensaje = "No tienes saldo suficiente en la cuenta origen para esta transferencia.";
                    throw new Exception($mensaje);
                }
            }

            $pdo->beginTransaction();

            if ($tipo === 'transferencia') {
                // Obtener nombre de cuenta origen
                $stmt = $pdo->prepare("SELECT nombre FROM cuentas WHERE id = ? AND id_usuario = ?");
                $stmt->execute([$cuenta_origen, $id_usuario]);
                $nombre_cuenta_origen = $stmt->fetchColumn() ?: 'Cuenta desconocida';

                // Obtener nombre de cuenta destino
                $stmt = $pdo->prepare("SELECT nombre FROM cuentas WHERE id = ? AND id_usuario = ?");
                $stmt->execute([$cuenta_destino, $id_usuario]);
                $nombre_cuenta_destino = $stmt->fetchColumn() ?: 'Cuenta desconocida';

                // Insertar movimiento egreso con nombre de cuenta destino
                $stmt = $pdo->prepare("INSERT INTO movimientos (id_usuario, id_cuenta, id_categoria, tipo, monto, descripcion, fecha) VALUES (?, ?, NULL, 'egreso', ?, ?, NOW())");
                $stmt->execute([$id_usuario, $cuenta_origen, $monto, "Transferencia a cuenta $nombre_cuenta_destino. " . $descripcion]);

                // Insertar movimiento ingreso con nombre de cuenta origen
                $stmt = $pdo->prepare("INSERT INTO movimientos (id_usuario, id_cuenta, id_categoria, tipo, monto, descripcion, fecha) VALUES (?, ?, NULL, 'ingreso', ?, ?, NOW())");
                $stmt->execute([$id_usuario, $cuenta_destino, $monto, "Transferencia desde cuenta $nombre_cuenta_origen. " . $descripcion]);

                // Actualizar saldos
                $stmt = $pdo->prepare("UPDATE cuentas SET saldo_actual = saldo_actual - ? WHERE id = ? AND id_usuario = ?");
                $stmt->execute([$monto, $cuenta_origen, $id_usuario]);

                $stmt = $pdo->prepare("UPDATE cuentas SET saldo_actual = saldo_actual + ? WHERE id = ? AND id_usuario = ?");
                $stmt->execute([$monto, $cuenta_destino, $id_usuario]);

            } else {
                // Movimiento normal (ingreso, egreso, ajuste)
                $stmt = $pdo->prepare("INSERT INTO movimientos (id_usuario, id_cuenta, id_categoria, tipo, monto, descripcion, fecha) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$id_usuario, $cuenta_origen, $categoria, $tipo, $monto, $descripcion]);

                if ($tipo === 'ajuste') {
                    // Reemplazar saldo
                    $stmt = $pdo->prepare("UPDATE cuentas SET saldo_actual = ? WHERE id = ? AND id_usuario = ?");
                    $stmt->execute([$monto, $cuenta_origen, $id_usuario]);
                } else {
                    // Sumar o restar saldo
                    $signo = $tipo === 'ingreso' ? '+' : '-';
                    $stmt = $pdo->prepare("UPDATE cuentas SET saldo_actual = saldo_actual $signo ? WHERE id = ? AND id_usuario = ?");
                    $stmt->execute([$monto, $cuenta_origen, $id_usuario]);
                }
            }

            $pdo->commit();

            header("Location: " . $_SERVER['REQUEST_URI']);
            exit();

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            if (!isset($mensaje)) {
                $mensaje = "Error al registrar movimiento: " . $e->getMessage();
            }
        }
    }
}
?>