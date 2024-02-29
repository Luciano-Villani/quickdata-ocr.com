<div class="card">
	<div class="card-header header-elements-inline">
		<h5 class="card-title">Alta Usuarios</h5>
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
		<?php echo form_open(base_url('Admin/Proceedores')); ?>
		<div class="row">
			<div class="col-md-6">
				<div class="form-group form-group-feedback form-group-feedback-right">
					<input type="text" class="form-control" placeholder=" username" name="username"
						value="<?php echo set_value('username'); ?>">
					<div class="form-control-feedback">
						<i class="icon-user-plus text-muted"></i>
					</div>
					<?php echo form_error('username', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
				</div>
			</div>

			<div class="col-md-6">
				<div class="form-group form-group-feedback form-group-feedback-right">
					<input type="email" class="form-control" placeholder="email" name="email"
						value="<?php echo set_value('email'); ?>">
					<div class="form-control-feedback">
						<i class="icon-mention text-muted"></i>
					</div>
					<?php echo form_error('email', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-md-6">
				<div class="form-group form-group-feedback form-group-feedback-right">
					<input type="text" class="form-control" placeholder="Nombre" name="first_name"
						value="<?php echo set_value('first_name'); ?>">
					<div class="form-control-feedback">
						<i class="icon-user-check text-muted"></i>
					</div>
					<?php echo form_error('first_name', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
				</div>
			</div>

			<div class="col-md-6">
				<div class="form-group form-group-feedback form-group-feedback-right">
					<input type="text" class="form-control" placeholder="Apellido" name="last_name"
						value="<?php echo set_value('last_name'); ?>">
					<div class="form-control-feedback">
						<i class="icon-user-check text-muted"></i>
					</div>
					<?php echo form_error('last_name', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-md-6">
				<div class="form-group form-group-feedback form-group-feedback-right">
					<input type="password" class="form-control" placeholder="password" name="password">
					<div class="form-control-feedback">
						<i class="icon-user-lock text-muted"></i>
					</div>
					<?php echo form_error('password', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
				</div>
			</div>

			<div class="col-md-6">
				<div class="form-group form-group-feedback form-group-feedback-right">
					<input type="password" class="form-control" placeholder="Password Confirmación" name="password_2">
					<div class="form-control-feedback">
						<i class="icon-user-lock text-muted"></i>
					</div>
					<?php echo form_error('password_2', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
				</div>
			</div>
		</div>
		<div class="form-group">
			<?php foreach ($grupos as $grupo): ?>
				<div class="form-check">
					<label class="form-check-label">
						<div class="">
							<span class="">
								<?php
								$atributos = array(
									'class' => 'form-input-styled',

								);


								echo form_checkbox('grupos[]', $grupo->id, set_checkbox('grupos', $grupo->id), $atributos);
								?>

								<!--						<input type="checkbox" name="grupos[]" class="form-input-styled" data-fouc="">-->
							</span>
						</div>
						<?= $grupo->description ?>
					</label>
				</div>
			<?php endforeach; ?>
			<?php echo form_error('grupos[]', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
		</div>

		<button type="submit" class="btn bg-teal-400 btn-labeled btn-labeled-right"><b><i class="icon-plus3"></i></b>
			Crear Cuenta</button>
		<?= form_close(); ?>
	</div>
</div>