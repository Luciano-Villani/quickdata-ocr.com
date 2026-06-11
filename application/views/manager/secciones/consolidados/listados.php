<style>
	.my-class-section {
		color: #000;

	}

	.select2.select2-container {
		width: 100% !important;
	}


	.select2-container {
		width: 100% !important;
	}

	.select2-search:after {
		content: '' !important;
	}

	.card {
		container {
 		 padding-left: 0 !important;
 		 padding-right: 0 !important;
		  width: 100% !important;
	}
	}
	.tablas {
		width: 1300px !important;
		margin-left: -92px!important;


	}
	/* SOLO dejamos el CSS para select2 si es NECESARIO, ajustado a la columna: */
.select2.select2-container {
    /* Permitir que el ancho sea controlado por la columna de Bootstrap */
    width: 100% !important; 
}
swal2-popup {
    background: #ffffff !important;
}
	
</style>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>




<div class="card tablas filtros-consolidados-card">

	<div class="card-header filtros-consolidados-header">
        <div class="filtros-consolidados-top">
            <div>
                <h4 class="filtros-consolidados-title">Facturas Consolidadas</h4>
            </div>
            <button id="toggle-filtros-consolidados" type="button" class="btn btn-link btn-sm filtros-consolidados-toggle" aria-expanded="true">
                <i class="icon-arrow-up12"></i> Ocultar filtros
            </button>
        </div>
        <div class="filtros-consolidados-tabs">
            <div class="btn-group" role="group" aria-label="Modo de vista">
                <button type="button" class="btn btn-sm btn-primary modo-reporte-btn active" id="modo-vista-consolidada" data-modo="consolidados">Vista consolidada</button>
                <button type="button" class="btn btn-sm btn-outline-primary modo-reporte-btn" id="modo-reporte-final" data-modo="reporte_final">Vista liquidación</button>
            </div>
        </div>
        <div id="filtros-consolidados-body">
		<div class="filtros-consolidados-grid">
		<label class="filtro-consolidado-control" for="id_proveedor">
            <span>Proveedor</span>

		<?php
				$js = array(
					'id' => 'id_proveedor',
					'class' => 'ssse',
					'multiple' => "multiple",

				);
				?>

				<?= form_dropdown('id_proveedor', $select_proveedores, set_value('id_proveedor'), $js); ?>

<script>
	$('#id_proveedor').select2({
		placeholder: 'Todos',
		minimumResultsForSearch: "-1",
		width: '100%',
		closeOnSelect: false,
		selectionCssClass: '',
	});
