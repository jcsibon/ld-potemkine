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

$load_catalog = 1;


if($load_catalog == 1)
{

  $file = array_map("str_getcsv", file("https://docs.google.com/spreadsheets/d/1s10qJviUHayRFRHxSbMGNDKaIg7-gyYAjz6kOPhPm6g/pub?gid=922201227&single=true&output=csv",FILE_SKIP_EMPTY_LINES));

  foreach($file[0] as $cell) {
    $keys[]=preg_replace('/[^A-Za-z0-9\-]/', '', $cell);
  }

  array_shift($file);

  foreach ($file as $i=>$row) {
      foreach($keys as $j=>$key)
        $newrow[$key] = str_replace("_", "-", $row[$j]);

    switch (substr($newrow['ParentID'],0,3)) {
        case "CCR":
            $CCU[$newrow['ID']]=$newrow;
        break;
        case "CCU":
            $SCU[$newrow['ID']]=$newrow;
        break;
        case "SCU":
            $CCN[$newrow['ID']]=$newrow;
        break;
        case "CCN":
            $SCN[$newrow['ID']]=$newrow;
        break;    
    }
  }

  foreach ($SCN as $row)
    $CCN[$row['ParentID']]["Content"][$row['ID']]=$row;

  foreach ($CCN as $row)
    $SCU[$row['ParentID']]["Content"][$row['ID']]=$row;

  foreach ($SCU as $row)
    $CCU[$row['ParentID']]["Content"][$row['ID']]=$row;

  $catalog=$CCU;

  $app['twig']->addGlobal('catalog', $catalog);

  $app->get('/catalog', function() use($app, $catalog) {
    echo '<pre>' . var_export($catalog, true) . '</pre>';
    die();
    return true;
  });
}



$app['twig']->addGlobal("uri", strtok(trim($_SERVER["REQUEST_URI"],"/"),'?'));

//die(json_encode($_GET));
$clients = json_decode(file_get_contents("data/clients.json"));

if(isset($_GET['client']) && isset($clients->{$_GET['client']}))
  $data['session'] = $clients->{$_GET['client']};
else
  $data['session'] = $clients->part;

$carts = json_decode(file_get_contents("data/carts.json"));

if(isset($_GET['cart'])) //&& isset($carts->{$_GET['cart']}))
  $data['session']->cart = $carts->{$_GET['cart']};
else
  $data['session']->cart = $carts->medium;

if(isset($_GET['delivery'])) //&& isset($carts->{$_GET['cart']}))
  $data['session']->cart->delivery = $carts->{$_GET['delivery']};
// else
//  $data['session']->cart->delivery = 0;

if(count($data['session']->cart->articles)) {
  $data['session']->cart->subtotal = 0;
  $data['session']->cart->length = 0;

  foreach ($data['session']->cart->articles as $article) {
    $data['session']->cart->length += $article->quantity;
    if(isset($article->promotion)) {
      $data['session']->cart->subtotal += $article->quantity * ($article->price + $article->promotion);
    } else {
      $data['session']->cart->subtotal += $article->quantity * $article->price;

    }
  }
  if(isset($data['session']->cart->promotion)) {
      $data['session']->cart->subtotal += $data['session']->cart->promotion; 
  }
  if(isset($data['session']->cart->coupon)) {
      $data['session']->cart->subtotal += $data['session']->cart->coupon; 
  }
  $data['session']->cart->vat = $data['session']->cart->subtotal / 6;

  $data['session']->cart->total = $data['session']->cart->subtotal;
  if(isset($data['session']->cart->delivery))
    $data['session']->cart->total += $data['session']->cart->deliveryfees;
}

$shops = json_decode(file_get_contents("data/shops.json"));

$data['store']['shops'] = array_slice(json_decode(file_get_contents("data/shops.json")),0,6);

if (isset($_GET['search'])) $data['session']->search = $_GET['search'];

if (isset($_GET['promo'])) $data['session']->promo = $_GET['promo'];

if (isset($_GET['colisage'])) $data['session']->colisage = $_GET['colisage'];

if (isset($_GET['stock'])) $data['session']->stock = $_GET['stock'];

$app['twig']->addGlobal('data', $data);



$app->get('/session', function() use($app) {
  header('Content-Type: application/json');
  die(json_encode($data));
  return true;
});




















$app->get('/', function() use($app) {
  $app['monolog']->addDebug('logging output.');
  return $app['twig']->render('pages/index/index.twig');
});

$app->get('/{urlname}-{code}/', function($code) use($app) {
  if(file_exists('views/contents/'.$code.".twig"))
  {
    $template = $code;
    return $app['twig']->render("contents/".$template.".twig",array("template"=>$template));   
  }
  else
  {
    $template = "";
    return $app['twig']->render('pages/universe/universe.twig',array("template"=>$template));
  }
})->assert('urlname', '[a-z\-]+')->assert('code', 'CCU[0-9\-]+');

$app->get('/{urlname}-{code}/', function($urlname, $code) use($app) {
  if(file_exists('views/contents/'.$code.".twig"))
  {
    $template = $code;
    return $app['twig']->render("contents/".$template.".twig",array("code"=>$code, "urlname"=>$urlname));   
  }
  else
  {
    $template = "";
    return $app['twig']->render('pages/family/family.twig',array("template"=>$template, "urlname"=>$urlname));
  }
})->assert('urlname', '[a-z\-]+')->assert('code', 'CCN[0-9\-]+');

$app->get('/{urlname}-{code}/', function($code) use($app) {
  if(file_exists('views/contents/'.$code.".twig"))
    $template = $code;
  else
    $template = "";
  return $app['twig']->render('pages/product/product.twig',array("template"=>$template));
})->assert('urlname', '[a-z\-]+')->assert('code', 'FPC[0-9\-]+');

$app->get('/{urlname}-{code}/', function($code) use($app) {
  if(file_exists('views/contents/'.$code.".twig"))
    $template = $code;
  else
    $template = "";
  return $app['twig']->render('pages/article/article.twig',array("template"=>$template));
})->assert('urlname', '[a-z\-]+')->assert('code', '[0-9\-]+');

$app->get('/SearchDisplay', function() use($app) {
  return $app['twig']->render('pages/SearchDisplay/SearchDisplay.twig');
});

$app->get('/TunnelShopCartView', function() use($app) {
  return $app['twig']->render('pages/TunnelShopCartView/TunnelShopCartView.twig');
});

$app->get('/LogonForm', function() use($app) {
  return $app['twig']->render('pages/LogonForm/LogonForm.twig');
});

$app->get('/UserRegistrationForm', function() use($app) {
  return $app['twig']->render('pages/UserRegistrationForm/UserRegistrationForm.twig');
});

$app->get('/TunnelCommandShippingView', function() use($app) {
  return $app['twig']->render('pages/TunnelCommandShippingView/TunnelCommandShippingView.twig');
});

$app->get('/TunnelCommandPaymentView', function() use($app) {
  return $app['twig']->render('pages/TunnelCommandPaymentView/TunnelCommandPaymentView.twig');
});

$app->get('/TunnelCommandConfirmation', function() use($app) {
  return $app['twig']->render('pages/TunnelCommandConfirmation/TunnelCommandConfirmation.twig');
});

$app->run();
