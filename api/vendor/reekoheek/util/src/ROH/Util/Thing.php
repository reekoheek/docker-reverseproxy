<?php

namespace ROH\Util;

use Closure;
use InvalidArgumentException;

class Thing extends Collection
{
    protected $handler;

    public function __construct($attributes)
    {
        if (is_callable($attributes) || is_object($attributes)) {
            $this->handler = $attributes;
        } elseif (is_array($attributes)) {
            parent::__construct($attributes);
        } else {
            throw new InvalidArgumentException('Thing argument must be instance of callable, object, or array');
        }

    }

    public function getHandler()
    {
        if (is_null($this->handler) && isset($this->attributes['class'])) {
            if (isset($this->attributes['config'])) {
                $config = $this->attributes['config'];
                if ($config instanceof Closure) {
                    $config = $config();
                }
                $this->handler = new $this->attributes['class']($config);
            } else {
                $this->handler = new $this->attributes['class']();
            }
        }
        return $this->handler;
    }

    public function __debugInfo()
    {
        if (is_null($this->handler)) {
            return [
                'handler' => $this['class'],
            ];
        } if (is_string($this->handler)) {
            return [
                'handler' => $this->handler.'()',
            ];
        } elseif (is_array($this->handler)) {
            return [
                'handler' => get_class($this->handler[0]).'::'.$this->handler[1].'()',
            ];
        } elseif (is_object($this->handler)) {
            return [
                'handler' => get_class($this->handler),
            ];
        }
    }
}
