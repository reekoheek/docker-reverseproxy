<?php
namespace ROH\Util;

class Composition
{
    protected $attributes = [];

    protected $core;

    protected $stack;

    public function compose($callback)
    {
        $this->attributes[] = new Thing($callback);

        return $this;
    }

    public function isCompiled()
    {
        return isset($this->stack);
    }

    public function compile()
    {
        if (!$this->isCompiled()) {
            $this->stack = [$this];
            $len = count($this->attributes);
            for ($i = $len - 1; $i >= 0; $i--) {
                $next = $this->stack[0];

                $handler = $this->attributes[$i]->getHandler();

                array_unshift($this->stack, function (
                    $context
                ) use (
                    $next,
                    $handler
                ) {
                    return call_user_func($handler, $context, $next);
                });
            }
        }

        return $this;
    }

    public function withCore($core)
    {
        $this->core = $core;
        return $this;
    }

    public function apply($context = null)
    {
        return $this->compile()->stack[0]($context);
    }

    public function __invoke($context)
    {
        if (isset($this->core)) {
            $core = $this->core;
            return $core($context);
        }
    }

    public function __debugInfo()
    {
        return [
            'chain' => $this->attributes,
        ];
    }
}
