<?php if ($this->ion_auth->is_super() || $this->ion_auth->is_admin()) { ?>

	<div class="mia  <?= $_SESSION['session_data']['cardCollapsed'] = 'card-collapsed' ?> card">


<style>

.dropzone {
   
 }
 .archivo-scroll-container {
      max-height: 300px; /* Ajusta la altura máxima según sea necesario */
      overflow-y: auto; /* Agrega el scroll vertical */
      border: 1px solid #ddd; /* Opcional: agrega un borde alrededor del contenedor */
      padding: 10px; /* Opcional: agrega padding al contenedor */
      margin-bottom: 10px; /* Opcional: agrega margen en la parte inferior */
    }
	.lote-process-modal .modal-dialog {
		max-width: 760px;
	}
	.lote-process-modal .modal-header {
		background: #c6d2e3;
		border-bottom: 0;
		padding: 14px 18px;
	}
	.lote-process-modal .modal-title {
		font-size: 18px;
		font-weight: 600;
	}
	.lote-process-status {
		display: grid;
		gap: 10px;
		grid-template-columns: repeat(4, 1fr);
		margin-bottom: 14px;
	}
	.lote-process-kpi {
		background: #f7f9fc;
		border: 1px solid #d9e2ef;
		border-radius: 8px;
		padding: 10px;
		text-align: center;
	}
	.lote-process-kpi strong {
		color: #12345d;
		display: block;
		font-size: 22px;
		line-height: 1;
	}
	.lote-process-kpi span {
		color: #697789;
		display: block;
		font-size: 12px;
		margin-top: 5px;
	}
	.lote-validation-result {
		border: 1px solid #d9e2ef;
		border-radius: 8px;
		display: none;
		margin-top: 14px;
		padding: 14px;
	}
	.lote-validation-result.ok {
		background: #effaf1;
		border-color: #bce4c2;
	}
	.lote-validation-result.warning {
		background: #fff8e8;
		border-color: #ffd37a;
	}
	.lote-validation-result.danger {
		background: #fff0f0;
		border-color: #ffb1b1;
	}
	.lote-validation-title {
		font-size: 16px;
		font-weight: 600;
		margin-bottom: 8px;
	}
	.lote-validation-actions {
		display: flex;
		flex-wrap: wrap;
		gap: 8px;
		margin-top: 12px;
	}
	@media (max-width: 768px) {
		.lote-process-status {
			grid-template-columns: repeat(2, 1fr);
		}
	}
	.dropzone {
		border: 2px dashed #b8c7dc !important;
		border-radius: 14px;
		background: #f8fbff;
		color: #12345d;
		min-height: 150px;
		padding: 28px 18px;
		transition: border-color .2s ease, background .2s ease;
	}
	.dropzone:hover {
		border-color: #1e88e5 !important;
		background: #f2f7ff;
	}
	.dropzone .dz-message {
		font-size: 16px;
		font-weight: 600;
		margin: 1.5em 0;
	}
	.dropzone .dz-preview .dz-progress {
		border-radius: 999px;
		height: 8px;
	}
	.lote-upload-shell {
		background: linear-gradient(135deg, #f7fbff 0%, #ffffff 58%, #eef5ff 100%);
		border: 1px solid #d9e2ef;
		border-radius: 12px;
		box-shadow: 0 6px 18px rgba(18, 52, 93, .08);
		padding: 16px;
		width: 100%;
	}
	.lote-upload-toggle {
		align-items: center;
		background: #12345d;
		border: 0;
		border-radius: 10px;
		box-shadow: 0 5px 14px rgba(18, 52, 93, .2);
		color: #fff;
		display: inline-flex;
		font-weight: 600;
		gap: 9px;
		padding: 11px 18px;
	}
	.lote-upload-toggle:hover,
	.lote-upload-toggle:focus {
		background: #0d2b4f;
		color: #fff;
	}
	.lote-upload-panel {
		border-top: 1px solid #d9e2ef;
		margin-top: 14px;
		padding-top: 16px;
	}
	.lote-upload-title {
		color: #12345d;
		font-size: 18px;
		font-weight: 700;
		margin: 0;
	}
	.lote-upload-subtitle {
		color: #697789;
		font-size: 13px;
		margin: 3px 0 0;
	}
	.lote-upload-steps {
		display: flex;
		flex-wrap: wrap;
		gap: 8px;
		margin: 12px 0 16px;
	}
	.lote-upload-step {
		align-items: center;
		background: #eef4fb;
		border-radius: 999px;
		color: #49617d;
		display: inline-flex;
		font-size: 12px;
		font-weight: 600;
		gap: 6px;
		padding: 7px 11px;
	}
	.lote-upload-step.active {
		background: #e8f2ff;
		color: #0d6efd;
	}
	.lote-upload-actions {
		align-items: flex-end;
		display: flex;
		gap: 12px;
		justify-content: space-between;
		margin-top: 12px;
	}
	.lote-process-btn {
		background: #1e88e5;
		border: 0;
		border-radius: 10px;
		color: #fff;
		font-weight: 700;
		min-width: 180px;
		padding: 11px 18px;
	}
	.lote-process-btn:disabled {
		background: #b8c7dc;
		cursor: not-allowed;
	}
	.lote-provider-code {
		max-width: 170px;
	}
	#collapseExample .card-body {
		background: linear-gradient(135deg, #f7fbff 0%, #ffffff 58%, #eef5ff 100%);
		border: 1px solid #d9e2ef;
		border-radius: 12px;
		box-shadow: 0 6px 18px rgba(18, 52, 93, .08);
		margin-top: 12px;
		padding: 16px;
	}
	#mydropzone {
		margin-top: 8px;
		max-width: none;
		padding-left: 0;
		padding-right: 0;
	}
	.lote-process-modal .modal-header {
		padding: 12px 18px;
	}
	.lote-process-modal .modal-body {
		padding: 16px 18px 12px;
	}
	.lote-process-modal .modal-footer {
		padding: 10px 18px;
	}
	.lote-process-modal .archivo-scroll-container {
		max-height: 220px;
		overflow-y: auto;
	}
	.lote-process-modal .lote-process-status {
		margin-bottom: 12px;
	}
	.lote-process-modal .lote-process-kpi {
		padding: 8px 10px;
	}
	.lote-process-modal .lote-validation-result {
		margin-top: 10px;
		padding: 12px;
	}
	.lote-validation-grid {
		display: grid;
		gap: 4px 18px;
		grid-template-columns: repeat(2, minmax(0, 1fr));
	}
	.lote-validation-grid div {
		line-height: 1.35;
	}
	.lote-process-modal .lote-validation-actions {
		margin-top: 10px;
	}
</style>


		<div class="card-header header-elements-inline">
			<button type="button" data-toggle="collapse" data-target="#collapseExample" class="lote-upload-toggle"><i class="icon-plus3"></i>
				<span>Agregar lote OCR</span></button>
			<div class="collapse" id="collapseExample">
				<div class="card card-body">
					<div class="d-flex flex-wrap align-items-start justify-content-between mb-3">
						<div>
							<h5 class="lote-upload-title">Nuevo lote de facturas</h5>
							<p class="lote-upload-subtitle">Selecciona proveedor, carga los PDFs y procesa la lectura OCR.</p>
						</div>
						<div class="lote-upload-steps">
							<span class="lote-upload-step active"><i class="icon-office"></i> 1. Proveedor</span>
							<span class="lote-upload-step"><i class="icon-file-pdf"></i> 2. PDFs</span>
							<span class="lote-upload-step"><i class="icon-upload7"></i> 3. Procesar</span>
						</div>
					</div>
					<div class="row">

				<div class="col-md-4">
					<div class="form-group form-group-feedback form-group-feedback-right  ">

					<?php
					$js = array(
						'id' => 'id_proveedor',
						'class' => ' select2 form-control custom-select ',
					);



					?>

					<?= form_dropdown('id_proveedor', $select_proveedores, set_value('id_proveedor', @$id_proveedor), $js); ?>
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
						<button disabled id="procesar_lote" type="button" class="lote-process-btn"><i class="icon-upload7"></i> Procesar lote</button>
					</div>






				</div>
				<div class="clearfix"></div>
				<div class="container d-none" id="mydropzone">
					<p class="text-muted mb-2">Arrastra los PDFs o haz click en el area de carga. El lote se prepara antes de consultar Azure.</p>
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
<style>
	.fac-consolidadas-col {
		width: 130px;
		min-width: 130px;
	}
	.fac-consolidadas-estado {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		gap: 6px;
		white-space: nowrap;
	}
</style>
<div class="card">
	<h5 class="card-title bg-titulo text-center text-dark"> Lista de Lotes</h5>


	<table class="table-bordered table-hover datatable-highlight datatable-ajax" style="width: 100%">
		<thead>
			<tr>
			<th><input type="checkbox" id="selectAllPost" class="select-checkbox" data-tabla="dataTable_publicaciones"></th>
				<th>Proveedor</th>
				<th>Fecha</th>
				<th>Facturas</th>
				<th>Sin Index</th>
				<th>Errores lectura</th>
				<th class="fac-consolidadas-col">Fac.<br>Consolidadas</th>
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


<div class="card d-none">
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
<div id="modal_backdrop" class="modal fade bd-example-modal-lg lote-process-modal" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                
                <h5 class="modal-title">Procesamiento de lote - <span id="modal_proveedor"></span></h5>
            </div>
            
            <div class="modal-body">
				<div class="lote-process-status">
					<div class="lote-process-kpi">
						<strong id="lote_total_archivos">0</strong>
						<span>Archivos</span>
					</div>
					<div class="lote-process-kpi">
						<strong id="lote_procesados">0</strong>
						<span>Procesados</span>
					</div>
					<div class="lote-process-kpi">
						<strong id="lote_errores_api">0</strong>
						<span>Error API</span>
					</div>
					<div class="lote-process-kpi">
						<strong id="lote_porcentaje">0%</strong>
						<span>Avance</span>
					</div>
				</div>
                <div class="progress" style="display: none;">
                    <div class="progress-bar" role="progressbar" style="width:0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                </div>
                
				<div class="archivo-scroll-container mt-3">
				<table id="tabla_archivos" class="table" style="background-color: #fff!important;">
                    <thead>
                        <tr>
                            <th>Archivos</th>
                            <th class="col-md-2">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Archivos se agregarán aquí dinámicamente -->
                    </tbody>
                </table>
            </div>
				<div id="lote_validation_result" class="lote-validation-result">
					<div class="lote-validation-title" id="lote_validation_title"></div>
					<div id="lote_validation_detail"></div>
					<div class="lote-validation-actions">
						<a id="lote_action_errores" class="btn btn-danger btn-sm" href="#" style="display:none;">Ver errores de lectura</a>
						<a id="lote_action_sin_index" class="btn btn-warning btn-sm" href="#" style="display:none;">Ver sin index</a>
						<a id="lote_action_completo" class="btn btn-primary btn-sm" href="#" style="display:none;">Revisar lote completo</a>
					</div>
				</div>
			</div>

            <div class="modal-footer">
                <button id="cerrar_modal" type="button" class="btn btn-link">Cerrar</button>
                <button id="enviar_archivos" type="button" class="btn btn-primary" disabled>Enviar archivos</button>
            </div>
        </div>
    </div>
</div>
<!-- /disabled backdrop -->



<script>




</script>
