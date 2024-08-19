<div class="panel-body" style="background-color: #f0f0f0;">
    <?php
    $a = 1;


    ?>
    <div class="col mt-3">
        <h3>Total Facturado: $ <?= $totalFactura ?></h3>
        <input readonly name="total_importe" type="hidden" class="form-control" placeholder="Total importe" value="<?= $totalFactura ?>">
        <?php echo form_error('total_importe', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
    </div>

    <?php foreach ($lineas as $li) : ?>
        <div data-file="<?= $li->id ?>" class="row">
            <div class="col">
                <h6>Cuenta: </h6>
                <h6 class="text-semibold no-margin-top"><?= $li->nro_cuenta ?></h6>
                <ul class="list list-unstyled">
                    <li>Importe $: &nbsp;<?= $li->total_importe ?></li>
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
                        <span data-importe="<?= $li->importe_1 ?>" data-tabla="_datos_api" data-id_file="<?= $li->id ?>" class="borrar-file acciones <?= $d_none ?> "><a title="Borrar file" href="#" class=""><i class=" text-danger icon-trash " title="Borrar "></i> </a> </span>
                        <span data-tabla="_datos_multiple" data-id_file="<?= $li->id ?>" class="reset-file acciones <?= $d_si?>"><a title="Reset file" href="#" class=""><i class=" text-success icon-reset " title="Reset"></i> </a> </span>
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
</div>
<div class="col" style="background-color: #f0f0f0;">
    <label class="form-label">Sub total:</label>
  <h3 > $ <span id="totalingresado"> <?= $resultIngresado; ?></span></h3>
    <?php echo form_error('total_importe_ingresado', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>

<div class="col mt-3">
    <span class="">Cuentas <?php echo  count($lineas) ?></span>
</div>
</div>