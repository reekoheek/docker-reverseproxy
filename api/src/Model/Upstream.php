<?php
namespace Rapi\Model;

use Bono\Http\Context;

class Upstream extends Base
{
    public static function get($attributes)
    {
        return new static($attributes);
    }

    public function validate(Context $context)
    {
        if (empty($context['normalized'])) {
            $context->throwError(400);
        }

        if (empty($this['server'])) {
            $context->throwError(400);
        }

        $this['port'] = $this['port'] ?: 80;
        $this['line'] = $this['server'].':'.$this['port'];
    }

    public function persist(Context $context)
    {
        $file = '/etc/nginx/upstream.d/'.$context['normalized'];
        $upstreams = [];
        if (file_exists($file)) {
            array_map(function ($line) use (&$upstreams) {
                preg_match('/server\s+(.+);$/', $line, $matches);
                if (empty($matches)) {
                    return;
                }

                $normalizedLine = trim($matches[1]);


                if ($normalizedLine === '127.0.0.1 down') {
                    return;
                }

                if ($normalizedLine === $this['line']) {
                    return;
                }

                $upstreams[] = [
                    'line' => $normalizedLine,
                ];
            }, explode("\n", file_get_contents($file)));
        }

        $upstreams[] = $this->toArray();

        $body = t('upstream', [
            'entry' => $context,
            'upstreams' => $upstreams
        ]);
        file_put_contents($file, $body);
    }

    public function remove(Context $context)
    {
        $this->validate($context);

        $file = '/etc/nginx/upstream.d/'.$context['normalized'];

        $upstreams = [];
        if (file_exists($file)) {
            array_map(function ($line) use (&$upstreams) {
                preg_match('/server\s+(.+);$/', $line, $matches);
                if (empty($matches)) {
                    return;
                }

                $normalizedLine = trim($matches[1]);

                if ($normalizedLine === '127.0.0.1 down') {
                    return;
                }

                if ($normalizedLine === $this['line']) {
                    return;
                }

                $upstreams[] = [
                    'line' => $normalizedLine,
                ];
            }, explode("\n", file_get_contents($file)));
        }

        $body = t('upstream', [
            'entry' => $context,
            'upstreams' => $upstreams
        ]);
        file_put_contents($file, $body);
    }
}
