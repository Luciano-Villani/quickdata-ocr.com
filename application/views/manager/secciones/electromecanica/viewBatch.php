<style>
.tabla_view {
	width: calc(100vw - 330px) !important;
	margin-left: -80px !important;
}
.tabla-view-compact {
	padding: 0;
}
.lote-toolbar {
	align-items: center;
	background: #c6d2e3;
	display: flex;
	gap: 14px;
	justify-content: space-between;
	padding: 6px 10px;
}
.lote-toolbar .card-title {
	font-size: 18px;
	line-height: 1.1;
	margin: 0;
}
.lote-toolbar .btn {
	padding: 7px 14px;
}
.tabla_view .dataTables_wrapper {
	padding: 0;
}
.tabla_view .dt-buttons,
.tabla_view .dataTables_length,
.tabla_view .dataTables_filter {
	margin: 8px 10px;
}
.tabla_view .dataTables_filter {
	float: right;
}
.tabla_view .dataTables_scrollBody {
	cursor: grab;
	overflow-y: hidden !important;
}
.datatable-ajax tr :first-child {
	padding: 5px;
}
.tabla_view table.dataTable thead th,
.tabla_view table.dataTable tbody td {
	line-height: 1.15;
	padding-bottom: 8px !important;
	padding-top: 8px !important;
	vertical-align: middle;
}
.tabla_view .badge {
	line-height: 1;
	padding: 5px 7px;
}
.tabla_view .dataTables_info,
.tabla_view .dataTables_paginate {
	padding-bottom: 8px;
	padding-top: 8px;
}
</style>




<div class="container-fuid card tabla_view tabla-view-compact">
	<div class="lote-toolbar">
		<h5 class="card-title">Archivos del lote</h5>
		<div class="btn-group batch-filter-buttons" role="group" aria-label="Filtros del lote">
			<button type="button" class="btn btn-primary active" data-filter="">Todas</button>
			<button type="button" class="btn btn-light" data-filter="sin_index">Sin Index</button>
			<button type="button" class="btn btn-light" data-filter="errores">Errores de lectura</button>
			<button type="button" class="btn btn-light" data-filter="pendientes">Pendientes</button>
		</div>
	</div>

	<table class="datatable-ajax display table-bordered table-hover datatable-highlight no-footer  " style="width: auto">
		<thead>
        <tr>
		
					<th>Cuenta</th>
					<th>Medidor</th>
					<th>Nro de factura</th>
					<th>Período</th>
					<th>Fecha emisión</th>
					<th>Vencimiento</th>
					<th>Total importe</th>
					<th>Total vencido</th>
					<th>Consumo</th>
					<th>Index</th>
					<th>codigo prov</th>
					<th>Validacion</th>
					<th>Acciones</th>
					<th>ID</th>
			</tr>
		</thead>
		

	</table>
</div>
