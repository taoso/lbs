<?php

$MAX_LNG = 117.215881;
$MIN_LNG = 116.832733;
$MAX_LAT = 36.741635;
$MIN_LAT = 36.597338;

function getLngLat($minLng, $maxLng, $minLat, $maxLat) {
    $r = mt_rand(0, 99999) / 10000;
    $lng = $minLng + ($maxLng - $minLng) * $r;
    $r = mt_rand(0, 99999) / 100000;
    $lat = $minLat + ($maxLat - $minLat) * $r;

    return array($lng, $lat);
}

$ch = curl_init();
for (;;) {
    $url = "http://192.168.1.125:8080/neighbors?";
    list($lng, $lat) = getLngLat($MIN_LNG, $MAX_LNG, $MIN_LAT, $MAX_LAT);
    $url .= http_build_query(array(
        'lng' => $lng,
        'lat' => $lat,
    ));
    echo $url. "\n";

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    $result = curl_exec($ch);
    $result = json_decode($result);
    echo count($result) . "\n";
}
curl_close();