</script>
</label>
		
            <label class="filtro-consolidado-control" for="id_secretaria">
                <span>Secretaria</span>
                <?php
                $js = array(
                    'id' => 'id_secretaria',
                    'class' => '',
                    'multiple' => "multiple",
                );
                ?>
                <?= form_dropdown('id_secretaria', $select_secretarias, '', $js); ?>

                <script>
                    $('#id_secretaria').select2({
                        placeholder: 'Todas',
                        minimumResultsForSearch: "-1",
                        width: '100%',
                        closeOnSelect: false,
                        selectionCssClass: '',
                    })
                </script>
            </label>

			<label class="filtro-consolidado-control" for="id_tipo_pago">
                <span>Tipo de pago</span>
				<?php
				$js = array(
					'id' => 'id_tipo_pago',
					'class' => '',
					'multiple' => "multiple",
				);
				?>
				<?= form_dropdown('id_tipo_pago', $select_tipo_pago, '', $js); ?>

				<script>
					$('#id_tipo_pago').select2({
						placeholder: 'Todos',
						minimumResultsForSearch: "-1",
						width: '100%',
						closeOnSelect: false,
						selectionCssClass: '',
						
					})
				</script>
			</label>
			<label class="filtro-consolidado-control" for="periodo_contable">
                <span>Periodo contable</span>
				<?php
				$js = array(
					'id' => 'periodo_contable',
					'class' => '',
					'multiple' => "multiple",
				);
				?>
				<?= form_dropdown('periodo_contable',$select_periodo_contable, '', $js); ?>

				<script>
					$('#periodo_contable').select2({
						placeholder: 'Todos',
      					tags: true,
						minimumResultsForSearch: "-1",
						width: '100%',
						closeOnSelect: false,
						selectionCssClass: '',
						
					})
				</script>
				
			</label>
            <label class="d-none" for="id_expediente">
                <?php
                $js = array(
                    'id' => 'id_expediente',
                    'class' => '',
                    'multiple' => "multiple",
                );
                ?>
                <?= form_dropdown('id_expediente', $select_expedientes, '', $js); ?>

                <script>
                    $('#id_expediente').select2({
                        placeholder: 'EXPEDIENTE',
                        minimumResultsForSearch: "-1",
                        width: '100%',
                        closeOnSelect: false,
                        selectionCssClass: '',
                    })
                </script>
            </label>
			<div class="filtro-consolidado-control filtro-consolidado-fecha">
				<label class="filtro-consolidado-check">
					<input type="checkbox"  class="radio"  value="1" name="tipo_fecha"  id="tipo-fecha" />
					<span data-popup="tooltip">Fecha de Consolidación</span>
				</label>
				<input type="text" name="daterange2" id="daterange2" class="form-control form-control-sm">
			</div>
			<div class="filtros-consolidados-actions">
				<button id="applyfilter" type="button" class="d-none"><b><i class="icon-filter3"></i></b>Aplicar Filtros</button>
                <button id="descarga-principal" type="button" class="btn btn-outline-primary btn-sm filtros-consolidados-download"><b><i class="icon-file-download"></i></b> Descargar reporte</button>
				<button id="resetfilter" type="button" class="btn btn-link btn-sm filtros-consolidados-clear"><b><i class="icon-reset"></i></b> Limpiar filtros</button>
				<!--<button id="descarga-exell" type="button" class="btn btn-outline-excel btn-sm"style="width: 160px";><b><i class="icon-file-excel"></i></b> DESCARGAR</button> -->
			</div>		

			
					
			
		</div>
        <div class="filtros-activos" id="filtros-activos"></div>
        </div>

		
		
	</div>
