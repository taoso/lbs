<?php
require 'vendor/autoload.php';
require 'src/autoload.php';

use Jalan\Location;
use Geo\GeoHash;
Use Predis\Client as RedisClient;

$app = new \Slim\Slim();
$app->container->singleton('redis', function () {
    $config = require 'config.php';
    return new RedisClient($config['redis']);
});

$app->get(
    '/geohash',
    function () use ($app) {
        $req = $app->request();
        $lng = $req->get('lng');
        $lat = $req->get('lat');

        $data = array();
        $data['hash'] = GeoHash::encode($lng, $lat);
        $keyLen = 6;
        $data['keys'][] = substr($data['hash'], 0, $keyLen);

        $data['pathes'][] = GeoHash::getRect($data['keys'][0]);

        $neighbors = GeoHash::expand($data['keys'][0]);
        foreach($neighbors as $neighbor) {
            $hash = substr($neighbor, 0, $keyLen);
            $data['keys'][] = $hash;
            $data['pathes'][] = GeoHash::getRect($hash);
        }

        $rep = $app->response();
        $rep->headers('Content-Type', 'application/json');
        $rep->write(json_encode($data));
    }
);

$app->put(
    '/user/:id/location',
    function ($id) use ($app) {
        $req = $app->request;
        $lng = $req->get('lng');
        $lat = $req->get('lat');

        $location = new Location();
        $location->setRedis($app->redis);
        $location->update($id, $lng, $lat);
    }
)->conditions(array('id' => '\d+'));

$app->get(
    '/neighbors',
    function () use($app) {
        $req = $app->request;
        $lng = $req->get('lng');
        $lat = $req->get('lat');

        $location = new Location();
        $location->setRedis($app->redis);
        $neighbors = $location->getNeighbors($lng, $lat);

        $data = array();
        foreach ($neighbors as $neighbor) {
            $data[] = array(
                'id' => $neighbor->id,
                'lng' => $neighbor->lng,
                'lat' => $neighbor->lat,
            );
        }

        $rep = $app->response();
        $rep->headers('Content-Type', 'application/json');
        $rep->write(json_encode($data));
    }
);

$app->run();
