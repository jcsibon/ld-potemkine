<?php


if(isset($argv[1])) 
{
	switch ($argv[1]) 
	{
		case 'generate':
		case 'g':
			if(isset($argv[2])) 
			{
				switch ($argv[2]) 
				{

					case 'layout':
					case 'l':
						if(isset($argv[3])) 
						{
							$argv[3] = lcfirst(str_replace("_", "", $argv[3]));
							echo "Génération du masque ".$argv[3].PHP_EOL;
							$layout = "web/views/layouts/".$argv[3].".twig";
							if(!file_exists($layout))
							{
								touch($layout);
							}
							else
							{
								die("Le masque existe déjà.".PHP_EOL);
							}
						}
						else
						{
							die("Vous devez préciser un intitulé.".PHP_EOL);
						}
						break;	
					case 'page':
					case 'p':
						if(isset($argv[3])) 
						{
							$argv[3] = lcfirst(str_replace("_", "", $argv[3]));
							echo "Génération de la vue ".$argv[3].PHP_EOL;
							$view = "web/views/pages/".$argv[3].".twig";

							if(file_exists($view) && (!isset($argv[4]) || $argv[4] != "-force"))
							{
								die("La vue existe déjà.".PHP_EOL);							
							}
							$viewContent = "";
							$viewContent .= '{% extends "layout.html" %}'.PHP_EOL;
							$viewContent .= PHP_EOL;
							$viewContent .= '{% block content %}'.PHP_EOL;
							$viewContent .= PHP_EOL;
							$viewContent .= 'Page '.$argv[3].PHP_EOL;
							$viewContent .= PHP_EOL;
							$viewContent .= '{% endblock %}'.PHP_EOL;
							file_put_contents($view, $viewContent);
						}
						else
						{
							die("Vous devez préciser un intitulé.".PHP_EOL);
						}
						break;	
					case 'storewidget':
					case 'w':
						if(isset($argv[3])) 
						{
							$argv[3] = lcfirst(str_replace("_", "", $argv[3]));
							echo "Génération du storewidget ".$argv[3].PHP_EOL;
							$dir = "web/views/storeWidgets/".$argv[3];
							if(!is_dir($dir))
							{
								mkdir($dir);
							}
							else
							{
								if(!isset($argv[4]) || $argv[4] != "-force")
								{
									die("Le dossier existe déjà.".PHP_EOL);									
								}
							}
							$twigContent = "";
							$twigContent .= PHP_EOL;
							$twigContent .= 'Storewidget '.$argv[3].PHP_EOL;
							file_put_contents($dir."/".$argv[3].".twig", $twigContent);
							touch($dir."/".$argv[3].".scss");
						}
						else
						{
							die("Vous devez préciser un intitulé.".PHP_EOL);
						}
						break;					
					default:
						die("Vous devez préciser un objet.".PHP_EOL);
						break;
				}	
			}
			else 
			{
				die("Commande incomplète".PHP_EOL);
			}
			break;
		default:
			die("Vous devez préciser une commande.".PHP_EOL);
			break;
	}	
}
else 
{
	die("Commande incomplète".PHP_EOL);
}
