<style>
    .migrar-indexacion-page {
        max-width: 1320px;
        margin: 0 auto;
    }
    .migrar-card {
        border: 1px solid #d9e2ef;
        border-radius: 10px;
        background: #fff;
        box-shadow: 0 8px 22px rgba(21, 55, 95, 0.06);
        margin-bottom: 18px;
    }
    .migrar-card-header {
        padding: 16px 20px;
        border-bottom: 1px solid #e7edf5;
        background: #f7faff;
        border-radius: 10px 10px 0 0;
    }
    .migrar-card-title {
        margin: 0;
        font-size: 20px;
        color: #0d2a57;
        font-weight: 700;
    }
    .migrar-card-subtitle {
        color: #6b7890;
        font-size: 13px;
        margin-top: 4px;
    }
    .migrar-card-body {
        padding: 20px;
    }
    .migrar-section-title {
        color: #0d2a57;
        font-size: 16px;
        font-weight: 700;
        margin-bottom: 12px;
    }
    .migrar-kv {
        border: 1px solid #e2e9f3;
        border-radius: 8px;
        padding: 12px 14px;
        height: 100%;
        background: #fbfdff;
    }
    .migrar-kv small {
        display: block;
        color: #7b8798;
        font-weight: 600;
        text-transform: uppercase;
        margin-bottom: 4px;
    }
    .migrar-kv strong {
        color: #14284b;
        font-size: 15px;
    }
    .migrar-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 11px;
        border-radius: 999px;
        background: #edf5ff;
        color: #0b63ce;
        font-weight: 700;
        font-size: 12px;
    }
    .migrar-summary {
        background: #fff8e8;
        border: 1px solid #ffe1a8;
        color: #624400;
        border-radius: 8px;
        padding: 14px;
        display: none;
    }
    .migrar-hidden {
        display: none;
    }
    .migrar-muted {
        color: #7d8795;
    }
    .migrar-cuentas-box {
        max-height: 150px;
        overflow-y: auto;
        border: 1px solid #e2e9f3;
        border-radius: 8px;
        padding: 10px 12px;
        background: #fbfdff;
    }
    .migrar-history-table {
        font-size: 13px;
    }
    .migrar-history-table td {
        vertical-align: middle;
    }
    .migrar-status {
        display: inline-flex;
        align-items: center;
        padding: 4px 8px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
    }
    .migrar-status-active {
        background: #e9f8ef;
        color: #138a3d;
    }
    .migrar-status-reverted {
        background: #f1f3f7;
        color: #687386;
    }
</style>

