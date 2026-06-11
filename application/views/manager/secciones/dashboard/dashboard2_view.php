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
  
  <style>
    /* --- FIJATE AQUÍ: eliminamos la grilla del contenedor principal --- */
.dash2-kpis {
    width: 100%;
    display: block;
}

/* GRID PRINCIPAL 4 COLUMNAS */
.kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
}

/* KPI más bajos */
.kpi-small {
    height: 90px !important;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

/* KPI alto */
.kpi-normal {
    height: 150px;
     grid-row: span 2;
}

/* BLOQUE PERÍODO/PROVEEDOR debajo de las 3 tarjetas pequeñas */
.kpi-context {
    grid-column: span 3;
    background: #ffffff;
    border-radius: 10px;
    padding: 12px 16px;
    border: 1px solid #e5e7eb;
    font-size: 15px;
    display: flex;
    align-items: center;
    gap: 25px;
    margin-top: 4px !important;
}

/* Mantengo tu card de contexto */
#dashboard-context.dash2-context {
    margin: 0 !important;
    padding: 12px 16px !important;
    background: var(--card) !important;
    border-radius: 10px !important;
    border: none !important;
    box-shadow: 0 8px 30px rgba(15,20,30,0.05);
    font-size: 0.9rem;
}
/* --- OVERRIDES SÓLO PARA ESTE DASHBOARD (NO ROMPE NADA) --- */
.dash2-kpis .kpi-grid {
    /* separacion horizontal (col-gap) = 14px, vertical (row-gap) = 6px */
    gap: 6px 14px !important;
    row-gap: 6px !important; /* por si el navegador interpreta gap distinto */
}

/* Evitar márgenes extra que puedan venir de .kpi-card heredado */
.dash2-kpis .kpi-card {
    margin: 0 !important;
}

/* Asegurar que el bloque contexto quede alineado al inicio de su celda */
.dash2-kpis .kpi-context {
    grid-column: 1 / span 3;   /* aseguramos que ocupe columnas 1..3 */
    margin-top: 0 !important;  /* quitar cualquier margen redundante */
    align-self: start;         /* forzar alineación al principio de la fila */
    /* mantenemos tus estilos visuales */
    background: #ffffff;
    border-radius: 10px;
    padding: 12px 16px;
    border: 1px solid #e5e7eb;
    font-size: 15px;
    display: flex;
    align-items: center;
    gap: 25px;
}



    
  </style>

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
          <div class="dash2-title">Tablero MVL</div>
          <div class="dash2-sub">Visión Operativa</div>
        </div>
      </div>

      <div class="dash2-controls">
        <div class="dash2-toggle">
          <button class="dash2-btn-toggle active" data-view="jurisdicciones" id="btn-view-jur">Proveedores</button>
          <button class="dash2-btn-toggle" data-view="proveedores" id="btn-view-prov">Canon</button>
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

    <!-- GRID COMPLETO DE 4 COLUMNAS -->
    <div class="kpi-grid">

        <!-- KPI 1 -->
        <div class="kpi-card kpi-small">
            <div class="kpi-title">Gasto mes</div>
            <div class="kpi-value" id="kpi-gasto-mes">$ 0</div>
            <div class="kpi-sub" id="kpi-gasto-mes-sub">—</div>
        </div>

        <!-- KPI 2 -->
        <div class="kpi-card kpi-small">
            <div class="kpi-title">Gasto año</div>
            <div class="kpi-value" id="kpi-gasto-anio">$ 0</div>
            <div class="kpi-sub" id="kpi-gasto-anio-sub">—</div>
        </div>

        <!-- KPI 3 -->
        <div class="kpi-card kpi-small" id="kpi-cobertura-card"> 
          <div class="kpi-title">Cobertura</div> 
    
           <div class="kpi-value" id="kpi-cobertura-valor">Cargando...</div> 
    
          <div class="kpi-info" id="kpi-cobertura-info"></div> 
    
           
    </div>

        <!-- KPI 4 (ALTO) -->
        <div class="kpi-card kpi-normal">
            <div class="kpi-title">Facturas procesadas</div>
            <div class="kpi-value" id="kpi-count">0</div>
            <div class="kpi-sub" id="kpi-periodo">Para el periodo seleccionado</div>
        </div>

        <!-- NUEVO BLOQUE PERÍODO / PROVEEDOR (OCUPA 3 COLUMNAS) -->
        <div id="dashboard-context" class="dash2-context kpi-context">
            <span><strong>Período:</strong> <span id="ctx-periodo">—</span></span>
            <span style="margin-left: 20px"><strong>Proveedor:</strong> <span id="ctx-proveedor">—</span></span>
        </div>

    </div>

</section>


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
  <script src="<?= base_url('assets/manager/js/secciones/dashboard2/dashboard.kpi.consolidacion.js') ?>"></script>
  <script src="<?= base_url('assets/manager/js/secciones/dashboard2/dashboard2.js') ?>"></script>
  
  
</body>
</html>