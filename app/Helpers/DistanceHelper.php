<?php

namespace App\Helpers;

use Exception;


class DistanceHelper
{
    static function calculate_distance($local_lat, $local_long, $event_lat, $event_long)
    {
        try {
            $degrees = rad2deg(acos((sin(deg2rad($local_lat))*sin(deg2rad($event_lat))) + (cos(deg2rad($local_lat))*cos(deg2rad($event_lat))*cos(deg2rad($local_long-$event_long)))));
            $kilometers = $degrees * 111.13384;
            $miles = $kilometers * 0.62137;
            return $miles;
        } catch (Exception $e)
        {
            return ($e->getMessage());
        }
    }
}
