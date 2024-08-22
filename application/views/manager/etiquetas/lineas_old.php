<div class="panel-body" style="background-color: #f0f0f0; border: 2px solid #10355E;"> <!-- Aquí agregas el fondo de color -->
    <?php $a = 1; ?>

    <div class="col" style="background-color: #C9D4E6;">
        
        <h3 class="pt-1" >Total facturado: $ <?= number_format((float)$totalFactura, 2, '.', '') ?></h3> <!-- Asegurar que sea un número flotante y formatear sin separadores de miles -->
        <input readonly name="total_importe" type="hidden" class="form-control" placeholder="Total importe" value="<?= number_format((float)$totalFactura, 2, '.', '') ?>"> <!-- Ajuste en el input -->
        <?php echo form_error('total_importe', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
     <div class="col pl-2">
            <h5 class="pb-1" id="cuentas-count">Cuentas Imputadas a esta factura: <?php echo count($lineas) ?></h5>
    </div>
    </div>
   

    <?php foreach ($lineas as $li) : ?>
        <div data-file="<?= $li->id ?>" class="row mx-2">
            <div class="col">
                <h6 class="text-semibold">Cuenta: <?= $li->nro_cuenta ?></h6>
                <ul class="list list-unstyled pb-2" style="margin-top: -10px;">
                    <li class="pb-1">Importe $: &nbsp;<?= number_format((float)$li->total_importe, 2, '.', '') ?></li> <!-- Ajuste en cada importe -->
                    <?php
                    $d_none = '';
                    $d_si = 'd-none';
                    if (count($lineas) >= 2) {
                        if ($a == 1) {
                            $d_none = 'd-none';
                            $d_si = '';
                            $a++;
                        }
                    ?>
                        <span data-importe="<?= $li->importe_1 ?>" data-tabla="_datos_api" data-id_file="<?= $li->id ?>" class="borrar-file acciones <?= $d_none ?>"><a title="Borrar file" href="#" class=""><i class=" text-danger icon-trash " title="Borrar "></i> </a> Borrar cuenta</span>
                        <span data-tabla="_datos_multiple" data-id_file="<?= $li->id ?>" class="reset-file acciones <?= $d_si?>"><a title="Reset file" href="#" class=""><i class=" text-success icon-reset " title="Reset"></i> </a> Resetear cuenta principal </span>
                        <?php
                    }else{
                        
                        ?>
                        
                        <span data-tabla="_datos_multiple" data-id_file="<?= $li->id ?>" class="reset-file acciones"><a title="Reset file" href="#" class=""><i class=" text-success icon-reset " title="Reset"></i> </a> </span>
                        <?php
                    }
                    ?>
                </ul>
            </div>


        </div>
    <?php endforeach ?>

    <div class="col" style="background-color: #10355E; color: white;">
    <div style="display: flex; align-items: center;">
        <h3 style="margin: 0;">Sub total:</h3>
        <h3 style="margin: 0; margin-left: 10px;">$ <span id="totalingresado"><?= number_format((float)$resultIngresado, 2, '.', '') ?></span></h3>
        <?php if ($resultIngresado == $totalFactura): ?>
            <i class="icon-checkmark text-success" style="margin-left: 10px;"></i>
        <?php else: ?>
            <i class="icon-warning text-danger" style="margin-left: 10px;"></i>
        <?php endif; ?>
    </div>
    <?php echo form_error('total_importe_ingresado', '<div class="invalid-feedback" style="display:block; color: white;">', "</div>"); ?>
</div>


</div>
