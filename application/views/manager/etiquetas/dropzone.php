
	<div class="row">
		<div class="col-8">
		<div class="card-header header-elements-inline">
		<h5 class="card-title">Carga máxima 10 archivos por lote</h5>
		<input type="text" id="id_proveedor"name="id_proveedor" readonly value="<?= $proveedor->id?>">
			<form action="/Admin/Lotes/Upload" class="dropzone dz-clickable" id="fileMultiple">
				<div class="dz-default dz-message"><span>Drop files  <span>or CLICK</span></span></div>
				<div class="fallback">
					<input name="file" type="file" multiple />
				</div>
             
			</form>
	</div>
		</div>
		<div class="col-4">

		</div>
	</div>

