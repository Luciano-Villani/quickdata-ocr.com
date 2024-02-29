<?php if($this->ion_auth->is_super() || $this->ion_auth->is_admin()) { ?>
	<div class="mia card">
		<div class="card-header header-elements-inline">
			<?php if ($this->BtnText == "Agregar") { ?>
				<button type="button" data-toggle="collapse" data-target="#collapseExample" class="btn btn-agregar bg-buton-blue btn-labeled btn-labeled-right"><b><i class="icon-plus3"></i>
			    </b>Agregar <?= ucfirst($this->router->fetch_class()) ?> </button>
				<?php } ?>
			
			<div class="header-elements">
				<div class="list-icons">
					
				</div>
			</div>
		</div>

		<div class="card-body collapse" id="collapseExample">

		<?php echo form_open(base_url('Admin/Proveedores'), array('id'=>'form-validate-jquery')); ?>
		<div class="row">
		<div class="col-md-3">
				<div class="form-group form-group-feedback form-group-feedback-right">
					<input required type="text" class="form-control campo-agregar" placeholder="Nombre de proveedor" name="nombre" value="<?php echo set_value('nombre'); ?>">
					<div class="form-control-feedback">
						<i class="icon-pencil3 text-muted"></i>
					</div>
					<?php echo form_error('nombre', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
				</div>
			</div>

			<div class="col-md-2">
				<div class="form-group form-group-feedback form-group-feedback-right">
					<input required type="text" class="form-control campo-agregar" placeholder="Código de proveedor" name="codigo" value="<?php echo set_value('codigo'); ?>">
					<div class="form-control-feedback">
						<i class="icon-pencil3 text-muted"></i>
					</div>
					<?php echo form_error('codigo', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
				</div>
			</div>
			<div class="col-md-2">
				<div class="form-group form-group-feedback form-group-feedback-right">
					<input required type="text" class="form-control campo-agregar" placeholder="Objeto del gasto" name="objeto_gasto" value="<?php echo set_value('objeto_gasto'); ?>">
					<div class="form-control-feedback">
						<i class="icon-pencil3 text-muted"></i>
					</div>
					<?php echo form_error('objeto_gasto', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
				</div>
			</div>
			<div class="col-md-4">
				<div class="form-group form-group-feedback form-group-feedback-right">
					<input required type="text" class="form-control campo-agregar" placeholder="Detalle del gasto" name="detalle_gasto" value="<?php echo set_value('detalle_gasto'); ?>">
					<div class="form-control-feedback">
						<i class="icon-pencil3 text-muted"></i>
					</div>
					<?php echo form_error('detalle_gasto', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
				</div>
			</div>
			
		</div>
		<div class="row">

			<div class="col-md-3">
				<div class="form-group form-group-feedback form-group-feedback-right">
					<input required type="text" class="form-control campo-agregar" placeholder="Unidad de Medida / Plan " name="unidad_medida" value="<?php echo set_value('unidad_medida'); ?>">
					<div class="form-control-feedback">
						<i class="icon-pencil3 text-muted"></i>
					</div>
					<?php echo form_error('unidad_medida', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
				</div>
			</div>
			<div class="col-md-7">
				<div class="form-group form-group-feedback form-group-feedback-right">
					<input required type="text" class="form-control campo-agregar" placeholder="URL API" name="urlapi" value="<?php echo set_value('urlapi'); ?>">
					<div class="form-control-feedback">
						<i class="icon-pencil3 text-muted"></i>
					</div>
					<?php echo form_error('urlapi', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
				</div>
			</div>
			<div class="col-md-2">
			<button type="submit" class="btn btn-filtrar"><b><i
					class="icon-upload"></i></b> Guardar</button>
					<?= form_close(); ?>
			</div>


		</div>



		
	</div>
</div>
<?php }?>
<div class="card">
<h5 class="card-title bg-titulo text-center text-dark"> Lista de <?= ucfirst($this->router->fetch_class()) ?></h5>
	
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