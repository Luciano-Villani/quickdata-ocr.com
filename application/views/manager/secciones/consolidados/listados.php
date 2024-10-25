<!-- 
<div class="card">
<div class="panel">
		<div class="panel-heading">
			<h5 class="panel-title">Multiple files desde su carpeta</h5>
		</div>

		<div class="panel-body">

			<p class="text-semibold">Multiple file upload example:</p>
			<form action="suber" class="dropzone dz-clickable" id="file-multiple">
				<div class="dz-default dz-message"><span>Drop files  <span>or CLICK</span></span></div>
				<div class="fallback">
					<input name="file" type="file" multiple />
				</div>
			</form>
		</div>
	</div>

</div> -->
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
	
</style>

<div class="card tablas" style="margin-top: -15px">
<h5 class="card-title bg-titulo text-center text-dark"> Filtros y Descarga de Reportes </h5>

	<div class="card-header" style="margin-top: -20px";>
		<div class="row">
		<label class="col-2" for="id_proveedor">

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
		placeholder: 'PROVEEDORES',
		minimumResultsForSearch: "-1",
		width: '100%',
		closeOnSelect: false,
		selectionCssClass: '',
	});
</script>
</label>
		
			<label class="col-2" for="id_tipo_pago">
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
						placeholder: 'TIPO DE PAGO',
						minimumResultsForSearch: "-1",
						width: '100%',
						closeOnSelect: false,
						selectionCssClass: '',
						
					})
				</script>
			</label>
			<label class="col-md-2" for="periodo_contable">
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
						placeholder: 'PERIODO CONTABLE',
      					tags: true,
						minimumResultsForSearch: "-1",
						width: '100%',
						closeOnSelect: false,
						selectionCssClass: '',
						
					})
				</script>
				
			</label>
			<div class="col-2 ">
				<label class="">
					<input type="checkbox"  class="radio"  value="1" name="tipo_fecha"  id="tipo-fecha" />
					<span data-popup="tooltip">Fecha de Consolidación</span>
				</label>
				<div class="col" style = "margin: -10px";>
					<input type="text" name="daterange2" id="daterange2" class="form-control ">
				</div>
			</div>
			<div class="col-2 ">
				<label class="">
					<input type="checkbox"  class="radio"  value="1" name="totalizador"  id="totalizador"/>
					<span data-popup="tooltip" style="color: lightgray">Totales Agrupados Juris/Prog</span>
				</label>
			</div>
			<div class="col-2">
				<button id="applyfilter" type="button" class="btn mb-1 btn-outline-dark btn-sm" style="width: 160px";><b><i class="icon-filter3"></i></b>Aplicar Filtros</button>
				<button id="resetfilter" type="button" class="btn mb-1 btn-outline-dark btn-sm"style="width: 160px";><b><i class="icon-reset"></i></b>Eliminar Filtros</button>
				<button id="descarga-exell" type="button" class="btn btn-outline-excel btn-sm"style="width: 160px";><b><i class="icon-file-excel"></i></b> DESCARGAR</button>
			</div>			
			
					
			
		</div>

		
		
	</div>
</div>
<style>
	#consolidados_dt_filter,
	#consolidados_dt_length {
		/* float: left; */
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
</style>
<div class="card tablas" style="margin-top: -15px">
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
					<th>Período</th>
					<th>Vencimiento</th>
					<th>Pasar a Prev.</th>
					<th>Importe factura</th>
					<th></th>
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

<script>


</script>