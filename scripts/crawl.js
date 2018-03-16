var fs = require('fs');
var got = require('got');
var YAML = require('json2yaml')
var mkdirp = require('mkdirp');


mkdirp("posts", function (err) {
	if (err) console.error(err)
});

fs.readdir('./../data/articles', (err, dir) => {
	for (var i = 0, path; path = dir[i]; i++) {
	    console.log(path);
	    articles = require("./../data/articles/" + path).result.listArticles;

		for (var j = 0, len = articles.length; j < len; j++) {
					
			element = articles[i];

			 
			(async () => {
			    try {
			        const response = await got('sindresorhus.com');
			        console.log(response.body);
			        //=> '<!doctype html> ...'
			    } catch (error) {
			        console.log(error.response.body);
			        //=> 'Internal server error ...'
			    }
			})();

			// $post["html"] = file_get_contents("https://www.lapeyre.fr".$row->url);

//			var contents = fs.readFileSync("https://www.lapeyre.fr" + element["url"]).toString();
//			console.log(contents);

			

			element['title'] = element["titre"];
			element["permalink"] = "articles/" + element["url"];

			element ["layout"] = "post";
			element ["lang"] = "fr-fr";

		  	// console.log(articles[i]['titre']);
		  	// console.log(element['permalink'].replace(/\//g, "-"));
			// echo "https://www.lapeyre.fr".$row->url.PHP_EOL;
			

			/*

			$i++;
			echo $row->url.PHP_EOL;

			$post = (array)$row;


			element ["uid"] = basename($row->url);



			

			$post["html"] = file_get_contents("https://www.lapeyre.fr".$row->url);

			// preg_match('<!-- BEGIN ArticleEditorial\.jsp -->([\S\s]*?)<!-- END ArticleEditorial\.jsp -->', $post["html"], $matches);
			preg_match('/<!-- Begin Content_Body_UI\.jspf-->([\S\s]*?)<!-- End Content_Body_UI\.jspf-->/', $post["html"], $matches);
			if(isset($matches[1]))
			{
			$matches[1] = html_entity_decode($matches[1]);
			$matches[1] = preg_replace('/>([\s]+)</', '><', $matches[1]);
			$matches[1] = preg_replace('/src="https:\/\/www.lapeyre.fr\//', 'src="\/', $matches[1]);
			$matches[1] = preg_replace('/hover_underline/', '', $matches[1]);
			//			$markdown = $converter->convert($matches[1]);
			//			$markdown = implode("\n", array_filter(array_map('trim', explode("\n", $markdown))));
			//			file_put_contents("packages/1801010900/mark/".basename($row->url).".md",$markdown);
			//$post['content']= $matches[1];
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
			echo json_encode($post["visuelMea"]).PHP_EOL;
			//		unset($post["visuelMea"]);
			//		unset($post["visuelDesktop"]);
			//		unset($post["visuelMobile"]);

			file_put_contents("posts/".basename($row->url).".html","---".PHP_EOL.Yaml::dump($post).PHP_EOL."---".PHP_EOL. $matches[1]);

			if($i === 5) 
			break;
			*/
			fs.writeFile('posts/' + element['permalink'].slice( 1 ).replace(/\//g, "-") + '.md', YAML.stringify(element) + '---', 'utf8', function (err) {
				if (err) {
					return console.log(err);
				}
			});



		}
	}
});
