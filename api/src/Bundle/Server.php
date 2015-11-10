<?php
namespace Rapi\Bundle;

use Bono\App;
use Bono\Http\Context;
use Bono\Bundle;
use Rapi\Model\Server as MServer;
use Rapi\Model\Upstream as MUpstream;

class Server extends Bundle
{
    public function __construct(array $options = [])
    {
        $options = App::getInstance()['nginx'] ?: [];

        parent::__construct($options);

        $this->routeMap(['GET'], '/', [$this, 'search']);
        $this->routeMap(['POST'], '/', [$this, 'create']);
        $this->routeMap(['DELETE'], '/', [$this, 'delete']);

        $this->routeMap(['GET'], '/reload', [$this, 'reload']);
        $this->routeMap(['POST'], '/{normalized}/upstream', [$this, 'upstreamCreate']);
        $this->routeMap(['DELETE'], '/{normalized}/upstream', [$this, 'upstreamDelete']);
    }

    public function cleanseUnavailableUpstreams()
    {
        $dir = '/etc/nginx/upstream.d/';
        if ($dh = opendir($dir)) {
            while (($file = readdir($dh)) !== false) {
                if (is_dir($dir.$file)) {
                    continue;
                }

                $upstreams = [];
                array_map(function ($line) use (&$upstreams) {
                    preg_match('/server\s+(.+);$/', $line, $matches);
                    if (empty($matches)) {
                        return;
                    }

                    $normalizedLine = trim($matches[1]);


                    if ($normalizedLine === '127.0.0.1 down') {
                        return;
                    }

                    $host = explode(':', $normalizedLine)[0];

                    $ip = gethostbyname($host);
                    if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
                        return;
                    }

                    $upstreams[] = [
                        'line' => $normalizedLine,
                    ];
                }, explode("\n", file_get_contents($dir.$file)));

                $body = t('upstream', [
                    'entry' => ['normalized' => $file],
                    'upstreams' => $upstreams
                ]);
                file_put_contents($dir.$file, $body);
            }
            closedir($dh);
        }
    }

    public function reload()
    {
        $this->cleanseUnavailableUpstreams();

        $descriptorspec = [
            ['pipe', 'r'],
            ['pipe', 'w'],
            ['pipe', 'w'],
        ];

        $process = proc_open('/usr/sbin/nginx -s reload', $descriptorspec, $pipes);

        if (!is_resource($process)) {
            throw new \Exception('Unexpected unknown error while reloading configuration');
        }

        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[0]);
        fclose($pipes[1]);
        $status = proc_get_status($process);
        proc_close($process);

        if ($status && $status['exitcode'] > 0) {
            throw new \Exception("Nginx reload failed,\n".$stderr);
        }
    }

    public function search(Context $context)
    {
        return MServer::find();
    }

    public function create(Context $context)
    {
        $body = $context->getParsedBody();

        $server = new MServer($body);
        $server->save($context);

        $this->reload();

        return $server->toArray();
    }

    public function delete(Context $context)
    {
        $body = $context->getParsedBody();
        $server = MServer::get($body);
        $server->remove($context);

        $this->reload();

        return [];
    }

    public function upstreamCreate(Context $context)
    {
        $body = $context->getParsedBody();

        $upstream = new MUpstream($body);
        $upstream->save($context);

        $this->reload();

        return $upstream->toArray();
    }

    public function upstreamDelete(Context $context)
    {
        $body = $context->getParsedBody();

        $upstream = MUpstream::get($body);
        $upstream->remove($context);

        $this->reload();

        return [];
    }
}
