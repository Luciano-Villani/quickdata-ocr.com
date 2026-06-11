<?php
if (isset($_SERVER['HTTP_REFERER'])) {
    $retorno = $_SERVER['HTTP_REFERER'];
} else {
    $retorno = "/Admin";
}

$id_registro = isset($result->id) ? $result->id : 0;
$comentarios_actuales = isset($result->comentarios) ? htmlspecialchars($result->comentarios, ENT_QUOTES, 'UTF-8') : '';
$esta_en_seguimiento = (isset($result->seguimiento) && $result->seguimiento == 1) ? 'checked' : '';
$archivo = array('', '', '', '');
if ($result && !empty($result->nombre_archivo)) {
    $archivo = explode('/', $result->nombre_archivo, 4);
}

$proveedor_nombre = 'Proveedor no encontrado';
if ($result && isset($select_proveedores[$result->id_proveedor])) {
    $proveedor_item = $select_proveedores[$result->id_proveedor];
    $proveedor_nombre = is_array($proveedor_item) && isset($proveedor_item['nombre']) ? $proveedor_item['nombre'] : $proveedor_item;
}

$errores = array();
if ($result) {
    if (empty($result->nro_cuenta) || strtoupper(trim($result->nro_cuenta)) === 'S/D') {
        $errores[] = 'Sin cuenta';
    }
    if (empty($result->fecha_emision) || stripos($result->fecha_emision, 'error') !== false || strtoupper(trim($result->fecha_emision)) === 'S/D') {
        $errores[] = 'Sin fecha emision';
    }
    if (empty($result->vencimiento_del_pago) || stripos($result->vencimiento_del_pago, 'error') !== false || strtoupper(trim($result->vencimiento_del_pago)) === 'S/D') {
        $errores[] = 'Sin vencimiento';
    }
    if ($result->total_importe === '' || $result->total_importe === null || strtoupper(trim($result->total_importe)) === 'S/D') {
        $errores[] = 'Sin importe';
    }
}

$estado_badge = empty($errores)
    ? '<span class="badge badge-success">Lectura OK</span>'
    : '<span class="badge badge-danger">' . implode(' / ', $errores) . '</span>';
?>

<style>
    .lectura-view-page .card {
        border: 1px solid #dce5f1;
        border-radius: 8px;
        box-shadow: 0 6px 18px rgba(18, 52, 86, 0.05);
    }
    .lectura-view-page .card-header {
        padding: 10px 16px;
    }
    .lectura-view-page .page-title {
        font-size: 22px;
        font-weight: 700;
        color: #12345b;
        margin: 0;
    }
    .lectura-view-page .page-subtitle {
        color: #7a8595;
        font-size: 12px;
        margin-top: 2px;
    }
    .lectura-view-page .section-title {
        font-size: 13px;
        font-weight: 700;
        color: #12345b;
        text-transform: uppercase;
        letter-spacing: .03em;
        margin: 10px 0 12px;
        padding-bottom: 6px;
        border-bottom: 1px solid #e8eef6;
    }
    .lectura-view-page label {
        font-weight: 600;
        color: #344767;
        margin-bottom: 5px;
    }
    .lectura-view-page .pdf-frame {
        width: 100%;
        height: 500px;
        border: 1px solid #dce5f1;
        border-radius: 6px;
        background: #f7f9fc;
    }
    .lectura-view-page .readonly-field {
        background: #f8fafc;
    }
    .lectura-view-page .indexacion-card {
        background: #f8fbff;
        border: 1px solid #e2eaf5;
        border-radius: 8px;
        padding: 12px;
    }
    .lectura-view-page .tracking-card textarea {
        min-height: 110px;
    }
</style>

