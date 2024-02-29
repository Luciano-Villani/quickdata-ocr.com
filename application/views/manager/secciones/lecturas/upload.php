<div class="card ">
	<div class="card-header header-elements-inline">
		<h5 class="card-title">
			<?= $this->data['page_title'] ?> -
			<?= $proveedor->nombre ?>
		</h5>

		<div id="Myheader-elements" class="header-elements"></div>
	</div>
</div>
<?= $dropzone ?>
<div class="card">
	<div class="card-header header-elements-inline">
		<h5 class="card-title">Datos en base - Lotes</h5>
	</div>
	<table class="table datatable-ajax  datatable" style="">
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