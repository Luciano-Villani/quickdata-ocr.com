<div class="card">
	<div class="card-header header-elements-inline">
		<h5 class="card-title"><?= $this->router->fetch_class() ?></h5>

	</div>
	<div class="card-body">
<a class="btn bg-teal-400 " href="/Admin/Indexaciones/agregar">Agregar</a>

<!--		<table id="usuarios_dt" class="datatable-basic dataTable no-footer">-->
	</div>
		<table id="usuarios_dt" class="table datatable-show-all dataTable no-footer">
			<thead>
				<tr>
					<th>#</th>
					<th>Id interno	</th>
					<th>Nro de cuenta</th>
					<th>Secretaria</th>
					<th>Dependencia</th>
					<th>Programa</th>
					<th>Proyecto</th>
					<th>proveedor</th>
					<th>Fecha alta</th>
					<th>Acciones</th>
				</tr>
			</thead>

		</table>

</div>
