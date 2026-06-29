<?php if ($this->ion_auth->is_electro() || $this->ion_auth->is_super() || $this->ion_auth->is_admin()) { ?>
<style>
    .edenor-auditoria-page {
        padding: 18px 20px 26px;
    }
    .edenor-auditoria-title {
        align-items: center;
        display: flex;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 14px;
    }
    .edenor-auditoria-title h2 {
        color: #061a4f;
        font-size: 26px;
        font-weight: 800;
        margin: 0;
    }
    .edenor-auditoria-title p {
        color: #526385;
        margin: 2px 0 0;
    }
    .edenor-title-actions {
        display: flex;
        gap: 8px;
        align-items: center;
    }
    .edenor-card {
        background: #fff;
        border: 1px solid #dce6f5;
        border-radius: 12px;
        box-shadow: 0 8px 22px rgba(6, 26, 79, .06);
        margin-bottom: 16px;
        padding: 16px;
    }
    .edenor-toolbar {
        display: grid;
        grid-template-columns: minmax(260px, 1.2fr) repeat(2, minmax(180px, .8fr)) auto;
        gap: 12px;
        align-items: end;
    }
    .edenor-toolbar label {
        color: #34466d;
        display: block;
        font-size: 12px;
        font-weight: 700;
        margin-bottom: 6px;
    }
    .edenor-toolbar .form-control,
    .edenor-toolbar .custom-select {
        border-color: #d5e0f0;
        min-height: 42px;
    }
    .edenor-kpis {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 12px;
    }
    .edenor-kpi {
        border: 1px solid #dce6f5;
        border-radius: 10px;
        padding: 13px 14px;
    }
    .edenor-kpi span {
        color: #526385;
        display: block;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
    }
    .edenor-kpi strong {
        color: #061a4f;
        display: block;
        font-size: 28px;
        line-height: 1.1;
        margin-top: 5px;
    }
    .edenor-kpi.good strong { color: #16804a; }
    .edenor-kpi.bad strong { color: #d93232; }
    .edenor-tabs {
        border-bottom: 1px solid #dce6f5;
        display: flex;
        gap: 6px;
        margin-bottom: 14px;
    }
    .edenor-tab {
        background: transparent;
        border: 0;
        border-bottom: 3px solid transparent;
        color: #526385;
        cursor: pointer;
        font-weight: 800;
        padding: 10px 14px;
    }
    .edenor-tab.active {
        border-bottom-color: #075cf7;
        color: #075cf7;
    }
    .edenor-section-title {
        color: #061a4f;
        font-size: 17px;
        font-weight: 800;
        margin: 0 0 10px;
    }
    .edenor-grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }
    .edenor-table-wrap {
        max-height: 310px;
        overflow: auto;
    }
    .edenor-table {
        font-size: 13px;
        margin-bottom: 0;
        width: 100%;
    }
    .edenor-table th {
        background: #c6d2e3;
        color: #061a4f;
        font-weight: 800;
        position: sticky;
        top: 0;
        z-index: 1;
    }
    .edenor-badge {
        border-radius: 999px;
        display: inline-flex;
        font-size: 12px;
        font-weight: 800;
        min-width: 34px;
        padding: 3px 9px;
        justify-content: center;
    }
    .edenor-badge.ap { background: #e7f1ff; color: #075cf7; }
    .edenor-badge.t1 { background: #ecfaf2; color: #16804a; }
    .edenor-badge.t2 { background: #fff5df; color: #b66b00; }
    .edenor-badge.t3 { background: #f3e9ff; color: #6c35d8; }
    .edenor-empty {
        color: #6a7893;
        padding: 18px;
        text-align: center;
    }
    @media (max-width: 1200px) {
        .edenor-toolbar,
        .edenor-kpis,
        .edenor-grid-2 {
            grid-template-columns: 1fr 1fr;
        }
    }
    @media (max-width: 768px) {
        .edenor-toolbar,
        .edenor-kpis,
        .edenor-grid-2 {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="edenor-auditoria-page" id="edenorAuditoria" data-periodos='<?= json_encode($periodos) ?>'>
    <div class="edenor-auditoria-title">
        <div>
            <h2>Auditoria Datos Edenor</h2>
            <p>Control mensual de cuentas recibidas por TXT, comparacion contra periodos anteriores y evolucion por tarifa.</p>
        </div>
        <div class="edenor-title-actions">
            <a href="#" id="edenorReporteBtn" class="btn btn-outline-primary">
                <i class="icon-download mr-1"></i> Descargar reporte
            </a>
        </div>
    </div>

    <div class="edenor-card">
        <form id="edenorImportForm" class="edenor-toolbar" enctype="multipart/form-data">
            <div>
                <label>Archivo TXT mensual</label>
                <input type="file" class="form-control" id="archivo_txt" name="archivo_txt" accept=".txt">
            </div>
            <div>
                <label>Periodo actual</label>
                <select id="periodo_actual" class="custom-select"></select>
            </div>
            <div>
                <label>Comparar contra</label>
                <select id="periodo_base" class="custom-select"></select>
            </div>
            <div>
                <button type="submit" class="btn btn-primary">
                    <i class="icon-upload mr-1"></i> Importar TXT
                </button>
            </div>
        </form>
        <div class="mt-2 text-muted" id="edenorImportHint">
            El TXT original se conserva en uploader/edenor_auditoria. Si el periodo ya existe, el sistema pedira confirmacion antes de reemplazar datos.
        </div>
    </div>

    <div class="edenor-card">
        <div class="edenor-tabs">
            <button type="button" class="edenor-tab active" data-tab="comparacion">Auditoria mensual</button>
            <button type="button" class="edenor-tab" data-tab="evolutivo">Evolutivo de cuentas</button>
        </div>

        <div id="tab_comparacion">
            <div class="edenor-kpis mb-3">
                <div class="edenor-kpi"><span id="kpi_actual_label">Total actual</span><strong id="kpi_actual">0</strong></div>
                <div class="edenor-kpi"><span id="kpi_base_label">Total comparado</span><strong id="kpi_base">0</strong></div>
                <div class="edenor-kpi good"><span>Altas</span><strong id="kpi_nuevas">0</strong></div>
                <div class="edenor-kpi bad"><span>Bajas</span><strong id="kpi_faltantes">0</strong></div>
                <div class="edenor-kpi"><span>Bimestrales</span><strong id="kpi_bimestrales">0</strong></div>
                <div class="edenor-kpi"><span>Recategorizadas</span><strong id="kpi_tarifa">0</strong></div>
            </div>

            <div class="edenor-card mb-3">
                <h4 class="edenor-section-title" id="titulo_resultado_periodos">Resultado de periodos comparados</h4>
                <div class="edenor-table-wrap">
                    <table class="table table-bordered edenor-table" id="tabla_resultado_periodos"></table>
                </div>
            </div>

            <div class="edenor-card mb-0">
                <h4 class="edenor-section-title" id="titulo_movimientos">Altas, bajas, bimestrales y recategorizadas</h4>
                <div class="edenor-table-wrap" style="max-height: 420px;">
                    <table class="table table-bordered edenor-table" id="tabla_movimientos"></table>
                </div>
            </div>
        </div>

        <div id="tab_evolutivo" style="display:none;">
            <h4 class="edenor-section-title">Evolutivo mensual de cuentas por tarifa</h4>
            <div class="edenor-table-wrap" style="max-height: 520px;">
                <table class="table table-bordered edenor-table" id="tabla_evolutivo"></table>
            </div>
        </div>
    </div>
</div>
<?php } ?>