</div>
<style>
    .filtros-consolidados-card {
        margin-top: -15px;
        border: 1px solid #dfe8f5;
        border-radius: 6px;
        box-shadow: 0 8px 20px rgba(10, 31, 68, .04);
    }
    .filtros-consolidados-header {
        position: relative;
        padding: 10px 18px 10px;
        background: #fff;
    }
    .filtros-consolidados-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        min-height: 34px;
        margin-bottom: 8px;
    }
    .filtros-consolidados-title {
        margin: 0;
        color: #0b1f4d;
        font-size: 1.25rem;
        font-weight: 700;
    }
    .filtros-consolidados-subtitle {
        display: block;
        color: #7a8495;
        font-size: .78rem;
    }
    .filtros-consolidados-tabs {
        position: absolute;
        top: 10px;
        left: 50%;
        transform: translateX(-50%);
        display: inline-flex;
        align-items: flex-end;
        border-bottom: 1px solid #dbe4f1;
        margin: 0;
        z-index: 2;
    }
    .filtros-consolidados-tabs .modo-reporte-btn {
        border-radius: 0;
        border-width: 0 0 2px 0;
        background: transparent;
        color: #0b4fc7;
        padding: 6px 16px;
        box-shadow: none;
    }
    .filtros-consolidados-tabs .modo-reporte-btn.btn-primary,
    .filtros-consolidados-tabs .modo-reporte-btn.active {
        background: #f5f9ff !important;
        color: #075cf7 !important;
        border-bottom-color: #075cf7;
        font-weight: 700;
    }
    .filtros-consolidados-tabs .modo-reporte-btn.btn-outline-primary {
        border-bottom-color: transparent;
    }
    .filtros-consolidados-grid {
        display: grid;
        grid-template-columns: 1.2fr 1.2fr 1fr 1fr 1.15fr auto;
        gap: 10px;
        align-items: end;
    }
    .filtro-consolidado-control {
        display: block;
        margin: 0;
        min-width: 0;
    }
    .filtro-consolidado-control > span,
    .filtro-consolidado-check span {
        display: block;
        margin-bottom: 3px;
        color: #53638c;
        font-size: .68rem;
        font-weight: 700;
    }
    .filtro-consolidado-control .select2-container .select2-selection--multiple {
        min-height: 34px;
        border-color: #d7dfeb;
        border-radius: 4px;
    }
    .filtro-consolidado-control .select2-container--default .select2-selection--multiple .select2-selection__choice {
        margin-top: 4px;
        font-size: .72rem;
    }
    .filtro-consolidado-fecha .form-control {
        height: 34px;
        font-size: .82rem;
    }
    .filtro-consolidado-check {
        display: flex;
        align-items: center;
        gap: 6px;
        margin: 0 0 3px;
    }
    .filtro-consolidado-check span {
        margin: 0;
    }
    .filtros-consolidados-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 8px;
        min-width: 280px;
        padding-bottom: 1px;
    }
    .filtros-consolidados-download {
        min-width: 150px;
        white-space: nowrap;
    }
    .filtros-consolidados-clear {
        color: #075cf7;
        font-weight: 600;
        text-decoration: none;
        white-space: nowrap;
    }
    .filtros-consolidados-toggle {
        color: #53638c;
        font-weight: 600;
        text-decoration: none;
        white-space: nowrap;
        padding-right: 18px;
    }
    .filtros-consolidados-card.filtros-colapsados #filtros-consolidados-body {
        display: none;
    }
    .filtros-consolidados-card.filtros-colapsados {
        margin-bottom: 8px;
    }
    .filtros-activos {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        min-height: 0;
        margin-top: 8px;
    }
    .filtro-chip {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 8px;
        border-radius: 999px;
        background: #eef4ff;
        color: #0b1f4d;
        font-size: .72rem;
        font-weight: 600;
    }
    @media (max-width: 1400px) {
        .filtros-consolidados-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
        .filtros-consolidados-actions {
            justify-content: flex-start;
        }
    }
	#consolidados_dt_filter,
	#consolidados_dt_length {
		/* float: left; */
	}

    #consolidados_dt_filter {
        padding-right: 18px;
    }

	.dataTables_filter input{
		text-transform: uppercase;
	}
	div.dt-button-collection {
		width: auto !important;

	}

	div[role='menu'] {
		display: flex !important;
		width: auto !important;
		
	}
	div.dataTables_wrapper {
    /* width: 1200px !important; */
       
    }
    #reporte_final_preview thead th,
    #reporte_final_preview .fila-subtotal {
        background: #fce4d6;
        font-weight: 600;
    }
    #reporte_final_preview td,
    #reporte_final_preview th {
        white-space: normal;
        vertical-align: middle;
        text-align: center;
        line-height: 1.25;
    }
    #reporte_final_preview {
        table-layout: fixed;
        min-width: 1420px;
    }
    #reporte_final_preview .importe {
        text-align: right;
    }
    #reporte_final_preview th:nth-child(1), #reporte_final_preview td:nth-child(1) { width: 150px; }
    #reporte_final_preview th:nth-child(2), #reporte_final_preview td:nth-child(2) { width: 115px; }
    #reporte_final_preview th:nth-child(3), #reporte_final_preview td:nth-child(3) { width: 145px; }
    #reporte_final_preview th:nth-child(4), #reporte_final_preview td:nth-child(4) { width: 145px; }
    #reporte_final_preview th:nth-child(5), #reporte_final_preview td:nth-child(5) { width: 125px; }
    #reporte_final_preview th:nth-child(6), #reporte_final_preview td:nth-child(6) { width: 100px; }
    #reporte_final_preview th:nth-child(7), #reporte_final_preview td:nth-child(7) { width: 105px; }
    #reporte_final_preview th:nth-child(8), #reporte_final_preview td:nth-child(8) { width: 105px; }
    #reporte_final_preview th:nth-child(9), #reporte_final_preview td:nth-child(9) { width: 110px; }
    #reporte_final_preview th:nth-child(10), #reporte_final_preview td:nth-child(10) { width: 120px; }
    #reporte_final_preview th:nth-child(11), #reporte_final_preview td:nth-child(11) { width: 125px; }
    #reporte_final_preview th:nth-child(12), #reporte_final_preview td:nth-child(12) { width: 110px; }
    #reporte_final_preview th:nth-child(13), #reporte_final_preview td:nth-child(13) { width: 135px; }
    #reporte-final-titulo {
        display: block;
        background: #f4b183;
        color: #000;
        font-size: 1.2rem;
        padding: 6px 10px;
        letter-spacing: .3px;
    }
