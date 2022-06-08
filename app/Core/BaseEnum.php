<?php


namespace App\Core;

use ReflectionClass;
use ReflectionException;

/**
 * Example of a typical enum:
 *
 *    class DayOfWeek extends Enum
 *    {
 *        const Sunday    = 0;
 *        const Monday    = 1;
 *        const Tuesday   = 2;
 *        const Wednesday = 3;
 *        const Thursday  = 4;
 *        const Friday    = 5;
 *        const Saturday  = 6;
 *    }
 *
 * Usage examples:
 *
 *     $monday = DayOfWeek::Monday                      // (int) 1
 *     DayOfWeek::isValidName('Monday')                 // (bool) true
 *     DayOfWeek::isValidName('monday', $strict = true) // (bool) false
 *     DayOfWeek::isValidValue(0)                       // (bool) true
 *     DayOfWeek::fromString('Monday')                  // (int) 1
 *     DayOfWeek::toString(DayOfWeek::Tuesday)          // (string) "Tuesday"
 *     DayOfWeek::toString(5)                           // (string) "Friday"
 **/
abstract class BaseEnum
{
    private static $constCacheArray = NULL;

    private static function getConstants()
    {
        try {
            if (self::$constCacheArray == NULL) {
                self::$constCacheArray = [];
            }
            $calledClass = get_called_class();
            if (!array_key_exists($calledClass, self::$constCacheArray)) {

                $reflect = new ReflectionClass($calledClass);
                self::$constCacheArray[$calledClass] = $reflect->getConstants();
            }
            return self::$constCacheArray[$calledClass];
        } catch (ReflectionException $e) {

        }
    }


    public static function isValidName($name, $strict = false): bool
    {
        $constants = self::getConstants();

        if ($strict) {
            return array_key_exists($name, $constants);
        }

        $keys = array_map('strtolower', array_keys($constants));
        return in_array(strtolower($name), $keys);
    }

    public static function isValidValue($value, $strict = true): bool
    {
        $values = array_values(self::getConstants());
        return in_array($value, $values, $strict);
    }

    public static function fromString($name)
    {
        if (self::isValidName($name, $strict = true)) {
            $constants = self::getConstants();
            return $constants[$name];
        }

        return false;
    }

    public static function toString($value)
    {
        if (self::isValidValue($value, $strict = true)) {
            return array_search($value, self::getConstants());
        }

        return false;
    }
}
