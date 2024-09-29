<?php if($this->ion_auth->is_super() || $this->ion_auth->is_admin()){ ?>
<div class="card">
<div class="card-header header-elements-inline ">
			<h5 class="card-title">Agregar  <?= ucfirst($this->router->fetch_class()) ?></h5>
			<div class="header-elements">
				<div class="list-icons">
				<div class="text-right">
					<button type="submit" class="acciones btn bg-teal-400 ">Agregar  <?= ucfirst($this->router->fetch_class()) ?> <a id="altaSecretaria" class="list-icons-item" data-action="collapse"></a></button>
				</div>

				</div>
			</div>
		</div>

	<div class="sss card-body collapse">
		<?php echo form_open(base_url('Admin/Proveedores'), array('id'=>'form-validate-jquery')); ?>
		<div class="row">
			<div class="col-md-2">
				<div class="form-group form-group-feedback form-group-feedback-right">
					<input required type="text" class="form-control" placeholder="Código de proveedor" name="codigo" value="<?php echo set_value('codigo'); ?>">
					<div class="form-control-feedback">
						<i class="icon-pencil3 text-muted"></i>
					</div>
					<?php echo form_error('codigo', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
				</div>
			</div>
			<div class="col-md-3">
				<div class="form-group form-group-feedback form-group-feedback-right">
					<input required type="text" class="form-control" placeholder="Nombre de proveedor" name="nombre" value="<?php echo set_value('nombre'); ?>">
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
					<input required type="text" class="form-control" placeholder="Objeto del gasto" name="objeto_gasto" value="<?php echo set_value('objeto_gasto'); ?>">
					<div class="form-control-feedback">
						<i class="icon-pencil3 text-muted"></i>
					</div>
					<?php echo form_error('objeto_gasto', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
				</div>
			</div>
			<div class="col-md-6">
				<div class="form-group form-group-feedback form-group-feedback-right">
					<input required type="text" class="form-control" placeholder="Detalle del gasto" name="detalle_gasto" value="<?php echo set_value('detalle_gasto'); ?>">
					<div class="form-control-feedback">
						<i class="icon-pencil3 text-muted"></i>
					</div>
					<?php echo form_error('detalle_gasto', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
				</div>
			</div>
		</div>
		<div class="row">

			<div class="col-md-4">
				<div class="form-group form-group-feedback form-group-feedback-right">
					<input required type="text" class="form-control" placeholder="Unidad de Medida / Plan " name="unidad_medida" value="<?php echo set_value('unidad_medida'); ?>">
					<div class="form-control-feedback">
						<i class="icon-pencil3 text-muted"></i>
					</div>
					<?php echo form_error('unidad_medida', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
				</div>
			</div>
			<div class="col-md-8">
				<div class="form-group form-group-feedback form-group-feedback-right">
					<input required type="text" class="form-control" placeholder="URL API" name="urlapi" value="<?php echo set_value('urlapi'); ?>">
					<div class="form-control-feedback">
						<i class="icon-pencil3 text-muted"></i>
					</div>
					<?php echo form_error('urlapi', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
				</div>
			</div>


		</div>



		<button type="submit" class="btn bg-teal-400 btn-labeled btn-labeled-right"><b><i class="icon-plus3"></i></b>
			Agregar</button>
		<?= form_close(); ?>
	</div>
</div>
<?php }?>
<div class="card">
	<div class="card-header header-elements-inline">
		<h5 class="card-title">Lista de <?= $page_title ?></h5>

	</div>
	<div class="card-body">
		<!--		<table id="usuarios_dt" class="datatable-basic dataTable no-footer">-->
	</div>
	<table id="proveedores_dt" class="table datatable-show-all dataTable no-footer">
		<thead>
			<tr>
				<th>#</th>
				<th>Código Proveedor</th>
				<th>Nombre</th>
				<th>Código Objeto del Gasto</th>
				<th>Objeto del Gasto</th>
				<th>Creado</th>
				<th>Estado</th>
			</tr>
		</thead>

	</table>

</div>