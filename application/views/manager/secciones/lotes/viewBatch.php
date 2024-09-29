
<style>
.tabla_view {
		width: 1250px !important;
		margin-left: -80px !important;

	
	}
</style>



<div class="container-fuid card tabla_view">
	<div class="card-header header-elements-inline">
		<h5 class="card-title">Archivos del lote</h5>
		<p>Datos del lote.</p>
	</div>

	<style>

.datatable-ajax tr :first-child {

 
  padding: 5px;
}
	</style>
	<table class="datatable-ajax display table-bordered table-hover datatable-highlight no-footer  " style="width: auto">
		<thead>
        <tr>
		<th>
			<input type="checkbox" id="selectAllPost" class="select-checkbox" data-tabla="dataTable_lecturas">
		</th>
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
					<th>Archivo</th>
					<th>Acciones</th>
			</tr>
		</thead>
		

	</table>
</div>