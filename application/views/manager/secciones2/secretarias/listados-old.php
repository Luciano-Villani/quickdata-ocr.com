
<div class="card ">
	<div class="card-header header-elements-inline">
		<h5 class="card-title">Agregar Secretaría</h5>
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
		<?php echo form_open(base_url('Admin/Secretarias')); ?>
		<div class="row">
			<div class="col-md-6">
				<div class="form-group form-group-feedback form-group-feedback-right">
					<input type="text" class="form-control" placeholder=" Jurisdicción - Rafam" name="rafam"
						value="<?php //echo set_value('rafam'); ?>">
					<div class="form-control-feedback">
						<i class=" icon-pencil3 text-muted"></i>
					</div>
					<?php echo form_error('rafam', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
				</div>
			</div>

			<div class="col-md-6">
				<div class="form-group form-group-feedback form-group-feedback-right">
					<input type="text" class="form-control" placeholder="Jurisdicción - Major" name="major"
						value="<?php //echo set_value('major');  ?>">
					<div class="form-control-feedback">
						<i class=" icon-pencil3 text-muted"></i>
					</div>
					<?php echo form_error('major', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-md-6">
				<div class="form-group form-group-feedback form-group-feedback-right">
					<input type="text" class="form-control" placeholder="Jurisdicción Descripción" name="secretaria"
						value="<?php //echo set_value('secretaria'); ?>">
					<div class="form-control-feedback">
						<i class=" icon-pencil3 text-muted"></i>
					</div>
					<?php echo form_error('secretaria', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
				</div>
			</div>


		</div>


		<button type="submit" class="btn bg-teal-400 btn-labeled btn-labeled-right"><b><i
					class="icon-plus3"></i></b>Guardar</button>
		<?= form_close(); ?>
	</div>
</div>
<div class="card">
	<div class="card-header header-elements-inline">
		<h5 class="card-title">Secretarías en base</h5>
		
	</div>
		<table id="usuarios_dt" class="table datatable-show-all dataTable no-footer">
			<thead>
				<tr>
					<th>#</th>
					<th>Jurisdiccion - Rafam</th>
					<th>Jurisdiccion Major</th>
					<th>Jurisdiccion Descripcion</th>
					<th>fecha alta</th>
					<th>Acciones</th>
				</tr>
			</thead>

		</table>
</div>
