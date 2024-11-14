function newexportaction(e, dt, button, config) {
  var self = this;
  var oldStart = dt.settings()[0]._iDisplayStart;
  
  dt.one("preXhr", function (e, s, data) {
    data.start = 0;
    data.length = 2147483647;
    
    dt.one("preDraw", function (e, settings) {
      if (button[0].className.indexOf("buttons-copy") >= 0) {
        $.fn.dataTable.ext.buttons.copyHtml5.action.call(self, e, dt, button, config);
      } else if (button[0].className.indexOf("buttons-excel") >= 0) {
        $.fn.dataTable.ext.buttons.excelHtml5.available(dt, config)
          ? $.fn.dataTable.ext.buttons.excelHtml5.action.call(self, e, dt, button, config)
          : $.fn.dataTable.ext.buttons.excelFlash.action.call(self, e, dt, button, config);
      } else if (button[0].className.indexOf("buttons-csv") >= 0) {
        $.fn.dataTable.ext.buttons.csvHtml5.available(dt, config)
          ? $.fn.dataTable.ext.buttons.csvHtml5.action.call(self, e, dt, button, config)
          : $.fn.dataTable.ext.buttons.csvFlash.action.call(self, e, dt, button, config);
      } else if (button[0].className.indexOf("buttons-pdf") >= 0) {
        $.fn.dataTable.ext.buttons.pdfHtml5.available(dt, config)
          ? $.fn.dataTable.ext.buttons.pdfHtml5.action.call(self, e, dt, button, config)
          : $.fn.dataTable.ext.buttons.pdfFlash.action.call(self, e, dt, button, config);
      } else if (button[0].className.indexOf("buttons-print") >= 0) {
        $.fn.dataTable.ext.buttons.print.action(e, dt, button, config);
      }
      
      dt.one("preXhr", function (e, s, data) {
        settings._iDisplayStart = oldStart;
        data.start = oldStart;
      });
      
      setTimeout(dt.ajax.reload, 0);
      return false;
    });
  });
  
  dt.ajax.reload();
}




