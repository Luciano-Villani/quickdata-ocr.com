// var dataArr = [];
//      $.get('Admin/Graphs', {
//       table:'_graph_api',
//       data_search:'',
//       length:-1,
//       start:'',
//     }, function(response) {
//             dataArr = JSON.parse(response);

//             console.log('response');
//             console.log(response);

//               initEchart(response);
//             });
// // make sure dataArr should be in array like [1,2,3],

// function initEchart(data){
//   var dom = document.getElementById('chart-container');
// var myChart2 = echarts.init(dom, null, {
//   renderer: 'canvas',
//   useDirtyRect: false
// });

// if (data && typeof data === 'object') {
//   myChart2.setOption(data);
// }

// window.addEventListener('resize', myChart2.resize);
// }
$(document).ready(function () {
  console.log("data");

  $("#depens").select2({
    ajax: {
      url: "Dependencias/get_dependencias",
      contentType: "application/json",
      dataType: "json",
      type: "POST",
      // delay: 250,
      data: {
        id: 1,
      },
      processResults: function (data) {
        console.log(data);
        var res = data.items.map(function (item) {
          return { id: item.id, text: item.name };
        });
        console.log("data");
        console.log(res);
        return {
          results: res,
        };
      },
    },
  });

  $('input[name="daterange2"]').daterangepicker(
    {
      showDropdowns: true,
      // autoUpdateInput: ,
      locale: {
        applyLabel: "Aplicar",
        cancelLabel: "Cancelar",
        format: "DD/MM/YYYY",
        customRangeLabel: "Búsqueda avanzada",
      },
      ranges: {
        Hoy: [moment(), moment()],
        Ayer: [moment().subtract(1, "days"), moment().subtract(1, "days")],
        "Ultimos 7 días": [moment().subtract(6, "days"), moment()],
        "Ultimos 30 días": [moment().subtract(29, "days"), moment()],
        "Este mes": [moment().startOf("month"), moment().endOf("month")],
        "Mes pasado": [
          moment().subtract(1, "month").startOf("month"),
          moment().subtract(1, "month").endOf("month"),
        ],
      },
    },
    function (start, end, label) {
      console.log(
        "New date range selected: " +
          start.format("YYYY-MM-DD") +
          " to " +
          end.format("YYYY-MM-DD") +
          " (predefined range: " +
          label +
          ")"
      );
    }
  );

  var json_url = "Admin/Graphs";

  var dom = document.getElementById("chart-container");
  var myChart2 = echarts.init(dom, null, {
    renderer: "canvas",
    useDirtyRect: false,
  });
  var app = {};
});
