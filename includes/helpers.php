<?php
function crearCategoriasPorDefecto($pdo, $id_usuario) {
    $categoriasIngreso = ['Salario', 'Venta', 'Intereses'];
    $categoriasEgreso = ['Alquiler', 'Comida', 'Transporte', 'Servicios', 'Salud'];

    $stmt = $pdo->prepare("INSERT INTO categorias (id_usuario, nombre, tipo) VALUES (?, ?, ?)");

    foreach ($categoriasIngreso as $nombre) {
        $stmt->execute([$id_usuario, $nombre, 'ingreso']);
    }

    foreach ($categoriasEgreso as $nombre) {
        $stmt->execute([$id_usuario, $nombre, 'egreso']);
    }
}
?>