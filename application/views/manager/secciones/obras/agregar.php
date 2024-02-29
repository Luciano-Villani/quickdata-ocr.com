<?php

?>

<div class="card">
	<div class="card-header header-elements-inline">
		<h5 class="card-title">
			Agregar
			<?= ucfirst($this->router->fetch_class()) ?>
		</h5>
		<div class="header-elements">
			<div class="list-icons">
				<a class="list-icons-item" data-action="collapse"></a>
				<a class="list-icons-item" data-action="reload"></a>
				<a class="list-icons-item" data-action="remove"></a>
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
		<?php echo form_open(base_url('Admin/Proyectos'), "id='myProgramForm'"); ?>
		<div class="row">
			<div class="col-md-4">
				<div class="form-group form-group-feedback form-group-feedback-right">
					<div class="form-control-feedback">
						<i class=" icon-pencil3 text-muted"></i>
					</div>
					<?php
					$js = array(
						'id' => 'select_secretaria',
						'class' => ' select2 form-control custom-select ',
					);
					?>

					<?= form_dropdown('id_secretaria', $select_secretarias, set_value('id_secretaria'), $js); ?>
					<?php echo form_error('id_secretaria', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>

				</div>
			</div>

			<div class="col-md-4">
				<div class="form-group form-group-feedback form-group-feedback-right">
					<div class="form-control-feedback">
						<i class=" icon-pencil3 text-muted"></i>
					</div>
					<?php
					$js = array(
						'id' => 'select_dependencia',
						'class' => ' select2 form-control custom-select ',
					);
					?>

					<?= form_dropdown('id_dependencia', '', set_value('id_dependencia'), $js); ?>
					<?php echo form_error('id_dependencia', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>

				</div>
			</div>
			<div class="col-md-4">
				<div class="form-group form-group-feedback form-group-feedback-right">
					<div class="form-control-feedback">
						<i class=" icon-pencil3 text-muted"></i>
					</div>
					<?php
					$js = array(
						'id' => 'select_programa',
						'class' => ' select2 form-control custom-select ',
					);
					?>

					<?= form_dropdown('id_programa', $select_programas, set_value('id_programa'), $js); ?>
					<?php echo form_error('id_programa', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>

				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-2">
				<div class="form-group form-group-feedback form-group-feedback-right">
					<input type="text" class="form-control" placeholder="Id Interno" name="id_interno"
						value="<?php //echo set_value('rafam'); ?>">
					<div class="form-control-feedback">
						<i class=" icon-pencil3 text-muted"></i>
					</div>
					<?php echo form_error('id_interno', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
				</div>
			</div>
			<div class="col-md-6">
				<div class="form-group form-group-feedback form-group-feedback-right">
					<input type="text" class="form-control" placeholder="Nombre Proyecto" name="descripcion"
						value="<?php //echo set_value('rafam'); ?>">
					<div class="form-control-feedback">
						<i class=" icon-pencil3 text-muted"></i>
					</div>
					<?php echo form_error('descripcion', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
				</div>
			</div>


		</div>


		<button type="submit" class="btn bg-teal-400 btn-labeled btn-labeled-right"><b><i
					class="icon-plus3"></i></b>Guardar</button>
		<?= form_close(); ?>
	</div>
</div>