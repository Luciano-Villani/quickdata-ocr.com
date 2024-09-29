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

    <?= form_open(base_url('Lecturas/views'), array('id' => 'form-validate-jquery')); ?>
    <div class="card-body row container-fluid">
        <div class="col-md-9">
            <div class="row mb-3">
                <div class="col-md-1 d-none">
                    <label class="form-label">#:</label>
                    <input hidden name='id' type="text" class="form-control" value="<?= $result->id ?>">
                </div>
                
                <div class="col-md-4">
    <div class="form-group form-group-feedback form-group-feedback-right">
        <label class="form-label">Proveedor:</label>
        <?php
        // Obtener el nombre del proveedor actual
        $proveedor_nombre = isset($select_proveedores[$result->id_proveedor]['nombre']) ? $select_proveedores[$result->id_proveedor]['nombre'] : 'Proveedor no encontrado';
        ?>
        <!-- Mostrar el nombre del proveedor en un campo de solo lectura -->
        <input type="text" class="form-control" value="<?= $proveedor_nombre ?>" readonly>
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

        <?php if ($this->ion_auth->is_super() || $this->ion_auth->is_admin()) { ?>
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
    <div class="col-12">
        <?php
        $fd = json_decode($result->dato_api);
        echo '<pre>';
        var_dump($fd->document->inference->pages[0]->prediction);
        echo '</pre>';
        ?>
    </div>
</div>
<?php } ?>

<script>

</script>
