<?php
// Crear un array para almacenar los números de cuenta
$cuentas = [];
$duplicados = [];

// Verifica si $result está definido y no es nulo
$result = isset($result) ? $result : null;

// Recorre las líneas para detectar duplicados
foreach ($lineas as $li) {
    if (in_array($li->nro_cuenta, $cuentas)) {
        $duplicados[] = $li->nro_cuenta;
    } else {
        $cuentas[] = $li->nro_cuenta;
    }
}

// Elimina duplicados de la lista de duplicados
$duplicados = array_unique($duplicados);

// Determina si hay duplicados
$hayDuplicados = !empty($duplicados);

// Consultar si existe el mismo nro_factura en otros registros si $result no es nulo
$nro_factura = $result ? $result->nro_factura : '';
$id_lote = $result ? $result->id_lote : 0; // Asegúrate de que `id_lote` esté definido en `$result`
$registro_factura_existente = $nro_factura 
    ? $this->Manager_model->get_alldata('_datos_api', 'nro_factura = "' . $nro_factura . '" AND id_lote != ' . $id_lote) 
    : [];
$hayDuplicadoFactura = !empty($registro_factura_existente);
?>

<div class="panel-body" style="background-color: #f0f0f0; border: 2px solid #10355E;">
    <?php $a = 1; ?>

    <div class="col" style="background-color: #C9D4E6;">
        <h3 class="pt-1">Total facturado: $ <?= number_format((float)$totalFactura, 2, '.', '') ?></h3>
        <input readonly name="total_importe" type="hidden" class="form-control" placeholder="Total importe" value="<?= number_format((float)$totalFactura, 2, '.', '') ?>">
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
                    <li class="pb-1">Importe $: &nbsp;<?= number_format((float)$li->total_importe, 2, '.', '') ?></li>
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
                        <span data-importe="<?= $li->importe_1 ?>" data-tabla="_datos_api" data-id_file="<?= $li->id ?>" class="borrar-file acciones <?= $d_none ?>">
                            <a title="Borrar file" href="#" class=""><i class="text-danger icon-trash" title="Borrar"></i> Borrar cuenta</a>
                        </span>
                        <span data-tabla="_datos_multiple" data-id_file="<?= $li->id ?>" class="reset-file acciones <?= $d_si ?>">
                            <a title="Reset file" href="#" class=""><i class="text-success icon-reset" title="Reset"></i> Resetear cuenta principal</a>
                        </span>
                    <?php
                    } else {
                    ?>
                        <span data-tabla="_datos_multiple" data-id_file="<?= $li->id ?>" class="reset-file acciones">
                            <a title="Reset file" href="#" class=""><i class="text-success icon-reset" title="Reset"></i></a>
                        </span>
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

    <!-- Modal de alerta de duplicados -->
    <?php if ($hayDuplicadoFactura): ?>
        <div id="facturaAlertModal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Alerta de Factura Duplicada</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>La factura con número <?= htmlspecialchars($nro_factura) ?> ya existe en otro lote.</p>
                        <p>Por favor, revisa los datos y corrige el número de factura si es necesario.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Modal de alerta de cuentas duplicadas -->
    <?php if ($hayDuplicados): ?>
        <div id="cuentasAlertModal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Alerta de Cuentas Duplicadas</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Se han detectado cuentas duplicadas:</p>
                        <ul>
                            <?php foreach ($duplicados as $cuentaDuplicada): ?>
                                <li><?= htmlspecialchars($cuentaDuplicada) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <p>Por favor, revisa los datos y corrige las cuentas duplicadas.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Muestra el modal de factura duplicada si existe
        if (document.getElementById('facturaAlertModal') && <?= json_encode($hayDuplicadoFactura) ?>) {
            $('#facturaAlertModal').modal('show');
        }

        // Muestra el modal de cuentas duplicadas si existe
        if (document.getElementById('cuentasAlertModal') && <?= json_encode($hayDuplicados) ?>) {
            $('#cuentasAlertModal').modal('show');
        }
    });
</script>

