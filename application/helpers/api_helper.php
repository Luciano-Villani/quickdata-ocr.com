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
                return 'Curl error: ' . curl_error($ch);
            }

            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $header = substr($response, 0, $header_size);
            $body = substr($response, $header_size);

            curl_close($ch);

            if (preg_match('/operation-location:\s*(.+)\r\n/i', $header, $matches)) {
                $operation_url = trim($matches[1]);

                do {
                    sleep(2);

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
                        break;
                    } else {
                        $header_size_get = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
                        $header_get = substr($result, 0, $header_size_get);
                        $body_get = substr($result, $header_size_get);

                        $result_data = json_decode($body_get, true);
                    }
                } while ($result_data['status'] === 'running');

                if (isset($result_data['analyzeResult']['documents'])) {
                    return $result_data['analyzeResult']['documents'];
                } else {
                    return $result_data;
                }
            } else {
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
            curl_close($ch);

            return json_decode($json, JSON_PRETTY_PRINT);
        }
    }
}

?>
