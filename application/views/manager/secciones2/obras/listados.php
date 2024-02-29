<?php if($this->ion_auth->is_super() || $this->ion_auth->is_admin()){ ?>
	<div class="mia   card">
		<div class="card-header header-elements-inline">
				<?php if ($this->BtnText == "Agregar") { ?>
									<button type="button" data-toggle="collapse" data-target="#collapseExample" class="btn bg-teal-400 btn-labeled btn-labeled-right"><b><i class="icon-plus3"></i></b>Agregar <?= ucfirst($this->router->fetch_class()) ?> </button>
								<?php } ?>
			<div class="header-elements">
				<div class="list-icons">
				<div class="text-right">
				<button type="button" data-toggle="collapse" data-target="#collapseExample" class="btn bg-teal-400 btn-labeled btn-labeled-right"><b><i class="icon-plus3"></i></b>Agregar <?= ucfirst($this->router->fetch_class()) ?> </button>

					<!-- <button type="submit" class="acciones btn bg-teal-400 ">Agregar <?= ucfirst($this->router->fetch_class()) ?> <a id="altaSecretaria" class="list-icons-item" data-action="collapse"></a></button> -->
				</div>

				</div>
			</div>
		</div>

	<div class="card-body collapse" id="collapseExample">
	<h5 class="card-title"><?= $this->BtnText . ' ' . ucfirst($this->router->fetch_class()) ?></h5>
		<?php echo form_open(base_url('Admin/Obras'), "id='form-validate-jquery'"); ?>
		<div class="row">
		<div class="col-md-4">
				<div class="form-group form-group-feedback form-group-feedback-right">
					<div class="form-control-feedback">
						<i class=" icon-pencil3 text-muted"></i>
					</div>
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
			<div class="col-md-4">
				<div class="form-group form-group-feedback form-group-feedback-right">
					<div class="form-control-feedback">
						<i class=" icon-pencil3 text-muted"></i>
					</div>
					<?php
					$js = array(
						'required'=>'required',
						'id' => 'select_programa',
						'class' => ' select2 form-control custom-select ',
					);
					?>

					<?= form_dropdown('id_programa', $select_programa, set_value('id_programa'), $js); ?>
					<?php echo form_error('id_programa', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>

				</div>
			</div>
			<div class="col-md-4">
				<div class="form-group form-group-feedback form-group-feedback-right">
					<div class="form-control-feedback">
						<i class=" icon-pencil3 text-muted"></i>
					</div>
					<?php
					$js = array(
						'required'=>'required',
						'id' => 'select_proyecto',
						'class' => ' select2 form-control custom-select ',
					);
					?>

					<?= form_dropdown('id_proyecto', $select_proyecto, set_value('id_proyecto'), $js); ?>
					<?php echo form_error('id_proyecto', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>

				</div>
			</div>			
			



		</div>
		<div class="row">
			<div class="col-md-2">
				<div class="form-group form-group-feedback form-group-feedback-right">
					<input required type="text" class="form-control" placeholder="Código de Obra" name="id_interno"
						value="<?php echo set_value('id_interno'); ?>">
					<div class="form-control-feedback">
						<i class=" icon-pencil3 text-muted"></i>
					</div>
					<?php echo form_error('id_interno', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
				</div>
			</div>
			<div class="col-md-6">
				<div class="form-group form-group-feedback form-group-feedback-right">
					<input required type="text" class="form-control" placeholder="Descripción de obra" name="descripcion"
						value="<?php echo set_value('descripcion'); ?>">
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
<?php } ?>
<div class="card">
	<div class="card-header header-elements-inline">
	<h5 class="card-title"> Lista de <?= ucfirst($this->router->fetch_class()) ?></h5>

	</div>
	<div class="card-body">

<!--<a class="btn bg-teal-400 " href="/Admin/Proyectos/agregar">Agregar</a> -->

<!--		<table id="usuarios_dt" class="datatable-basic dataTable no-footer">-->
	</div>
		<table id="usuarios_dt" class="table datatable-show-all dataTable no-footer">
			<thead>
				<tr>
					<th>#</th>
					<th>Código obra	</th>
					<th>Descripción</th>
					<th>Proyecto</th>
					<th>Programa</th>
					<th>Secretaria	</th>
					<th>Acciones</th>
				</tr>
			</thead>

		</table>

</div>
