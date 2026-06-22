<?php if ($this->ion_auth->is_electro() || $this->ion_auth->is_super() || $this->ion_auth->is_admin()) { ?>

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
			.lote-process-modal .modal-dialog {
				max-width: 760px;
			}
			.lote-process-modal .modal-header {
				background: #c6d2e3;
				border-bottom: 0;
				padding: 12px 18px;
			}
			.lote-process-modal .modal-title {
				font-size: 18px;
				font-weight: 600;
			}
			.lote-process-modal .modal-body {
				padding: 16px 18px 12px;
			}
			.lote-process-modal .modal-footer {
				padding: 10px 18px;
			}
			.lote-process-status {
				display: grid;
				gap: 10px;
				grid-template-columns: repeat(4, 1fr);
				margin-bottom: 12px;
			}
			.lote-process-kpi {
				background: #f7f9fc;
				border: 1px solid #d9e2ef;
				border-radius: 8px;
				padding: 8px 10px;
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
			.lote-process-modal .archivo-scroll-container {
				max-height: 220px;
				overflow-y: auto;
			}
			.lote-validation-result {
				border: 1px solid #d9e2ef;
				border-radius: 8px;
				display: none;
				margin-top: 10px;
				padding: 12px;
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
			.lote-validation-grid {
				display: grid;
				gap: 4px 18px;
				grid-template-columns: repeat(2, minmax(0, 1fr));
			}
			.lote-validation-actions {
				display: flex;
				flex-wrap: wrap;
				gap: 8px;
				margin-top: 10px;
			}
			@media (max-width: 768px) {
				.lote-process-status,
				.lote-validation-grid {
					grid-template-columns: repeat(2, 1fr);
				}
			}
			</style>
		

<?php
$dysplay = 'd-none'; 
if (!$this->ion_auth->is_electro()){
	$dysplay = '';

} 
?>
		<div class="card-header header-elements-inline <?= $dysplay?>">
			<button type="button" data-toggle="collapse" data-target="#collapseExample" class="btn btn-agregar mb-1 bg-buton-blue btn-labeled btn-labeled-right"><b><i class="icon-plus3"></i>
				</b>Agregar Nuevo Lote </button>
			<div class="collapse" id="collapseExample">
				<div class="card card-body">
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
	<h5 class="card-title bg-titulo text-center text-dark"> Lista de Lotes Electromecánica</h5>


	<table class="table-bordered table-hover datatable-highlight datatable-ajax" style="width: 100%">
		<thead>
			<tr>
			<th><input type="checkbox" id="selectAllPost" class="select-checkbox" data-tabla="dataTable_publicaciones"></th>
				<th>Proveedor</th>
				<th>Código</th>
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
					<div class="lote-process-kpi"><strong id="lote_total_archivos">0</strong><span>Archivos</span></div>
					<div class="lote-process-kpi"><strong id="lote_procesados">0</strong><span>Procesados</span></div>
					<div class="lote-process-kpi"><strong id="lote_errores_api">0</strong><span>Error API</span></div>
					<div class="lote-process-kpi"><strong id="lote_porcentaje">0%</strong><span>Avance</span></div>
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
