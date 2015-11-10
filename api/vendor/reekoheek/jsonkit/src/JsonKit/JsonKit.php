<?php

namespace JsonKit;

class JsonKit
{
    protected static $stack;

    public static function replaceObject($data)
    {
        if ($data instanceof \JsonKit\JsonSerializer) {
            if (in_array($data, self::$stack)) {
                return '*** circular ***';
            }
            array_push(self::$stack, $data);
            $data = $data->jsonSerialize();
            array_pop(self::$stack);
            if (is_object($data)) {
                $data = (array) $data;
            }
        }

        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = self::replaceObject($value);
            }
        }

        return $data;
    }

    public static function encode($data)
    {
        self::$stack = array();

        $jsonData = self::replaceObject($data);
        return json_encode($jsonData);
    }
}
