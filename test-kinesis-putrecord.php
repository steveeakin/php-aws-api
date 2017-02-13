<?php

$key = "ACCESS KEY";
$secretKey = "SECRET KEY";
$streamName = "YOUR STREAM NAME";
$region = "us-east-1";
$awsUrl = "kinesis.us-east-1.amazonaws.com";

$data = 'YOUR DATA';

$data = json_encode(array(
    "StreamName" => $streamName,
    "Data" => base64_encode($data),
   	"PartitionKey" => md5($data)
));

date_default_timezone_set('GMT');

$dateShort = date('Ymd');
$dateLong = date('Ymd\THis\Z');

$payload = hash('sha256', $data);

$canonical = "POST\n/\n\nhost:" . $awsUrl . "\nx-amz-date:" . $dateLong . "\nx-amz-target:Kinesis_20131202.PutRecord\n\nhost;x-amz-date;x-amz-target\n" . $payload;

$toSign = "AWS4-HMAC-SHA256\n" . $dateLong . "\n" . $dateShort . "/" . $region . "/kinesis/aws4_request\n" . hash('sha256', $canonical);

$dateKey = hash_hmac('sha256', $dateShort, "AWS4" . $secretKey, true);
$regionKey = hash_hmac('sha256', $region, $dateKey, true);
$serviceKey = hash_hmac('sha256', "kinesis", $regionKey, true);
$signingKey = hash_hmac('sha256', 'aws4_request', $serviceKey, true);

$sig = hash_hmac('sha256', $toSign, $signingKey);

$header[] = "Content-Type: application/x-amz-json-1.1";
$header[] = "Host: " . $awsUrl;
$header[] = "X-Amz-Date: " . $dateLong;
$header[] = "X-Amz-Target: Kinesis_20131202.PutRecord";
$header[] = "Authorization: AWS4-HMAC-SHA256 Credential=" . $key . "/" . $dateShort . "/" . $region . "/kinesis/aws4_request SignedHeaders=host;x-amz-date;x-amz-target, Signature=" . $sig;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://' . $awsUrl);
curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_VERBOSE, 1);
curl_setopt($ch, CURLOPT_HEADER, 1);
$result = curl_exec($ch);
curl_close($ch);

var_dump($result);
exit;

?>