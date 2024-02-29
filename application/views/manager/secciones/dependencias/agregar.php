<div class="card">
	<div class="card-header header-elements-inline">
		<h5 class="card-title">
			<?= $this->router->fetch_class() ?>
		</h5>
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
		<?php echo form_open(base_url('Admin/Dependencias/agregar')); ?>
		<div class="row">
			<div class="col-md-6">
				<div class="form-group form-group-feedback form-group-feedback-right">
					<div class="form-control-feedback">
						<i class=" icon-pencil3 text-muted"></i>
					</div>
					<?php
					$js = array(
						'id' => 'secretaria',
						'class' => ' select2 form-control custom-select ',
					);
					?>

					<?= form_dropdown('secretaria', $select_secretarias, set_value('secretaria'), $js); ?>
					<?php echo form_error('secretaria', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>

				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-6">
				<div class="form-group form-group-feedback form-group-feedback-right">
					<input type="text" class="form-control" placeholder="Nombre Dependencia" name="dependencia"
						value="<?php //echo set_value('rafam'); ?>">
					<div class="form-control-feedback">
						<i class=" icon-pencil3 text-muted"></i>
					</div>
					<?php echo form_error('dependencia', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
				</div>
			</div>


		</div>


		<button type="submit" class="btn bg-teal-400 btn-labeled btn-labeled-right"><b><i
					class="icon-plus3"></i></b>Guardar</button>
		<?= form_close(); ?>
	</div>
	</div>