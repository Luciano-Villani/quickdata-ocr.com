<?php
if (isset($_SERVER['HTTP_REFERER'])) {
    $retorno = $_SERVER['HTTP_REFERER'];
} else {
    $retorno = "/Admin";
}
?>

<div class="card">
    <div class="card-header header-elements-inline">
        <h5 class="card-title bg-titulo text-center text-dark">Factura PDF subida / datos leídos del modelo</h5>
        <div class="header-elements">
            <div class="list-icons">
                <a href="<?= $retorno ?>" type="button" class="mt-3 btn-agregar bg-buton-blue btn">
                    <b><i class="icon-backward"></i></b> Volver
                </a>
            </div>
        </div>
    </div>

    <div class="card-body">
        <?php if ($result && file_exists($result->nombre_archivo)) { ?>
            <embed src="<?= base_url($result->nombre_archivo . '#toolbar=1&navpanes=3&scrollbar=1&zoom=120') ?>" type="application/pdf" width="100%" height="500px">
        <?php } else {
            echo 'No existe el archivo PDF';
        } ?>
    </div>
</div>

<?php if ($result) { ?>
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Datos de la lectura Nro:<?= $result->id ?></h5>
    </div>

    <?= form_open(base_url('/Electromecanica/Lecturas/Views/'.$result->id), array('id' => 'form-validate-jquery')); ?>
    <div class="card-body row container-fluid">
        <div class="col-md-9">
            <div class="row mb-3">
                <div class="col-md-1 d-none">
                    <label class="form-label">#:</label>
                    <input hidden name='id' type="text" class="form-control" value="<?= $result->id ?>">
                </div>
                
                <div class="col-md-4">
    <div class="form-group form-group-feedback form-group-feedback-right">
        <label class="form-label">Provedor:</label>
        <?php

 
        // Obtener el nombre del proveedor actual
        ?>
        <!-- Mostrar el nombre del proveedor en un campo de solo lectura -->
        <input type="text" class="form-control" value="<?= $result->nombre_proveedor ?>" readonly>
    </div>
</div>



                <div class="col-md-3">
                    <label class="form-label">Nro de cuenta:</label>
                    <input type="text" name="nro_cuenta" class="form-control" placeholder="Nro de cuenta" value="<?= $result->nro_cuenta ?>">
                    <?= form_error('nro_cuenta', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Nro de medidor:</label>
                    <input name="nro_medidor" type="text" class="form-control" placeholder="Nro medidor" value="<?= $result->nro_medidor ?>">
                    <?= form_error('nro_medidor', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Nro de factura:</label>
                    <input name="nro_factura" type="text" class="form-control" placeholder="Nro factura" value="<?= $result->nro_factura ?>">
                    <?= form_error('nro_factura', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Período del consumo:</label>
                    <input name="periodo_del_consumo" type="text" class="form-control" placeholder="Período de consumo" value="<?= $result->periodo_del_consumo ?>">
                    <?= form_error('periodo_del_consumo', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Fecha de emisión:</label>
                    <input id="fecha_emision" name="fecha_emision" type="date" class="form-control" placeholder="Fecha de emisión" value="<?= $result->fecha_emision ?>">
                    <?= form_error('fecha_emision', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Total Importe:</label>
                    <input name="total_importe" type="text" class="form-control" placeholder="Total importe" value="<?= $result->total_importe ?>">
                    <?= form_error('total_importe', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label">Vencimiento:</label>
                    <input id="vencimiento_del_pago" name="vencimiento_del_pago" type="date" class="form-control" placeholder="Vencimiento" value="<?= $result->vencimiento_del_pago ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Total vencido:</label>
                    <input name="total_vencido" type="text" class="form-control" placeholder="Total vencido" value="<?= $result->total_vencido ?>">
                    <?= form_error('total_vencido', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Consumo:</label>
                    <input name="consumo" type="text" class="form-control" placeholder="Consumo" value="<?= $result->consumo ?>">
                </div>
            </div>
            <div class="col-md-12">
    <?php echo form_open('Electromecanica/Lecturas/guardar_comentario_en_consolidados', array('id' => 'form-comentarios')); ?>
    <div class="col-md-12">
        <!-- Campo de comentarios -->
        <div class="form-group">
            <label for="comentarios">Comentarios:</label>
            <textarea class="form-control" id="comentarios" name="comentarios" rows="4" placeholder="Ingrese comentarios"></textarea>
        </div>

        <!-- Checkboxes y botón en la misma fila -->
        <div class="form-group row">
            <div class="col-md-2">
                <div class="form-check">
                    <!-- Checkbox "Resuelto" -->
                    <input type="checkbox" class="form-check-input" id="resuelto" name="resuelto" value="1">
                    <label class="form-check-label" for="resuelto">Resuelto</label>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    <!-- Botón para enviar comentario -->
                    <button type="submit" class="btn btn-agregar bg-buton-blue" id="enviar_comentario">Enviar Comentario</button>
                </div>
            </div>
        </div>
    </div>
<?php echo form_close(); ?>


</div>


            <div class="row mb-6">
                <div class="col-md-6">
                    <label class="form-label">Nombre Archivo:</label>
                    <?php $archivo = explode('/', $result->nombre_archivo, 4); ?>
                    <input disabled readonly type="text" class="form-control" placeholder="Nombre de archivo:" value="<?= $archivo[3] ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Ruta del archivo:</label>
                    <input disabled readonly type="text" class="form-control" placeholder="Ruta del archivo" value="<?= $result->nombre_archivo ?>">
                </div>
            </div>
        </div>

        <?php if (($this->ion_auth->is_super() || $this->ion_auth->is_admin()) && (!$this->ion_auth->is_electro())) { ?>
        <div class="col-md-3">
            <div class="col mb-3">
                <button type="submit" class="btn btn-agregar bg-buton-blue btn-labeled btn-labeled-right">
                    <b><i class="icon-floppy-disk"></i></b>Guardar datos modificados
                </button>
            </div>
        </div>
        <?php } ?>
    </div>
    <?= form_close(); ?>

    <div class="card-header">
        <h5 class="mb-0">Datos de indexación</h5>
    </div>
    <div class="card-body row container-fluid">
        <div class="card">
            <table id="indexaciones_dt" class="table datatable-show-all dataTable no-footer">
                <thead>
                    <tr>
                        <th>Expediente</th>
                        <th>Nro de cuenta</th>
                        <th>Secretaria</th>
                        <th>Dependencia</th>
                        <th>Programa</th>
                        <th>Proyecto</th>
                        <th>Proveedor</th>
                        <th>Tipo pago</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

<?php
}

?>

<?php
// Obtener los valores de seguimiento y resuelto desde la base de datos
$seguimiento = isset($result->seguimiento) ? $result->seguimiento : 0; // 1 si en seguimiento, 0 si resuelto
$resuelto = isset($result->resuelto) ? $result->resuelto : 0; // 0 si resuelto, 1 si no resuelto
?>
<script>
    $(document).ready(function() {
        // Al escribir en el campo de comentarios, desmarcar "Resuelto" y cambiar seguimiento a 1
        $('#comentarios').on('input', function() {
            if ($(this).val().trim() !== '') {
                $('#resuelto').prop('checked', false); // Desmarcar "Resuelto"
            }
        });

        // Cuando se cambie el estado del checkbox "Resuelto", actualizar el campo de seguimiento
        $('#resuelto').change(function() {
            if ($(this).prop('checked')) {
                // Si se marca "Resuelto", establecer seguimiento en 0
                $('#seguimiento').val(0);
            } else {
                // Si no se marca "Resuelto", establecer seguimiento en 1
                $('#seguimiento').val(1);
            }
        });

        // Al hacer submit, también guardar el valor de seguimiento
        $('#enviar_comentario').on('click', function() {
            if ($('#resuelto').prop('checked')) {
                $('#seguimiento').val(0); // Si está marcado "Resuelto", seguimiento será 0
            } else {
                $('#seguimiento').val(1); // Si no está marcado, seguimiento será 1
            }
        });
    });
</script>