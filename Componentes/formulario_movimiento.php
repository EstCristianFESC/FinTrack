<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Registrar Movimiento</title>
    <!-- Vincula el CSS -->
    <link rel="stylesheet" href="../assets/css/componentes.css" />
</head>
<body>

<!-- Botón para abrir el modal -->
<button onclick="document.getElementById('modalMovimiento').style.display='flex'" class="btn" style="margin: 20px; padding: 10px 20px;">
    ➕ Registrar movimiento
</button>

<!-- Modal -->
<div id="modalMovimiento" class="modal">
    <div class="modal-content">
        <h3>Nuevo Movimiento</h3>
        <form method="POST">
            <label>Cuenta:</label>
            <select name="cuenta" required>
                <?php foreach ($cuentas as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                <?php endforeach; ?>
            </select>

            <label>Tipo:</label>
            <select name="tipo" id="tipoSelect" onchange="toggleCategorias()" required>
                <option value="ingreso">Ingreso</option>
                <option value="egreso">Egreso</option>
            </select>

            <label>Categoría:</label>
            <select name="categoria" id="categoriaSelect" required>
                <?php foreach ($categorias['ingreso'] as $cat): ?>
                    <option data-tipo="ingreso" value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
                <?php endforeach; ?>
                <?php foreach ($categorias['egreso'] as $cat): ?>
                    <option data-tipo="egreso" value="<?= $cat['id'] ?>" style="display:none"><?= htmlspecialchars($cat['nombre']) ?></option>
                <?php endforeach; ?>
            </select>

            <label>Monto:</label>
            <input type="number" step="0.01" name="monto" required />

            <label>Descripción:</label>
            <input type="text" name="descripcion" />

            <button type="submit" name="registrar_movimiento" class="btn">Guardar</button>
            <button type="button" onclick="document.getElementById('modalMovimiento').style.display='none'" class="btn btn-cancel">Cancelar</button>
        </form>
    </div>
</div>

<!-- JS para alternar categorías según tipo -->
<script>
function toggleCategorias() {
    let tipo = document.getElementById("tipoSelect").value;
    let options = document.querySelectorAll("#categoriaSelect option");

    options.forEach(opt => {
        opt.style.display = opt.getAttribute("data-tipo") === tipo ? "block" : "none";
    });

    const firstVisible = Array.from(options).find(opt => opt.style.display === "block");
    if (firstVisible) {
        document.getElementById("categoriaSelect").value = firstVisible.value;
    }
}
</script>

</body>
</html>