<div class="lectura-view-page">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h3 class="page-title">Lectura OCR #<?= $id_registro; ?></h3>
                <div class="page-subtitle">Revision y correccion manual de datos leidos desde factura PDF.</div>
            </div>
            <div class="d-flex align-items-center">
                <div class="mr-3"><?= $estado_badge; ?></div>
                <a href="<?= $retorno ?>" type="button" class="btn btn-light">
                    <i class="icon-arrow-left8"></i> Volver
                </a>
            </div>
        </div>
    </div>

    <?php if (!$result) { ?>
        <div class="alert alert-warning">No se encontro la lectura solicitada.</div>
    <?php } else { ?>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Factura PDF subida / datos leidos del modelo</h5>
            <small class="text-muted"><?= htmlspecialchars(isset($archivo[3]) ? $archivo[3] : '', ENT_QUOTES, 'UTF-8'); ?></small>
        </div>
        <div class="card-body">
            <?php if ($result && file_exists($result->nombre_archivo)) { ?>
                <embed src="<?= base_url($result->nombre_archivo . '#toolbar=1&navpanes=3&scrollbar=1&zoom=120') ?>" type="application/pdf" class="pdf-frame">
            <?php } else { ?>
                <div class="alert alert-warning mb-0">No existe el archivo PDF.</div>
            <?php } ?>
        </div>
    </div>

    <div class="card">
        <?= form_open(base_url('Lecturas/views'), array('id' => 'form-validate-jquery')); ?>
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Datos de la lectura</h5>
                <small class="text-muted">Corregir datos faltantes o mal leidos antes de consolidar.</small>
            </div>
            <?php if ($this->ion_auth->is_super() || $this->ion_auth->is_admin()) { ?>
                <button type="submit" class="btn btn-primary">
                    <i class="icon-floppy-disk"></i> Guardar datos modificados
                </button>
            <?php } ?>
        </div>
        <div class="card-body">
            <input hidden name="id" type="text" class="form-control" value="<?= $result->id ?>">

            <div class="section-title">Datos principales</div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Proveedor</label>
                        <input type="text" class="form-control readonly-field" value="<?= htmlspecialchars($proveedor_nombre, ENT_QUOTES, 'UTF-8'); ?>" readonly>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Nro de cuenta</label>
                        <input type="text" name="nro_cuenta" class="form-control" placeholder="Nro de cuenta" value="<?= htmlspecialchars($result->nro_cuenta, ENT_QUOTES, 'UTF-8'); ?>">
                        <?= form_error('nro_cuenta', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Nro de medidor</label>
                        <input name="nro_medidor" type="text" class="form-control" placeholder="Nro medidor" value="<?= htmlspecialchars($result->nro_medidor, ENT_QUOTES, 'UTF-8'); ?>">
                        <?= form_error('nro_medidor', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Nro de factura</label>
                        <input name="nro_factura" type="text" class="form-control" placeholder="Nro factura" value="<?= htmlspecialchars($result->nro_factura, ENT_QUOTES, 'UTF-8'); ?>">
                        <?= form_error('nro_factura', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Periodo consumo</label>
                        <input name="periodo_del_consumo" type="text" class="form-control" placeholder="Periodo de consumo" value="<?= htmlspecialchars($result->periodo_del_consumo, ENT_QUOTES, 'UTF-8'); ?>">
                        <?= form_error('periodo_del_consumo', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
                    </div>
                </div>
            </div>

            <div class="section-title">Fechas, importes y consumo</div>
            <div class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Fecha emision</label>
                        <input id="fecha_emision" name="fecha_emision" type="date" class="form-control" value="<?= htmlspecialchars($result->fecha_emision, ENT_QUOTES, 'UTF-8'); ?>">
                        <?= form_error('fecha_emision', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Vencimiento</label>
                        <input id="vencimiento_del_pago" name="vencimiento_del_pago" type="date" class="form-control" value="<?= htmlspecialchars($result->vencimiento_del_pago, ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Total importe</label>
                        <input name="total_importe" type="text" class="form-control" placeholder="Total importe" value="<?= htmlspecialchars($result->total_importe, ENT_QUOTES, 'UTF-8'); ?>">
                        <?= form_error('total_importe', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Total vencido</label>
                        <input name="total_vencido" type="text" class="form-control" placeholder="Total vencido" value="<?= htmlspecialchars($result->total_vencido, ENT_QUOTES, 'UTF-8'); ?>">
                        <?= form_error('total_vencido', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Consumo</label>
                        <input name="consumo" type="text" class="form-control" placeholder="Consumo" value="<?= htmlspecialchars($result->consumo, ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                </div>
            </div>

            <div class="section-title">Archivo</div>
            <div class="row">
                <div class="col-md-5">
                    <div class="form-group">
                        <label>Nombre archivo</label>
                        <input disabled readonly type="text" class="form-control readonly-field" value="<?= htmlspecialchars(isset($archivo[3]) ? $archivo[3] : '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                </div>
                <div class="col-md-7">
                    <div class="form-group">
                        <label>Ruta archivo</label>
                        <input disabled readonly type="text" class="form-control readonly-field" value="<?= htmlspecialchars($result->nombre_archivo, ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                </div>
            </div>
        </div>
        <?= form_close(); ?>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Datos de indexacion</h5>
        </div>
        <div class="card-body">
            <div class="indexacion-card">
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
    </div>

    <?= form_open(base_url('Consolidados/guardar_seguimiento'), array('id' => 'form-seguimiento')); ?>
    <div class="card tracking-card">
        <div class="card-header">
            <h5 class="mb-0">Seguimiento interno</h5>
        </div>
        <div class="card-body">
            <input type="hidden" name="id_registro" value="<?= $id_registro; ?>">
            <div class="form-group">
                <label>Comentarios o datos de seguimiento</label>
                <textarea name="comentarios" class="form-control" rows="4" placeholder="Escriba aqui los comentarios de seguimiento..."><?= $comentarios_actuales; ?></textarea>
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <label class="mb-0">
                    <input type="checkbox" name="en_seguimiento" value="1" <?= $esta_en_seguimiento; ?>>
                    Marcar como En Seguimiento / Pendiente
                </label>
                <button type="submit" class="btn btn-primary">
                    <i class="icon-floppy-disk"></i> Guardar seguimiento
                </button>
            </div>
        </div>
    </div>
    <?= form_close(); ?>

    <?php } ?>
</div>
