<?php

$url = $_GET['url'];
$url_parts = parse_url($url);

if (!preg_match('#gov.tw$#', $url_parts['host'])) {
    echo 'gov.tw only';
    exit;
}

$agent = "govproxy from http://{$_SERVER['HTTP_HOST']} by IP: {$_SERVER['REMOTE_ADDR']}";
$referer = $url;

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

$curl = curl_init($url);
curl_setopt($curl, CURLOPT_REFERER, $referer);
curl_setopt($curl, CURLOPT_USERAGENT, $agent);
curl_exec($curl);
