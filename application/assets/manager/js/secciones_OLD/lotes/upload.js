Dropzone.options.fileMultiple = {
  autoProcessQueue: false,
  maxFiles: 10,
  parallelUploads: 10,
  paramName: "file",
  url: "/Admin/Lotes/Upload",
  init: function () {
    this.on("uploadprogress", function (file, progress) {
      console.log("uploadi");
      console.log(progress);

      var progressBar = file.previewElement.querySelector(".dz-upload");
      progressBar.style.width = progress + "%";
      progressBar.innerHTML = progress + "%";
    });

    this.on("success", function (file, response) {
      console.log("succes response");
      response = JSON.parse(response);
      console.log(response.status);
      alertas(response);

      if (response.status == "error") {
        myDropzone.removeFile(file);
      }

      var progressBar = file.previewElement.querySelector(".dz-upload");
      progressBar.classList.add("bg-success");
      progressBar.innerHTML = "Subido";

    });
    myDropzone = this;
    var submitButton = document.querySelector("div#Myheader-elements");

    submitButton.addEventListener("click", function () {
      //   var myDropzone = Dropzone.forElement(".dropzone");
      myDropzone.processQueue();
      // $.blockUI();
    });
  },

  sending: function (file, xhr, formData) {
    console.log("sending");
    formData.append("id_proveedor", $("#id_proveedor").val());
    formData.append("code_lote", $("#code").val());
    formData.append("cant", this.files.length);
  },
  queuecomplete: function (file, response) {
    console.log('queuecomplete function');
    dt();
    // cerrarLote();
  
  },
  //   accept: function(file, done) {
  //     $("body span#intoText").html(this.files.length);
  // }
};

$(document).ready(function () {
  var base_url = $("body").data("base_url");
  dt();
});

function dt() {
  $(".datatable-ajax").dataTable().fnDestroy();
  var base_url = $("body").data("base_url");
  $(".datatable-ajax").dataTable({
    language: {
      url: base_url + "assets/manager/js/plugins/tables/translate/spanish.json",
    },
    processing: true,
    serverSide: true,
    ajax: {
      data: { table: "_lotes", id_proveedor: $("#id_proveedor").val() },
      url: "/Admin/Lotes/lotes_dt/",
      type: "POST",
      error: function (jqXHR, textStatus, errorThrown) {
        alert(jqXHR.status + textStatus + errorThrown);
      },
    },
    initComplete: function () {
      this.api()
        .columns()
        .every(function () {
          var column = this;
          var select = $(
            '<select class="filter-select" data-placeholder="Filter"><option value=""></option></select>'
          )
            .appendTo($(column.footer()).not(":last-child").empty())
            .on("change", function () {
              var val = $.fn.dataTable.util.escapeRegex($(this).val());

              column.search(val ? "^" + val + "$" : "", true, false).draw();
            });

          column
            .data()
            .unique()
            .sort()
            .each(function (d, j) {
              select.append('<option value="' + d + '">' + d + "</option>");
            });
        });
    },
  });
}

function cerrarLote() {
    alert();
      var formData = new FormData();
      formData.append("id_proveedor", $("#id_proveedor").val());
      formData.append("id_lote", $("#id_lote").val());
      formData.append("code_lote", $("#code").val());
      formData.append("cant", $("span#intoText").html());
    
      $.ajax({
        url: "/Admin/Lotes/Generate",
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
      }); // cerrar lote
    }