</style>
<div class="card tablas" id="vista-consolidada-card" style="margin-top: -15px">
<h5 class="card-title bg-titulo text-center text-dark"> Facturas Consolidadas</h5>
<div class="card-header" style="margin-top: -15px">
<div id="consulta"></div>
<div id="request"></div>
		<table id="consolidados_dt" class="datatable-ajax table-bordered table-hover datatable-highlight" style="width: auto">
			<thead>
				
				<tr>
				
					<th>Período Cont</th>
					<th>Proveedor</th>
					<th>Expediente</th>
					<th>Secretaría</th>
					<th>Juridicción</th>
					<th>Programa</th>
					<th>Jurisdicción+prog</th>
					<th>O del gasto</th>
					<th>Dependencia</th>
					<th>Direccion</th>
					<th>Tipo Pago</th>
					<th>Acuerdo</th>
					<th>Nro cuenta</th>
					<th>Nro factura</th>
					<th>Período Consumo</th>
					<th>Vencimiento</th>
					<th>Pasar a Prev.</th>
					<th>Importe factura</th>
					<th>Acc.</th>
				</tr>
			</thead>
			<!--<tfoot>
				<tr>
					<th>#</th>
					<th>#</th>
					<th>#</th>
					<th>Período Contable</th>
					<th>Empresa</th>
					<th>Expediente</th>
					<th>Secretaría</th>
					<th>Juridicción</th>
					<th>Programa</th>
					<th>Jurisdicción + prog</th>
					<th>O del gasto</th>
					<th>Dependencia</th>
					<th>Direccion</th>
					<th>Tipo Pago</th>
					<th>Nro Cuenta</th>
					<th>Nro factura</th>
					<th>Período</th>
					<th>Vencimiento del pago</th>
					<th>Pasar a Preventivas</th>
					<th>Importe factura</th>
					<th></th>
				</tr>
			</tfoot> -->

		</table>

	</div>
</div>

<div class="card tablas d-none" id="reporte-final-card" style="margin-top: -15px">
<h5 class="card-title bg-titulo text-center text-dark"> Reportes final </h5>
<div class="card-header" style="margin-top: -15px">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div>
            <strong id="reporte-final-titulo">Aplique filtros para generar la vista previa.</strong>
            <div class="text-muted" id="reporte-final-resumen"></div>
        </div>
        <button id="descargar-reporte-final" type="button" class="btn btn-success btn-sm">
            <b><i class="icon-file-excel"></i></b> Descargar Excel
        </button>
    </div>
    <div class="table-responsive" style="max-height: 520px;">
        <table id="reporte_final_preview" class="table table-bordered table-hover table-sm mb-0">
            <thead>
                <tr>
                    <th>Proveedor</th>
                    <th>Expediente</th>
                    <th>Secretaria</th>
                    <th>Dependencia</th>
                    <th>Juridiccion</th>
                    <th>Programa</th>
                    <th>O del gasto</th>
                    <th>Tipo Pago</th>
                    <th>Nro cuenta</th>
                    <th>Nro factura</th>
                    <th>Periodo</th>
                    <th>Vencimiento</th>
                    <th>Importe factura</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="13" class="text-center text-muted">Sin datos para mostrar.</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
</div>

<script>
window.REPORTE_FINAL_PERIODO_ACTUAL = '<?= strtoupper(fecha_es(date("Y-m-d"), 'F a', false)) ?>';

</script>
