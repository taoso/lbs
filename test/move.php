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

function getUserId() {
    return mt_rand(1, 200000);
}

$ch = curl_init();
for (;;) {
    $id = getUserId();
    $url = "http://192.168.1.125:8080/user/$id/location?";
    list($lng, $lat) = getLngLat($MIN_LNG, $MAX_LNG, $MIN_LAT, $MAX_LAT);
    $url .= http_build_query(array(
        'lng' => $lng,
        'lat' => $lat,
    ));
    echo $url. "\n";

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    $result = curl_exec($ch);
}
curl_close();
