<?php

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

$categories = json_decode(file_get_contents("data/sas/categories copie.json"),1)["category"];

foreach($categories as $row)
{
	$row['url'] = str_replace("_", "-", $row['identifier']);
	$row['identifier'] = preg_replace('/([\S\s]*)(CCN|CCU|SCU)([0-9]+)/', '$2$3', $row['identifier']);


	if(isset($row['parentIdentifier']))
	{
		$row['parentIdentifier'] = preg_replace('/([\S\s]*)(CCN|CCU|SCU)([0-9]+)/', '$2$3', $row['parentIdentifier']);
	}
	if(isset($row['imageFull']['content']))
		$row['imageFull']['content'] = "https://statics.lapeyre.fr/img/catalogue/" . $row['imageFull']['content'];
	if(isset($row['imageThumbnail']['content']))
		$row['imageThumbnail']['content'] = "https://statics.lapeyre.fr/img/catalogue/" . $row['imageThumbnail']['content'];

	$categories[$row['identifier']] = $row;
}

foreach (glob("data/log/*") as $file) {
	foreach(json_decode(file_get_contents($file),1) as $row)
	{
		if(isset($row['identifier']))
		{
			foreach($row as $key => $value)
			{
				$categories[$row['identifier']][$key] = $value;			
			}
		}
	}
}



foreach($categories as $row)
{
	$childrens[$row['parentIdentifier']][] = $row['identifier'];
	$parents[$row['identifier']]=$row['parentIdentifier'];

	if(!isset($row['parentIdentifier']))
	{
		$tree[$row['identifier']] = array();
		foreach($categories as $row2)
		{
			if($row['identifier'] === $row2['parentIdentifier'])
			{
				$tree[$row['identifier']][$row2['identifier']] = array();
				foreach($categories as $row3)
				{
					if($row2['identifier'] === $row3['parentIdentifier'])
					{
						$tree[$row['identifier']][$row2['identifier']][$row3['identifier']] = array();
						foreach($categories as $row4)
						{
							if($row3['identifier'] === $row4['parentIdentifier'])
							{
								$tree[$row['identifier']][$row2['identifier']][$row3['identifier']][$row4['identifier']] = array();
							}
						}
					}
				}
			}
		}
	}
}



$app['twig']->addGlobal('categories', $categories);
$app['twig']->addGlobal('tree', $tree);
$app['twig']->addGlobal('childrens', $childrens);
$app['twig']->addGlobal('parents', $parents);


$app->get('/categories', function() use($app, $categories) {
	header('Content-Type: application/json');
	die(json_encode($categories));
	return true;
});

$app->get('/tree', function() use($app, $tree) {
	header('Content-Type: application/json');
	die(json_encode($tree));
	return true;
});


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
			@$data['session']->cart->subtotal += $data['session']->cart->promotion; 
	}
	if(isset($data['session']->cart->coupon)) {
			@$data['session']->cart->subtotal += $data['session']->cart->coupon; 
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
	return $app['twig']->render('pages/index/index.twig');
});

/* CATALOGUE */
$app->get('/{urlname}-{code}/', function($code) use($app, $categories) {
	// die("FPC");
	if(file_exists('views/contents/'.$code.".twig"))
	{
		return $app['twig']->render("contents/".$code.".twig",array("content"=>$content));
	}
	else
	{
		if(isset($categories[$code]["template"]))
		{
			return $app['twig']->render("pages/".$categories[$code]["template"]."/".$categories[$code]["template"].".twig",array("content"=>$content));
		}
		else
		{
		$template = "";
			switch (substr($code, 0, 3)) {
				case "CCU":
					return $app['twig']->render('pages/universe/universe.twig',array("content"=>$content));
				break;
				case "CCN":
					return $app['twig']->render('pages/family/family.twig',array("content"=>$content));
				break;
				case "FPC":
					return $app['twig']->render('pages/product/product.twig',array("content"=>$content));
				break;
				default:
					return $app['twig']->render('pages/article/article.twig',array("template"=>$template));      
			}
		}
	}
	/*

	$content = array();
	if(file_exists('data/radar/'.$code.".json"))
	{
		$content = json_decode(file_get_contents("data/radar/".$code.".json"),1);
	}
	if(file_exists('data/layer/'.$code.".json"))
	{
		foreach(json_decode(file_get_contents("data/layer/".$code.".json"),1) as $row=>$field)
			$content[$row]=$field;
	}
	if(file_exists('views/contents/'.$code.".twig"))
	{
		return $app['twig']->render("contents/".$code.".twig",array("content"=>$content));
	}
	else
	{
		if(isset($categories[$code]["template"]))
		{
			return $app['twig']->render("pages/".$categories[$code]["template"]."/".$categories[$code]["template"].".twig",array("content"=>$content));
		}
		else
		{
		$template = "";
			switch (substr($code, 0, 3)) {
				case "CCU":
					return $app['twig']->render('pages/universe/universe.twig',array("content"=>$content));
				break;
				case "CCN":
					return $app['twig']->render('pages/family/family.twig',array("content"=>$content));
				break;
				case "FPC":
					return $app['twig']->render('pages/product/product.twig',array("content"=>$content));
				break;
				default:
					return $app['twig']->render('pages/article/article.twig',array("template"=>$template));      
			}
		}
	}
	*/
})->assert('urlname', '[a-z\-]+')->assert('code', '(CCU|SCU|CCN|FPC)[0-9\-]+');

$app->get('/SearchDisplay', function() use($app) {
	return $app['twig']->render('pages/SearchDisplay/SearchDisplay.twig');
});

/* COMPTE */
$app->get('/UserRegistrationForm', function() use($app) {
	return $app['twig']->render('pages/UserRegistrationForm/UserRegistrationForm.twig');
});
$app->get('/LogonForm', function() use($app) {
	return $app['twig']->render('pages/LogonForm/LogonForm.twig');
});

/* TUNNEL */
$app->get('/TunnelShopCartView', function() use($app) {
	return $app['twig']->render('pages/TunnelShopCartView/TunnelShopCartView.twig');
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
$app->get('/exampleProduct', function() use($app) {
	return $app['twig']->render('pages/family/exampleProduct.twig');
});
$app->run();
