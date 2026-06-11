<style>
	.indexaciones-page .card {
		border: 1px solid #dce5f1;
		border-radius: 8px;
		box-shadow: 0 6px 18px rgba(18, 52, 86, 0.05);
	}
	.indexaciones-page > .card > .card-header {
		padding: 8px 16px !important;
	}
	.indexaciones-page .page-title {
		font-size: 21px;
		font-weight: 700;
		color: #12345b;
		margin: 0;
	}
	.indexaciones-page .page-subtitle {
		color: #7a8595;
		font-size: 12px;
		margin-top: 2px;
	}
	.indexaciones-page .section-title {
		font-size: 14px;
		font-weight: 700;
		color: #12345b;
		text-transform: uppercase;
		letter-spacing: .03em;
		margin: 18px 0 12px;
		padding-bottom: 6px;
		border-bottom: 1px solid #e8eef6;
	}
	.indexaciones-page label {
		font-weight: 600;
		color: #344767;
		margin-bottom: 5px;
	}
	.indexaciones-page .form-actions {
		border-top: 1px solid #e8eef6;
		padding-top: 16px;
		margin-top: 8px;
	}
	.indexaciones-page .table-card-header {
		background: #c8d4e6;
		color: #1c2f4d;
		padding: 10px 16px;
		border-radius: 8px 8px 0 0;
	}
	.indexaciones-page #indexaciones_dt {
		text-transform: uppercase;
		width: 100%;
	}
	.indexaciones-page .dataTables_filter {
		padding-right: 14px;
	}
	.indexaciones-page .dataTables_filter input {
		min-width: 260px;
	}
	.indexaciones-page .dt-buttons {
		margin-left: 14px;
	}
</style>

