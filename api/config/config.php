<?php
use Bono\App;

return [
    'nginx' => [

    ],
    'middlewares' => [
        // write json response
        function ($context, $next) {
            $next($context);

            $context->withContentType('application/json');
            $context->write(json_encode($context->getState()));
        },
        [
            'class' => Bono\Middleware\BodyParser::class,
            'config' => [
                'parsers' => [
                    'application/json' => function ($context) {
                        $body = file_get_contents('php://input');
                        return $context->withParsedBody(json_decode($body, true));
                    },
                ]
            ]
        ]
    ],
    'bundles' => [
        [
            'uri' => '/server',
            'class' => Rapi\Bundle\Server::class,
        ],
    ],
    'routes' => [
        [
            'uri' => '/',
            'handler' => function ($context) {
                return [
                    'application_name' => 'reverse-proxy-api',
                    'modules' => [
                        'server' => '/server',
                    ]
                ];
            }
        ]
    ],
];
