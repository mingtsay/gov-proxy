<?php

$url = $_GET['url'];
$url_parts = parse_url($url);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

if (!preg_match('#gov.tw$#', $url_parts['host'])) {
    echo 'proxy *.gov.tw only';
    exit;
}

$agent = "govproxy from http://{$_SERVER['HTTP_HOST']} by IP: {$_SERVER['REMOTE_ADDR']}";
if (0 === strpos($url, 'http://jirs.judicial.gov.tw/FJUD/PrintFJUD03_0.aspx')) {
    $referer = 'http://jirs.judicial.gov.tw/FJUD/FJUDQRY03_1.aspx';
} else {
    $referer = $url;
}

$curl = curl_init($url);
curl_setopt($curl, CURLOPT_REFERER, $referer);
curl_setopt($curl, CURLOPT_USERAGENT, $agent);
curl_setopt($curl, CURLOPT_HTTPHEADER, array("X-Forwarded-For: {$_SERVER['REMOTE_ADDR']}"));
curl_exec($curl);
