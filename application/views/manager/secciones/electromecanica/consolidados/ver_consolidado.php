<?php
if (isset($_SERVER['HTTP_REFERER'])) {
    $retorno = $_SERVER['HTTP_REFERER'];
} else {
    $retorno = "/Admin";
}
?>

<div class="card">
    <div class="card-header header-elements-inline">
        <h5 class="card-title bg-titulo text-center text-dark">Factura / Comentarios y datos adicionales</h5>
        
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
    <form action="<?= base_url('Electromecanica/Consolidados/guardar_comentario_en_consolidados'); ?>" method="POST">
    <div class="form-group">
        
        <textarea name="comentarios" id="comentarios" class="form-control" rows="5" placeholder="Agregue los comentarios o datos de seguimiento aqui..."><?= isset($comentario->comentarios) ? $comentario->comentarios : ''; ?></textarea>
    </div>

    <div class="form-group form-check">
        <input type="checkbox" class="form-check-input" id="resuelto" name="resuelto" 
            <?= isset($comentario->seguimiento) && $comentario->seguimiento == 1 ? 'checked' : ''; ?>>
        <label class="form-check-label" for="resuelto">En Seguimiento</label>
      
    </div>
    <div class="form-group text-center">
    <button type="submit" class="btn-agregar bg-buton-blue btn" id="enviar_comentario">Guardar comentario</button>
</div>

    <input type="hidden" name="id" value="<?= $consolidado->id; ?>">

   
</form>
<!-- Aquí va el bloque de mensaje después del formulario -->
<!-- Aquí va el bloque de mensaje después del formulario -->
<?php if (isset($comentario->comentarios) && !empty($comentario->comentarios)): ?>
    <?php 
        // Determinar el estado según el checkbox
        $estado = isset($comentario->seguimiento) && $comentario->seguimiento == 1 ? 'En seguimiento' : 'Resuelto';
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
        var resuelto = document.getElementById('resuelto');

        // Función para actualizar el estado del checkbox (habilitado o deshabilitado)
        function updateCheckboxState() {
            if (comentarios.value.trim() === "") {  // Si el textarea está vacío
                resuelto.disabled = true;  // Deshabilitar el checkbox
            } else {
                resuelto.disabled = false;  // Habilitar el checkbox si hay texto
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





