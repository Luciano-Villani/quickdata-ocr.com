<?php

// if ($this->ion_auth->is_admin()  ){
// echo 'si';
// }else{
//     echo 'no';
// }

?>


<div class="card ">
    <div class="card-header header-elements-inline">
        <h5 class="card-title"><?= $this->data['page_title'] ?></h5>
        <div class="header-elements">
            <div class="list-icons">
                <a class="list-icons-item" data-action="collapse"></a>
            </div>
        </div>
    </div>

    <div class="card-body">

        <embed src="<?= base_url($result->nombre_archivo . '#toolbar=1&navpanes=3&scrollbar=1&zoom=115') ?>" type="application/pdf" width="100%" height="500px">
    </div>

</div>

<div id="pdfContainer"></div>


<?php
if ($result) {
?>

    <div class="card">
        <div class="card-header ">
            <h5 class="mb-0">Datos del modelo #<?= $result->id ?></h5>

        </div>


        <?php echo form_open(base_url('Electromecanica/Lecturas/Views'), array('id' => 'form-validate-jquery')); ?>

        <div class="card-body row container-fluid">

            <div class="col-md-9">
                <div class="row mb-3">
                    <div class="col-md-1 d-none  ">
                        <label class="form-label">#:</label>
                        <input hidden name='id' type="text" class="form-control" placeholder="" value="<?= $result->id ?>">
                    </div>
                    <div class="col-md-4 ">

                        <div class="form-group form-group-feedback form-group-feedback-right">
                            <label class="form-label">Proveedor:</label>
                            <div class="form-control-feedback">
                            </div>
                            <?php
                            $js = array(
                                'required' => 'required',
                                'id' => 'proveedor',
                                'disabled' => 'disabled',
                                'class' => ' select2 form-control custom-select ',
                            );
                            ?>

                            <?= form_dropdown('proveedor', $select_proveedores, @$result->id_proveedor, $js); ?>
                            <?php echo form_error('proveedor', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>

                        </div>

                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Nro de cuenta:</label>
                        <input type="text" name="nro_cuenta" class="form-control" placeholder="Nro de cuenta" value="<?= trim($result->nro_cuenta) ?>">
                        <?php echo form_error('nro_cuenta', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>

                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Nro de medidor:</label>
                        <input name="nro_medidor" type="text" class="form-control" placeholder="Nro medidor" value="<?= $result->nro_medidor ?>">
                        <?php echo form_error('nro_medidor', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>

                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Nro de factura:</label>
                        <input name="nro_factura" type="text" class="form-control" placeholder="Nro factura" value="<?= $result->nro_factura ?>">
                        <?php echo form_error('nro_factura', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>

                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Período del consumo:</label>
                        <input name="periodo_del_consumo" type="text" class="form-control" placeholder="Período de consumo" value="<?= $result->periodo_del_consumo ?>">
                        <?php echo form_error('periodo_del_consumo', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Fecha de emisión:<?php echo  $result->fecha_emision ?></label>
                        <input id="fechaemision" name="fecha_emision" type="date" class="form-control" value="<?= trim($result->fecha_emision) ?>">
                        <?php echo form_error('fecha_emision', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
                    </div>
                    <script>
                        $("#fechaemision_no").AnyTime_picker({
                            format: "%d-%m-%Z"
                        });
                    </script>
                    <div class="col-md-2">
                        <label class="form-label">Total Importe:</label>
                        <input name="total_importe" type="text" class="form-control" placeholder="Total importe" value="<?= $result->total_importe ?>">
                        <?php echo form_error('total_importe', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
                    </div>
                </div>

                <div class="row mb-3">

                    <div class="col-md-3">
                        <label class="form-label">Vencimiento:<?= $result->vencimiento_del_pago ?></label>
                        <input id="vencimiento_del_pago" name="vencimiento_del_pago" type="date" class="form-control" placeholder="Vencimiento" value="<?= trim($result->vencimiento_del_pago) ?>">
                    </div>
                    <script>
                        $("#vencimiento_del_pago_NO").AnyTime_picker({
                            format: "%d-%m-%Z"
                        });
                    </script>
                    <div class="col-md-2">
                        <label class="form-label">Total vencido:</label>
                        <input name="total_vencido" type="text" class="form-control" placeholder="Total vencido" value="<?= $result->total_vencido ?>">
                        <?php echo form_error('total_vencido', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>

                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Consumo:</label>
                        <input name="consumo" type="text" class="form-control" placeholder="Consumo" value="<?= $result->consumo ?>">
                    </div>
                </div>

                <div class="row mb-6">
                    <div class="col-md-6">
                        <label class="form-label">Nombre Archivo</label>
                        <?php

                        $archivo = explode('/', $result->nombre_archivo, 4);

                        ?>
                        <input disabled readonly type="text" class="form-control" placeholder="Nombre de archivo:" value="<?= $archivo[3] ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Ruta del archivo:</label>
                        <input disabled readonly type="text" class="form-control" placeholder="Ruta del archivo" value="<?= $result->nombre_archivo ?>">
                    </div>

                </div>

            </div>
            <?php if ($this->ion_auth->is_super() || $this->ion_auth->is_admin() || $this->ion_auth->is_electro()) { ?>
                <div class="col-md-3">
                    <div class="col mb-3">
                        <button type="submit" class="btn bg-teal-400 btn-labeled btn-labeled-right"><b><i class="icon-plus3"></i></b>Modificar datos</button>
                    </div>
                    <div class="col mb-3">
                        <!-- <button type="submit" class="btn btn-danger btn-labeled btn-labeled-right"><b><i class="icon-plus3"></i></b>Elimimar datos</button> -->
                    </div>

                </div>
            <?php } ?>

        </div>
        <?php echo form_close(); ?>

        <div class="card-header ">
            <h5 class="mb-0">Datos de indexación</h5>
        </div>
        <div class="card-body row container-fluid">
            <div class="card">

                <table id="indexaciones_dt" class="table datatable-show-all dataTable no-footer">
                    <thead>
                        <tr>
                            <th>ID interno</th>
                            <th>Nro de cuenta</th>
                            <th>Secretaria</th>
                            <th>Dependencia</th>
                            <th>Programa</th>
                            <th>Proyecto</th>
                            <th>proveedor</th>
                            <th>Tipo pago</th>

                        </tr>
                    </thead>

                </table>

            </div>
        </div>
        <div class="col-12">
            <?php

            $fd = json_decode($result->dato_api);
            // echo '<pre>';
            // var_dump($fd->document->inference->pages[0]->prediction);
            // echo '</pre>';

            $updateData = [];
            foreach ($fd->document->inference->pages[0]->prediction as $key => $item) {

                $elem = trim($key);

                $totalIndices = count($fd->document->inference->pages[0]->prediction->$elem->values);

                $valorCampo = '';
                $paso = 0;
                if ($totalIndices > 1) {

                    for ($paso; $paso < $totalIndices; $paso++) {
                        $valorCampo .= ' ' . trim($fd->document->inference->pages[0]->prediction->$elem->values[$paso]->content);
                    }
                    $updateData[$elem] = $valorCampo;
                }else{

                    echo $key;
                    echo $totalIndices;
                    $updateData[$elem] = trim($fd->document->inference->pages[0]->prediction->$elem->values[0]->content);
                }

            }
            echo '<pre>--->';
            var_dump($updateData);
            echo '</pre>';
            ?>
        </div>
    </div>

<?php
}

?>