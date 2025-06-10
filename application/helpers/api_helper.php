<?php
if (!function_exists('apiRest')) {
    function apiRest($file, $endPoint, $procesar_por = 'local')
    {
        if (!function_exists('curl_init')) {
            exit("cURL isn't installed for " . phpversion());
        }

        $FILE_PATH = $file['full_path'];
        $MIME_TYPE = 'application/pdf';

        if ($procesar_por === 'azure') {
            $API_KEY = 'a49c210e168941658db7c33e33218733';
            $wait_time = 2;
        } elseif ($procesar_por === 'azure2') {
            $API_KEY = '716c75SXPYp6Wvw1VUATXJM37mccGIMVqS8tNDMvHc4a5Gi60siVJQQJ99AKACZoyfiXJ3w3AAALACOGsdKb';
            $wait_time = 6;
        } else {
            $API_KEY = 'N/A';
            $wait_time = 0;
        }

        log_message('error', "Procesando con: $procesar_por - API Key usada: $API_KEY - URL: $endPoint");

        if ($procesar_por === 'azure' || $procesar_por === 'azure2') {
            $headers = array(
                "Ocp-Apim-Subscription-Key: $API_KEY",
                "Content-Type: application/pdf"
            );

            $data = file_get_contents($FILE_PATH);

            $realizarPostAzure = function () use ($endPoint, $headers, $data) {
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
                  //  log_message('error', "cURL error: " . curl_error($ch));
                    return array('error' => curl_error($ch));
                }
                $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
                $header = substr($response, 0, $header_size);
                $body = substr($response, $header_size);
                curl_close($ch);
                return array('header' => $header, 'body' => $body);
            };

            $attempts = 0;
            $max_attempts = 2;
            $operation_url = null;

            do {
                $attempts++;
                log_message('error', "Intento #$attempts de obtener Operation-Location...");
                $result = $realizarPostAzure();
                if (isset($result['error'])) return array('error' => $result['error']);

                $header = $result['header'];
                $body = $result['body'];
                //log_message('error', "Respuesta recibida (intento $attempts): " . print_r($body, true));

                if (preg_match('/operation-location:\s*(.+)\r\n/i', $header, $matches)) {
                    $operation_url = trim($matches[1]);
                    break;
                }

                if ($attempts < $max_attempts) {
                    sleep(2);
                }
            } while ($attempts < $max_attempts);

            if ($operation_url) {
                do {
                    sleep($wait_time);
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
                       // log_message('error', "cURL error en GET: " . curl_error($ch));
                        break;
                    }

                    //log_message('error', "Respuesta GET: " . print_r($result, true));
                    $header_size_get = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
                    $body_get = substr($result, $header_size_get);
                    curl_close($ch);

                    $result_data = json_decode($body_get, true);
                } while ($result_data['status'] === 'running');

                if (isset($result_data['analyzeResult']['documents'])) {
                    return $result_data['analyzeResult']['documents'];
                } else {
                    return $result_data;
                }
            } else {
                log_message('error', "No se encontró Operation-Location en ninguno de los intentos.");
                return array('error' => 'No se encontró la cabecera Operation-Location en la respuesta.');
            }
        } else {
            // Lógica para Mindee
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
            //log_message('error', "Respuesta Mindee: " . print_r($json, true));

            curl_close($ch);
            return json_decode($json, JSON_PRETTY_PRINT);
        }
    }
}
?>
