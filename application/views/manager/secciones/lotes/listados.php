

<div class="card ">
	<div class="card-header header-elements-inline">
		<h5 class="card-title"><?= $this->data['page_title'] ?></h5>
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
		<?php echo form_open(base_url('Admin/Lecturas/Upload'),"id='myProgramForm'"); ?>
		<div class="row">
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
		</div>

		<?= form_close(); ?>
	</div>
</div>

<!-- 
<div class="card">
<div class="panel">
		<div class="panel-heading">
			<h5 class="panel-title">Multiple files desde su carpeta</h5>
		</div>

		<div class="panel-body">

			<p class="text-semibold">Multiple file upload example:</p>
			<form action="suber" class="dropzone dz-clickable" id="file-multiple">
				<div class="dz-default dz-message"><span>Drop files  <span>or CLICK</span></span></div>
				<div class="fallback">
					<input name="file" type="file" multiple />
				</div>
			</form>
		</div>
	</div>

</div> -->
<style>
	.dataTables_filter input{
		text-transform: uppercase;
	}
</style>
<div class="card">
	<div class="card-header header-elements-inline">
		<h5 class="card-title">Datos en base</h5>
		<div>
        Toggle column: <a class="toggle-vis" data-column="0">Name</a> - <a class="toggle-vis" data-column="1">Position</a> - <a class="toggle-vis" data-column="2">Office</a> - <a class="toggle-vis" data-column="3">Age</a> - <a class="toggle-vis" data-column="4">Start date</a> - <a class="toggle-vis" data-column="5">Salary</a>
    </div>
	</div>
		<table id="lecturas_dt" class="display compact">
			<thead>
				<tr>
					<th>ID</th>
					<th>Proveedors</th>
					<th>Cuenta</th>
					<th>Medidor</th>
					<th>Nro de factura</th>
					<th>Pedríodo</th>
					<th>Fecha emision</th>
					<th>Vencimiento</th>
					<th>Total importe</th>
					<th>Total vencido</th>
					<th>Consumo</th>
					<th>index</th>
					<th>Accioones</th>
				</tr>
			</thead>

		</table>
</div>

<script>


</script>