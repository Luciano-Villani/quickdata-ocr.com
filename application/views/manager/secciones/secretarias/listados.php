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
		
			


			<?php echo form_open(base_url('Admin/Secretarias'),array('id'=>'form-validate-jquery')); ?>
			<div class="row">

				<div class="col-md-2">
					<div class="form-group form-group-feedback form-group-feedback-right">
						<input required type="text" class="form-control  " placeholder="Jurisdicción - Major" name="major" value="">
						<div class="form-control-feedback">
							<i class=" icon-pencil3 text-muted"></i>
						</div>
						<?php echo form_error('major', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
					</div>
				</div>
			

			
				<div class="col-md-6">
					<div class="form-group form-group-feedback form-group-feedback-right">
						<input required type="text" class="form-control  " placeholder="Jurisdicción - Descripción" name="secretaria" value="<?php echo set_value('secretaria');?>">
						<div class="form-control-feedback">
							<i class=" icon-pencil3 text-muted"></i>
						</div>
						<?php echo form_error('secretaria', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
					</div>
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

<table id="secretarias_dt" class="table-bordered table-hover datatable-highlight">
		<thead>
			<tr>
			
				<th>Jurisdicción - Major</th>
				<th>Jurisdicción - Descripcion</th>
		
				<th>Acciones</th>
			</tr>
		</thead>

	</table>
</div>

<script>

  </script>