<?php if ($this->ion_auth->is_super() || $this->ion_auth->is_admin()) { ?>
	<div class=" card">
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

			<?php echo form_open(base_url('Admin/Programas'), "id='form-validate-jquery'"); ?>

			<div class="row">
				<div class="col-md-3">
					<div class="form-group form-group-feedback form-group-feedback-right  ">
						<div class="form-control-feedback">

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

				<!-- <div class="col-md-4">
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
			</div> -->
				<div class="col-md-2">
					<div class="form-group form-group-feedback form-group-feedback-right">
						<input required type="text" class="form-control  " placeholder="Código" name="id_interno" value="<?php //echo set_value('rafam'); 
																															?>">
						
						<?php echo form_error('id_interno', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
					</div>
				</div>
				<div class="col-md-6">
					<div class="form-group form-group-feedback form-group-feedback-right">
						<input required type="text" class="form-control  " placeholder="Descripción del Programa" name="descripcion" value="<?php //echo set_value('rafam'); 
																																			?>">
						
						<?php echo form_error('descripcion', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
					</div>
				</div>
				<div class="col-md-1">
				<input readonly="readonly" class="form-control" type="text" name="id">
			</div>	
				<div class="col-md-2">
					<button type="submit" class="btn btn-filtrar"><b><i class="icon-upload"></i></b> Guardar</button>
					<?= form_close(); ?>
				</div>
			</div>
		</div>
	</div>
<?php } ?>
<div class="card">
	<h5 class="card-title bg-titulo text-center text-dark"> Lista de <?= ucfirst($this->router->fetch_class()) ?></h5>
	<table id="programas_dt" class="table-bordered table-hover datatable-highlight no-footer dataTable">
		<thead>
			<tr>
				<th></th>
				<th>Descripción</th>
				<th>Secretaria</th>
				<th>Acciones</th>
			</tr>
		</thead>
	</table>
</div>