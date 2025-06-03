<link rel="stylesheet" href="../assets/css/componentes.css">

<button onclick="document.getElementById('modalCuenta').style.display='flex'" class="btn" style="margin: 10px;">
    ➕ Nueva Cuenta
</button>

<div id="modalCuenta" class="modal">
    <div class="modal-content">
        <h3>Crear nueva cuenta</h3>
        <form method="POST">
            <label>Nombre:</label>
            <input type="text" name="nombre" placeholder="Ej: Nequi, Efectivo" required>

            <label>Tipo:</label>
            <input type="text" name="tipo" placeholder="Ej: digital, banco, efectivo">

            <label>Saldo inicial:</label>
            <input type="number" name="saldo" step="0.01" value="0" required>

            <button type="submit" name="crear_cuenta" class="btn">Crear</button>
            <button type="button" onclick="document.getElementById('modalCuenta').style.display='none'" class="btn btn-cancel">Cancelar</button>
        </form>
    </div>
</div>