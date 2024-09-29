<?php if ($this->ion_auth->is_electro() ||$this->ion_auth->is_super() || $this->ion_auth->is_admin()) { ?>
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
		
			<?php echo form_open(base_url('Electromecanica/Dependencias'), array('id' => 'form-validate-jquery')); ?>
			
			<!-- <div class="row">
				<div class="col-md-1">
					<input readonly type="text" class="form-control"  name="id_dependencia" value="<?php echo @$id_dependencia;?>">
				</div>
			</div> -->
			
			<div class="row">
				<div class="col-md-3">
					<div class="form-group form-group-feedback  ">
						<div class="form-control-feedback">
						</div>
						<?php
						$js = array(
							'required' => 'required',
							'id' => 'id_secretaria',
							'class' => ' select2 form-control custom-select ',
						);
						?>

						<?= form_dropdown('id_secretaria', $select_secretarias, @$id_secretaria, $js); ?>
						<?php echo form_error('id_secretaria', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>

					</div>
				</div>
			
				<div class="col-md-3">
					<div class="form-group form-group-feedback form-group-feedback-right">
						<input required type="text" class="form-control  " placeholder="Nombre Dependencia" name="dependencia" value="<?php echo @$dependencia;?>">
						<div class="form-control-feedback">
							<i class=" icon-pencil3 text-muted"></i>
						</div>
						<?php echo form_error('dependencia', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
					</div>
				</div>	
				
				<div class="col-md-3">
					<div class="form-group form-group-feedback form-group-feedback-right">
						<input required type="text" class="form-control  " placeholder="Dirección de Dependencia" name="direccion" value="<?php echo @$direccion;?>">
						<div class="form-control-feedback">
							<i class=" icon-pencil3 text-muted"></i>
						</div>
						<?php echo form_error('direccion', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
					</div>
				</div>
				<div class="col-md-2">
					<button type="submit" class="btn btn-filtrar"><b><i
					class="icon-upload"></i></b> Guardar</button>
					<?= form_close(); ?>
				</div>	
			
						
				
			</div>

</div>
	</div>
<?php } ?>
<div class="card">
<h5 class="card-title bg-titulo text-center text-dark"> Lista de <?= ucfirst($this->router->fetch_class()) ?></h5>
	<div class="row card-body">

	<div class="col-4 form-group form-group-feedback  ">
						<div class="form-control-feedback">
						</div>
						<?php
						$js = array(
							'required' => 'required',
							'id' => 'search_secretaria',
							'class' => ' select2 form-control custom-select ',
						);
						?>
						<?= form_dropdown('search_secretaria', $select_secretarias, @$id_secretaria, $js); ?>
					</div>	
		<!--<a class="btn bg-teal-400 " href="/Admin/Dependencias/agregar">Agregar</a> -->
		<!--		<table id="usuarios_dt" class="datatable-basic dataTable no-footer">-->
	</div>
	<table id="dependencias_dt" class="table-bordered table-hover datatable-highlight " style="width: 100%">
		<thead>
			<tr>
				<th></th>
				<th>Secretaría</th>
				<th>Dependencia</th>
				<th>Dirección</th>
				<th></th>
			</tr>
		</thead>
		

	</table>

</div>