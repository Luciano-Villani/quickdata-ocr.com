<?php
$kpis = $calendario['kpis'];
$issues = $calendario['issues'];
$filas = $calendario['filas'];
$dias = $calendario['dias'];
$historicos = $calendario['historicos'];
$filtros = $calendario['filtros'];
$anio = (int) $calendario['anio'];
$mes = (int) $calendario['mes'];
$nombreMes = $calendario['nombre_mes'];
$primerDia = new DateTime(sprintf('%04d-%02d-01', $anio, $mes));
$offset = ((int) $primerDia->format('N')) - 1;
$mesActual = new DateTime(sprintf('%04d-%02d-01', $anio, $mes));
$mesAnterior = clone $mesActual;
$mesAnterior->modify('-1 month');
$mesSiguiente = clone $mesActual;
$mesSiguiente->modify('+1 month');
$baseCalendarioUrl = $modulo === 'electromecanica' ? base_url('Electromecanica/Vencimientos') : base_url('Admin/Vencimientos');
$labels = array(
    'consolidada' => array('Consolidada', 'badge-success'),
    'subida_pendiente' => array('Subida sin consolidar', 'badge-warning'),
    'vencida_subida' => array('Subida vencida', 'badge-danger'),
    'sin_subir' => array('Sin subir', 'badge-info'),
    'vencida_sin_subir' => array('Vencida sin subir', 'badge-danger'),
    'sin_actividad' => array('Sin actividad', 'badge-danger'),
);

