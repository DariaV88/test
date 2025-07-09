<?php

// Общая функция для выполнения cURL-запросов
function makeCurlRequest($url, $method = 'GET', $postFields = null, $headers = []) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
    
    if ($postFields !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    }
    
    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        die('cURL Error: ' . curl_error($ch));
    }
    curl_close($ch);
    $result = json_decode($response, true);

    if ($result === null) {
        echo $response;
    }

    return $result;
}

// Функция для API Bitrix24
function bitrixApiPost($method, $params = []) {
    global $webhookUrl;
    $url = $webhookUrl . $method . '.json';
    
    return makeCurlRequest(
        $url,
        'POST',
        http_build_query($params)
    );
}

// Функция для API AmoCRM
function amoApiPost($data) {

    global $amocrmDomain;
    global $amocrmToken;
    $url = "$amocrmDomain/api/v4/leads/complex";
    $jsonData = json_encode($data);
    $headers = [
  'Authorization: Bearer '.$amocrmToken,
  'Content-Type: application/json',];

    return makeCurlRequest(
        $url,
        'POST',
        $jsonData,
        $headers
    );
    
}