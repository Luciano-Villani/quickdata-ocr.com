<?php if($this->ion_auth->is_super() || $this->ion_auth->is_electro()|| $this->ion_auth->is_admin()) { ?>
	<div class=" card">
		<div class="card-header header-elements-inline">
			<?php if ($this->BtnText == "Agregar") { ?>
				<button type="button" data-toggle="collapse" data-target="#collapseExample" class="btn-add btn btn-agregar bg-buton-blue btn-labeled btn-labeled-right"><b><i class="icon-plus3"></i>
			    </b>Agregar <?= ucfirst($this->router->fetch_class()) ?> </button>
				<?php } ?>
			
			<div class="header-elements">
				<div class="list-icons">
					
				</div>
			</div>
		</div>

		<div class="card-body collapse" id="collapseExample">

		<?php echo form_open(base_url('Electromecanica/Proveedores'), array('id'=>'proveedoresForm')); ?>
		<div class="row">
	
					<input required type="hidden" class="form-control  " placeholder="ID" name="id" value="<?php echo set_value('id'); ?>">
				<div class="col-md-3">
				<div class="form-group form-group-feedback form-group-feedback-right">
					<input required type="text" class="form-control  " placeholder="Nombre de proveedor" name="nombre" value="<?php echo set_value('nombre', @$nombre); ?>">
					<div class="form-control-feedback">
						<i class="icon-pencil3 text-muted"></i>
					</div>
					<?php echo form_error('nombre', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
				</div>
			</div>

			<div class="col-md-2">
				<div class="form-group form-group-feedback form-group-feedback-right">
					<input required type="text" class="form-control  " placeholder="Código de proveedor" name="codigo" value="<?php echo set_value('codigo'); ?>">
					<div class="form-control-feedback">
						<i class="icon-pencil3 text-muted"></i>
					</div>
					<?php echo form_error('codigo', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
				</div>
			</div>
			<div class="col-md-2">
				<div class="form-group form-group-feedback form-group-feedback-right">
					<input required type="text" class="form-control  " placeholder="Objeto del gasto" name="objeto_gasto" value="<?php echo set_value('objeto_gasto'); ?>">
					<div class="form-control-feedback">
						<i class="icon-pencil3 text-muted"></i>
					</div>
					<?php echo form_error('objeto_gasto', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
				</div>
			</div>
			<div class="col-md-4">
				<div class="form-group form-group-feedback form-group-feedback-right">
					<input required type="text" class="form-control  " placeholder="Detalle del gasto" name="detalle_gasto" value="<?php echo set_value('detalle_gasto'); ?>">
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
					<input required type="text" class="form-control  " placeholder="Unidad de Medida / Plan " name="unidad_medida" value="<?php echo set_value('unidad_medida'); ?>">
					<div class="form-control-feedback">
						<i class="icon-pencil3 text-muted"></i>
					</div>
					<?php echo form_error('unidad_medida', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
				</div>
			</div>
			<div class="col-md-7">
				<div class="form-group form-group-feedback form-group-feedback-right">
					<input required type="text" class="form-control  " placeholder="URL API" name="urlapi" value="<?php echo set_value('urlapi'); ?>">
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
	
	<table id="proveedores_dt" class="table-bordered table-hover datatable-highlight " style="width: 100%">
		<thead>
			<tr>
				<th>#</th>
				<th>Código Proveedor</th>
				<th>Nombre</th>
				<th>Código Objeto del Gasto</th>
				<th>Objeto del Gasto</th>
				<th>Creado</th>
				<th>Estado</th>
				<th></th>
			</tr>
		</thead>

	</table>

</div>