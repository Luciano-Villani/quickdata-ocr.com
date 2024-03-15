$(document).ready(function () {
  var base_url = $("body").data("base_url");

  var table = $("#lecturas_dt").DataTable({
    dom: "frtip",
    columnDefs: [
      {
        targets: -1,
        //			className: 'dt-body-right',
        bSortable: false,
      },
      { visible: false, targets: [0] }
    ],
    language: {
      url: base_url + "assets/manager/js/plugins/tables/translate/spanish.json",
    },
    serverSide: true,
    type: "POST",
    dataSrc: "",
    ajax: {
      url: base_url + "lecturas/list_dt",
      type: "POST",
      error: function (jqXHR, textStatus, errorThrown) {
        alert(jqXHR.status + textStatus + errorThrown);
      },
    },
  });

  $("#myProgramForm").on("change", "select#id_proveedor", function () {
		if($(this).val() > 0){
			$( "#myProgramForm" ).trigger( "submit" );
		}
  });
  $("#myProgramForm").on("change", "select#did_proveedor", function () {

    var dato = new FormData();
    dato.append("id", $(this).val());

    $.ajax({
      type: "POST",
      contentType: false,
      //    				dataType: 'json',
      data: dato,
      processData: false,
      cache: false,
      beforeSend: function () {
        $("#select_dependencia ").empty();
      },
      url: $("body").data("base_url") + "Admin/Dependencias/get_dependencias",
      success: function (result) {
        var obj = jQuery.parseJSON(result);
        console.log("resultwwwww");
        console.log(Object.keys(obj.data).length);
        console.log(result);

        if (Object.keys(obj.data).length > 0) {
          $("#select_dependencia").removeAttr("disabled");
          $("#select_dependencia").append(
            '<option selected value="0">SELECCIONE DEPENDENCIA</option>'
          );

          $.each(obj.data, function (id, value) {
            $("#select_dependencia").append(
              '<option value="' +
                value["id"] +
                '">' +
                value["dependencia"] +
                "</option>"
            );
          });
        } else {
          $("#select_dependencia").append(
            '<option selected value="0">SIN DEPENDENCIA</option>'
          );
          $("#select_dependencia").attr("disabled", "disabled");
        }

        //    					toastr.success('Registro Editado correctamente!', 'Categorías');
      },
      error: function (xhr, errmsg, err) {
        console.log(xhr.status + ": " + xhr.responseText);
      },
    });
  });


  document.querySelectorAll('a.toggle-vis').forEach((el) => {

    el.addEventListener('click', function (e) {
        e.preventDefault();
 
        let columnIdx = e.target.getAttribute('data-column');
        let column = table.column(columnIdx);
 
        // Toggle the visibility
        column.visible(!column.visible());
    });
});
});

Dropzone.options.fileMultiple = {
  // camelized version of the `id`

  autoProcessQueue: true,
  paramName: "file", // The name that will be used to transfer the file
  maxFilesize: 3, // MB
  url: "/Lecturas/upload",
  acceptedFiles: "application/pdf",
  dragstart(e) {
    alert(e);
  },
  // addedfile:function(file){
  // 	console.log('file');
  // 	console.log(file);
  // 	$("#total").html(this.files.length);
  // },
  init: function () {
    this.on("addedfile", function (file, responseText) {
      $("#Accepteda").html(this.getAcceptedFiles().length);
      $("#Uploadinga").html(this.getUploadingFiles().length);
      $("#Queueda").html(this.getQueuedFiles().length);
      $("#Rejecteda").html(this.getRejectedFiles().length);
      $("#totala").html(this.files.length);

      $("#total").html(this.files.length);
    }),
      this.on("success", function (file, responseText) {
        $("#Accepteda").html(this.getAcceptedFiles().length);
        $("#Uploadinga").html(this.getUploadingFiles().length);
        $("#Queueda").html(this.getQueuedFiles().length);
        $("#Rejecteda").html(this.getRejectedFiles().length);
        $("#totala").html(this.files.length);
        console.log(responseText);
      });
  },
  uploadprogress: function (file, progress, bytesSent) {
    console.log(file);
    console.log(bytesSent);
  },
  queuecomplete: function (file, response) {},
  // accept: function(file, done) {
  // //   if (file.name == "justinbieber.jpg") {
  // //     done("Naha, you don't.");
  // //   }
  // //   else { done('asdsaasa'); }
  // }
};
// Dropzone.options.fileMultiple = {
// 	dictDefaultMessage: 'Drop fileAAAAAA to upload <span>or CLICK</span>',
// 	complete: function(){
// 		alert();
// 	},
// 	init: function() {
// 	  this.on("addedfile", file => {
// 		console.log("A file has been added");
// 	  });
// 	}
//   };
