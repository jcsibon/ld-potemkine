<?php

ini_set("memory_limit", "-1");
set_time_limit(0);
error_reporting(E_ALL);

require('../vendor/autoload.php');

$app = new Silex\Application();

$app['debug'] = false;

// Register the monolog logging service
$app->register(new Silex\Provider\MonologServiceProvider(), array(
	'monolog.logfile' => 'php://stderr',
));

// Register view rendering
$app->register(new Silex\Provider\TwigServiceProvider(), array(
		'twig.path' => __DIR__.'/views',
));


$articles = json_decode(file_get_contents("data/articles.json"),1);
$app['twig']->addGlobal('articles', $articles);

$categories = json_decode(file_get_contents("data/categories.json"),1);
$app['twig']->addGlobal('categories', $categories);
$app->get('/categories', function() use($app, $categories) {
	header('Content-Type: application/json');
	die(json_encode($categories));
	return true;
});

$tree = json_decode(file_get_contents("data/tree.json"),1);
$app['twig']->addGlobal('tree', $tree);
$app->get('/tree', function() use($app, $tree) {
	header('Content-Type: application/json');
	die(json_encode($tree));
	return true;
});

$childrens = json_decode(file_get_contents("data/childrens.json"),1);
$app['twig']->addGlobal('childrens', $childrens);
$app->get('/childrens', function() use($app, $childrens) {
	header('Content-Type: application/json');
	die(json_encode($childrens));
	return true;
});

$parents = json_decode(file_get_contents("data/parents.json"),1);
$app['twig']->addGlobal('parents', $parents);

$app['twig']->addGlobal("uri", strtok(trim($_SERVER["REQUEST_URI"],"/"),'?'));


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
if (strpos($_SERVER['SERVER_NAME'], '.test') > 0 || strpos($_SERVER['SERVER_NAME'], 'herokuapp') > 0) $data['session']->preprod = 'true';
$app['twig']->addGlobal('data', $data);

$app->get('/session', function() use($app) {
	header('Content-Type: application/json');
	die(json_encode($data));
	return true;
});

$app->get('/', function() use($app) {
	return $app['twig']->render('pages/index/index.twig');
});

/* Debug */
// print_r($categories);

/* CATALOGUE */
$app->get('/{urlname}-{code}/', function($code) use($app, $categories, $parents) {

	$breadcrumb = array($code);
	if($parents[$code][0] != "")
	{
		$breadcrumb[] = $parents[$code][0];
		if($parents[$parents[$code][0]][0] !="")
		{
			$breadcrumb[] = $parents[$parents[$code][0]][0];
			if($parents[$parents[$parents[$code][0]][0]][0] !="")
			{
				$breadcrumb[] = $parents[$parents[$parents[$code][0]][0]][0];
				if($parents[$parents[$parents[$parents[$code][0]][0]][0]][0] !="")
				{
					$breadcrumb[] = $parents[$parents[$parents[$parents[$code][0]][0]][0]][0];
					if($parents[$parents[$parents[$parents[$parents[$code][0]][0]][0]][0]][0] !="")
					{
						$breadcrumb[] = $parents[$parents[$parents[$parents[$parents[$code][0]][0]][0]][0]][0];
					}
				}
			}
		}
	}

	$breadcrumb = array_reverse($breadcrumb);
	$app['twig']->addGlobal('breadcrumb', $breadcrumb);

	if(isset($categories[$code]['dimensionsPanes']))
	{
		$lines = explode(PHP_EOL, file_get_contents("http://export.beezup.com/Lapeyre/Target2sell_FRA/84b2e910-4e02-4477-af42-225e7d0705e6"));
		$keys = str_getcsv($lines[0],"|");
		array_shift($lines);

		foreach ($lines as $line) {
			$line = str_getcsv($line,"|");
			if((int)$line[0]) {
		    	$beezup[(int)$line[0]] = array_combine($keys, $line);
			}
		}

		foreach ($categories[$code]['dimensionsPanes'] as $keypane => $pane) {
			foreach ($pane['tabs'] as $keytab => $tab) {
				$table[$tab['product']] = array("columns"=>[],"items"=>[]);
				foreach(json_decode(utf8_encode(file_get_contents("data/sas/".$tab['product'].".json")),1)['product']['itemList']['item'] as $item)
				{
					preg_match('/[\S\s]*(?:direction\s(?P<Tirant>[G|D]*))?[\S\s]*(?:Tab.|Tabl.|TabL.|Tableau)[\s]*H[\.]*(?P<H>[0-9]+)[\s]*x[\s]*[l]*[\.]*(?P<l>[0-9]+)/', $item['label']['content'], $matches);
					// die("https://www.lapeyre.fr/wcs/resources/store/10101/productview/".$item['sku']);
					// die(json_encode($matches,1));
					$row = array(
						"url" => $item['seoItem']['urlKeyword']['content'],
						"sku" => $item['sku'], 
						// "price" => number_format(json_decode(file_get_contents("https://www.lapeyre.fr/wcs/resources/store/10101/productview/".$item['sku']),1)['CatalogEntryView'][0]['Price'][0]['priceValue'], 0, ',', '' ),
						"price" => number_format($beezup[$item['sku']]['PRICE'], 0, ',', '' ),
						"content" => $item['label']['content']
					);
					$categories[$code]['dimensionsPanes'][$keypane]['tabs'][$keytab]["table"]['items'][(int)$matches["H"]][(int)$matches["l"]] = $row;
					if(!in_array((int)$matches["l"], $categories[$code]['dimensionsPanes'][$keypane]['tabs'][$keytab]["table"]['columns']))
						$categories[$code]['dimensionsPanes'][$keypane]['tabs'][$keytab]["table"]['columns'][] = (int)$matches["l"];
				}
				asort($categories[$code]['dimensionsPanes'][$keypane]['tabs'][$keytab]["table"]['columns']);
			}
		}
//		header('Content-Type: application/json');
//		die(json_encode($categories[$code]));
		$app['twig']->addGlobal('categories', $categories);
	}
	if(file_exists('views/contents/'.$code.'/'.$code.".twig"))
	{
		return $app['twig']->render("contents/".$code.'/'.$code.".twig");
	}
	else
	{
		if(isset($categories[$code]["template"]))
		{
			return $app['twig']->render("pages/".$categories[$code]["template"]."/".$categories[$code]["template"].".twig");
		}
		else
		{
			switch (substr($code, 0, 3)) {
				case "CCU":
					return $app['twig']->render('pages/universe/universe.twig');
				break;
				case "CCN":
					return $app['twig']->render('pages/family/family.twig');
				break;
				case "FPC":
					return $app['twig']->render('pages/product/product.twig');
				break;
				default:
					return $app['twig']->render('pages/article/article.twig');
			}
		}
	}
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
$app->get('/exampleBundle', function() use($app) {
	return $app['twig']->render('pages/productBundle/productBundle.twig');
});
$app->run();