if (!function_exists('fecha_vencimiento_ui')) {
    function fecha_vencimiento_ui($fecha)
    {
        return $fecha ? date('d/m/Y', strtotime($fecha)) : '-';
    }
}
?>
<style>
    .vencimientos-dashboard .page-title-card { margin-bottom: 12px; }
    .vencimientos-dashboard .page-title-card h4 { font-size: 1.25rem; padding: .55rem !important; }
    .vencimientos-dashboard .kpi-card { border-left: 5px solid #234d79; min-height: 88px; cursor: pointer; margin-bottom: 10px; }
    .vencimientos-dashboard .kpi-card .card-body { padding: .75rem 1rem !important; }
    .vencimientos-dashboard .kpi-card .numero { font-size: 1.85rem; font-weight: 600; line-height: 1; }
    .vencimientos-dashboard .kpi-card small { color: #777; }
    .kpi-obligaciones { border-left-color: #234d79 !important; }
    .kpi-consolidadas { border-left-color: #43a047 !important; }
    .kpi-subidas { border-left-color: #1e88e5 !important; }
    .kpi-por-consolidar { border-left-color: #fb8c00 !important; }
    .issue-card { border-left: 5px solid #d32f2f; cursor: pointer; }
    .issue-card.warning { border-left-color: #fbc02d; }
    .issue-card.dark { border-left-color: #8e0000; }
    .issue-card.previous { border-left-color: #6d4c41; }
    .issue-card .numero { font-size: 1.7rem; font-weight: 600; line-height: 1; }
    .vencimientos-dashboard .issue-card .card-body { padding: .65rem 1rem !important; }
    .vencimientos-dashboard .issues-wrapper .card-header { padding: .55rem 1rem; }
    .vencimientos-dashboard .issues-wrapper .card-body { padding: .65rem 1rem !important; }
    .calendario-card .card-header { padding: .55rem 1rem; }
    .calendario-card .card-body { padding: .75rem 1rem 1rem; }
    .calendario-head { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; background: #c5d1e3; padding: 8px 10px; }
    .calendario-head .card-title { line-height: 1.2; }
    .calendario-nav { display: flex; gap: 6px; align-items: center; flex-wrap: wrap; }
    .calendario-nav .btn { min-width: 118px; background: #fff; border-color: #b7c3d3; color: #42526b; }
    .calendario-nav .btn-primary-outline,
    .calendario-nav .btn-outline-primary { color: #0d6efd; border-color: #0d6efd; }
    .calendario-card .leyenda-dia { padding: 10px 2px 2px; }
    .calendario-obligaciones { display: grid; grid-template-columns: repeat(7, minmax(96px, 1fr)); border: 1px solid #d9dde5; }
    .cal-dia-header { background: #c5d1e3; font-weight: 600; text-align: center; padding: 8px 6px; border-right: 1px solid #d9dde5; }
    .cal-dia { min-height: 104px; border-top: 1px solid #d9dde5; border-right: 1px solid #d9dde5; padding: 7px; cursor: pointer; transition: all .15s ease-in-out; }
    .cal-dia:hover { box-shadow: inset 0 0 0 2px #234d79; }
    .cal-dia.seleccionado { box-shadow: inset 0 0 0 3px #0d47a1; }
    .cal-dia-vacio { background: #f7f7f7; border-top: 1px solid #d9dde5; border-right: 1px solid #d9dde5; }
    .cal-dia .numero-dia { font-size: 1.15rem; font-weight: 600; margin-bottom: 5px; }
    .cal-dia .contador { display: block; font-size: .78rem; line-height: 1.25; }
    .sin-compromisos { background: #f2f4f7; color: #8a8f99; }
    .dia-verde { background: #e8f5e9; }
    .dia-amarillo { background: #fff8e1; }
    .dia-rojo { background: #ffebee; }
    .dia-azul { background: #e3f2fd; }
    .leyenda-dia { margin-bottom: .7rem !important; }
    .leyenda-dia span { margin-right: 15px; white-space: nowrap; }
    .leyenda-dia i { display: inline-block; width: 12px; height: 12px; border-radius: 2px; margin-right: 4px; vertical-align: -1px; }
    #vencimientos_dt td { vertical-align: middle; }
    .fila-vencida { background: #fff5f5; }
    .fila-consolidada { color: #666; }
    .historial-meses { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 12px; }
    .historial-mes { border: 1px solid #ddd; border-radius: 3px; padding: 6px 8px; min-width: 58px; text-align: center; background: #f7f7f7; }
    .historial-mes.consolidada { background: #e8f5e9; border-color: #81c784; }
    .historial-mes.subida_pendiente { background: #fff8e1; border-color: #ffca28; }
    .historial-mes.sin_datos { background: #fafafa; color: #999; }
    .tabla-vencimientos-toolbar { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; justify-content: space-between; }
    .tabla-vencimientos-toolbar .acciones-tabla { display: flex; gap: 8px; flex-wrap: wrap; }
    .dt-buttons { display: none; }
</style>

<div class="vencimientos-dashboard">
    <div class="card page-title-card">
        <h4 class="card-title bg-titulo text-center text-dark p-2 mb-0">
            <?= html_escape($titulo_pagina) ?>
        </h4>
    </div>

    <div class="row">
        <div class="col-xl-4">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="card kpi-card kpi-obligaciones filtro-kpi" data-filtro-estado="">
                        <div class="card-body py-2">
                            <div class="numero"><?= (int) $kpis['obligaciones'] ?></div>
                            <div>Obligaciones del mes</div>
                            <small>Cuentas esperadas o pendientes</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="card kpi-card kpi-subidas filtro-kpi" data-filtro-estado="subidas">
                        <div class="card-body py-2">
                            <div class="numero"><?= (int) $kpis['subidas'] ?></div>
                            <div>Subidas</div>
                            <small>Leidas por OCR</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="card kpi-card kpi-consolidadas filtro-kpi" data-filtro-estado="consolidada">
                        <div class="card-body py-2">
                            <div class="numero"><?= (int) $kpis['consolidadas'] ?></div>
                            <div>Consolidadas</div>
                            <small>Ciclo cerrado</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="card kpi-card kpi-por-consolidar filtro-kpi" data-filtro-estado="por_consolidar">
                        <div class="card-body py-2">
                            <div class="numero"><?= (int) $kpis['por_consolidar'] ?></div>
                            <div>Por consolidar</div>
                            <small>Subidas pendientes</small>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="card issues-wrapper">
                        <div class="card-header py-2">
                            <h6 class="mb-0">Temas a atender</h6>
                        </div>
                        <div class="card-body py-2">
                            <div class="card issue-card warning filtro-kpi mb-2" data-filtro-estado="vencen_7">
                                <div class="card-body py-2">
                                    <div class="numero"><?= (int) $issues['vencen_7'] ?></div>
                                    <div>Vencen en 7 dias</div>
                                    <small class="text-muted">Pendientes proximas</small>
                                </div>
                            </div>
                            <div class="card issue-card filtro-kpi mb-2" data-filtro-estado="vencidas">
                                <div class="card-body py-2">
                                    <div class="numero"><?= (int) $issues['vencidas'] ?></div>
                                    <div>Vencidas</div>
                                    <small class="text-muted">Sin cierre operativo</small>
                                </div>
                            </div>
                            <div class="card issue-card dark filtro-kpi mb-0" data-filtro-estado="sin_actividad">
                                <div class="card-body py-2">
                                    <div class="numero"><?= (int) $issues['sin_actividad'] ?></div>
                                    <div>Sin actividad</div>
                                    <small class="text-muted">Dos o mas ciclos sin factura</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="card calendario-card">
                <div class="card-header calendario-head">
                    <h5 class="card-title text-capitalize">Calendario de obligaciones - <?= html_escape($nombreMes) ?> <?= $anio ?></h5>
                    <div class="calendario-nav">
                        <a class="btn btn-sm btn-outline-secondary" href="<?= $baseCalendarioUrl ?>?anio=<?= (int) $mesAnterior->format('Y') ?>&mes=<?= (int) $mesAnterior->format('n') ?>">
                            <i class="icon-arrow-left8"></i> Mes anterior
                        </a>
                        <a class="btn btn-sm btn-outline-primary" href="<?= $baseCalendarioUrl ?>">
                            Mes actual
                        </a>
                        <a class="btn btn-sm btn-outline-secondary" href="<?= $baseCalendarioUrl ?>?anio=<?= (int) $mesSiguiente->format('Y') ?>&mes=<?= (int) $mesSiguiente->format('n') ?>">
                            Mes siguiente <i class="icon-arrow-right8"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="leyenda-dia text-muted mb-3">
                        <span><i style="background:#f2f4f7"></i>Sin compromisos</span>
                        <span><i style="background:#e8f5e9"></i>Consolidado</span>
                        <span><i style="background:#fff8e1"></i>Alerta 7 dias / por consolidar</span>
                        <span><i style="background:#ffebee"></i>Vencido</span>
                        <span><i style="background:#e3f2fd"></i>Futuro</span>
                    </div>
                    <div class="calendario-obligaciones" id="calendario-obligaciones">
                        <?php foreach (array('lun.', 'mar.', 'mie.', 'jue.', 'vie.', 'sab.', 'dom.') as $diaNombre): ?>
                            <div class="cal-dia-header"><?= $diaNombre ?></div>
                        <?php endforeach; ?>
                        <?php for ($i = 0; $i < $offset; $i++): ?>
                            <div class="cal-dia-vacio"></div>
                        <?php endfor; ?>
                        <?php foreach ($dias as $dia): ?>
                            <div class="cal-dia <?= html_escape($dia['clase']) ?>" data-fecha="<?= html_escape($dia['fecha']) ?>">
                                <div class="numero-dia"><?= (int) $dia['dia'] ?></div>
                                <?php if ((int) $dia['total'] > 0): ?>
                                    <span class="contador"><strong><?= (int) $dia['total'] ?></strong> obligaciones</span>
                                    <span class="contador"><?= (int) $dia['consolidadas'] ?> consolidadas</span>
                                    <?php if ((int) $dia['por_consolidar'] > 0): ?>
                                        <span class="contador"><?= (int) $dia['por_consolidar'] ?> por consolidar</span>
                                    <?php endif; ?>
                                    <?php if ((int) $dia['sin_subir'] > 0): ?>
                                        <span class="contador"><?= (int) $dia['sin_subir'] ?> sin subir</span>
                                    <?php endif; ?>
                                    <?php if ((int) $dia['vencidas'] > 0): ?>
                                        <span class="contador text-danger"><?= (int) $dia['vencidas'] ?> vencidas</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="contador">Sin compromisos</span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card" id="tabla-obligaciones-card">
        <div class="card-header">
            <div class="tabla-vencimientos-toolbar">
                <div>
                    <h5 class="card-title mb-0">Obligaciones y guia de pagos</h5>
                    <div id="filtro_fecha_label" class="text-muted"></div>
                </div>
                <div class="acciones-tabla">
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="ver_mes_completo">Ver mes completo</button>
                    <button type="button" class="btn btn-sm btn-success" id="descargar_vencimientos_excel">
                        <i class="icon-file-excel"></i> Descargar Excel
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3">
                    <select id="filtro_estado_vencimiento" class="form-control">
                        <option value="">Todos los estados</option>
                        <option value="vencidas">Vencidas</option>
                        <option value="vencen_7">Vencen en 7 dias</option>
                        <option value="pendientes_anteriores">Pendientes del mes anterior</option>
                        <option value="sin_actividad">Sin actividad</option>
                        <option value="por_consolidar">Por consolidar</option>
                        <option value="sin_subir">Sin subir</option>
                        <option value="consolidada">Consolidadas</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select id="filtro_proveedor_vencimiento" class="form-control">
                        <option value="">Todos los proveedores</option>
                        <?php foreach ($filtros['proveedores'] as $proveedor): ?>
                            <option value="<?= html_escape($proveedor) ?>"><?= html_escape($proveedor) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select id="filtro_pago_vencimiento" class="form-control">
                        <option value="">Debito y OP</option>
                        <?php foreach ($filtros['pagos'] as $pago): ?>
                            <option value="<?= html_escape($pago) ?>"><?= html_escape($pago) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" value="1" id="ocultar_consolidadas">
                        <label class="form-check-label" for="ocultar_consolidadas">Ocultar consolidadas</label>
                    </div>
                    <div class="form-check mt-1">
                        <input class="form-check-input" type="checkbox" value="1" id="ocultar_sin_actividad">
                        <label class="form-check-label" for="ocultar_sin_actividad">Ocultar sin actividad</label>
                    </div>
                </div>
            </div>

            <table id="vencimientos_dt" class="display table-bordered table-hover" style="width:100%">
                <thead>
                    <tr>
                        <th></th>
                        <th>Vencimiento</th>
                        <th>Estado</th>
                        <th>Proveedor / Cuenta</th>
                        <th>Pago</th>
                        <th>Periodicidad</th>
                        <th>Ultima consolidacion</th>
                        <th>Subida</th>
                        <th>Accion</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($filas as $fila): ?>
                    <?php
                    $label = isset($labels[$fila['estado']]) ? $labels[$fila['estado']] : array($fila['estado_label'], 'badge-secondary');
                    $rowClass = !empty($fila['vencida']) ? 'fila-vencida' : ($fila['estado'] === 'consolidada' ? 'fila-consolidada' : '');
                    ?>
                    <tr class="<?= $rowClass ?>"
                        data-id-indexador="<?= (int) $fila['id_indexador'] ?>"
                        data-fecha="<?= html_escape($fila['fecha_esperada']) ?>"
                        data-estado="<?= html_escape($fila['estado']) ?>"
                        data-vencida="<?= !empty($fila['vencida']) ? '1' : '0' ?>"
                        data-sin-actividad="<?= !empty($fila['sin_actividad']) ? '1' : '0' ?>"
                        data-pendiente-anterior="<?= !empty($fila['pendiente_anterior']) ? '1' : '0' ?>"
                        data-alerta7="<?= !empty($fila['alerta_7']) ? '1' : '0' ?>"
                        data-proveedor="<?= html_escape($fila['proveedor']) ?>"
                        data-pago="<?= html_escape($fila['tipo_pago']) ?>">
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-primary ver-historico" title="Ver historico">+</button>
                        </td>
                        <td data-order="<?= html_escape($fila['fecha_esperada']) ?>">
                            <?= fecha_vencimiento_ui($fila['fecha_esperada']) ?>
                            <?php if (!empty($fila['pendiente_anterior'])): ?>
                                <small class="d-block text-danger">Pendiente mes anterior</small>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge <?= $label[1] ?>"><?= html_escape($label[0]) ?></span></td>
                        <td>
                            <strong><?= html_escape($fila['proveedor']) ?></strong>
                            <small class="d-block">Cuenta: <?= html_escape($fila['nro_cuenta']) ?></small>
                            <?php if (!empty($fila['dependencia'])): ?>
                                <small class="d-block text-muted"><?= html_escape($fila['dependencia']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= html_escape($fila['tipo_pago']) ?></td>
                        <td><?= $fila['periodicidad_meses'] === 2 ? 'Bimestral' : 'Mensual' ?></td>
                        <td><?= fecha_vencimiento_ui($fila['fecha_ultimo_pago']) ?></td>
                        <td><?= fecha_vencimiento_ui($fila['fecha_subida']) ?></td>
                        <td>
                            <?php if (!empty($fila['url'])): ?>
                                <a class="btn btn-sm btn-outline-primary" href="<?= html_escape($fila['url']) ?>">Ver</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
window.vencimientosHistoricos = <?= json_encode($historicos, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
</script>
