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

        <embed src="<?= base_url($result->nombre_archivo . '#toolbar=1&navpanes=3&scrollbar=1&zoom=150' )?>" type="application/pdf" width="100%" height="500px">
    </div>

</div>



<div class="card">
    <div class="card-header ">
        <h5 class="mb-0">Datos del modelo</h5>

    </div>

    <form action="#">

        <div class="card-body row container-fluid">
            <div class="col-md-9">
                <div class="row mb-3">
                    <div class="col-md-2 ">
                        <label class="form-label">Proveedor:</label>
                        <input type="text" class="form-control" placeholder="Nombre proveedor" value="<?= $result->nombre_proveedor ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Nro de cuenta:</label>
                        <input type="text" class="form-control" placeholder="Nro de cuenta" value="<?= $result->nro_cuenta ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Nro de medidor:</label>
                        <input type="text" class="form-control" placeholder="Nro medidor" value="<?= $result->nro_medidor ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Nro de factura:</label>
                        <input type="text" class="form-control" placeholder="Nro factura" value="<?= $result->nro_factura ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Período del consumo:</label>
                        <input type="text" class="form-control" placeholder="Período de consumo" value="<?= $result->periodo_del_consumo ?>">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-2">
                        <label class="form-label">Fecha de emisión:</label>
                        <input type="text" class="form-control" placeholder="Fecha de emisión:" value="<?= fecha_es($result->fecha_emision) ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Vencimiento:</label>
                        <input type="text" class="form-control" placeholder="Vencimiento" value="<?= fecha_es($result->vencimiento_del_pago) ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Total Importe:</label>
                        <input type="text" class="form-control" placeholder="Total importe" value="<?= $result->total_importe ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Total vencido:</label>
                        <input type="text" class="form-control" placeholder="Total vencido" value="<?= $result->total_vencido ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Consumo:</label>
                        <input type="text" class="form-control" placeholder="Consumo" value="<?= $result->consumo ?>">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-2">
                        <label class="form-label">Nombre Archivo</label>
                        <?php

                        $archivo = explode('/', $result->nombre_archivo, 4);

                        ?>
                        <input readonly type="text" class="form-control" placeholder="Nombre de archivo:" value="<?= $archivo[3] ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Ruta del archivo:</label>
                        <input readonly type="text" class="form-control" placeholder="Ruta del archivo" value="<?= $result->nombre_archivo ?>">
                    </div>

                </div>

            </div>
            <?php if($this->ion_auth->is_super() || $this->ion_auth->is_admin()){ ?>
            <div class="col-md-3">
                <div class="col mb-3">
                    <button type="submit" class="btn bg-teal-400 btn-labeled btn-labeled-right"><b><i class="icon-plus3"></i></b>Modificar datos</button>
                </div>
                <div class="col mb-3">
                    <button type="submit" class="btn btn-danger btn-labeled btn-labeled-right"><b><i class="icon-plus3"></i></b>Elimimar datos</button>
                </div>

            </div>
            <?php }?>

        </div>
    </form>

    <div class="card-header ">
        <h5 class="mb-0">Datos de indexación</h5>
    </div>
    <div class="card-body row container-fluid">
        <div class="card">

            <table id="indexaciones_dt" class="table datatable-show-all dataTable no-footer">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Id interno </th>
                        <th>Nro de cuenta</th>
                        <th>Secretaria</th>
                        <th>Dependencia</th>
                        <th>Programa</th>
                        <th>Proyecto</th>
                        <th>proveedor</th>
                        <th>Fecha alta</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

            </table>

        </div>
    </div>

</div>