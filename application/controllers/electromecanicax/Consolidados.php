<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Consolidados extends backend_controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('manager/Electromecanica_model');

        $this->data['select_secretarias'] = $this->Electromecanica_model->obtener_contenido_select('_secretarias', 'SELECCIONE SECRETARÍA', 'secretaria', 'id ASC');
        $this->data['select_dependencias'] = $this->Electromecanica_model->obtener_contenido_select('_dependencias', 'SELECCIONE DEPENDENCIA', 'dependencia', 'id ASC');
        $this->data['select_proyectos'] = $this->Electromecanica_model->obtener_contenido_select('_proyectos', 'SELECCIONE PROYECTO', 'descripcion', 'id ASC');
    }

    public function delete()
    {
        $this->db->where('nombre_archivo', $_REQUEST['file']);
        $this->db->delete('_consolidados_canon');

        $data = array(
            'consolidado' => 0
        );

        $this->db->where('nombre_archivo', $_REQUEST['file']);
        $this->db->update('_datos_api_canon', $data);

        $this->db->where('code', $_REQUEST['lote']);
        $this->db->update('_lotes_canon', $data);

        $response = array(
            'mensaje' => 'Datos borrados',
            'title' => '_consolidados_canon',
            'status' => 'success',
        );

        echo json_encode($response);
        exit();
    }






    public function list_dt_canon($tipo = null, $tabla = null, $search = '')
{
    if ($this->input->is_ajax_request()) {
        $data = array();
        
        // Registrar el contenido de $_POST en el log de errores
        log_message('debug', 'Datos POST: ' . print_r($_POST, true));

        // Registrar la última consulta SQL en el log de errores
        log_message('debug', 'Consulta SQL: ' . $this->db->last_query());



        // Obtener los datos a mostrar en la tabla
        $memData = $this->Electromecanica_model->getRows($_POST);
        // Más depuración (por ejemplo, los datos de $memData)
        log_message('debug', 'Datos obtenidos: ' . print_r($memData, true));
        
        

        // Información de depuración
        $request = $_REQUEST;
        $consulta = $this->db->last_query();
       



        foreach ($memData as $r) {
            // Obtener datos adicionales según la lógica de la aplicación
            $indexador = $this->Electromecanica_model->getWhere('_indexaciones_canon', 'nro_cuenta="' . $r->nro_cuenta . '"');

            // Definir acciones (botones/ver/editar)
            $accionesVer = '<a title="ver archivo" href="/Electromecanica/Consolidados/ver/' . $r->id_consolidado . '" class=" "><i class="icon-eye4" title="ver archivo" style="margin-right: 10px;"></i></a> ';
            $accionesDelete = '<span class="borrar_dato acciones" data-lote="' . $r->lote . '" data-file="' . $r->nombre_archivo . '"><a title="Borrar lote" href="#" class=""><i class="text-danger icon-trash" title="Borrar Datos"></i></a></span>';

            // Lógica para mostrar los valores correctos en caso de que los datos sean nulos o vacíos
            $acuerdo = $r->acuerdo_pago == '' ? 'SIN ACUERDO' : $r->acuerdo_pago;

            // QUITO ACCIONES A USUARIO ELECTRO
            if ($this->ion_auth->is_electro()) {
                $accionesDelete = '';
            }

            // Lógica para el ícono de alerta según el campo comentarios y seguimiento
            $comentarios = isset($r->comentarios) ? $r->comentarios : '';  // Obtener el valor de comentarios
            $seguimiento = $r->seguimiento;  // Obtener el valor de seguimiento

            // Generar el ícono de alerta
            $alertaIcono = ''; // Inicia como vacío
            if (!empty($comentarios)) {
                // Si hay texto, mostrar el ícono de alerta con el color adecuado
                if ($seguimiento == 1) {
                    // Ícono de seguimiento (Rojo) con margen lateral
                    $alertaIcono = '<i class="text-danger icon-alert" title="En seguimiento" style="margin-right: 10px;"></i>';
                } else {
                    // Ícono resuelto (Verde) con margen lateral
                    $alertaIcono = '<i class="text-success icon-alert" title="Resuelto" style="margin-right: 10px;"></i>';
                }
            } else {
                // Si no hay texto, el ícono grisado con margen lateral
                $alertaIcono = '<i class="text-muted icon-alert" title="No hay comentarios" style="margin-right: 10px;"></i>';
            }


            // Ajustar los datos para que correspondan con las columnas requeridas
            $data[] = array(
                $r->proveedora,                      // Proveedor 0
                $r->nro_factura,                     // Nro Factura 1
                $r->nro_cuenta,                      // Nro Cuenta 2
                $r->nro_medidor,                     // Nro Medidor 3
                $r->dependencia,                     // Dependencia 4
                $r->dependencia_direccion,           // Dirección de Consumo 5
                $r->nombre_cliente,                   // Nombre Cliente 6
                $r->consumo,                         // Consumo 7
                $r->unidad_medida,                   // U. Med 8
                $r->cosfi,                           // Cosfi 9
                $r->tgfi,                            // Tgfi 10
                $r->importe,                         // Importe Total 11
                $r->mes_fc,                          // Mes Fc (convertido a número de mes) 12
                $r->anio_fc,                         // Año Fc (convertido a número de año) 13
                fecha_es($r->fecha_vencimiento, 'd-m-a', false), // Vencimiento 14
                $r->impuestos,                       // Impuestos 15
                $r->bimestre,                        // Bimestre 16
                $r->liquidacion,                     // Liquidación 17
                $r->cargo_variable_hasta,            // Cargo Variable Hasta 18
                $r->cargo_fijo,                      // Cargo Fijo 19
                $r->monto_car_var_hasta,             // Monto Cargo Var Hasta 20
                $r->moto_var_mayor,                  // Moto Var Mayor 21
                $r->otros_conseptos,                 // Otros Conceptos 22
                $r->conceptos_electricos,            // Conceptos Eléctricos 23
                $r->energia_inyectada,               // Energía Inyectada 24
                $r->pot_punta,                       // Pot Punta 25
                $r->pot_fuera_punta_cons,            // Pot Fuera Punta Cons 26
                $r->ener_punta_cons,                   // Energía Punta Act 27
                $r->ener_resto_cons,                   // Energía Resto Act 28
                $r->ener_valle_cons,                   // Energía Valle Act 29
                $r->ener_reac_act,                    // Energía Reac Act 30
                $r->cargo_pot_contratada,            // Cargo Pot Contratada 31
                $r->cargo_pot_ad,                    // Cargo Pot Ad 32
                $r->cargo_pot_excd,                  // Cargo Pot Excedente 33
                $r->recargo_tgfi,                    // Recargo TGFI 34
                $r->consumo_pico_vig,                // Consumo Pico Vigente 35
                $r->cargo_pico,                      // Cargo Pico 36
                $r->consumo_resto_vig,               // Consumo Resto Vigente 37
                $r->cargo_resto,                     // Cargo Resto 38
                $r->consumo_valle_vig,               // Consumo Valle Vigente 39
                $r->cargo_valle,                     // Cargo Valle 40
                $r->e_actual,                        // E Actual 41
                $r->cargo_contr,                     // Cargo Contratado 42
                $r->cargo_adq,                       // Cargo Adquirido 43
                $r->cargo_exc,                       // Cargo Excedente 44
                $r->cargo_var,                       // Cargo Variable 45
                $r->total_vencido,                   // Total Vencido 46
                $r->ener_reac_cons,                  // Energía Reactiva Consumida 47
                $r->tipo_de_tarifa,                  // Energía Reactiva Consumida 48
                $r ->dias_de_consumo,                //49
                $r ->dias_comprendidos,               //50
                $r ->consumo_dias_comprendidos,       // 51
                $r ->periodo_del_consumo,            // 52
                $r ->e_activa,  // 53       
                $r ->e_reactiva,   //54  
                $r ->subsidio,   //55  
                $r->p_contratada, //56
                $r->p_registrada, //57
                $r->consumo_pico_ant, //58
                $r->consumo_resto_ant, //59
                $r->consumo_valle_ant, //60
                $r->p_excedida, //61
                $r->cargo_fijo_cant, //62
                $alertaIcono . $accionesVer . $accionesDelete,      // Acciones 63
                $r->id_proveedor,                      // ID del Proveedor 64
            );
        }

        // Configuración de respuesta para DataTable
        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->Electromecanica_model->countAll(),
            "recordsFiltered" => $this->Electromecanica_model->countFiltered($_POST),
            "data" => $data,
            "consulta" => $consulta,
            "mirequest" => $request,
        );

        // Enviar los datos en formato JSON
        echo json_encode($output);
    }
}



    public function listados()
    {
        $css = array(
            base_url('assets/manager/js/plugins/daterange-picker/daterange-picker.css'),
        );

        $script = array(
            base_url('assets/manager/js/secciones/electromecanica/consolidados/listados.js')
        );

        $this->data['css_common'] = $this->css_common;
        $this->data['css'] = $css;
        $this->data['script_common'] = $this->script_common;
        $this->data['script'] = $script;

        $this->data['select_secretarias'] = $this->Electromecanica_model->obtener_contenido_select('_secretarias', 'SELECCIONE SECRETARÍA', 'secretaria', 'id ASC');
        $this->data['select_programa'] = $this->Electromecanica_model->obtener_contenido_select('_programas', 'SELECCIONE PROGRAMA', 'descripcion', 'id ASC');
        $this->data['select_proyecto'] = $this->Electromecanica_model->obtener_contenido_select('_proyectos', 'SELECCIONE PROYECTO', 'descripcion', 'id ASC');
        $this->data['select_proveedores'] = $this->Electromecanica_model->obtener_contenido_select('_proveedores_canon', '', 'nombre', 'id ASC', false);
        $this->data['select_anios'] = $this->Electromecanica_model->obtener_contenido_select('_consolidados_canon', '', 'anio_fc', 'anio_fc ASC', false);
        // $this->data['select_tipo_pago'] = $this->Electromecanica_model->obtener_contenido_select('_tipo_pago', '', 'tip_nombre', 'tip_id ASC', false);
        // $this->data['select_periodo_contable'] = $this->Electromecanica_model->obtener_contenido_select('_consolidados_canon', '', 'periodo_contable', 'periodo_contable DESC', false);

        if ($_SERVER['REQUEST_METHOD'] === "POST") {
            $this->form_validation->set_rules('id_proyecto', 'ID Proyecto', 'trim|in_select[0]');
            $this->form_validation->set_rules('id_interno', 'ID interno', 'trim|required');
            $this->form_validation->set_rules('descripcion', 'Descripción', 'trim|required');

            if ($this->form_validation->run() != FALSE) {
                $datos = array(
                    'id_proyecto' => $this->input->post('id_proyecto'),
                    'id_interno' => $this->input->post('id_interno'),
                    'descripcion' => $this->input->post('descripcion'),
                );
                $this->Electromecanica_model->grabar_datos("_obras", $datos);
                redirect(base_url('Admin/Obras'));
            }
        }

        $this->data['content'] = $this->load->view('manager/secciones/electromecanica/consolidados/listados', $this->data, TRUE);
        $this->load->view('manager/head', $this->data);
        $this->load->view('manager/index', $this->data);
        $this->load->view('manager/footer', $this->data);
    }

    public function agregar($id = NULL)
    {
        $this->data['css_common'] = $this->css_common;
        $this->data['css'] = '';

        $this->data['script_common'] = $this->script_common;
        $this->data['script'] = '';

        if ($id) {
            $this->data['obra'] = $this->Electromecanica_model->getWhere('_obras', 'id = ' . $id, true);
        }

        $this->data['content'] = $this->load->view('manager/secciones/' . strtolower($this->router->fetch_class()) . '/' . $this->router->fetch_method(), $this->data, TRUE);
        $this->load->view('manager/head', $this->data);
        $this->load->view('manager/index', $this->data);
        $this->load->view('manager/footer', $this->data);
    }



    public function debug_query()
    {
        // Asegúrate de cargar la base de datos
        $this->load->database();

        // Preparar la consulta
        $this->db->select(
            'CONCAT(_consolidados_canon.proveedor, " (", _consolidados_canon.codigo_proveedor,")") as proveedora,
             CONCAT(_consolidados_canon.jurisdiccion, " ", _consolidados_canon.id_programa) as sumajuris,
             _consolidados_canon.id as id_consolidado,
             UPPER(_consolidados_canon.secretaria),
             _consolidados_canon.proveedor,
             _consolidados_canon.*'
        );

        $this->db->from('_consolidados_canon');
        $this->db->order_by('_consolidados_canon.id', 'DESC');

        // Obtener la consulta compilada
        $compiled_query = $this->db->get_compiled_select();

        // Mostrar la consulta generada
        echo '<pre>' . htmlspecialchars($compiled_query) . '</pre>';
    }

    public function obtener_datos_grafico()
{
    // Recibir el valor y convertirlo explícitamente a booleano
    $agrupar_por_mes = $this->input->post('agrupar_por_mes') === 'true';

    // Recibir los filtros
    $filtros = $this->input->post('filtros');

    // Según el valor de 'agrupar_por_mes', se llamará a una u otra función del modelo
    if ($agrupar_por_mes) {
        // Si se agrupa por mes, obtenemos los datos correspondientes a los filtros
        $datos_grafico = $this->Electromecanica_model->contar_registros_por_mes($filtros);
    } else {
        // Si no se agrupa por mes, obtenemos los datos por proveedor (tarifa)
        $datos_grafico = $this->Electromecanica_model->contar_registros_por_proveedor_canon($filtros);
    }

    // Devolver los datos en formato JSON
    echo json_encode($datos_grafico);
}


