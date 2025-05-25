<link rel="stylesheet" href="../assets/css/componentes.css">

<button onclick="document.getElementById('modalCategoria').style.display='flex'" class="btn">
    ➕ Nueva Categoría
</button>

<div id="modalCategoria" class="modal">
    <div class="modal-content">
        <h3>Nueva Categoría</h3>
        <form method="POST">
            <input type="text" name="nombre" placeholder="Nombre" required>
            
            <select name="tipo" required>
                <option value="ingreso">Ingreso</option>
                <option value="egreso">Egreso</option>
            </select>

            <button type="submit" name="crear_categoria" class="btn">Crear</button>
            <button type="button" onclick="document.getElementById('modalCategoria').style.display='none'" class="btn btn-cancel">Cancelar</button>
        </form>
    </div>
</div>