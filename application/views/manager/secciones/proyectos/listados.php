<?php if ($this->ion_auth->is_super() || $this->ion_auth->is_admin()) { ?>
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
		<?php echo form_open(base_url('Admin/Proyectos'),array('id'=>'form-validate-jquery')); ?>
		<div class="row">
			<div class="col-md-3">
				<div class="form-group form-group-feedback form-group-feedback-right campo-agregar">
					
					<?php
					$js = array(
						'required' => 'required',
						'id' => 'select_secretaria',
						'class' => ' select2 form-control custom-select ',
					);
					?>

					<?= form_dropdown('id_secretaria', $select_secretarias, set_value('id_secretaria'), $js); ?>
					<?php echo form_error('id_secretaria', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>

				</div>
			</div>


			<div class="col-md-3">
				<div class="form-group form-group-feedback form-group-feedback-right campo-agregar">
					
					<?php
					$js = array(
						'required' => 'required',
						'id' => 'select_programa',
						'class' => ' select2 form-control custom-select ',
					);
					?>

					<?= form_dropdown('id_programa', $select_programas, set_value('id_programa'), $js); ?>
					<?php echo form_error('id_programa', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>

				</div>
			</div>
			<div class="col-md-1">
				<div class="form-group form-group-feedback form-group-feedback-right campo-agregar">
					<input required type="text" class="form-control" placeholder="Código" name="id_interno" value="<?php //echo set_value('rafam'); 
																												?>">
					<div class="form-control-feedback">
						<i class=" icon-pencil3 text-muted"></i>
					</div>
					<?php echo form_error('id_interno', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
				</div>
			</div>
			
		</div>
		<div class="row">
			<div class="col-md-6">
				<div class="form-group form-group-feedback form-group-feedback-right campo-agregar">
					<input required type="text" class="form-control" placeholder="Descripción Proyecto" name="descripcion" value="<?php //echo set_value('rafam'); 
																													?>">
					<div class="form-control-feedback">
						<i class=" icon-pencil3 text-muted"></i>
					</div>
					<?php echo form_error('descripcion', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
				</div>
			</div>
			<div class="col-md-4">
			<button type="submit" class="btn btn-filtrar"><b><i
					class="icon-upload"></i></b> Guardar</button>
			<?= form_close(); ?>
			</div>
			<!-- <div class="col-md-3">
				<div class="form-group form-group-feedback form-group-feedback-right">
					<div class="form-control-feedback">
						<i class=" icon-pencil3 text-muted"></i>
					</div>
					<?php
					$js = array(
						'required' => 'required',
						'id' => 'select_dependencia',
						'class' => ' select2 form-control custom-select ',
					);
					?>

					<?= form_dropdown('id_dependencia', '', set_value('id_dependencia'), $js); ?>
					<?php echo form_error('id_dependencia', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>

				</div>
			</div> -->


		</div>


	</div>
</div>
<?php } ?>
<div class="card">
<h5 class="card-title bg-titulo text-center text-dark"> Lista de <?= ucfirst($this->router->fetch_class()) ?></h5>
	
		

	<table id="usuarios_dt" class="table datatable-show-all dataTable no-footer">
		<thead>
			<tr>
				<th># </th>
				<th>Código proyecto</th>
				<th>Descripción</th>
				<th>Programa</th>
				<th>Secretaría</th>
				<th>Acciones</th>
			</tr>
		</thead>

	</table>

</div>