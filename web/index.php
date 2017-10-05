<?php
error_reporting(E_ALL);
require('../vendor/autoload.php');

$app = new Silex\Application();
$app['debug'] = true;

// Register the monolog logging service
$app->register(new Silex\Provider\MonologServiceProvider(), array(
  'monolog.logfile' => 'php://stderr',
));

// Register view rendering
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views',
));


//die(json_encode($_GET));
$clients = json_decode(file_get_contents("data/clients.json"));


if(isset($_GET['client']) && isset($clients->{$_GET['client']}))
  $data['session']=$clients->{$_GET['client']};
else
  $data['session']=$clients->guest;

$app['twig']->addGlobal('data', $data);


$app->get('/session', function() use($app) {
  echo '<pre>' . var_export($data, true) . '</pre>';
  die();
  return true;
});


$app->get('/', function() use($app) {
  $app['monolog']->addDebug('logging output.');
  return $app['twig']->render('pages/index.twig');
});

$app->get('/TunnelCommandShippingView', function() use($app) {
  $app['monolog']->addDebug('logging output.');
  return $app['twig']->render('pages/TunnelCommandShippingView.twig');
});

$app->run();
