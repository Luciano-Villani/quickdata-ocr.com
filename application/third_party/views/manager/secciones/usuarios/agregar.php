
<?php 
//echo 'acsascsac';

//var_dump($usuario);
if(isset($usuario)){

	echo '<pre>';
	var_dump( $usuario); 
	echo '</pre>';
	
}

?>

<div class="card">
<h5 class="card-title bg-titulo text-center text-dark">Alta de Usuario</h5>
	

	<div class="card-body">
		
		<?php echo  form_open(base_url('Admin/usuarios/agregar'));?>
		<div class="row">
			<div class="col-md-6">
				<div class="form-group form-group-feedback form-group-feedback-right">
					<input type="text" class="form-control  " placeholder=" username" name="username"  value="<?php echo set_value('username'); ?>">
					<div class="form-control-feedback">
						<i class="icon-user-plus text-muted"></i>
					</div>
					<?php echo form_error('username','<div class="invalid-feedback" style="display:block;">',"</div>");?>
				</div>
			</div>

			<div class="col-md-6">
				<div class="form-group form-group-feedback form-group-feedback-right">
					<input type="email" class="form-control  " placeholder="email" name="email"  value="<?php echo set_value('email');  ?>">
					<div class="form-control-feedback">
						<i class="icon-mention text-muted"></i>
					</div>
					<?php echo form_error('email','<div class="invalid-feedback" style="display:block;">',"</div>");?>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-md-6">
				<div class="form-group form-group-feedback form-group-feedback-right">
					<input type="text" class="form-control  " placeholder="Nombre" name="first_name"  value="<?php echo set_value('first_name'); ?>">
					<div class="form-control-feedback">
						<i class="icon-user-check text-muted"></i>
					</div>
					<?php echo form_error('first_name','<div class="invalid-feedback" style="display:block;">',"</div>");?>
				</div>
			</div>

			<div class="col-md-6">
				<div class="form-group form-group-feedback form-group-feedback-right">
					<input type="text" class="form-control  " placeholder="Apellido" name="last_name"  value="<?php echo set_value('last_name'); ?>">
					<div class="form-control-feedback">
						<i class="icon-user-check text-muted"></i>
					</div>
					<?php echo form_error('last_name','<div class="invalid-feedback" style="display:block;">',"</div>");?>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-md-6">
				<div class="form-group form-group-feedback form-group-feedback-right">
					<input type="password" class="form-control  " placeholder="password" name="password">
					<div class="form-control-feedback">
						<i class="icon-user-lock text-muted"></i>
					</div>
					<?php echo form_error('password','<div class="invalid-feedback" style="display:block;">',"</div>");?>
				</div>
			</div>

			<div class="col-md-6">
				<div class="form-group form-group-feedback form-group-feedback-right">
					<input type="password" class="form-control  " placeholder="Password Confirmación" name="password_2">
					<div class="form-control-feedback">
						<i class="icon-user-lock text-muted"></i>
					</div>
					<?php echo form_error('password_2','<div class="invalid-feedback" style="display:block;">',"</div>");?>
				</div>
			</div>
		</div>
		<div class="form-group">
			<?php foreach($grupos as $grupo):?>
			<div class="form-check">
				<label class="form-check-label">
					<div class="">
						<span class="">
							<?php 
							$atributos=array(
								'class' => 'form-input-styled',
				
							);
							

						echo form_checkbox('grupos[]', $grupo->id,set_checkbox('grupos',$grupo->id),$atributos);
							?>
							
							<!--						<input type="checkbox" name="grupos[]" class="form-input-styled" data-fouc="">-->
						</span>
					</div>
					<?= $grupo->description?>
				</label>
			</div>
			<?php endforeach;?>
			<?php echo form_error('grupos[]','<div class="invalid-feedback" style="display:block;">',"</div>");?>
		</div>

		<button type="submit" class="btn btn-filtrar"><b><i
					class="icon-upload"></i></b> Guardar</button>
					<?= form_close(); ?>
	</div>
