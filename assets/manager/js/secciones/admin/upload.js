$(document).ready(function () {
  $("#intoText").html("*");

  $("#lotes_dt").dataTable().fnClearTable();
  $("#lotes_dt").dataTable().fnDestroy();
  dt();
});

function dt() {
  $("#lotes_dt").dataTable().fnDestroy();
  var base_url = $("body").data("base_url");
  var id_proveedor = $("#id_proveedor").val();

  var dato = new FormData();
  dato.append("id_proveedor", id_proveedor);

  var table = $("#lotes_dt").DataTable({
    'pageLenght':50,
    initComplete: function () {
      this.api()
          .columns()
          .every(function () {
              let column = this;

              // Create select element
              let select = document.createElement('select');
              select.add(new Option(''));
              column.footer().replaceChildren(select);

              // Apply listener for user change in value
              select.addEventListener('change', function () {
                  var val = DataTable.util.escapeRegex(select.value);

                  column
                      .search(val ? '^' + val + '$' : '', true, false)
                      .draw();
              });

              // Add list of options
              column
                  .data()
                  .unique()
                  .sort()
                  .each(function (d, j) {
                      select.add(new Option(d));
                  });
          });
  },
    // dom: "frtip",
    columnDefs: [
      {
        targets: -1,
        //			className: 'dt-body-right',
        // bSortable: false,
      },
      { visible: false, targets: [ ] },
    ],

    language: {
      url: base_url + "assets/manager/js/plugins/tables/translate/spanish.json",
    },
    processing: true,
    serverSide: true, 
    ajax: {
      data:{table:"_lotes",id_proveedor:$("#id_proveedor").val()},
      url: base_url + "Admin/Lecturas/lotes_dt/" + id_proveedor,
      type: "POST",
      error: function (jqXHR, textStatus, errorThrown) {
        alert(jqXHR.status + textStatus + errorThrown);
      },
    },
  });
}

function buscarInfoPanel() {
  console.log('buscarInfoPanel');
  var formData = new FormData();
  formData.append("id_proveedor", $("#id_proveedor").val());
  formData.append("code", $("#code").val());
  $.ajax({
    url: "getInfoPanel",
    type: "POST",
    contentType: false,
    dataType: 'json',
    data: formData,
    processData: false,
    cache: false,
    success: function (data) {
      $("div.header-elements").html(data);
    },
    error: function (request, error) {
      alert("Request: " + JSON.stringify(request));
    },
  });
}
function cerrarLote() {

  var formData = new FormData();
  formData.append("id_proveedor", $("#id_proveedor").val());
  formData.append("id_lote", $("#id_lote").val());
  formData.append("code_lote", $("#code").val());
  formData.append("cant", $("span#intoText").html());

  $.ajax({
    url: "cerrarLote",
    type: "POST",
    contentType: false,
    dataType: 'json',
    data: formData,
    processData: false,
    cache: false,
    success: function (data) {
      console.log('cerrarLote');
      console.log(data);
      $("div.header-elements").html(data);
      myDropzone.removeAllFiles();
  
    },
    error: function (request, error) {
      alert("Request: " + JSON.stringify(request));
    },
  });
}

Dropzone.options.fileMultiple = {
  addRemoveLinks: false,
  dictRemoveFile: "Quitar",
  // maxFiles: 20,
  autoProcessQueue: false,
  paramName: "file", // The name that will be used to transfer the file
  maxFilesize: 3, // MB
  url: "upload",
  acceptedFiles: "application/pdf",
  dragstart: function (e) {
    console.log("dragstart");
    console.log(e);
  },
  removedfile: function (file) {
    console.log('removedfile');
    file.previewElement.remove();
    if(this.files.length == 0){
      $("#Myheader-elements").html('');
    }else{
      $("body span#intoText").html(this.files.length);
    }
  },
  success() {
    console.log('succes');
    // $.unblockUI();
    // location.reload();
  },  successmultiple() {
    console.log('successmultiple');
  },
  
  // addedfile:function(file){
  // 	console.log('file');
  // 	console.log(file);
  // 	$("#total").html(this.files.length);
  // },
  init: function () {
    
    console.log('init');

    var submitButton = document.querySelector("div#Myheader-elements");
    myDropzone = this;
    submitButton.addEventListener("click", function () {
      // $.blockUI();
      myDropzone.processQueue();

    });
    this.on("drop", function (event) {
      console.log("drop");

      if ($("#Myheader-elements").html() == "") {
        // alert('1');
        buscarInfoPanel();
        $("body span#intoText").html(this.files.length);
      } else {
        $("body span#intoText").html(this.files.length);
      }
    });

    this.on("dragstart", function (file) {
      console.log("dragstart");
      console.log(file);
    }),
      this.on("addedfile", function (file) {
        console.log("addedfile");
        if ($("#Myheader-elements").html() == "") {
          // alert('1');
          buscarInfoPanel();
          $("body span#intoText").html(this.files.length);
        } else {
          $("body span#intoText").html(this.files.length);
        }
      

        // dt();

        $("#Accepteda").html(this.getAcceptedFiles().length);
        $("body > #Uploadinga").html(this.getUploadingFiles().length);
        $("#intoText").html(this.getQueuedFiles().length);
        $("#Rejecteda").html(this.getRejectedFiles().length);
        $("body > #intoText").html(this.files.length);

        $("#total").html(this.files.length);
      }),
      this.on("success", function (file, responseText) {
        $("#Accepteda").html(this.getAcceptedFiles().length);
        $("#Uploadinga").html(this.getUploadingFiles().length);
        $("#Queueda").html(this.getQueuedFiles().length);
        $("#Rejecteda").html(this.getRejectedFiles().length);
        $("#totala").html(this.files.length);
        // console.log(responseText);
      });
  },
  uploadprogress: function (file, progress, bytesSent) {
    console.log("uploadprogress");
    // // console.log(file);
    // console.log(progress);
  },
  queuecomplete: function (file, response) {
    console.log('queuecomplete function');
    // dt();
    // cerrarLote();
  
  },
  complete: function () {

    console.log('complete function');
 
  
  },
  sending: function (file, xhr, formData) {
    console.log('sending');
    formData.append("id_proveedor", $("#id_proveedor").val());
    formData.append("id_lote", $("#id_lote").val());
    formData.append("code_lote", $("#code").val());
    formData.append("cant", this.files.length);
  },
  // accept: function(file, done) {
  // //   if (file.name == "justinbieber.jpg") {
  // //     done("Naha, you don't.");
  // //   }
  // //   else { done('asdsaasa'); }
  // }
};

