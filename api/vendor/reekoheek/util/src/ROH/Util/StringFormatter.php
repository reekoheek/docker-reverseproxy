<?php
namespace ROH\Util;

class StringFormatter
{
    public function __construct($format)
    {
        $this->format = $format;
    }

    public function format($data)
    {
        if ($this->isStatic()) {
            return $this->format;
        } else {
            return preg_replace_callback('/{(\w+)}/', function ($matches) use ($data) {
                return $data[$matches[1]];
            }, $foreignLabel);
        }
    }

    public function isStatic()
    {
        return strpos($foreignLabel, '{') === false;
    }
}
