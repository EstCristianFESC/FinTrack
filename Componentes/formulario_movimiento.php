<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Registrar Movimiento</title>
    <link rel="stylesheet" href="../assets/css/componentes.css" />
</head>
<body>

<button onclick="document.getElementById('modalMovimiento').style.display='flex'" class="btn" style="margin: 10px; padding: 10px 20px;">
    ➕ Registrar movimiento
</button>

<div id="modalMovimiento" class="modal">
    <div class="modal-content">
        <h3>Nuevo Movimiento</h3>
        <form method="POST">
            <label>Cuenta origen:</label>
            <select name="cuenta" id="cuentaOrigen" required>
                <?php foreach ($cuentas as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                <?php endforeach; ?>
            </select>

            <label>Tipo:</label>
            <select name="tipo" id="tipoSelect" onchange="toggleCampos()" required>
                <option value="ingreso">Ingreso</option>
                <option value="egreso">Egreso</option>
                <option value="ajuste">Ajuste</option>
                <?php if (count($cuentas) > 1): ?>
                    <option value="transferencia">Transferencia entre cuentas</option>
                <?php endif; ?>
            </select>
            
            <label id="labelCuentaDestino" style="display:none;">Cuenta destino:</label>
            <select name="cuenta_destino" id="cuentaDestino" style="display:none;">
                <?php foreach ($cuentas as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                <?php endforeach; ?>
            </select>

            <label id="labelCategoria" style="display:inline-block;">Categoría:</label>
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

<script>
function toggleCampos() {
    let tipo = document.getElementById("tipoSelect").value;

    const categoriaSelect = document.getElementById("categoriaSelect");
    const labelCategoria = document.getElementById("labelCategoria");
    const cuentaDestino = document.getElementById("cuentaDestino");
    const labelCuentaDestino = document.getElementById("labelCuentaDestino");
    const cuentaOrigen = document.getElementById("cuentaOrigen");

    if (tipo === "ingreso" || tipo === "egreso") {
        categoriaSelect.style.display = "inline-block";
        categoriaSelect.required = true;
        labelCategoria.style.display = "inline-block";

        let options = categoriaSelect.querySelectorAll("option");
        options.forEach(opt => {
            opt.style.display = opt.getAttribute("data-tipo") === tipo ? "block" : "none";
        });

        const firstVisible = Array.from(options).find(opt => opt.style.display === "block");
        if (firstVisible) {
            categoriaSelect.value = firstVisible.value;
        }

        cuentaDestino.style.display = "none";
        cuentaDestino.required = false;
        labelCuentaDestino.style.display = "none";

    } else if (tipo === "ajuste") {
        categoriaSelect.style.display = "none";
        categoriaSelect.required = false;
        labelCategoria.style.display = "none";

        cuentaDestino.style.display = "none";
        cuentaDestino.required = false;
        labelCuentaDestino.style.display = "none";

    } else if (tipo === "transferencia") {
        categoriaSelect.style.display = "none";
        categoriaSelect.required = false;
        labelCategoria.style.display = "none";

        cuentaDestino.style.display = "inline-block";
        cuentaDestino.required = true;
        labelCuentaDestino.style.display = "inline-block";

        actualizarCuentaDestino();
    }
}


function actualizarCuentaDestino() {
    const cuentaOrigenVal = document.getElementById("cuentaOrigen").value;
    const cuentaDestino = document.getElementById("cuentaDestino");
    
    for (let option of cuentaDestino.options) {
        option.disabled = option.value === cuentaOrigenVal;
    }
    
    if (cuentaDestino.value === cuentaOrigenVal) {
        for (let option of cuentaDestino.options) {
            if (!option.disabled) {
                cuentaDestino.value = option.value;
                break;
            }
        }
    }
}

document.getElementById("cuentaOrigen").addEventListener("change", () => {
    if (document.getElementById("tipoSelect").value === "transferencia") {
        actualizarCuentaDestino();
    }
});

document.getElementById("tipoSelect").addEventListener("change", toggleCampos);

document.addEventListener("DOMContentLoaded", toggleCampos);
</script>
</body>
</html>