<div class="migrar-indexacion-page">
    <div class="migrar-card">
        <div class="migrar-card-header d-flex justify-content-between align-items-center">
            <div>
                <h3 class="migrar-card-title">Migrar cuenta / dependencia</h3>
                <div class="migrar-card-subtitle">
                    Actualiza la estructura vigente de indexadores. No modifica facturas consolidadas.
                </div>
            </div>
            <a href="<?= base_url('Admin/Indexaciones') ?>" class="btn btn-outline-primary">
                <i class="icon-arrow-left8"></i> Volver a indexadores
            </a>
        </div>
        <div class="migrar-card-body">
            <div class="row align-items-end">
                <div class="col-md-7">
                    <label class="font-weight-semibold">Número de cuenta / acuerdo</label>
                    <input type="text" id="migrar_nro_cuenta" class="form-control form-control-lg" placeholder="Ej: 2235189">
                </div>
                <div class="col-md-3">
                    <button type="button" id="btn_buscar_migracion" class="btn btn-primary btn-lg btn-block">
                        <i class="icon-search4"></i> Buscar cuenta
                    </button>
                </div>
            </div>
            <div id="migrar_alert" class="alert mt-3 migrar-hidden"></div>
        </div>
    </div>

    <div id="migrar_panel" class="migrar-hidden">
        <div class="migrar-card">
            <div class="migrar-card-header">
                <h4 class="migrar-card-title">Estructura actual</h4>
            </div>
            <div class="migrar-card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <div class="migrar-kv"><small>Proveedor</small><strong id="actual_proveedor">-</strong></div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="migrar-kv"><small>Cuenta</small><strong id="actual_cuenta">-</strong></div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="migrar-kv"><small>Tipo pago</small><strong id="actual_tipo_pago">-</strong></div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="migrar-kv"><small>Expediente</small><strong id="actual_expediente">-</strong></div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="migrar-kv"><small>Secretaría</small><strong id="actual_secretaria">-</strong></div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="migrar-kv"><small>Programa</small><strong id="actual_programa">-</strong></div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="migrar-kv"><small>Proyecto</small><strong id="actual_proyecto">-</strong></div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="migrar-kv"><small>Dependencia</small><strong id="actual_dependencia">-</strong></div>
                    </div>
                </div>
                <input type="hidden" id="id_indexacion_actual">
                <input type="hidden" id="id_dependencia_actual">
                <input type="hidden" id="id_secretaria_actual">
            </div>
        </div>

        <div class="migrar-card">
            <div class="migrar-card-header">
                <h4 class="migrar-card-title">Alcance de la migración</h4>
            </div>
            <div class="migrar-card-body">
                <div class="row">
                    <div class="col-md-6">
                        <label class="d-block">
                            <input type="radio" name="alcance_migracion" value="cuenta" checked>
                            <strong>Solo esta cuenta</strong>
                        </label>
                        <p class="migrar-muted mb-0">Actualiza únicamente la cuenta buscada.</p>
                    </div>
                    <div class="col-md-6">
                        <label class="d-block">
                            <input type="radio" name="alcance_migracion" value="dependencia">
                            <strong>Todas las cuentas de la dependencia actual</strong>
                        </label>
                        <p class="migrar-muted mb-2">
                            Afecta <span class="migrar-pill"><span id="total_cuentas_dependencia">0</span> cuentas</span>
                        </p>
                        <div id="lista_cuentas_dependencia" class="migrar-cuentas-box migrar-hidden"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="migrar-card">
            <div class="migrar-card-header">
                <h4 class="migrar-card-title">Nueva estructura</h4>
            </div>
            <div class="migrar-card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Secretaría destino</label>
                        <?php
                        $js = array(
                            'id' => 'migrar_secretaria',
                            'class' => 'select2 form-control custom-select',
                        );
                        ?>
                        <?= form_dropdown('migrar_secretaria', $select_secretarias, '', $js); ?>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Programa destino</label>
                        <select id="migrar_programa" class="select2 form-control custom-select" disabled>
                            <option value="">Seleccione programa</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Proyecto destino</label>
                        <select id="migrar_proyecto" class="select2 form-control custom-select" disabled>
                            <option value="0">Sin proyecto</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Dependencia destino</label>
                        <select id="migrar_dependencia" class="select2 form-control custom-select" disabled>
                            <option value="">Seleccione dependencia</option>
                        </select>
                    </div>
                </div>

                <div id="mover_dependencia_wrap" class="alert alert-info migrar-hidden">
                    <label class="mb-1">
                        <input type="checkbox" id="mover_dependencia_actual" value="1">
                        <strong>Mover también la dependencia actual a la nueva secretaría</strong>
                    </label>
                    <div class="small">
                        Si se marca, la dependencia actual cambia formalmente de secretaría y se conserva como dependencia destino.
                    </div>
                </div>

                <div class="form-group">
                    <label>Observación</label>
                    <textarea id="migrar_observacion" class="form-control" rows="2" placeholder="Motivo o referencia interna de la migración"></textarea>
                </div>

                <div id="migrar_resumen" class="migrar-summary mb-3"></div>

                <button type="button" id="btn_guardar_migracion" class="btn btn-success btn-lg" disabled>
                    <i class="icon-checkmark4"></i> Confirmar migración
                </button>
            </div>
        </div>

        <div class="migrar-card">
            <div class="migrar-card-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="migrar-card-title">Historial de migraciones</h4>
                    <div class="migrar-card-subtitle">
                        Auditoria de cambios sobre esta cuenta. Desde aqui se puede revertir una migracion vigente.
                    </div>
                </div>
                <button type="button" id="btn_refrescar_historial" class="btn btn-outline-primary btn-sm">
                    <i class="icon-sync"></i> Actualizar
                </button>
            </div>
            <div class="migrar-card-body">
                <div id="migrar_historial_empty" class="alert alert-info mb-0">
                    Busque una cuenta para ver su historial.
                </div>
                <div id="migrar_historial_wrap" class="table-responsive migrar-hidden">
                    <table class="table table-bordered table-striped migrar-history-table mb-0">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Cuenta</th>
                                <th>Alcance</th>
                                <th>Anterior</th>
                                <th>Nuevo</th>
                                <th>Observacion</th>
                                <th>Estado</th>
                                <th class="text-center">Accion</th>
                            </tr>
                        </thead>
                        <tbody id="migrar_historial_body"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="modal_revertir_migracion" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Revertir migracion</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="revertir_id_migracion">
                <p class="mb-2">
                    Esta accion vuelve la cuenta a la estructura anterior. Si la migracion afecto una dependencia completa,
                    se revertira el grupo completo.
                </p>
                <div class="alert alert-warning">
                    La reversion no modifica facturas consolidadas.
                </div>
                <div class="form-group">
                    <label>Motivo de reversion</label>
                    <textarea id="revertir_observacion" class="form-control" rows="3" placeholder="Ej: carga equivocada, solicitud del area, correccion operativa"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">Cancelar</button>
                <button type="button" id="btn_confirmar_reversion" class="btn btn-danger">
                    <i class="icon-undo2"></i> Revertir migracion
                </button>
            </div>
        </div>
    </div>
</div>
