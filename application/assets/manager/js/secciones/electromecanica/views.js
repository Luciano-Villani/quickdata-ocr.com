function cargar(){

  console.log('carga');



}
setTimeout(function(){
  cargar();
}, 0);
$(document).ready(function () {
  console.log('ready');


var base_url = $("body").data("base_url");
var nro_cuenta = $("body").data("nro_cuenta");

  var dato = new FormData();
  dato.append("data_search", nro_cuenta);




  console.log("data");

  var dato = new FormData();
  dato.append("nro_cuenta", nro_cuenta);

var tabla_index = $('#indexaciones_dt').DataTable({
    dom: "frtip",
    "pageLength": 50,
    "order": [],	
    "autoWidth": true,
    "columnDefs": [
      
	{ visible: false, targets: [] }
    ],
    language: {
        url: base_url+ 'assets/manager/js/plugins/tables/translate/spanish.json'
    },
    processing: true,
    serverSide: true,
    // responsive: true,
    type: "POST",
    order: [1, "desc"],
    "ajax":{
        "data":{"nro_cuenta":nro_cuenta, 'table':'_indexaciones_canon'},
        "url": base_url + "Electromecanica/Lecturas/indexaciones_cuenta",
        "type": "POST",
     
    }
});
});