function initDatatable(search = false, type = 0) {
  var prove = $("#id_proveedor").val() || [];
  var fecha = $("#tipo-fecha").is(":checked") ? $("#daterange2").val() : false;
  var mes_fc = $('#id_mes_fc').val() || [];
  var anio_fc = $('#id_anio_fc').val() && $('#id_anio_fc').val().length > 0 ? $('#id_anio_fc').val() : null;
  var cosfiFilter = $('#cosfi_filter').is(':checked');
  
  var consFilter = $('#cons_filter').is(':checked');
  var const3Filter = $('#const3_filter').is(':checked');

  var tgfiFilter = $('#tgfi_filter').is(':checked');

  // Calcula la altura disponible dinámicamente
  let tableHeight = Math.min($(window).height() - $('#consolidados_dt').offset().top - 50, 450);

  $("#consolidados_dt").DataTable().destroy();

  var table = $("#consolidados_dt").DataTable({
      fixedHeader: {
          header: true,
      },
      dom: "Blfrtip",
      scrollX: true,
      scrollCollapse: true,
      scrollY: tableHeight + "px",
      lengthMenu: [
          [10, 25, 50, 100, -1],
          [10, 25, 50, 100, "All"],
      ],
      pageLength: 50,
      buttons: [
          {
              extend: "excelHtml5",
              exportOptions: {
                  columns: ":visible",
              },
              text: "Excel",
              titleAttr: "Excel",
              action: newexportaction,
              className: "d-none",
          },
          {
              text: 'Ver / Ocultar Columnas',
              className: 'btn custom-colvis-menu button',
              action: function (e, dt, button, config) {
                  // Crear el menú manualmente
                  let menu = $('<div class="custom-colvis-menu"></div>');

                   // Agregar botones de visibilidad de columnas
                    dt.columns().every(function (index) {
                      if (index > 0 && index < dt.columns().count() - 1) { // Excluye la primera columna y la última
                          let column = this;
                          let btn = $('<button class="btn btn-info">' + column.header().textContent + '</button>')
                              .on('click', function () {
                                  // Cambiar visibilidad de la columna
                                  column.visible(!column.visible());
                                  updateButtonClass($(this), column.visible());
                              });

                          // Actualizar clase del botón según la visibilidad inicial
                          updateButtonClass(btn, column.visible());
                          menu.append(btn);
                      }
                  });

                  // Mostrar el menú
                  menu.appendTo('body').css({
                      position: 'absolute',
                      left: button.offset().left,
                      top: button.offset().top + button.outerHeight(),
                  }).fadeIn();

                  // Cerrar el menú al hacer clic fuera
                  setTimeout(function() {
                      $(document).on('click.customColvis', function (event) {
                          if (!$(event.target).closest('.custom-colvis-menu').length && !$(event.target).is(button)) {
                              menu.fadeOut(function () {
                                  $(this).remove();
                              });
                              $(document).off('click.customColvis'); // Desvincular el evento
                          }
                      });
                  }, 0);
              }
          }
      ],
      columnDefs: [
          { targets: [0], title: "Proveedor", data: 0, visible: false },
          {
            targets: [1],
            title: "Medidor",
            data: 48,
            orderable: false,
            render: function (data, type, row) {
                if (/T3|BT/i.test(data)) { 
                    return "T3";
                } else if (/T1/i.test(data)) {
                    return "T1";
                } else if (/T2/i.test(data)) {
                    return "T2";
                }
                return data;
            }
        },
        {
          targets: [2], // Nueva columna "Subtarifa"
          title: "Categoría",
          data: 48,
          orderable: false,
          render: function (data, type, row) {
              const match = data.match(/T[1-3]\s*-\s*(\w+)/i);
              if (match) {
                  return match[1]; // Retorna el valor después de T1-, T2- o T3-
              } else if (/T2/i.test(data)) {
                  return "0"; // Si es T2, retorna "T2"
              } else if (/T3|BT/i.test(data)) {
                  return "0"; // Si es T3, retorna "T3"
              }
              return "0"; // Por defecto, retorna "0"
          }
      },
      {
        targets: [3], // Nueva columna "tension"
        title: "Tensión",
        data: 48,
        orderable: false,
        render: function (data, type, row) {
            // Si el valor contiene T1 o T2
            if (/T1|T2/i.test(data)) {
                return "0"; // Para T1-xx o T2 retorna 0
            }
    
            // Si es T3 y contiene "S BT" o "BT"
            const matchT3 = data.match(/T3[\s*-]*(.*)/i);
            if (matchT3) {
                return matchT3[1]; // Retorna lo que sigue después de T3 (por ejemplo, "S BT" o "BT")
            }
    
            return "0"; // Por defecto, retorna "0" si no coincide con ninguno de los casos anteriores
        }
    },
    

  

          
    { targets: [4], title: "Nro factura", data: 1, orderable: false },
    { targets: [5], title: "Nro cuenta", data: 2, width: "20px", orderable: false },
    { targets: [6], title: "Medidor", data: 3, orderable: false },
    {
      targets: [7], // Índice de la columna "Dependencia"
      title: "Dependencia",
      data: 4,
      orderable: false,
      createdCell: function (td, cellData) {
          $(td).css({
              'max-width': '150px', // Establecer max-width
              'overflow': 'hidden', // Ocultar desbordamiento
              'white-space': 'nowrap', // No permitir salto de línea
              'text-overflow': 'ellipsis' // Agregar puntos suspensivos si es necesario
          });
  
          // Agregar el tooltip con el texto completo
          $(td).attr('title', cellData);
      }
  },
  
    { 
      targets: [8], 
      title: "Dirección de Consumo", 
      data: 5, 
      orderable: false,
      createdCell: function (td, cellData) {
          $(td).css({
              'max-width': '150px',
              'overflow': 'hidden',
              'text-overflow': 'ellipsis',
              'white-space': 'nowrap'
          });
  
          // Agregar el tooltip con el texto completo
          $(td).attr('title', cellData);
      }
  },
  
        { 
          targets: [9], 
          title: "Nombre Cliente", 
          data: 6, 
          orderable: false,
          createdCell: function (td, cellData) {
              $(td).css({
                  'max-width': '150px',
                  'overflow': 'hidden',
                  'text-overflow': 'ellipsis',
                  'white-space': 'nowrap'
              });
              // Agregar el tooltip con el texto completo
              $(td).attr('title', cellData);
          }
      },
      
              
        { targets: [10], title: "Cons kWh/kW", data: 7 ,orderable: false },
        { targets: [11], title: "Cosfi", data: 9 ,orderable: false },
        { targets: [12], title: "Tgfi", data: 10 ,orderable: false },
        { targets: [13], title: "E Activa kWh", data: 53 ,orderable: false },
        { targets: [14], title: "E Reactiva kVArh", data: 54 ,orderable: false },
        { targets: [15], title: "Importe $", data: 11 ,orderable: false },
        { targets: [16], title: "Vencimiento", data: 14 ,orderable: false },
        { targets: [17], title: "Impuestos $", data: 15 ,orderable: false },
        { targets: [18], title: "Bimestre", data: 16 ,orderable: false },
        { targets: [19], title: "Liquidación", data: 17 ,orderable: false },
        { targets: [20], title: "P Contr Kw", data: 56 ,orderable: false },
        { targets: [21], title: "Cargo Fijo $", data: 19 ,orderable: false },
        { targets: [22], title: "Cargo Var $", data: 20 , visible: false ,orderable: false },
        { targets: [23], title: "Cargo Var > $", data: 21 , visible: false ,orderable: false },
        { targets: [24], title: "Otros Conceptos $", data: 22 ,orderable: false },
        { targets: [25], title: "Conceptos Eléctricos $", data: 23 ,orderable: false },
        { targets: [26], title: "P Excedida", data: 61 ,orderable: false },
        { targets: [27], title: "Pot Punta", data: 25 ,orderable: false },
        { targets: [28], title: "Pot Fuera Punta", data: 26 ,orderable: false },
        { targets: [29], title: "Energía Punta", data: 27 ,orderable: false },
        { targets: [30], title: "Energía Resto", data: 28 ,orderable: false },
        { targets: [31], title: "Energía Valle", data: 29 ,orderable: false },
        { targets: [32], title: "Energía Reac Act", data: 30 ,orderable: false },
        { targets: [33], title: "Cargo Pot Contratada $", data: 31 ,orderable: false },
        { targets: [34], title: "Cargo Pot Ad $", data: 32, orderable: false },
        { targets: [35], title: "Cargo Pot Excedida $", data: 33 ,orderable: false },
        { targets: [36], title: "Recargo TGFI $", data: 34 ,orderable: false },
        { targets: [37], title: "Cons.Pico Vigente", data: 35 ,orderable: false },
        { targets: [38], title: "Con.Valle Vigente", data: 39 , visible: false ,orderable: false }, //ojo
        { targets: [39], title: "Cargo Pico $", data: 36 , orderable: false }, //t3
        { targets: [40], title: "Cargo Resto $", data: 38 ,orderable: false },
        { targets: [41], title: "Cons.Resto Vigente", data: 37, orderable: false },
        { targets: [42], title: "Cargo Valle $", data: 40 ,orderable: false },
        { targets: [43], title: "E Actual", data: 41 ,orderable: false },
        { targets: [44], title: "Cargo Contratado", data: 42 ,orderable: false },
        { targets: [45], title: "Cargo Adquirida $", data: 43 ,orderable: false },
        { targets: [46], title: "Cargo Excedidaente $", data: 44 ,orderable: false },
        { targets: [47], title: "Cargo Variable $", data: 45 ,orderable: false },
        { targets: [48], title: "Total Vencido $", data: 46 ,orderable: false },
        { targets: [49], title: "E Reactiva kVArh", data: 47 ,orderable: false },
        { targets: [50], title: "P Registrada", data: 57 ,orderable: false },
        { targets: [51], title: "U.Med", data: 8, visible: false ,orderable: false },
        { targets: [52], title: "Días Cons", data: 49 ,orderable: false },
        { targets: [53], title: "Días Comp", data: 50 ,orderable: false },
        { targets: [54], title: "Cons DC kWh", data: 51 ,orderable: false },
        {
          targets: [55],
          title: "Período Consumo",
          data: 52,
          orderable: false,
          createdCell: function(td) {
              $(td).css('max-width', '155px'); // Ajusta el ancho máximo a 150px
          }
      },
        { targets: [56], title: "Mes Fc", data: 12 ,orderable: false },
        { targets: [57], title: "Año Fc", data: 13 ,orderable: false },
        { targets: [58], title: "Subsidio", data: 55 ,orderable: false },
        { targets: [59], title: "Car Var Hasta kw", visible: false, data: 18 ,orderable: false },
        { targets: [60], title: "Cons.Pico Anterior", data: 58 ,orderable: false },
        { targets: [61], title: "Cons.Resto Anterior", data: 59 ,orderable: false },
        { targets: [62], title: "Cons.Valle Anterior", data: 60 ,orderable: false },
        
        { targets: [63], title: "Energía Inyectada", data: 24 ,orderable: false },
        { targets: [64], title: "Cargo Cant", data: 62 ,orderable: false },


        { targets: [65], title: "Acc.", data: 63 ,orderable: false },
        

        
    
      ],
      language: {
          url: "/assets/manager/js/plugins/tables/translate/spanish.json",
      },
      processing: true,
      serverSide: true,
      order: false,
      ajax: {
          data: {
              type: type,
              table: "_consolidados_canon",
              data_search: search,
              id_proveedor: prove,
              fecha: fecha,
              mes_fc: mes_fc,
              anio_fc: anio_fc,
              cos_fi: cosfiFilter,
              consumo: consFilter,
              p_registrada: const3Filter,

              tg_fi: tgfiFilter
          },
          url: "/Electromecanica/Consolidados/list_dt_canon",
          type: "POST",
          dataSrc: function (json) {
            // Aquí agregas el console.log para depurar los índices del array
            //console.log("Datos recibidos desde el servidor:", json);

            // Si los datos recibidos son arrays de objetos, por ejemplo, podemos iterar
            json.data.forEach((row, index) => {
              //  console.log(`Índice ${index}:`, row);
            });

            // Devuelve los datos para el DataTable
            return json.data;
        }
    },
});
     


  // Función para actualizar la clase del botón según la visibilidad
function updateButtonClass(button, isVisible) {
  if (isVisible) {
      button.addClass('custom-colvis-menu button'); // Columna visible
      button.css({
          backgroundColor: '#113966', // Color de fondo original
          color: 'white' // Color del texto original
      });
  } else {
      button.removeClass('custom-colvis-menu button'); // Columna oculta
      button.css({
          backgroundColor: '#5E5E5E', // Color de fondo para columna oculta
          color: 'white' // Color del texto para columna oculta
      });
  }
}

    

    

    $(document).ready(function () {
      // Inicializar DataTable
      var table = $('#consolidados_dt').DataTable();
    
      // Función para aplicar visibilidad a las columnas según el proveedor seleccionado
      function aplicarVisibilidad() {
        var selectedProveedor = $("#id_proveedor").val(); // Obtener proveedor seleccionado
        //console.log(selectedProveedor);
       // console.log("ejecutando visibilidad - Proveedor seleccionado:", selectedProveedor);
    
        if (table.columns().count() > 10) { // Verificar que existen al menos 11 columnas
          if (selectedProveedor == '1') {
            table.column(3).visible(false); // Tension
            table.column(12).visible(false);  // Tgfi
            table.column(13).visible(false);
            table.column(14).visible(false);
            table.column(20).visible(false);
            table.column(26).visible(false);   //P excedida T3
            table.column(27).visible(false);   // Pot Punta
            table.column(28).visible(false);   // Pot Fuera Punta Cons
            table.column(29).visible(false);   // Energía Punta Act
            table.column(30).visible(false);   // Energía Resto Act
            table.column(31).visible(false);   // Energía Valle Act
            table.column(32).visible(false);   // Energía Reac Act
            table.column(33).visible(false);   // Cargo Pot Contratada
            table.column(34).visible(false);   // Cargo Pot Ad
            table.column(35).visible(false);   // Cargo Pot Excedente
            table.column(36).visible(false);   // Recargo TGFI
            table.column(37).visible(false);   // Consumo Pico Vigente
            table.column(38).visible(false);   // Cargo Pico
            table.column(39).visible(false);   // Consumo Resto Vigente
            table.column(40).visible(false);   // Cargo Resto
            table.column(41).visible(false);   // Consumo Valle Vigente
            table.column(42).visible(false);   // Cargo Valle
            table.column(43).visible(false);   // E Actual
            table.column(44).visible(false);   // Cargo Contratado
            table.column(45).visible(false);   // Cargo Adquirido
            table.column(46).visible(false);   // Cargo Excedente
            table.column(47).visible(false);   // Cargo Variable
            table.column(49).visible(false);
            table.column(50).visible(false);
            table.column(58).visible(false);
            table.column(59).visible(false);
            table.column(60).visible(false);
            table.column(61).visible(false);
            table.column(62).visible(false);
            table.column(64).visible(false);
            
          

          } else if (selectedProveedor == '2') {
            table.column(2).visible(false); // Categoria
            table.column(3).visible(false); // Tension
            table.column(12).visible(false); // Tgfi
            table.column(18).visible(false); // Bimestre
            table.column(19).visible(false); // Liquidación
            table.column(22).visible(false); // Cargo Var
            table.column(23).visible(false); // Cargo Var >
            table.column(26).visible(false);   //P excedida T3
            table.column(27).visible(false); // Pot Punta
            table.column(28).visible(false); // Pot Fuera Punta Cons
            table.column(29).visible(false); // Energía Punta Act
            table.column(30).visible(false); // Energía Resto Act
            table.column(31).visible(false); // Energía Valle Act
            table.column(32).visible(false); // Energía Reac Act
            table.column(33).visible(false);   // Cargo Pot Contratada
            table.column(34).visible(false);   // Cargo Pot Ad
            table.column(35).visible(false);   // Cargo Pot Excedente


            table.column(36).visible(false); // Recargo TGFI
            table.column(37).visible(false); // Consumo Pico Vigente
            table.column(38).visible(false); // Cargo Pico
            table.column(39).visible(false); // Consumo Resto Vigente
            table.column(40).visible(false); // Cargo Resto
            table.column(41).visible(false); // Consumo Valle Vigente
            table.column(42).visible(false); // Cargo Valle
            table.column(43).visible(false); // E Actual
            table.column(49).visible(false); // E Reac Cons
            table.column(50).visible(false);
            table.column(51).visible(false); // Días Cons
            table.column(52).visible(false); // Días Comp
            table.column(53).visible(false); // cons dc
            table.column(54).visible(false); // cons dc

            table.column(58).visible(false); // Cargo Variable Hasta
            table.column(59).visible(false);
            table.column(60).visible(false);
            table.column(61).visible(false);
            table.column(62).visible(false);
            table.column(64).visible(false);
            


          } else if (selectedProveedor == '3') {
            table.column(2).visible(false); // Categoria
            table.column(10).visible(false);   // Cons kWh
            table.column(11).visible(false);   // Cosfi
            table.column(13).visible(false);
            table.column(14).visible(false);
            table.column(18).visible(false);   // Bimestre
            table.column(19).visible(false);   // Liquidación
            table.column(21).visible(false);   // Cargo Fijo
            table.column(22).visible(false);   // Cargo Var
            table.column(23).visible(false);   // Cargo Var >
            table.column(24).visible(false);   // Otros Conceptos
            table.column(25).visible(false);   // Conceptos Eléctricos
            table.column(44).visible(false);   // Cargo Contratado
            table.column(45).visible(false);   // Cargo Adquirido
            table.column(46).visible(false);   // Cargo Excedente
            table.column(47).visible(false);   // Cargo Variable
            table.column(51).visible(false);   // Días Cons
            table.column(52).visible(false);   // Días Comp
            table.column(53).visible(false);   // Cons DC
            table.column(54).visible(false);   // e activa
            table.column(55).visible(false);   // e reactiva
            table.column(58).visible(false);   // Cargo Variable Hasta
            table.column(59).visible(false)
            table.column(64).visible(false);

            
          }
          else if (selectedProveedor == '5') {
            table.column(3).visible(false); // Tension
            table.column(6).visible(false); // medidor
            table.column(11).visible(false);  // cosfi
            table.column(12).visible(false);  // Tgfi
            table.column(13).visible(false);
            table.column(14).visible(false);
            table.column(18).visible(false);   // Bimestre
            table.column(19).visible(false);   // Liquidación
            table.column(20).visible(false);
            table.column(26).visible(false);   //P excedida T3
            table.column(27).visible(false);   // Pot Punta
            table.column(28).visible(false);   // Pot Fuera Punta Cons
            table.column(29).visible(false);   // Energía Punta Act
            table.column(30).visible(false);   // Energía Resto Act
            table.column(31).visible(false);   // Energía Valle Act
            table.column(32).visible(false);   // Energía Reac Act
            table.column(33).visible(false);   // Cargo Pot Contratada
            table.column(34).visible(false);   // Cargo Pot Ad
            table.column(35).visible(false);   // Cargo Pot Excedente
            table.column(36).visible(false);   // Recargo TGFI
            table.column(37).visible(false);   // Consumo Pico Vigente
            table.column(38).visible(false);   // Cargo Pico
            table.column(39).visible(false);   // Consumo Resto Vigente
            table.column(40).visible(false);   // Cargo Resto
            table.column(41).visible(false);   // Consumo Valle Vigente
            table.column(42).visible(false);   // Cargo Valle
            table.column(43).visible(false);   // E Actual
            table.column(44).visible(false);   // Cargo Contratado
            table.column(45).visible(false);   // Cargo Adquirido
            table.column(46).visible(false);   // Cargo Excedente
            table.column(47).visible(false);   // Cargo Variable
            table.column(49).visible(false);
            table.column(50).visible(false);
            table.column(51).visible(false);   // Días Cons
            table.column(52).visible(false);   // Días Comp
            table.column(53).visible(false);   // unidad de medida
            table.column(54).visible(false);   // Cons DC
            table.column(58).visible(false);
            table.column(59).visible(false);
            table.column(60).visible(false);
            table.column(61).visible(false);
            table.column(62).visible(false);
            table.column(63).visible(false); //energia inyectada
            table.column(64).visible(false);
        } 
        
      }}
    
      // Listener para el cambio en el select de proveedor
     // $("#id_proveedor").on("change", function () {
   //     aplicarVisibilidad(); // Aplicar visibilidad al cambiar proveedor
    // });
    // Listener independiente para habilitar o deshabilitar el checkbox según el proveedor seleccionado
   // Listener independiente para habilitar o deshabilitar el checkbox según el proveedor seleccionado
      // Deshabilitar el checkbox al cargar la página
    // Deshabilitar ambos checkboxes al cargar la página
    $('#cons_filter').prop('disabled', true);
    $('#const3_filter').prop('disabled', true);

    $("#id_proveedor").on("change", function () {
        const selectedValues = $(this).val() || []; // Obtener los valores seleccionados o un array vacío si no hay selección
        
        // Condición para habilitar o deshabilitar el checkbox cons_filter
        if (selectedValues.includes("1") || selectedValues.includes("2")) {
            $('#cons_filter').prop('disabled', false); // Habilitar checkbox cons_filter
        } else {
            $('#cons_filter').prop('disabled', true); // Deshabilitar checkbox cons_filter
            $('#cons_filter').prop('checked', false);  // Desmarcar el checkbox si se deshabilita
        }

        // Condición para habilitar o deshabilitar el checkbox const3_filter
        if (selectedValues.includes("3")) {
            $('#const3_filter').prop('disabled', false); // Habilitar checkbox const3_filter
        } else {
            $('#const3_filter').prop('disabled', true); // Deshabilitar checkbox const3_filter
            $('#const3_filter').prop('checked', false);  // Desmarcar el checkbox si se deshabilita
        }
    });

    
    
      // Ejecutar aplicarVisibilidad cada vez que se redibuja la tabla (incluye el filtrado)
      table.off('draw'); // Asegúrate de desregistrar cualquier evento previo
     table.on('draw', function () {
      aplicarVisibilidad();
     });
    
    
    });

    // Evento para seleccionar la fila con un solo clic
    $('#consolidados_dt tbody').on('click', 'tr', function () {
      // Elimina la clase 'selected' de cualquier fila previamente seleccionada
      table.$('tr.selected').removeClass('selected');
      // Agrega la clase 'selected' a la fila clickeada
      $(this).addClass('selected');
  });

    // Evento para redirigir con doble clic
    $('#consolidados_dt tbody').on('dblclick', 'tr', function (e) {
      e.stopPropagation();  // Detiene la propagación del evento
      //console.log("dblclick evento ejecutado");
      // Encuentra el enlace en la columna correspondiente
      var $link = $(this).find('a[title="ver archivo"]');
      if ($link.length) {
          // Abre el enlace en una nueva pestaña
          window.open($link.attr('href'), '_blank');
      }
    });


    
    
}

