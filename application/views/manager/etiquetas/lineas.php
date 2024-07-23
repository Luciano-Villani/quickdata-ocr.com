<div class="col-md-3">
    <div class="panel-body">
        <?php 
        $a = 1;
        ?>
        <?php foreach ($lineas as $li) : ?>
            <div class="row">
                <div class="col">
                    <h6 class="text-semibold no-margin-top"><?= $li->nro_cuenta ?></h6>
                    <ul class="list list-unstyled">
                        <li>Importe $: &nbsp;<?= $li->total_importe ?></li>
                        <?php
                        $d_none = '';
                        if (count($lineas) >=2) {
                            if($a == 1){
                                $d_none = 'd-none';
                                $a++;
                            }
                        ?>
                            <span data-tabla="_datos_api" data-id_file="<?= $li->id ?>" class="borrar-file acciones <?= $d_none ?> "><a title="Borrar file" href="#" class=""><i class=" text-danger icon-trash " title="Borrar "></i> </a> </span>
                        <?php
                        }
                        ?>
                    </ul>
                </div>
                
                
            </div>
            <?php endforeach ?>
    </div>
    <div class="col">
        <label class="form-label">Sub total:</label>
        <input name="total_importe_ingresado" readonly ="text" class="form-control" placeholder="0" value="<?= $resultIngresado;?>">
        <?php echo form_error('total_importe_ingresado', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
    </div>
    <div class="col">
        <label class="form-label">Total Importe Factura:</label>
        <input readonly name="total_importe" type="text" class="form-control" placeholder="Total importe" value="<?= $resultMulti ?>">
        <?php echo form_error('total_importe', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
    </div>
    <div class="col">
        <div class=" ">
            <?php

            $archivo = explode('/', $result->nombre_archivo, 4);
            ?>
            <span class="">Líneas <?php echo  count($lineas) ?></span>
        </div>
    </div>

</div>