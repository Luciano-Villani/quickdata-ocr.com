<?php 
// application/views/manager/secciones/consolidados/lista_seguimiento_ajax.php
if (empty($registros)): ?>
    <a href="#" class="dropdown-item">No hay cuentas en seguimiento.</a>
<?php else: ?>
    <?php foreach ($registros as $registro): ?>
        <a href="<?= base_url('Admin/Consolidados/ver/' . $registro->id) ?>" class="dropdown-item d-flex align-items-center">
            <i class="icon-alarm text-danger mr-3"></i>
            <div>
                <span class="font-weight-semibold">Proveedor: <?= $registro->proveedor ?></span>
                <div class="text-muted font-size-sm">Nro. Cuenta: <?= $registro->nro_cuenta ?></div>
            </div>
        </a>
        <div class="dropdown-divider"></div>
    <?php endforeach; ?>
   
<?php endif; ?>
