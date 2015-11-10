<?php
namespace Rapi\Model;

use Bono\Http\Context;

class Server extends Base
{
    public static function find()
    {
        $result = [];

        $dir = '/etc/nginx/sites-enabled/';
        if ($dh = opendir($dir)) {
            while (($file = readdir($dh)) !== false) {
                if (is_dir($dir.$file)) {
                    continue;
                }

                $result[] = [
                    'file' => $file,
                ];
            }
            closedir($dh);
        }
        return $result;
    }

    public static function get($row)
    {
        $zone = new Server($row, 1);
        return $zone;
    }

    public function validate(Context $context)
    {
        if (empty($this['name'])) {
            $context->throwError(400);
        }

        $this['listen'] = $this['listen'] ?: 80;
        $this['normalized'] = str_replace('.', '_', $this['name']);
    }

    public function persist(Context $context)
    {
        $body = t('server', [
            'entry' => $this,
        ]);
        file_put_contents('/etc/nginx/sites-enabled/'.$this['normalized'], $body);

        $body = t('upstream', [
            'entry' => $this,
            'upstreams' => []
        ]);
        file_put_contents('/etc/nginx/upstream.d/'.$this['normalized'], $body);
    }

    public function remove(Context $context)
    {
        $this->validate($context);

        if (file_exists('/etc/nginx/sites-enabled/'.$this['normalized'])) {
            unlink('/etc/nginx/sites-enabled/'.$this['normalized']);
        }

        if (file_exists('/etc/nginx/upstream.d/'.$this['normalized'])) {
            unlink('/etc/nginx/upstream.d/'.$this['normalized']);
        }
    }
}