private function escribir_log($mensaje)
{
    $ruta_log = APPPATH . 'controllers/electromecanicax/log_datos_grafico.txt'; // Ruta del archivo de log
    $fecha_hora = date('Y-m-d H:i:s'); // Fecha y hora actual
    $linea = "[{$fecha_hora}] {$mensaje}" . PHP_EOL;

    // Escribir en el archivo
    file_put_contents($ruta_log, $linea, FILE_APPEND);
}

public function probar_contar_registros_por_mes()
{
    // Cargar el helper solo para esta función
    $this->load->helper('global');

    // Cargar el modelo
    $this->load->model('Electromecanica_model');

    // Registrar log antes de la ejecución
    escribir_log('Iniciando prueba de contar_registros_por_mes.');

    // Llamar al método del modelo
    $resultado = $this->Electromecanica_model->contar_registros_por_mes();

    // Registrar log después de obtener el resultado
    escribir_log('Resultado obtenido: ' . json_encode($resultado));

    // Mostrar el resultado en formato JSON para depuración
    echo json_encode($resultado);
}




public function test_query()
{
    // Selección de campos
    $this->db->select(
        'CONCAT(_consolidados_canon.proveedor, " (", _consolidados_canon.codigo_proveedor,")" ) as proveedora,
        CONCAT(_consolidados_canon.jurisdiccion," ",_consolidados_canon.id_programa ) as sumajuris,
        _consolidados_canon.id as id_consolidado,
        UPPER(_consolidados_canon.secretaria), UPPER(_consolidados_canon.acuerdo_pago),
        _consolidados_canon.proveedor, _consolidados_canon.*'
    );

    // Ejecutar la consulta sin ningún filtro
    $query = $this->db->get('_consolidados_canon');

    // Obtener todos los registros como un array
    $result = $query->result_array();

    // Mostrar los registros con un índice
    echo "<pre>";
    foreach ($result as $index => $row) {
        echo "Registro #" . ($index + 1) . ":\n"; // Numeración empezando desde 1
        
        // Recorrer las claves de cada registro y mostrar su índice dentro del array
        foreach ($row as $key => $value) {
            echo "    Clave: $key | Valor: $value\n"; // Mostrar la clave y su valor
        }

        echo "\n\n"; // Espacio entre registros
    }
    echo "</pre>";

    // Retornar el array completo de resultados (opcional)
    return $result;
}


