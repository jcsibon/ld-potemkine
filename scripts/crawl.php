<?php



require 'vendor/autoload.php';

// use League\HTMLToMarkdown\HtmlConverter;
// $converter = new HtmlConverter(array('strip_tags' => true,'header_style'=>'atx'));

use Symfony\Component\Yaml\Yaml;

header('Content-Type: application/json');

foreach(glob("data/articles/*") as $file)
{
	echo $file.PHP_EOL;
	foreach(json_decode(file_get_contents($file))->result->listArticles as $row)
	{
		$i++;
		echo $row->url.PHP_EOL;

		$post = (array)$row;


		$post["type"] = "post";
		$post["lang"] = "fr-fr";
		$post["grouplang"] = "WkpgXykAAOwmJeNO";
		$post["uid"] = basename($row->url);
		
		echo "https://www.lapeyre.fr".$row->url.PHP_EOL;
	
		$post["html"] = file_get_contents("https://www.lapeyre.fr".$row->url);
		
		// preg_match('<!-- BEGIN ArticleEditorial\.jsp -->([\S\s]*?)<!-- END ArticleEditorial\.jsp -->', $post["html"], $matches);
		preg_match('/<!-- Begin Content_Body_UI\.jspf-->([\S\s]*?)<!-- End Content_Body_UI\.jspf-->/', $post["html"], $matches);
		if(isset($matches[1]))
		{
			$matches[1] = preg_replace('/>([\s]+)</', '><', $matches[1]);
//			$markdown = $converter->convert($matches[1]);
//			$markdown = implode("\n", array_filter(array_map('trim', explode("\n", $markdown))));
//			file_put_contents("packages/1801010900/mark/".basename($row->url).".md",$markdown);
			$post['content']= $matches[1];
		}

		unset($post["html"]);


		unset($post["template"]);
		unset($post["titre"]);
		unset($post["titreMEA"]);
		unset($post["surTitre"]);
		unset($post["tempsLecture"]);
		unset($post["libelleType"]);
		unset($post["url"]);
		unset($post["thematiques"]);
		unset($post["piecesHabitation"]);
		unset($post["produits"]);
		unset($post["sujets"]);
		unset($post["tags"]);
		unset($post["visuelMea"]);
		unset($post["visuelDesktop"]);
		unset($post["visuelMobile"]);

		file_put_contents("_posts/".basename($row->url).".json",Yaml::dump($post));

		if($i === 5) 
			break;
	}
	break;
}