<div class="indexaciones-page">
	<?php if ($this->ion_auth->is_super() || $this->ion_auth->is_admin()) { ?>
		<div class="card">
			<div class="card-header d-flex justify-content-between align-items-center">
				<div>
					<h3 class="page-title">Indexadores de cuentas</h3>
					<div class="page-subtitle">Alta, edicion y mantenimiento de cuentas asociadas a estructura programatica.</div>
				</div>
				<div class="d-flex align-items-center">
					<a href="<?= base_url('Admin/Indexaciones/migrar') ?>" class="btn btn-outline-primary mr-2">
						<i class="icon-shuffle"></i> Migrar cuenta
					</a>
					<?php if ($this->BtnText == "Agregar") { ?>
						<button type="button" data-toggle="collapse" data-target="#formulario1" class="btn btn-primary">
							<i class="icon-plus3"></i> Agregar indexacion
						</button>
					<?php } ?>
				</div>
			</div>

			<div class="card-body <?= $collapse ?>" id="formulario1">
				<?php echo form_open(base_url('Admin/Indexaciones'), array('id' => 'form-validate-jquery')); ?>
				<input type="hidden" name="id_indexacion" value="<?php echo @$id_indexacion; ?>">

				<div class="section-title">Cuenta y proveedor</div>
				<div class="row">
					<div class="col-md-1">
						<div class="form-group">
							<label>ID</label>
							<input readonly="readonly" class="form-control" type="text" name="id">
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label>Proveedor</label>
							<?php
							$js = array(
								'id' => 'id_proveedor',
								'class' => 'select2 form-control custom-select',
							);
							?>
							<?= form_dropdown('id_proveedor', $select_proveedores, set_value('id_proveedor', @$id_proveedor), $js); ?>
							<?php echo form_error('id_proveedor', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
						</div>
					</div>
					<div class="col-md-3">
						<div class="form-group">
							<label>Nro de cuenta / acuerdo</label>
							<input type="text" class="form-control" placeholder="Nro de cuenta" name="nro_cuenta" value="<?php echo set_value('nro_cuenta', @$nro_cuenta); ?>">
							<?php echo form_error('nro_cuenta', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
						</div>
					</div>
					<div class="col-md-2">
						<div class="form-group">
							<label>Expediente</label>
							<input type="text" class="form-control" placeholder="Expediente" name="expediente" value="<?php echo set_value('expediente', @$indexador->expediente); ?>">
							<?php echo form_error('expediente', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
						</div>
					</div>
					<div class="col-md-2">
						<div class="form-group">
							<label>Acuerdo pago</label>
							<input type="text" class="form-control" placeholder="Acuerdo de pago" id="acuerdo_pago" name="acuerdo_pago" value="<?php echo @$acuerdo_pago; ?>">
							<?php echo form_error('acuerdo_pago', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
						</div>
					</div>
				</div>

				<div class="section-title">Estructura programatica</div>
				<div class="row">
					<div class="col-md-4">
						<div class="form-group">
							<label>Secretaria</label>
							<?php
							$js = array(
								'id' => 'select_secretaria',
								'class' => 'select2 form-control custom-select',
							);
							?>
							<?= form_dropdown('id_secretaria', $select_secretarias, set_value('id_secretaria', @$id_secretaria), $js); ?>
							<?php echo form_error('id_secretaria', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label>Programa</label>
							<?php
							$js = array(
								'id' => 'select_programa',
								'disabled' => 'disabled',
								'class' => 'select2 form-control custom-select',
							);
							?>
							<?= form_dropdown('id_programa', $select_programas, set_value('id_programa', @$seleccion_programa), $js, $programas_id_interno); ?>
							<?php echo form_error('id_programa', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label>Proyecto</label>
							<?php
							$js = array(
								'id' => 'select_proyecto',
								'disabled' => 'disabled',
								'class' => 'select2 form-control custom-select',
							);
							?>
							<?= form_dropdown('id_proyecto', $select_proyectos, set_value('id_proyecto', @$id_proyecto), $js); ?>
							<?php echo form_error('id_proyecto', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label>Dependencia</label>
							<?php
							$js = array(
								'id' => 'select_dependencia',
								'disabled' => 'disabled',
								'class' => 'select2 form-control custom-select',
							);
							?>
							<?= form_dropdown('id_dependencia', $select_dependencias, set_value('id_dependencia', @$id_dependencia), $js); ?>
							<?php echo form_error('id_dependencia', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
						</div>
					</div>
				</div>

				<div class="section-title">Pago y vencimientos</div>
				<div class="row align-items-end">
					<div class="col-md-3">
						<div class="form-group">
							<label>Tipo de pago</label>
							<?php
							$js = array(
								'required' => 'required',
								'id' => 'tipo_pago',
								'class' => 'select2 form-control custom-select',
							);
							?>
							<?= form_dropdown('tipo_pago', $select_tipo_pago, set_value('tipo_pago', @$tipo_pago), $js); ?>
							<?php echo form_error('tipo_pago', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
						</div>
					</div>
					<div class="col-md-3">
						<div class="form-group">
							<label>Periodicidad</label>
							<select id="periodicidad_meses" name="periodicidad_meses" class="form-control">
								<option value="1" <?= (int) @$periodicidad_meses === 1 ? 'selected' : '' ?>>Mensual</option>
								<option value="2" <?= (int) @$periodicidad_meses === 2 ? 'selected' : '' ?>>Bimestral</option>
							</select>
						</div>
					</div>
					<div class="col-md-3">
						<div class="form-group">
							<label>Dias de alerta</label>
							<input type="number" min="1" class="form-control" id="dias_alerta" name="dias_alerta" placeholder="Dias alerta" value="<?= (int) @$dias_alerta ?: 7 ?>">
						</div>
					</div>
					<div class="col-md-3">
						<div class="form-group mb-2">
							<label class="d-block">Control de vencimiento</label>
							<label class="mb-0">
								<input type="checkbox" id="control_vencimiento" name="control_vencimiento" value="1" <?= !isset($control_vencimiento) || (int) $control_vencimiento === 1 ? 'checked' : '' ?>>
								Incluir en calendario
							</label>
						</div>
					</div>
				</div>

				<div class="form-actions d-flex justify-content-end">
					<a href="<?= base_url('Admin/Indexaciones') ?>" class="btn btn-light mr-2">Cancelar</a>
					<button type="submit" class="<?= $this->BtnText ?> btn btn-primary">
						<i class="icon-floppy-disk"></i> <?= $this->BtnText ?> indexacion
					</button>
				</div>
				<?= form_close(); ?>
			</div>
		</div>
	<?php } ?>

	<div class="card">
		<div class="table-card-header d-flex justify-content-between align-items-center">
			<div>
				<h5 class="mb-0">Lista de indexadores</h5>
				<small>Cuentas activas asociadas a proveedor, dependencia y estructura programatica.</small>
			</div>
		</div>
		<div class="card-body p-0">
			<table id="indexaciones_dt" class="display table-bordered table-hover datatable-highlight">
				<thead>
					<tr>
						<th></th>
						<th></th>
						<th>Proveedor</th>
						<th>Exp</th>
						<th>Nro de cuenta</th>
						<th>Secretaria</th>
						<th>Programa</th>
						<th>Proyecto</th>
						<th>Dependencia</th>
						<th>Periodicidad</th>
						<th>Control vencimiento</th>
						<th>Acciones</th>
					</tr>
				</thead>
			</table>
		</div>
	</div>
</div>
