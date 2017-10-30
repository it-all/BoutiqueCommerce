<?php
declare(strict_types=1);

use It_All\Spaghettify\Src\Infrastructure\Security\Authentication\AuthenticationService;
use It_All\Spaghettify\Src\Infrastructure\Security\Authorization\AuthorizationService;
use Slim\Views\TwigExtension;
use Slim\Views\Twig;

// DIC configuration

// -----------------------------------------------------------------------------
// Services (Dependencies)
// -----------------------------------------------------------------------------

// Database
$container['database'] = function($container) use ($database) {
    return $database;
};

// Authentication
$container['authentication'] = function($container) {
    $settings = $container->get('settings');
    return new AuthenticationService($settings['authentication']['maxFailedLogins'], $settings['authentication']['adminHomeRoutes']);
};

// Authorization
$container['authorization'] = function($container) {
    $settings = $container->get('settings');
    return new AuthorizationService($settings['authorization']);
};

// System Events (Database Log)
$container['systemEvents'] = function($container) use ($systemEventsModel) {
    return $systemEventsModel;
};

// Twig
$container['view'] = function ($container) {
    $settings = $container->get('settings');
    $view = new Twig($settings['view']['paths'], [
        'cache' => $settings['view']['pathCache'],
        'auto_reload' => $settings['view']['autoReload'],
        'debug' => $settings['view']['debug']
    ]);

    // Instantiate and add Slim specific extension
    $basePath = rtrim(str_ireplace('index.php', '', $container['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new TwigExtension($container->router, $basePath));

    if ($settings['view']['debug']) {
        // allows {{ dump(var) }}
        $view->addExtension(new Twig_Extension_Debug());
    }

    // make authentication class available inside templates
    $view->getEnvironment()->addGlobal('authentication', [
        'check' => $container->authentication->check(),
        'user' => $container->authentication->user()
    ]);

    // make authorization class available inside templates
    $view->getEnvironment()->addGlobal('authorization', [
        'check' => $container->authorization->check()
    ]);

    if (isset($_SESSION[SESSION_ADMIN_NOTICE])) {
        $view->getEnvironment()->addGlobal('adminNotice', $_SESSION[SESSION_ADMIN_NOTICE]);
        unset($_SESSION[SESSION_ADMIN_NOTICE]);
    }

    // frontend
    if (isset($_SESSION[SESSION_NOTICE])) {
        $view->getEnvironment()->addGlobal('notice', $_SESSION[SESSION_NOTICE]);
        unset($_SESSION[SESSION_NOTICE]);
    }

    // make some config setting available inside templates
    $view->getEnvironment()->addGlobal('isLive', $settings['isLive']);
    $view->getEnvironment()->addGlobal('businessName', $settings['businessName']);

    // allow access to current script name inside templates
    $view->getEnvironment()->addGlobal('currentUri', $container['request']->getUri()->getPath());

    return $view;
};

// Mailer
$container['mailer'] = function($container) {
    $settings = $container->get('settings');
    return $settings['mailer'];
};

// Form Validation
$container['validator'] = function ($container) {
    return new \It_All\Spaghettify\Src\Infrastructure\Utilities\ValitronValidatorExtension();
};

// CSRF
$container['csrf'] = function ($container) {
    $storage = null; // cannot directly pass null because received by reference.
    // setting the persistentTokenMode parameter true allows redisplaying a form with errors with a render rather than redirect call and will not cause CSRF failure if the page is refreshed (http://blog.ircmaxell.com/2013/02/preventing-csrf-attacks.html)
    $guard = new \Slim\Csrf\Guard('csrf', $storage, null, 200, 16, true);
    $guard->setFailureCallable(function ($request, $response, $next) {
        $request = $request->withAttribute("csrf_status", false);
        return $next($request, $response);
    });
    return $guard;
};
