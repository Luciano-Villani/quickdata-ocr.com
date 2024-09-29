<div class="card ">
	<div class="card-header header-elements-inline">
		<h5 class="card-title">
			<?= $this->data['page_title'] ?> -
			<?= $proveedor->nombre ?>
		</h5>

		<div id="Myheader-elements" class="header-elements">
			<div>

				<input type="" id="code" value="<?= $code?>">
				<input type="" id="id_lote" value="">
				<input type="" id="cantidad_archivos" value="">
			</div>
<button  type="button" class="mt-3 btn bg-teal-400 btn-labeled"><b><i class=" icon-database-insert"></i></b> Lote: <span id="intoLote"><?= $code?></span> - Procesar <span id="intoText"></span> archivos </button>
		</div>
	</div>
</div>
<?= $dropzone ?>
<div class="card">
	<div class="card-header header-elements-inline">
		<h5 class="card-title">Datos en base - Lotes</h5>
	</div>
	<table class="table table-bordered table-hover datatable-highlight datatable-ajax " style="">
		<thead>
			<tr>
				<th>id</th>
				<th>Proveedor</th>
				<th>Código lote</th>
				<th>Fecha</th>
				<th>Fecha indexado</th>
				<th>Consolidado</th>
				<th>cant de archivos</th>
				<th>usuario</th>
				<th>Acciones</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th>id</th>
				<th>Proveedor</th>
				<th>Código lote</th>
				<th>Fecha</th>
				<th>usuario</th>
				<th>Fecha indexado</th>
				<th>Consolidado</th>
				<th>cantidad</th>
				<th>Acciones</th>
			</tr>
		</tfoot>

	</table>
</div>