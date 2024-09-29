<div class="card">
	<div class="card-header header-elements-inline">
		<h5 class="card-title">Alta Proveedores</h5>
		<div class="header-elements">
			<div class="list-icons">
				<a class="list-icons-item" data-action="collapse"></a>

			</div>
		</div>
	</div>

	<div class="card-body">
		<!--
		<div class="text-center mb-3">
			<i class="icon-plus3 icon-2x text-success border-success border-3 rounded-round p-3 mb-3 mt-1"></i>
			<h5 class="mb-0">Create account</h5>
			<span class="d-block text-muted">All fields are required</span>
		</div>
-->
		<?php echo form_open(base_url('Admin/Proveedores')); ?>
		<div class="row">
			<div class="col-md-3">
				<div class="form-group form-group-feedback form-group-feedback-right">
					<input type="text" class="form-control" placeholder="Código de proveedor" name="codigo"
						value="<?php echo set_value('codigo'); ?>">
					<div class="form-control-feedback">
						<i class="icon-pencil3 text-muted"></i>
					</div>
					<?php echo form_error('codigo', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
				</div>
			</div>
			<div class="col-md-4">
				<div class="form-group form-group-feedback form-group-feedback-right">
					<input type="text" class="form-control" placeholder="Nombre de proveedor" name="nombre"
						value="<?php echo set_value('nombre'); ?>">
					<div class="form-control-feedback">
						<i class="icon-pencil3 text-muted"></i>
					</div>
					<?php echo form_error('nombre', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
				</div>
			</div>
		</div>
		<div class="row">

			<div class="col-md-2">
				<div class="form-group form-group-feedback form-group-feedback-right">
					<input type="text" class="form-control" placeholder="Objeto del gasto" name="objeto_gasto"
						value="<?php echo set_value('objeto_gasto'); ?>">
					<div class="form-control-feedback">
						<i class="icon-pencil3 text-muted"></i>
					</div>
					<?php echo form_error('objeto_gasto', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
				</div>
			</div>
			<div class="col-md-6">
				<div class="form-group form-group-feedback form-group-feedback-right">
					<input type="text" class="form-control" placeholder="Detalle del gasto" name="detalle_gasto"
						value="<?php echo set_value('detalle_gasto'); ?>">
					<div class="form-control-feedback">
						<i class="icon-pencil3 text-muted"></i>
					</div>
					<?php echo form_error('detalle_gasto', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
				</div>
			</div>
		</div>
		<div class="row">

			<div class="col-md-8">
				<div class="form-group form-group-feedback form-group-feedback-right">
					<input type="text" class="form-control" placeholder="URL API" name="urlapi"
						value="<?php echo set_value('urlapi'); ?>">
					<div class="form-control-feedback">
						<i class="icon-pencil3 text-muted"></i>
					</div>
					<?php echo form_error('urlapi', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
				</div>
			</div>


		</div>



		<button type="submit" class="btn bg-teal-400 btn-labeled btn-labeled-right"><b><i class="icon-plus3"></i></b>
			Crear Proveedor</button>
		<?= form_close(); ?>
	</div>
</div>
<div class="card">
	<div class="card-header header-elements-inline">
		<h5 class="card-title">
			<?= $page_title ?> en tabla
		</h5>

	</div>
	<div class="card-body">
		<!--		<table id="usuarios_dt" class="datatable-basic dataTable no-footer">-->
	</div>
	<table id="proveedores_dt" class="table datatable-show-all dataTable no-footer">
		<thead>
			<tr>
				<th>#</th>
				<th>codigo</th>
				<th>Nombre</th>
				<th>objeto gasto</th>
				<th>detalle gasto</th>
				<th>creado</th>
				<th>estado</th>
			</tr>
		</thead>

	</table>

</div>