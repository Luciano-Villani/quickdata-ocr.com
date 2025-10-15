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
</style>


		<div class="card-header header-elements-inline">
			<button type="button" data-toggle="collapse" data-target="#collapseExample" class="btn btn-agregar mb-1 bg-buton-blue btn-labeled btn-labeled-right"><b><i class="icon-plus3"></i>
				</b>Agregar Nuevo Lote </button>
			<div class="collapse" id="collapseExample">
				<div class="card card-body">
					<div class="row">

					<div class="col-md-4">
    <div class="form-group form-group-feedback form-group-feedback-right">
        <select name="id_proveedor" id="id_proveedor" class="select2 form-control custom-select">
            <?php foreach ($select_proveedores as $id => $proveedor): ?>
                <?php if (is_array($proveedor)): ?>
                    <option value="<?= $id ?>" data-procesar-por="<?= $proveedor['procesar_por'] ?>">
                        <?= $proveedor['nombre'] ?>
                    </option>
                <?php else: ?>
                    <option value="<?= $id ?>"><?= $proveedor ?></option>
                <?php endif; ?>
            <?php endforeach; ?>
        </select>
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
			<th><input type="checkbox" id="selectAllPost" class="select-checkbox" data-tabla="dataTable_publicaciones"></th>
				<th>Proveedor</th>
				<th>Código</th>
				<th>Fecha</th>
				<th>Facturas</th>
				<th>Errores</th>
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


<div class="card d-none">
	<h5 class="card-title bg-titulo text-center text-dark"> Datos leídos</h5>

	<table id="lecturas_dt" class=" datatable-highlight" style="width: 100%">
		<thead>
			<tr>
				<th>Proveedor</th>
				<th>Cuenta</th>
				<th>Medidor</th>
				<th>Nro de factura</th>
				<th>Período</th>
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
                
                <h5 class="modal-title">Proveedor: <span id="modal_proveedor"></span></h5>
            </div>
            
            <div class="modal-body">
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
			</div>

            <div class="modal-footer">
                <button id="cerrar_modal" type="button" class="btn btn-link" data-dismiss="modal">Cerrar</button>
                <button id="enviar_archivos" type="button" class="btn btn-primary" disabled>Enviar archivos</button>
            </div>
        </div>
    </div>
</div>
<!-- /disabled backdrop -->



<script>




</script>