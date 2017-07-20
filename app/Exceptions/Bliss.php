<?php

namespace App\Exceptions;

use Exception;

class Bliss
{
    /**
     * E.G.
     * - Bliss::exception(function() { ... })
     * - Bliss::queryException('/dupplicate entry/i', function() { ... })
     */
    public static function __callStatic($method, $arguments)
    {
        if (isset($arguments[1])) {
            $regex = array_shift($arguments);
        }

        try {
            return $arguments[0]();
        } catch (Exception $e) {
            if (class_basename($e) != ucfirst($method)) {
                throw $e;
            }

            if (isset($regex) && !preg_match($regex, $e->getMessage())) {
                throw $e;
            }
        }
    }
}
