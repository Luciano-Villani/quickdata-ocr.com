<?php if($this->ion_auth->is_super() || $this->ion_auth->is_admin()){ ?>
	
<div class="card">
<div class="card-header header-elements-inline ">
			<h5 class="card-title">Agregar  <?= ucfirst($this->router->fetch_class()) ?></h5>
			<div class="header-elements">
				<div class="list-icons">
				<div class="text-right">
					<button  aria-controls="mui"stype="submit" data-toggle="collapse" href="#mui" class="acciones btn bg-teal-400 ">Agregar  <?= ucfirst($this->router->fetch_class()) ?> <a id="altaSecretaria" class="list-icons-item" data-action="collapse"></a></button>
				</div>

				</div>
			</div>
		</div>
		<div class="sss card-body collapse" id="mui">
		<?php echo form_open(base_url('Admin/Indexaciones/agregar'), "id='myProgramForm'"); ?>
		<div class="row">
		<!-- <div class="col-md-2">
				<div class="form-group form-group-feedback form-group-feedback-right">
					<input type="text" class="form-control" placeholder="Id Interno" name="id_interno"
						value="<?php //echo set_value('rafam'); ?>">
					<div class="form-control-feedback">
						<i class=" icon-pencil3 text-muted"></i>
					</div>
					<?php echo form_error('id_interno', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
				</div>
			</div> -->
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
			<div class="col-md-4">
				<div class="form-group form-group-feedback form-group-feedback-right">
					<div class="form-control-feedback">
						<i class=" icon-pencil3 text-muted"></i>
					</div>
					<?php
					$js = array(
						'id' => 'select_proyecto',
						'class' => ' select2 form-control custom-select ',
					);
					?>

					<?= form_dropdown('id_proyecto', $select_proyectos, set_value('id_proyecto'), $js); ?>
					<?php echo form_error('id_proyecto', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>

				</div>
			</div>			
			<div class="col-md-3">
				<div class="form-group form-group-feedback form-group-feedback-right">
					<input type="text" class="form-control" placeholder="Expediente" name="expediente"
						value="<?php echo set_value('expediente'); ?>">
					<div class="form-control-feedback">
						<i class=" icon-pencil3 text-muted"></i>
					</div>
					<?php echo form_error('expediente', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-3">
				<div class="form-group form-group-feedback form-group-feedback-right">
					<input type="text" class="form-control" placeholder="Nro de cuenta" name="nro_cuenta"
						value="<?php echo set_value('nro_cuenta'); ?>">
					<div class="form-control-feedback">
						<i class=" icon-pencil3 text-muted"></i>
					</div>
					<?php echo form_error('nro_cuenta', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
				</div>
			</div>
			<div class="col-md-4">
				<div class="form-group form-group-feedback form-group-feedback-right">
					<div class="form-control-feedback">
						<i class=" icon-pencil3 text-muted"></i>
					</div>
					<?php
					$js = array(
						'id' => 'id_proveedor',
						'class' => ' select2 form-control custom-select ',
					);
					?>

					<?= form_dropdown('id_proveedor', $select_proveedores, set_value('id_proveedor'), $js); ?>
					<?php echo form_error('id_proveedor', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>

				</div>
			</div>
			<div class="col-md-2">
					<div class="form-group form-group-feedback form-group-feedback-right">
						<div class="form-control-feedback">
							<i class=" icon-pencil3 text-muted"></i>
						</div>
						<?php
						$js = array(
							'required' => 'required',
							'id' => 'tipo_pago',
							'class' => ' select2 form-control custom-select ',
						);
						?>
	
						<?= form_dropdown('tipo_pago', $select_tipo_pago, set_value('tipo_pago'), $js); ?>
						<?php echo form_error('tipo_pago', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
	
					</div>
				</div>


		</div>




		<button type="submit" class="btn bg-teal-400 btn-labeled btn-labeled-right"><b><i class="icon-plus3"></i></b>
			Agregar</button>
		<?= form_close(); ?>
	</div>
	<div class="card-body d-none">
		<!--
		<div class="text-center mb-3">
			<i class="icon-plus3 icon-2x text-success border-success border-3 rounded-round p-3 mb-3 mt-1"></i>
			<h5 class="mb-0">Create account</h5>
			<span class="d-block text-muted">All fields are required</span>
		</div>
-->
		<?php echo form_open(base_url('Admin/Indexaciones/agregar'), "id='myProgramForm'"); ?>
		<div class="row">
		<!-- <div class="col-md-2">
				<div class="form-group form-group-feedback form-group-feedback-right">
					<input type="text" class="form-control" placeholder="Id Interno" name="id_interno"
						value="<?php //echo set_value('rafam'); ?>">
					<div class="form-control-feedback">
						<i class=" icon-pencil3 text-muted"></i>
					</div>
					<?php echo form_error('id_interno', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
				</div>
			</div> -->
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
			<div class="col-md-4">
				<div class="form-group form-group-feedback form-group-feedback-right">
					<div class="form-control-feedback">
						<i class=" icon-pencil3 text-muted"></i>
					</div>
					<?php
					$js = array(
						'id' => 'select_proyecto',
						'class' => ' select2 form-control custom-select ',
					);
					?>

					<?= form_dropdown('id_proyecto', $select_proyectos, set_value('id_proyecto'), $js); ?>
					<?php echo form_error('id_proyecto', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>

				</div>
			</div>			
			<div class="col-md-3">
				<div class="form-group form-group-feedback form-group-feedback-right">
					<input type="text" class="form-control" placeholder="Expediente" name="expediente"
						value="<?php echo set_value('expediente'); ?>">
					<div class="form-control-feedback">
						<i class=" icon-pencil3 text-muted"></i>
					</div>
					<?php echo form_error('expediente', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-3">
				<div class="form-group form-group-feedback form-group-feedback-right">
					<input type="text" class="form-control" placeholder="Nro de cuenta" name="nro_cuenta"
						value="<?php echo set_value('nro_cuenta'); ?>">
					<div class="form-control-feedback">
						<i class=" icon-pencil3 text-muted"></i>
					</div>
					<?php echo form_error('nro_cuenta', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
				</div>
			</div>
			<div class="col-md-4">
				<div class="form-group form-group-feedback form-group-feedback-right">
					<div class="form-control-feedback">
						<i class=" icon-pencil3 text-muted"></i>
					</div>
					<?php
					$js = array(
						'id' => 'id_proveedor',
						'class' => ' select2 form-control custom-select ',
					);
					?>

					<?= form_dropdown('id_proveedor', $select_proveedores, set_value('id_proveedor'), $js); ?>
					<?php echo form_error('id_proveedor', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>

				</div>
			</div>
			<div class="col-md-2">
					<div class="form-group form-group-feedback form-group-feedback-right">
						<div class="form-control-feedback">
							<i class=" icon-pencil3 text-muted"></i>
						</div>
						<?php
						$js = array(
							'required' => 'required',
							'id' => 'tipo_pago',
							'class' => ' select2 form-control custom-select ',
						);
						?>
	
						<?= form_dropdown('tipo_pago', $select_tipo_pago, set_value('tipo_pago'), $js); ?>
						<?php echo form_error('tipo_pago', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
	
					</div>
				</div>


		</div>


		<button type="submit" class="btn bg-teal-400 btn-labeled btn-labeled-right"><b><i
					class="icon-plus3"></i></b>Agregar</button>
		<?= form_close(); ?>
	</div>
</div>
<?php }?>
<div class="card">
	<div class="card-header header-elements-inline">
		<h5 class="card-title">Lista de <?= $this->router->fetch_class() ?></h5>

	</div>
	<div class="card-body">
<!-- <a class="btn bg-teal-400 " href="/Admin/Indexaciones/agregar">Agregar</a> -->

<!--		<table id="usuarios_dt" class="datatable-basic dataTable no-footer">-->
	</div>
	<table id="indexaciones_dt" class="table-bordered table-hover datatable-highlight " style="width: 100%">
			<thead>
				<tr>
					<th></th>
					<th>#</th>
					<th>Nro de cuenta</th>
					<th>Secretaria</th>
					<th>Dependencia</th>
					<th>Programa</th>
					<th>Proyecto</th>
					<!-- <th>proveedor</th>
					<th>Tipo Pago</th>
					<th>Acciones</th> -->
				</tr>
			</thead>
		</table>
		
</div>
