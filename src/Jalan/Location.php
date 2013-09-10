<?php
namespace Jalan;

use \Geo\GeoHash;

class Location {
    static private $keyLen = 6;
    static $redis;

    public $id;
    public $lng;
    public $lat;

    function __construct($id = null, $lng = null, $lat = null) {
        $this->id = $id;
        $this->lng = $lng;
        $this->lat = $lat;
    }

    function setRedis($redis) {
        $this->redis = $redis;
    }

    function update($id, $lng, $lat) {
        $oldLng = $this->redis->hGet(self::getInfoKey($id), 'lng');
        $oldLat = $this->redis->hGet(self::getInfoKey($id), 'lat');
        if ($oldLng && $oldLat) {
            $hash = GeoHash::encode($oldLng, $oldLat);
            $this->redis->sRem(self::getKey($hash), $id);
        }

        $this->redis->hSet(self::getInfoKey($id), 'lat', $lat);
        $this->redis->hSet(self::getInfoKey($id), 'lng', $lng);

        $hash = GeoHash::encode($lng, $lat);
        $this->redis->sAdd(self::getKey($hash), $id);
    }

    function getNeighbors($lng, $lat) {
        $hash = GeoHash::encode($lng, $lat);
        $hash = substr($hash, 0, self::$keyLen);
        $ids = $this->redis->sMembers(self::getKey($hash));

        foreach (GeoHash::expand($hash) as $hash) {
            $ids = array_merge($ids, $this->redis->sMembers(self::getKey($hash)));
        }
        $neighbors = array();
        foreach ($ids as $id) {
            $neighbors[] = new self(
                $id,
                $this->redis->hGet(self::getInfoKey($id), 'lng'),
                $this->redis->hGet(self::getInfoKey($id), 'lat')
            );
        }

        return $neighbors;
    }

    static function getKey($geohash) {
        return "loc:" . substr($geohash, 0, self::$keyLen);
    }

    static function getInfoKey($id) {
        return "loc:info:{$id}";
    }
}
