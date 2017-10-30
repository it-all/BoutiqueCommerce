<?php
declare(strict_types=1);

return [
    'settings' => [

        // Slim Settings
        'outputBuffering' => 'append',

        'addContentLengthHeader' => false, // if this is not disabled, slim/App.php line 585 triggered an exception related to error handling, when the php set_error_handler() function was triggered

        'routerCacheFile' => $config['storage']['cache']['routerCacheFile'],

        'authentication' => [
            'maxFailedLogins' => $config['maxFailedLogins'],
            'adminHomeRoutes' => $config['adminHomeRoutes']
        ],

        'authorization' => $config['adminMinimumPermissions'],

        // Twig Settings
        'view' => [
            'paths' => [
                $config['pathTemplates'],
                $config['pathTwigMacros']
            ],
            'pathCache' => $config['storage']['cache']['pathCache'].'twig/',
            'autoReload' => $config['twigAutoReload'],
            'debug' => true
        ],

        // phpMailer Settings
        'mailer' => $mailer,

        'storage' => [
            'pathTwigCache' => $config['storage']['cache']['pathCache'].'twig/',
        ],

        'dirs' => [
            'admin' => $config['dirs']['admin']
        ],

        // General settings
        'isLive' => $config['isLive'],
        'businessName' => $config['businessName'],
        'emails' => $config['emails'],
        'trimAllUserInput' => $config['trimAllUserInput'],
        'adminDefaultRole' => $config['adminDefaultRole'],
        'adminHomeRoutes' => $config['adminHomeRoutes']
    ],

    //Override the default Not Found Handler
    'notFoundHandler' => function ($container) {
        return function ($request, $response) use ($container) {
            return $container['response']
                ->withStatus(404)
                ->withHeader('Content-Type', 'text/html')
                ->withRedirect($container->router->pathFor(ROUTE_PAGE_NOT_FOUND));
        };
    }
];