function actualizarConsumoAct() {
    // Cargar el modelo de base de datos
    $ci = &get_instance();
    $ci->load->database();

    // Contadores de registros
    $registrosTotal = 0;
    $registrosModificados = 0;
    $registrosNoModificados = 0;
    $registrosError = 0;

    // Seleccionar los registros de la tabla _datos_api_canon que tienen JSON almacenado
    $query = $ci->db->select('id, dato_api, e_activa')
                    ->from('_datos_api_canon')
                    ->get();

    // Iterar sobre cada registro
    foreach ($query->result() as $row) {
        $registrosTotal++;
        
        // Decodificar el JSON desde `dato_api`
        $jsonData = json_decode($row->dato_api);

        // Verificar que el JSON y los datos necesarios existan
        if (isset($jsonData->document->inference->pages[0]->prediction->consumo_act->values[0]->content)) {
            // Obtener el valor correcto de consumo_act desde el JSON
            $consumoAct = $jsonData->document->inference->pages[0]->prediction->consumo_act->values[0]->content;

            // Asegurarse de que el valor sea numérico y aplicar la multiplicación solo si es menor a 1000
            $consumoAct = is_numeric($consumoAct) && $consumoAct < 1000 ? $consumoAct * 1000 : (float)$consumoAct;

            // Mostrar el valor actual y el valor nuevo para depuración
            echo "ID: {$row->id} | e_activa actual: {$row->e_activa} | consumoAct nuevo: {$consumoAct}\n";

            // Actualizar el valor en la base de datos solo si es diferente al valor almacenado
            if ($consumoAct != $row->e_activa) {
                $ci->db->where('id', $row->id)
                       ->update('_datos_api_canon', ['e_activa' => $consumoAct]);
                
                echo "Registro con ID {$row->id} actualizado a {$consumoAct}.\n";
                $registrosModificados++;
            } else {
                echo "Registro con ID {$row->id} ya está correcto.\n";
                $registrosNoModificados++;
            }
        } else {
            echo "Registro con ID {$row->id} no contiene el valor consumo_act en JSON.\n";
            $registrosError++;
        }
    }

    // Mostrar resumen al finalizar
    echo "\nResumen de la actualización:\n";
    echo "Total de registros procesados: {$registrosTotal}\n";
    echo "Registros modificados: {$registrosModificados}\n";
    echo "Registros ya correctos: {$registrosNoModificados}\n";
    echo "Registros con error en JSON: {$registrosError}\n";
}
function actualizarEActivaConsolidados() {
    // Cargar el modelo de base de datos
    $ci = &get_instance();
    $ci->load->database();

    // Contadores para el resumen final
    $registrosTotal = 0;
    $registrosActualizados = 0;
    $registrosError = 0;

    // Seleccionar los registros de la tabla _consolidados_canon con id_lectura_api
    $query = $ci->db->select('id, id_lectura_api, e_activa')
                    ->from('_consolidados_canon')
                    ->get();

    // Iterar sobre cada registro
    foreach ($query->result() as $row) {
        $registrosTotal++;

        // Obtener el valor de e_activa desde la tabla _datos_api_canon basado en id_lectura_api
        $datosApiQuery = $ci->db->select('e_activa')
                                ->from('_datos_api_canon')
                                ->where('id', $row->id_lectura_api)
                                ->get();

        // Verificar si se encontró el registro correspondiente en _datos_api_canon
        if ($datosApiQuery->num_rows() > 0) {
            $eActivaCorregida = $datosApiQuery->row()->e_activa;

            // Actualizar e_activa en _consolidados_canon solo si es diferente
            if ($row->e_activa != $eActivaCorregida) {
                $ci->db->where('id', $row->id)
                       ->update('_consolidados_canon', ['e_activa' => $eActivaCorregida]);
                
                echo "Registro con ID {$row->id} actualizado a {$eActivaCorregida}.\n";
                $registrosActualizados++;
            } else {
                echo "Registro con ID {$row->id} ya tiene el valor correcto.\n";
            }
        } else {
            // Si no se encuentra el id_lectura_api en _datos_api_canon
            echo "Error: No se encontró el registro id_lectura_api {$row->id_lectura_api} en _datos_api_canon.\n";
            $registrosError++;
        }
    }

    // Mostrar resumen al finalizar
    echo "\nResumen de la actualización:\n";
    echo "Total de registros procesados en _consolidados_canon: {$registrosTotal}\n";
    echo "Registros actualizados: {$registrosActualizados}\n";
    echo "Errores (id_lectura_api no encontrado): {$registrosError}\n";
}
public function actualizarConsumosConsolidados() {
    // Cargar el modelo de base de datos
    $this->load->database();

    // Contadores para el resumen
    $registrosTotal = 0;
    $registrosActualizados = 0;
    $registrosSinRelacion = 0;

    // Seleccionar los registros de la tabla _datos_api_canon con los campos de consumo
    $query = $this->db->select('id, consumo_pico_ant, consumo_resto_ant, consumo_valle_ant')
                      ->from('_datos_api_canon')
                      ->get();

    // Iterar sobre cada registro en _datos_api_canon
    foreach ($query->result() as $row) {
        $registrosTotal++;

        // Buscar en _consolidados_canon el registro relacionado con el id_lectura_api
        $consolidadoQuery = $this->db->select('id')
                                     ->from('_consolidados_canon')
                                     ->where('id_lectura_api', $row->id)
                                     ->get();

        // Verificar si existe un registro relacionado en _consolidados_canon
        if ($consolidadoQuery->num_rows() > 0) {
            $consolidadoId = $consolidadoQuery->row()->id;

            // Datos a actualizar
            $dataUpdate = [
                'consumo_pico_ant' => $row->consumo_pico_ant,
                'consumo_resto_ant' => $row->consumo_resto_ant,
                'consumo_valle_ant' => $row->consumo_valle_ant,
            ];

            // Actualizar los campos en _consolidados_canon
            $this->db->where('id', $consolidadoId)
                     ->update('_consolidados_canon', $dataUpdate);

            echo "Registro en _consolidados_canon con ID {$consolidadoId} actualizado: ";
            echo "consumo_pico_ant = {$row->consumo_pico_ant}, ";
            echo "consumo_resto_ant = {$row->consumo_resto_ant}, ";
            echo "consumo_valle_ant = {$row->consumo_valle_ant}.\n";

            $registrosActualizados++;
        } else {
            echo "No se encontró un registro en _consolidados_canon con id_lectura_api {$row->id}.\n";
            $registrosSinRelacion++;
        }
    }

    // Resumen al finalizar
    echo "\nResumen de la actualización:\n";
    echo "Total de registros procesados en _datos_api_canon: {$registrosTotal}\n";
    echo "Registros actualizados en _consolidados_canon: {$registrosActualizados}\n";
    echo "Registros sin relación en _consolidados_canon: {$registrosSinRelacion}\n";
}
function actualizarPContratada() {
   
        // Cargar el modelo de base de datos
        $ci = &get_instance();
        $ci->load->database();
    
        // Contadores de registros
        $registrosTotal = 0;
        $registrosModificados = 0;
        $registrosNoModificados = 0;
        $registrosError = 0;
    
        // Seleccionar los registros de la tabla _datos_api_canon con id_proveedor = 2 que tienen JSON almacenado
        $query = $ci->db->select('id, dato_api, p_contratada')
                        ->from('_datos_api_canon')
                        ->where('id_proveedor', 2) // Solo registros con id_proveedor = 2
                        ->get();
    
        // Iterar sobre cada registro
        foreach ($query->result() as $row) {
            $registrosTotal++;
    
            // Decodificar el JSON desde `dato_api`
            $jsonData = json_decode($row->dato_api);
    
            // Verificar si el JSON fue decodificado correctamente
            if (json_last_error() !== JSON_ERROR_NONE) {
                echo "Error de JSON en el registro ID: {$row->id}. No se pudo decodificar el JSON.\n";
                $registrosError++;
                continue;
            }
    
            // Verificar que el valor de `contratada` esté presente en el JSON
            if (isset($jsonData->document->inference->pages[0]->prediction->contratada->values[0]->content)) {
                // Obtener el valor de `contratada`
                $contratada = $jsonData->document->inference->pages[0]->prediction->contratada->values[0]->content;
    
                // Asegurarse de que el valor sea numérico
                $contratada = is_numeric($contratada) ? (float)$contratada : 0;
    
                // Verificar si `p_contratada` es 0.00 antes de actualizar
                if ($row->p_contratada == 0.00 && $contratada > 0) {
                    // Actualizar `p_contratada` en la base de datos
                    $ci->db->where('id', $row->id)
                           ->update('_datos_api_canon', ['p_contratada' => $contratada]);
    
                    echo "Registro con ID {$row->id} actualizado a {$contratada}.\n";
                    $registrosModificados++;
                } else {
                    echo "Registro con ID {$row->id} ya está correcto o no necesita cambios.\n";
                    $registrosNoModificados++;
                }
            } else {
                echo "Registro con ID {$row->id} no contiene el valor 'contratada' en JSON.\n";
                $registrosError++;
            }
        }
    
        // Mostrar resumen al finalizar
        echo "\nResumen de la depuración:\n";
        echo "Total de registros procesados: {$registrosTotal}\n";
        echo "Registros modificados: {$registrosModificados}\n";
        echo "Registros ya correctos o sin cambios necesarios: {$registrosNoModificados}\n";
        echo "Registros con error en JSON: {$registrosError}\n";
    }
    function actualizarPContratadaCanon() {
        // Cargar el modelo de base de datos
        $ci = &get_instance();
        $ci->load->database();
    
        // Contadores de registros
        $registrosTotal = 0;
        $registrosActualizados = 0;
        $registrosNoActualizados = 0;
    
        // Consultar registros de `_consolidados_canon` con p_contratada = 0.00 y obtener el valor de `_datos_api_canon`
        $query = $ci->db->select('_consolidados_canon.id, _consolidados_canon.p_contratada, _datos_api_canon.p_contratada AS p_contratada_actualizada')
                        ->from('_consolidados_canon')
                        ->join('_datos_api_canon', '_consolidados_canon.id_lectura_api = _datos_api_canon.id')
                        ->where('_consolidados_canon.p_contratada', 0.00)
                        ->where('_datos_api_canon.p_contratada !=', 0.00)  // Solo registros donde p_contratada en _datos_api_canon sea distinto de 0
                        ->get();
    
        // Iterar sobre cada registro para actualizar
        foreach ($query->result() as $row) {
            $registrosTotal++;
    
            // Actualizar el valor de p_contratada en _consolidados_canon si es 0.00
            $ci->db->where('id', $row->id)
                   ->update('_consolidados_canon', ['p_contratada' => $row->p_contratada_actualizada]);
    
            echo "Registro con ID {$row->id} actualizado de 0.00 a {$row->p_contratada_actualizada}.\n";
            $registrosActualizados++;
        }
    
        // Si no se encontraron registros para actualizar
        if ($registrosActualizados == 0) {
            $registrosNoActualizados++;
            echo "No se encontraron registros para actualizar.\n";
        }
    
        // Mostrar resumen al finalizar
        echo "\nResumen de la actualización de p_contratada:\n";
        echo "Total de registros procesados: {$registrosTotal}\n";
        echo "Registros actualizados: {$registrosActualizados}\n";
        echo "Registros sin necesidad de actualización: {$registrosNoActualizados}\n";
    }

    public function ver_consolidados($id = NULL)
{
    // Validar que se pasó un ID válido
    if (is_null($id)) {
        show_404();  // Muestra una página de error 404 si no se pasó un ID
        return;
    }

    // Depuración: Verificar el ID recibido y convertirlo a un entero
    $id = intval($id); // Convertir el ID a entero

    // Obtener los detalles del consolidado desde la base de datos
    $consolidado = $this->Electromecanica_model->getWhere('_consolidados_canon', 'id = ' . $id, true);

    // Si no se encuentra el consolidado, mostrar un error
    if (!$consolidado) {
        show_404();  // Muestra una página de error 404 si no se encontró el consolidado
        return;
    }

    // Obtener los comentarios y el estado de seguimiento del consolidado
    $comentario = $this->Electromecanica_model->get_comentario_por_id($id);

    // Pasar los datos a la vista
    $this->data['consolidado'] = $consolidado;
    $this->data['comentario'] = $comentario;  // Agregar los comentarios
    $this->data['id'] = $id;  // Pasar el ID al formulario

    $this->data['css_common'] = $this->css_common;
    $this->data['script_common'] = $this->script_common;

    // Cargar la vista con la nueva ruta
    $this->data['content'] = $this->load->view('manager/secciones/electromecanica/consolidados/ver_consolidado', $this->data, TRUE);

    // Cargar las vistas de cabecera, cuerpo y pie
    $this->load->view('manager/head', $this->data);
    $this->load->view('manager/index', $this->data);
    $this->load->view('manager/footer', $this->data);
}

    
public function guardar_comentario_en_consolidados() {
    // Verifica si el formulario fue enviado por POST
    if ($this->input->post()) {
        // Obtener los datos enviados desde el formulario
        $comentarios = $this->input->post('comentarios');
        $resuelto = $this->input->post('resuelto'); // El valor del checkbox
        $id_consolidado = $this->input->post('id');  // El ID del consolidado, si lo estás enviando

        // Si el checkbox "Resuelto" está marcado, establecer seguimiento en 1, si no, 0
        $seguimiento = ($resuelto) ? 1 : 0;

        // Crear el array con los datos a guardar
        $data = [
            'comentarios' => $comentarios,
            'seguimiento' => $seguimiento,  // Guardar el valor de seguimiento
            'consolidado_id' => $id_consolidado
        ];

        // Llamar al modelo para guardar los comentarios y el estado de seguimiento
        if ($this->Electromecanica_model->guardar_comentarios($data)) {
            // Generar el mensaje de estado
            $estado = ($seguimiento == 1) ? 'En seguimiento' : 'Resuelto';

            // Mensaje con estado de seguimiento/resuelto
            $mensaje = "Comentarios agregados correctamente. Estado: " . $estado;

            // Almacenar el mensaje en la sesión
            $this->session->set_flashdata('mensaje', $mensaje);

            // Redirigir al mismo lugar o a donde necesites
            redirect('Electromecanica/Consolidados/ver/' . $id_consolidado);
        } else {
            // Si no se guardó correctamente, muestra un mensaje de error
            $this->session->set_flashdata('error', 'Hubo un problema al guardar el comentario.');
            redirect('Electromecanica/Consolidados/ver/' . $id_consolidado);
        }
    } else {
        // Si no se envió el formulario, muestra un error 404 o redirige a una página segura
        show_404();
    }
}

    public function editar_comentario($id_consolidado) {
        // Obtener los datos del comentario y seguimiento desde la base de datos
        $comentario = $this->Electromecanica_model->get_comentario_por_id($id_consolidado);
    
        // Si no existe el consolidado, mostrar error
        if (!$comentario) {
            show_404();
        }
    
        // Cargar la vista con los datos
        $data['comentario'] = $comentario;
        $data['id'] = $id_consolidado;  // Pasar el ID al formulario
    
        $this->load->view('editar_comentario', $data);
    }
    
   
    
    
    


}
