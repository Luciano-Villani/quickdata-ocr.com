$(document).ready(function () {

	var base_url = $("body").data('base_url');


	$("form#form-validate-jquery").validate({
		rules:{
			tipo_pago:{
				required: true,
				min:1
			},			
			codigo:"required",
			nombre:"required",
			objeto_gasto:"required",
			detalle_gasto:"required",
			unidad_medida:"required",
			urlapi:"required",
		},
		messages:{
			tipo_pago: "Seleccione una opción",
			codigo: "El campo es requerido",
			nombre: "El campo es requerido",
			objeto_gasto: "El campo es requerido",
			detalle_gasto: "El campo es requerido",
			unidad_medida: "El campo es requerido",
			urlapi: "El campo es requerido",
		}
	});
	

	var mi_tabla = $('#proveedores_dt').DataTable({
		dom: 'frtip',
		columnDefs: [{ 
            orderable: false,
            width: '100px',
            targets: [ 0 ]
        },
		{ visible: false, targets: [0] }],
		language: {
			url: base_url + 'assets/manager/js/plugins/tables/translate/spanish.json'
		},
		serverSide: true,
		"type": "POST",
		
		ajax: {
			url: base_url + 'proveedores/list_proveedores_dt',
			type: 'POST',
			error: function (jqXHR, textStatus, errorThrown) {
				alert(jqXHR.status + textStatus + errorThrown);
			}
		},
		initComplete: function () {
			this.api()
				.columns()
				.every(function () {
					let column = this;
					let title = 'titulo';
	 
					// Create input element
					let input = document.createElement('input');
					input.placeholder = title;
				
	 
					// Event listener for user input
					input.addEventListener('keyup', () => {
						if (column.search() !== this.value) {
							column.search(input.value).draw();
						}
					});
				});
		}
	});
	$('#myInput').on( 'keyup', function () {
		table.search( this.value ).draw();
	} );
});
