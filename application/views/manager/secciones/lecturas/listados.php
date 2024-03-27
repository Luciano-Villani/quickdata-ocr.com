<?php if ($this->ion_auth->is_super() || $this->ion_auth->is_admin()) { ?>

	<div class="mia  <?= $_SESSION['session_data']['cardCollapsed'] = 'card-collapsed' ?> card">





		<div class="card-header header-elements-inline">
			<button type="button" data-toggle="collapse" data-target="#collapseExample" class="btn btn-agregar mb-1 bg-buton-blue btn-labeled btn-labeled-right"><b><i class="icon-plus3"></i>
				</b>Agregar Nuevo Lote </button>
			<div class="collapse" id="collapseExample">
				<div class="card card-body">
					<div class="row">

						<div class="col-md-4">
							<div class="  form-group form-group-feedback form-group-feedback-right">

								<?php
								$js = array(
									'id' => 'id_proveedor',
									'class' => ' select2 form-control custom-select ',
								);
								?>

								<?= form_dropdown('id_proveedor', $select_proveedores, set_value('id_proveedor'), $js); ?>
								<?php echo form_error('id_proveedor', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>

							</div>
						</div>
						<div class="col-md-2">
							<div class="form-group">
								<input type="text" class="form-control  " readonly="readonly" placeholder="Código de Proveedor" id="codeproveedor" value="">
							</div>
							<!-- <input type="text" readonly="readonly" id="codeproveedor" value=""> -->
							<input  type="hidden" readonly="readonly" id="code" value="<?= $code ?>">
						</div>
					</div>


					<div class="col-md-3">
						<button disabled id="procesar_lote" type="button" class="btn-filtrar btn "><b><i class=" icon-upload7"></i></b> Procesar Lote</button>
					</div>






				</div>
				<div class="clearfix"></div>
				<div class="container d-none" id="mydropzone">
					<div class="row">
						<div class="col-md-12">
							<form action="procesasFIle" class="dropzone"></form>
						</div>
					</div>
				</div>
			</div>
		</div>

	</div>
<?php } ?>
<div class="card">
	<h5 class="card-title bg-titulo text-center text-dark"> Lista de Lotes</h5>


	<table class="table-bordered table-hover datatable-highlight datatable-ajax" style="width: 100%">
		<thead>
			<tr>
				<th>Proveedor</th>
				<th>Código</th>
				<th>Fecha</th>
				<th>Facturas</th>
				<th>Sin Index</th>
				<th>Consolidado</th>
				<th>Usuario</th>
				<th>Acciones</th>
			</tr>
		</thead>
		<!--<tfoot>
			<tr>
				<th>Proveedor</th>
				<th>Código</th>
				<th>Fecha</th>
				<th>Files</th>
				<th>Errores</th>
				<th>Consolidado</th>
				<th>usuario</th>
				<th>Acciones</th>
			</tr>
		</tfoot> -->

	</table>
</div>








<!-- 
<div class="card">
<div class="panel">
		<div class="panel-heading">
			<h5 class="panel-title">Multiple files desde su carpeta</h5>
		</div>

		<div class="panel-body">

			<p class="text-semibold">Multiple file upload example:</p>
			<form action="suber" class="dropzones dz-clickable" id="file-multiple">
				<div class="dz-default dz-message"><span>Drop files  <span>or CLICK</span></span></div>
				<div class="fallback">
					<input name="file" type="file" multiple />
				</div>
			</form>
		</div>
	</div>

</div> -->
<div class="card ">
	<h5 class="card-title bg-titulo text-center text-dark"> Datos leídos</h5>

	<table id="lecturas_dt" class=" datatable-highlight" style="width: 100%">
		<thead>
			<tr>
				<th>Proveedor</th>
				<th>Cuenta</th>
				<th>Medidor</th>
				<th>Nro de factura</th>
				<th>Pedríodo</th>
				<th>Fecha emision</th>
				<th>Vencimiento</th>
				<th>Total importe</th>
				<th>Total vencido</th>
				<th>Próximo vencimiento</th>
				<th>archivo</th>
				<th>Acciones</th>
			</tr>
		</thead>

	</table>
</div>





<!-- Disabled backdrop -->
<div id="modal_backdrop" class="modal fade bd-example-modal-lg" data-backdrop="false">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h5 class="modal-title">Proveedor: <span id="modal_proveedor"></span> </h5>
			</div>

			<div class="modal-body">
				<table id="tabla_archivos" class="table " style="background-color: #fff!important;">
					<thead>
						<tr>
							<th>Archivo</th>
							<th class="col-md-2">Estado</th>

						</tr>
					</thead>
					<tbody id="">


					</tbody>
				</table>
			</div>

			<div class="modal-footer">
				<button id="cerrar_modal" type="button" class="btn btn-link" data-dismiss="modal">Cerrar</button>
				<button disabled='disabled' id="enviar_archivos" type="button" class="btn btn-primary">Enviar archivos</button>
			</div>
		</div>
	</div>
</div>
<!-- /disabled backdrop -->

<script>




</script>