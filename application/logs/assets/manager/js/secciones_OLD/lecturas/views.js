$(document).ready(function () {

var base_url = $("body").data("base_url");
var nro_cuenta = $("body").data("nro_cuenta");

  var dato = new FormData();
  dato.append("nro_cuenta", nro_cuenta);

  $("#indexaciones_dtss").DataTable({
    dom: "frtip",
    columnDefs: [
      {
        targets: -1,
        //			className: 'dt-body-right',
        bSortable: false,
      },
      { visible: false, targets: [] }
    ],
    language: {
      url: base_url + "assets/manager/js/plugins/tables/translate/spanish.json",
    },
    serverSide: true,
   
    ajax: {
      data: dato,
      processData: false,
      cache: false,
      url: base_url + "Admin/Lecturas/indexaciones_dt/"+nro_cuenta,
      type: "POST",
      error: function (jqXHR, textStatus, errorThrown) {
        alert(jqXHR.status + textStatus + errorThrown);
      },
    },
  });

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

    "ajax": {
        "data":{"nro_cuenta":nro_cuenta},
        "url": base_url + "Admin/Lecturas/indexaciones_dt",
        "type": "POST"
    }
});
});