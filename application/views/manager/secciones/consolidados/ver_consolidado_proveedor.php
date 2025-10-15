<?php
if (isset($_SERVER['HTTP_REFERER'])) {
    $retorno = $_SERVER['HTTP_REFERER'];
} else {
    $retorno = "/Admin";
}

// En el controlador ver(), cargaste $this->data['result'] = $consolidado,
// pero la vista de Electromecánica usa $consolidado y $comentario.
// Usaremos la variable $result (si es la que envías) como el objeto principal.
// Para imitar la vista de Electromecánica, asumiremos que $result contiene el registro principal.
// La variable $result contiene todos los campos, incluyendo comentarios y seguimiento.
$consolidado = $result; 

// Ya que la vista de Electromecánica usa $comentario->seguimiento,
// podemos igualar $comentario a $result, ya que en el modelo de proveedores 
// la función get_comentario_por_id trae toda la fila (incluyendo el seguimiento).
$comentario = $result; 
?>

<div class="card">
    <div class="card-header header-elements-inline">
        <h5 class="card-title bg-titulo text-center text-dark">Factura / Comentarios y datos adicionales (Proveedor)</h5>
        <div class="header-elements">
            <div class="list-icons">
                <a href="<?= $retorno ?>" type="button" class="mt-3 btn-agregar bg-buton-blue btn">
                    <b><i class="icon-backward"></i></b> Volver
                </a>
            </div>
        </div>
    </div>

    <div class="card-body">
        <?php if ($consolidado && file_exists($consolidado->nombre_archivo)) { ?>
            <embed src="<?= base_url($consolidado->nombre_archivo . '#toolbar=1&navpanes=3&scrollbar=1&zoom=120') ?>" type="application/pdf" width="100%" height="500px">
        <?php } else {
            echo 'No existe el archivo PDF';
        } ?>
    </div>
</div>

<?php if ($consolidado) { ?>
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Datos de seguimiento para la cuenta Nro: <?= $consolidado->nro_cuenta ?></h5>
    </div>

    <div class="col-md-12">
    <form action="<?= base_url('Consolidados/guardar_seguimiento'); ?>" method="POST">
    <div class="form-group">
        
        <textarea name="comentarios" id="comentarios" class="form-control" rows="5" placeholder="Agregue los comentarios o datos de seguimiento aqui..."><?= isset($comentario->comentarios) ? $comentario->comentarios : ''; ?></textarea>
    </div>

    <div class="form-group form-check">
        <input type="checkbox" class="form-check-input" id="en_seguimiento" name="en_seguimiento" 
            <?= isset($comentario->seguimiento) && $comentario->seguimiento == 1 ? 'checked' : ''; ?>>
        <label class="form-check-label" for="en_seguimiento">En Seguimiento</label>
      
    </div>
    <div class="form-group text-center">
    <button type="submit" class="btn-agregar bg-buton-blue btn" id="enviar_comentario">Guardar comentario</button>
</div>

    <input type="hidden" name="id_registro" value="<?= $consolidado->id; ?>">

    
</form>
<?php if (isset($comentario->comentarios) && !empty($comentario->comentarios)): ?>
    <?php 
        // Determinar el estado según el valor de 'seguimiento' (1 es En Seguimiento)
        $estado = isset($comentario->seguimiento) && $comentario->seguimiento == 1 ? 'En seguimiento' : 'Resuelto/Archivado';
        // Determinar el color del mensaje (rojo para "En seguimiento", verde para "Resuelto")
        $alertClass = $estado == 'En seguimiento' ? 'alert-danger' : 'alert-success';
    ?>
    <div class="alert <?= $alertClass ?>">
        Cuenta con observaciones -> Estado: <?= $estado ?>
    </div>
<?php endif; ?>

    
<?php } ?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var comentarios = document.getElementById('comentarios');
        // 🌟🌟🌟 ID CORREGIDO: en_seguimiento 🌟🌟🌟
        var en_seguimiento = document.getElementById('en_seguimiento');

        // Función para actualizar el estado del checkbox (habilitado o deshabilitado)
        function updateCheckboxState() {
            // Hacemos el checkbox siempre editable para Proveedores (opcional, Electromecánica lo deshabilita si está vacío)
            // Si quieres mantener la lógica de Electromecánica:
            if (comentarios.value.trim() === "") {  // Si el textarea está vacío
                 en_seguimiento.disabled = true;  // Deshabilitar el checkbox
            } else {
                 en_seguimiento.disabled = false; // Habilitar el checkbox si hay texto
            }
        }

        // Ejecutar la función al cargar la página para ajustar el estado inicial
        updateCheckboxState();

        // Evento 'input' para cuando el usuario escribe o borra en el textarea
        comentarios.addEventListener('input', function () {
            updateCheckboxState(); // Actualiza el estado del checkbox al escribir
        });
    });
</script>
