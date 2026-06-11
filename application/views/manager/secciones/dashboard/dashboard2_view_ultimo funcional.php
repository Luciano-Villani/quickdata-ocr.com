<?php
defined('BASEPATH') OR exit('No direct script access allowed');
// $mes_actual y $anio_actual vienen de tu controller (si están)
// Puedes pasarlos si quieres, pero el dashboard obtiene periodos vía AJAX.
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Dashboard2 — Control de Gastos</title>

  <!-- CSS -->
  <link rel="stylesheet" href="<?= base_url('assets/manager/css/dashboard2.css') ?>">

  <!-- ApexCharts -->
  <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
  <!-- jQuery (tu proyecto ya lo tiene, pero lo incluimos por seguridad) -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <!-- DataTables (opcional, sólo si usarás tablas avanzadas) -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
  <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

  <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body class="dash2-body">
  <div class="dash2-shell">
    <!-- HEADER -->
    <header class="dash2-header">
      <div class="dash2-brand">
        <img src="<?= base_url('assets/img/logo.png') ?>" alt="logo" class="dash2-logo">
        <div>
          <div class="dash2-title">Tablero Ejecutivo — Control de Gasto Municipal</div>
          <div class="dash2-sub">Visión Operativa · Drill-down Jurisdicción → Programa → Proyecto → Dependencia</div>
        </div>
      </div>

      <div class="dash2-controls">
        <div class="dash2-toggle">
          <button class="dash2-btn-toggle active" data-view="jurisdicciones" id="btn-view-jur">Jurisdicciones</button>
          <button class="dash2-btn-toggle" data-view="proveedores" id="btn-view-prov">Proveedores</button>
        </div>

        <div class="dash2-filtergroup">
          <label class="dash2-label">Período</label>
          <select id="select-periodo" class="dash2-select"></select>

          <label class="dash2-label">Proveedor</label>
          <select id="select-proveedor" class="dash2-select">
            <option value="">— Todos —</option>
          </select>

          <button id="btn-refresh-dash" class="dash2-button">Actualizar</button>
        </div>
      </div>
    </header>

    <!-- MAIN GRID -->
    <main class="dash2-main">
      <!-- KPIs -->
      <section class="dash2-kpis" aria-label="Indicadores">
        <div class="kpi-card">
          <div class="kpi-title">Gasto mes</div>
          <div class="kpi-value" id="kpi-gasto-mes">$ 0</div>
          <div class="kpi-sub" id="kpi-gasto-mes-sub">—</div>
        </div>
        <div class="kpi-card">
          <div class="kpi-title">Gasto año</div>
          <div class="kpi-value" id="kpi-gasto-anio">$ 0</div>
          <div class="kpi-sub" id="kpi-gasto-anio-sub">—</div>
        </div>
        <div class="kpi-card">
          <div class="kpi-title">Presupuesto ejecutado</div>
          <div class="kpi-value" id="kpi-pres">% 0</div>
          <div class="kpi-progress"><div id="kpi-pres-bar" class="kpi-pres-bar"></div></div>
        </div>
        <div class="kpi-card">
    <div class="kpi-title">Facturas procesadas</div>
    <div class="kpi-value" id="kpi-count">0</div>
    <div class="kpi-sub" id="kpi-periodo">Para el periodo seleccionado</div>
   
</div>

      </section>
  <div id="dashboard-context" class="dash2-context">
    <span><strong>Período:</strong> <span id="ctx-periodo">—</span></span>
    <span style="margin-left: 20px"><strong>Proveedor:</strong> <span id="ctx-proveedor">—</span></span>


</div>

      <!-- CARDS + CHART -->
      <section class="dash2-content">
        <div class="dash2-left">
          <!-- BARRA DE PROGRESO DEL DRILLDOWN -->
<div id="steps-bar" class="steps-bar">
  
  <div class="step-item" data-step="jur">
    <div class="step-title">Jurisdicciones</div>
    <div class="step-sub" id="step-jur-sub">Seleccione una jurisdicción</div>
  </div>

  <div class="step-item disabled" data-step="prog">
    <div class="step-title">Programas</div>
    <div class="step-sub" id="step-prog-sub">Seleccione un programa</div>
  </div>

  <div class="step-item disabled" data-step="proy">
    <div class="step-title">Proyectos</div>
    <div class="step-sub" id="step-proy-sub">Seleccione un proyecto</div>
  </div>

  <div class="step-item disabled" data-step="dep">
    <div class="step-title">Dependencias</div>
    <div class="step-sub" id="step-dep-sub">Seleccione una dependencia</div>
  </div>

</div>

<!-- Oculto el título original para compatibilidad -->
<h3 class="section-title" id="titulo-principal" style="display:none;">Jurisdicciones</h3>

          <div id="cards-container" class="cards-grid"></div>
        </div>

        <div class="dash2-right">
          <div class="chart-card">
            <div class="chart-header">
              <div>Comparación mensuales</div>
              <div class="chart-actions">
                <button id="btn-toggle-chart-type" class="icon-btn">Línea/Bar</button>
              </div>
            </div>
            <div id="chart-comparison" class="chart-area"></div>
          </div>

          <div class="chart-card small">
            <div class="chart-header"><div>Top 8 Proveedores</div></div>
            <div id="chart-top-providers" class="chart-area mini"></div>
          </div>
        </div>
      </section>

    </main>

    <footer class="dash2-footer">
      <div>dashboard2 · versión 1 · <?= date('Y-m-d H:i') ?></div>
      <div id="dash-status" class="dash2-status">Listo</div>
    </footer>
  </div>

  <!-- JS principal -->
  <script>
    // Variables de configuración (ajusta si tu base_url está en otra ruta)
    const BASE_URL_DASH = '<?= base_url("Dashboard/") ?>';
  </script>
  <script src="<?= base_url('assets/manager/js/secciones/dashboard2/dashboard2.js') ?>"></script>
</body>
</html>
