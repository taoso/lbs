<?php
namespace Geo;

class GeoHash {
    static public function encode($lng, $lat, $prec=0.00001) {
        $table = "0123456789bcdefghjkmnpqrstuvwxyz";
        if($prec) {
            $p = $prec;
        } else {
            $lap = strlen($lat)-strpos($lat,".");
            $lop = strlen($lng)-strpos($lng,".");
            $p = pow(10,-max($lap-1,$lop-1,0))/2;
        }
        $minlat = -90; $maxlat = 90; $minlng = -180; $maxlng = 180;
        $latE = 90; $lngE = 180;
        $i=0; $hash = ""; $error = 180;
        while($error>=$p) {
            $chr = 0;
            for($b=4;$b>=0;--$b) {
                if((1&$b) == (1&$i)) {
                    $next = ($minlng+$maxlng)/2;
                    if($lng>$next) {
                        $chr |= pow(2,$b);
                        $minlng = $next;
                    } else {
                        $maxlng = $next;
                    }
                    $lngE /= 2;
                } else {
                    $next = ($minlat+$maxlat)/2;
                    if($lat>$next) {
                        $chr |= pow(2,$b);
                        $minlat = $next;
                    } else {
                        $maxlat = $next;
                    }
                    $latE /= 2;
                }
            }
            $hash .= $table[$chr];
            $i++;
            $error = min($latE,$lngE);
        }
        return $hash;
    }

    static public function expand($hash) {
        list($minlng, $maxlng, $minlat, $maxlat) = self::decode($hash);
        $dlng = ($maxlng - $minlng) / 2;
        $dlat = ($maxlat - $minlat) / 2;
        return array(
            self::encode($minlng - $dlng, $maxlat + $dlat),
            self::encode($minlng + $dlng, $maxlat + $dlat),
            self::encode($maxlng + $dlng, $maxlat + $dlat),
            self::encode($minlng - $dlng, $maxlat - $dlat),
            self::encode($maxlng + $dlng, $maxlat - $dlat),
            self::encode($minlng - $dlng, $minlat - $dlat),
            self::encode($minlng + $dlng, $minlat - $dlat),
            self::encode($maxlng + $dlng, $minlat - $dlat),
        );
    }

    static public function getRect($hash) {
        list($minlng, $maxlng, $minlat, $maxlat) = self::decode($hash);
        return array(
            array($minlng, $minlat),
            array($minlng, $maxlat),
            array($maxlng, $maxlat),
            array($maxlng, $minlat),
        );
    }

    static private function decode($hash) {
        $table = "0123456789bcdefghjkmnpqrstuvwxyz";
        $minlat =  -90; $maxlat = 90; $minlng = -180; $maxlng = 180;
        $latE = 90; $lngE = 180;
        for($i=0,$c=strlen($hash);$i<$c; $i++) {
            $v = strpos($table,$hash[$i]);
            if(1&$i) {
                if(16&$v)$minlat = ($minlat+$maxlat)/2; 
                else $maxlat = ($minlat+$maxlat)/2;
                if(8&$v) $minlng = ($minlng+$maxlng)/2; 
                else $maxlng = ($minlng+$maxlng)/2;
                if(4&$v) $minlat = ($minlat+$maxlat)/2; 
                else $maxlat = ($minlat+$maxlat)/2;
                if(2&$v) $minlng = ($minlng+$maxlng)/2; 
                else $maxlng = ($minlng+$maxlng)/2;
                if(1&$v) $minlat = ($minlat+$maxlat)/2; 
                else $maxlat = ($minlat+$maxlat)/2;
                $latE /= 8;
                $lngE /= 4;
            } 
            else {
                if(16&$v)$minlng = ($minlng+$maxlng)/2; 
                else $maxlng = ($minlng+$maxlng)/2;
                if(8&$v) $minlat = ($minlat+$maxlat)/2; 
                else $maxlat = ($minlat+$maxlat)/2;
                if(4&$v) $minlng = ($minlng+$maxlng)/2; 
                else $maxlng = ($minlng+$maxlng)/2;
                if(2&$v) $minlat = ($minlat+$maxlat)/2; 
                else $maxlat = ($minlat+$maxlat)/2;
                if(1&$v) $minlng = ($minlng+$maxlng)/2; 
                else $maxlng = ($minlng+$maxlng)/2;
                $latE /= 4;
                $lngE /= 8;
            }
        }
        return array($minlng, $maxlng, $minlat, $maxlat);
    }
}