$(document).ready(function () {
  $('input[name="daterange2"]').daterangepicker({
    showDropdowns: true,
    locale: {
      applyLabel: "Aplicar",
      cancelLabel: "Cancelar",
      format: "DD/MM/YYYY",
      customRangeLabel: "Búsqueda avanzada",
    },
    ranges: {
      'Hoy': [moment(), moment()],
      'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
      'Ultimos 7 días': [moment().subtract(6, 'days'), moment()],
      'Ultimos 30 días': [moment().subtract(29, 'days'), moment()],
      'Este mes': [moment().startOf('month'), moment().endOf('month')],
      'Mes pasado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
    },
  }, function(start, end, label) {
    //console.log('New date range selected: ' + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD') + ' (predefined range: ' + label + ')');
  });
  
  var range = $('input[name="daterange2d"]').daterangepicker({
    showDropdowns: true,
    showCustomRangeLabel: true,
    locale: {
      format: "DD/MM/YYYY",
      customRangeLabel: "Búsqueda avanzada",
    },
    ranges: {
      Hoy: [moment(), moment()],
      Ayer: [moment().subtract(1, "days"), moment().subtract(1, "days")],
      "Últimos 7 Días": [moment().subtract(6, "days"), moment()],
      "Últimos 30 Días": [moment().subtract(29, "days"), moment()],
      "Este Mes": [moment().startOf("month"), moment().endOf("month")],
      "Mes Pasado": [
        moment().subtract(1, "month").startOf("month"),
        moment().subtract(1, "month").endOf("month"),
      ],
    },
  }, function(start, end, label) {
    if (validarSeleccionFecha()) {
      var tipo_fecha = $('input[name="tipo_fecha"]:checked').val();
      var strSearchvar = start.format("YYYY-MM-DD") + "@" + end.format("YYYY-MM-DD");
      var parametrosUrl = "?tipo_fecha=" + tipo_fecha + "&filtro=1&buscarFechas=" + strSearchvar;
      // initDatatable(strSearchvar, 1);
      // $('#consolidados_dt').DataTable().search('<searchstring>');
    }
  });
  
  range.on("cancel.daterangepicker", function () {});
  range.on("load.daterangepicker", function () {});
  
  var drp = $('input[name="daterange2"]').data("daterangepicker");
  initDatatable();
  
  var base_url = $("body").data("base_url");
  
  $("body").on("click", "#resetfilter", function (e) {
    e.preventDefault();
    $("#tipo-fecha").prop('checked', false);
    $("#id_proveedor").val("").trigger("change");
    //$("#id_tipo_pago").val("").trigger("change");
    //$("#periodo_contable").val("").trigger("change");
    $("#id_mes_fc").val("").trigger("change");
    $("#id_anio_fc").val("").trigger("change");
    $('#daterange2').data('daterangepicker').setEndDate(new Date);
    $('#daterange2').data('daterangepicker').setStartDate(new Date);

    // Resetear los nuevos checkboxes (cosfi y tgfi)
    $("#cosfi_filter").prop('checked', false); // Restablecer el checkbox de Cos Fi
    $("#cons_filter").prop('checked', false); // Restablecer el checkbox de Cosumo = 0

    $("#tgfi_filter").prop('checked', false);  // Restablecer el checkbox de Tg Fi


    initDatatable();

   
  });
  
  $("body").on("click", "#applyfilter", function (e) {
    e.preventDefault();
    initDatatable(false, 4);
    
  });
  
  $("body").on("click", "#descarga-exell", function (e) {
    e.preventDefault();
    $("body .buttons-excel").trigger("click");
  });
  
  $("body").on("change", "select#selectperiodo", function (e) {
    e.preventDefault();
    if ($(this).val() == "0") {
      initDatatable();
      return;
    }
    initDatatable($('select[id="selectperiodo"] option:selected').text(), 4);
  });
  
  document.querySelectorAll("a.toggle-vis").forEach((el) => {
    el.addEventListener("click", function (e) {
      e.preventDefault();
      let columnIdx = e.target.getAttribute("data-column");
      let column = table.column(columnIdx);
      column.visible(!column.visible());
    });
  });
  
  function validarSeleccionFecha() {
    var accesorios = document.querySelectorAll('input[name="tipo_fecha"]:checked');
    if (accesorios.length <= 0) {
      Swal.fire({
        icon: "error",
        title: "Oops...",
        text: "Debe seleccionar un tipo de Fecha!",
      });
      return false;
    }
    return true;
  }
  
  function filterData(tableParams) {
    $("#consolidados_dt").DataTable().destroy();
    $("#consolidados_dt")
      .DataTable({
        ajax: {
          data: {
            table: "_consolidados_canon",
            date_search: tableParams,
            search: { value: "" },
            draw: 1,
            start: 1,
            length: 10,
          },
          url: base_url + "Consolidados/list_dt_canon",
          type: "POST",
        },
      })
      .ajax.reload();
  }
  
  function sleep(milliseconds) {
    var start = new Date().getTime();
    for (var i = 0; i < 1e7; i++) {
      if (new Date().getTime() - start > milliseconds) {
        break;
      }
    }
  }
  
  $("body").on("click", "span.borrar_dato", function (e) {
    e.preventDefault();
    var dato = new FormData();
    var file = $(this).data("file");
    var lote = $(this).data("lote");

    // Mostrar los valores en la consola para verificar
    console.log("File:", file);
    console.log("Lote:", lote);

    dato.append("file", file);
    dato.append("lote", lote);
    
    $.confirm({
        autoClose: "cancel|10000",
        title: "Eliminar Datos y archivos",
        content: "Confirma eliminar datos del archivo : " + file + "  ?",
        buttons: {
            confirm: {
                text: "Borrar",
                btnClass: "btn-blue",
                action: function () {
                    $.ajax({
                        type: "POST",
                        contentType: false,
                        dataType: "json",
                        data: dato,
                        processData: false,
                        cache: false,
                        beforeSend: function () {},
                        url: $("body").data("base_url") + "Electromecanica/Consolidados/delete",
                        success: function (result) {
                            $("body #applyfilter").trigger('click');
                        },
                        error: function (xhr, errmsg, err) {
                            console.log(xhr.status + ": " + xhr.responseText);
                        },
                    });
                },
            },
            cancel: {
                text: "Cancelar",
                btnClass: "btn-red",
                action: function () {},
            },
        },
    });
});


});
