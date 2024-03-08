<?php if ($this->ion_auth->is_super() || $this->ion_auth->is_admin()) { ?>
	<div class=" card">
		<div class="card-header header-elements-inline">
			<?php if ($this->BtnText == "Agregar") { ?>
				<button type="button" data-toggle="collapse" data-target="#collapseExample" class="btn btn-agregar bg-buton-blue btn-labeled btn-labeled-right"><b><i class="icon-plus3"></i>
					</b>Agregar <?= ucfirst($this->router->fetch_class()) ?> </button>
			<?php } ?>

		</div>

		<div class="card-body <?= $collapse?>" id="collapseExample">
			<?php echo form_open(base_url('Admin/Indexaciones'), array('id' => 'form-validate-jquery')); ?>
			<div class="row">

				<div class="col-md-1 d-none">
					<div class="form-group">
						<input type="text" readonly class="form-control" placeholder="" name="id_indexacion" value="<?php echo @$id_indexacion; ?>">

						<?php echo form_error('id_indexacion', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
					</div>
				</div>
				<div class="col-md-2">
					<div class="form-group form-group-feedback form-group-feedback-right  ">

						<?php
						$js = array(
							'id' => 'id_proveedor',
							'class' => ' select2 form-control custom-select ',
						);



						?>

						<?= form_dropdown('id_proveedor', $select_proveedores, set_value('id_proveedor', @$id_proveedor), $js); ?>
						<?php echo form_error('id_proveedor', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>

					</div>
				</div>
				<div class="col-md-2">
					<div class="form-group form-group-feedback form-group-feedback-right">
						<input type="text" class="form-control  " placeholder="Nro de cuenta" name="nro_cuenta" value="<?php echo set_value('nro_cuenta', @$nro_cuenta); ?>">

						<?php echo form_error('nro_cuenta', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
					</div>
				</div>
				<div class="col-md-3">
					<div class="form-group form-group-feedback form-group-feedback-right  ">

						<?php
						$js = array(
							'id' => 'select_secretaria',
							'class' => ' select2 form-control custom-select ',
						);
						?>

						<?= form_dropdown('id_secretaria', $select_secretarias, set_value('id_secretaria', @$id_secretaria), $js); ?>
						<?php echo form_error('id_secretaria', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>

					</div>
				</div>

				<div class="col-md-4">
					<div class="form-group form-group-feedback form-group-feedback-right  ">

						<?php
						$js = array(
							'id' => 'select_programa',
							'disabled' => 'disabled',
							'class' => ' select2 form-control custom-select ',
						);


						?>

						<?= form_dropdown('id_programa', $select_programas, set_value('id_programa', @$seleccion_programa), $js, $programas_id_interno); ?>
						<?php echo form_error('id_programa', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>

					</div>
				</div>


			</div>


			<div class="row">
				<div class="col-md-3">
					<div class="form-group form-group-feedback form-group-feedback-right  ">

						<?php
						$js = array(
							'id' => 'select_proyecto',
							'disabled' => 'disabled',
							'class' => ' select2 form-control custom-select ',
						);
						?>

						<?= form_dropdown('id_proyecto', $select_proyectos, set_value('id_proyecto', @$id_proyecto), $js); ?>
						<?php echo form_error('id_proyecto', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>

					</div>
				</div>
				<div class="col-md-3">
					<div class="form-group form-group-feedback form-group-feedback-right  ">

						<?php
						$js = array(
							'id' => 'select_dependencia',
							'disabled' => 'disabled',
							'class' => ' select2 form-control custom-select ',
						);

						?>



						<?= form_dropdown('id_dependencia', $select_dependencias, set_value('id_dependencia', @$id_dependencia),$js); ?>
						<?php echo form_error('id_dependencia', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>

					</div>
				</div>
				<div class="col-md-2">
					<div class="form-group form-group-feedback form-group-feedback-right  ">
						<input type="text" class="form-control" placeholder="Exp" name="expediente" value="<?php echo set_value('expediente',@$indexador->expediente); ?>">

						<?php echo form_error('expediente', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
					</div>
				</div>
				<div class="col-md-2">
					<div class="form-group form-group-feedback form-group-feedback-right  ">

						<?php
						$js = array(
							'required' => 'required',
							'id' => 'tipo_pago',
							
							'class' => ' select2 form-control custom-select ',
						);
						?>

						<?= form_dropdown('tipo_pago', $select_tipo_pago, set_value('tipo_pago', @$tipo_pago), $js); ?>
						<?php echo form_error('tipo_pago', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>

					</div>
				</div>
				<div class="col-md-2">
					<button type="submit" class=" <?= $this->BtnText ?> btn btn-filtrar"><b><i class="icon-upload"></i></b><?= $this->BtnText ?></button>
					<?= form_close(); ?>
				</div>
			</div>

		</div>
	</div>
<?php } ?>
<?php


?>
<div class="card">
	<h5 class="card-title bg-titulo text-center text-dark"> Lista de <?= ucfirst($this->router->fetch_class()) ?></h5>
	<style>
		#indexaciones_dt {
			text-transform: uppercase;
		}
	</style>
	<table id="indexaciones_dt" class="display table-bordered table-hover datatable-highlight" style="width: 100%">
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
					<th>Tipo Pago</th> -->
				<th></th>
			</tr>
		</thead>
	</table>

</div>