<?php
if (!function_exists('apiRest')) {
    function apiRest($file, $endPoint, $procesar_por = 'local')
    {
        // Definir la ruta del archivo de log
        $logFile = __DIR__ . '/apiRest_log.txt';
        
        // Registrar inicio del proceso
        file_put_contents($logFile, "Inicio de apiRest - Procesar_por: $procesar_por\n", FILE_APPEND);
        
        // Registrar el endpoint y el archivo que se está procesando
        file_put_contents($logFile, "Endpoint: $endPoint\n", FILE_APPEND);
        file_put_contents($logFile, "Archivo a procesar: " . $file['full_path'] . "\n", FILE_APPEND);
        
        if (!function_exists('curl_init')) {
            file_put_contents($logFile, "Error: cURL no está instalado.\n", FILE_APPEND);
            exit("cURL isn't installed for " . phpversion());
        }

        $FILE_PATH = $file['full_path'];
        $MIME_TYPE = 'application/pdf';

        if ($procesar_por === 'azure') {
            $API_KEY = 'a49c210e168941658db7c33e33218733';
            $headers = array(
                "Ocp-Apim-Subscription-Key: $API_KEY",
                "Content-Type: application/pdf"
            );

            $data = file_get_contents($FILE_PATH);

            // Iniciar la solicitud de análisis a Azure
            $ch = curl_init();
            curl_setopt_array($ch, array(
                CURLOPT_URL => $endPoint,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $data,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER => true,
                CURLOPT_FOLLOWLOCATION => true
            ));

            $response = curl_exec($ch);

            if (curl_errno($ch)) {
                $error = curl_error($ch);
                file_put_contents($logFile, "Curl error: $error\n", FILE_APPEND);
                return 'Curl error: ' . $error;
            }

            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $header = substr($response, 0, $header_size);
            $body = substr($response, $header_size);

            file_put_contents($logFile, "Respuesta de Azure (headers): $header\n", FILE_APPEND);
            file_put_contents($logFile, "Respuesta de Azure (body): $body\n", FILE_APPEND);

            curl_close($ch);

            if (preg_match('/operation-location:\s*(.+)\r\n/i', $header, $matches)) {
                $operation_url = trim($matches[1]);

                file_put_contents($logFile, "Operation URL: $operation_url\n", FILE_APPEND);

                do {
                    sleep(4);

                    $ch = curl_init();
                    curl_setopt_array($ch, array(
                        CURLOPT_URL => $operation_url,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_HEADER => true,
                        CURLOPT_CUSTOMREQUEST => 'GET',
                        CURLOPT_HTTPHEADER => array(
                            "Ocp-Apim-Subscription-Key: $API_KEY"
                        ),
                    ));

                    $result = curl_exec($ch);

                    if (curl_errno($ch)) {
                        $error = curl_error($ch);
                        file_put_contents($logFile, "Curl GET error: $error\n", FILE_APPEND);
                        break;
                    } else {
                        $header_size_get = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
                        $header_get = substr($result, 0, $header_size_get);
                        $body_get = substr($result, $header_size_get);

                        file_put_contents($logFile, "Respuesta GET de Azure (headers): $header_get\n", FILE_APPEND);
                        file_put_contents($logFile, "Respuesta GET de Azure (body): $body_get\n", FILE_APPEND);

                        $result_data = json_decode($body_get, true);
                    }
                } while ($result_data['status'] === 'running');

                if (isset($result_data['analyzeResult']['documents'])) {
                    file_put_contents($logFile, "Resultado final: " . json_encode($result_data['analyzeResult']['documents']) . "\n", FILE_APPEND);
                    return $result_data['analyzeResult']['documents'];
                } else {
                    file_put_contents($logFile, "Error: No se encontraron documentos en el resultado.\n", FILE_APPEND);
                    return $result_data;
                }
            } else {
                file_put_contents($logFile, "Error: No se encontró la cabecera Operation-Location en la respuesta.\n", FILE_APPEND);
                return array('error' => 'No se encontró la cabecera Operation-Location en la respuesta.');
            }
        } else {
            // Lógica para Mindee (actual)
            $API_KEY = 'f4b6ebe406cdb615674ae37aabc48929';
            $url = $endPoint;

            $headers = array(
                "Authorization: Token $API_KEY"
            );

            $data = array(
                "document" => new CURLFile(
                    $FILE_PATH,
                    $MIME_TYPE,
                    substr($FILE_PATH, strrpos($FILE_PATH, "/") + 1)
                )
            );

            $ch = curl_init();
            curl_setopt_array($ch, array(
                CURLOPT_URL => $url,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_POSTFIELDS => $data,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true
            ));

            $json = curl_exec($ch);

            if (curl_errno($ch)) {
                $error = curl_error($ch);
                file_put_contents($logFile, "Curl Mindee error: $error\n", FILE_APPEND);
            }

            file_put_contents($logFile, "Respuesta de Mindee: $json\n", FILE_APPEND);

            curl_close($ch);

            return json_decode($json, JSON_PRETTY_PRINT);
        }
    }
}
?>
