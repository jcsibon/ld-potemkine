<?php

ini_set("memory_limit", "-1");
set_time_limit(0);

foreach (glob("src/data/sas/*") as $file) 
{
	if($file == "src/data/sas/categories.json")
	{
		$fileContent = json_decode(utf8_encode(file_get_contents($file)),1);
		if($fileContent)
		{
			foreach($fileContent['category'] as $row)
			{
				if($row['published']['content'])
				{
					echo $row['identifier'].PHP_EOL;
					$row['url'] = str_replace("_", "-", $row['identifier']);
					$row['identifier'] = preg_replace('/([\S\s]*)(CCN|CCU|SCU)([0-9]+)/', '$2$3', $row['identifier']);


					if(isset($row['parentIdentifier']))
					{
						$row['parentIdentifier'] = array(preg_replace('/([\S\s]*)(CCN|CCU|SCU)([0-9]+)/', '$2$3', $row['parentIdentifier']));
					}
					if(isset($row['imageFull']['content']))
						$row['imageFull']['content'] = "https://statics.lapeyre.fr/img/catalogue/" . $row['imageFull']['content'];
					if(isset($row['imageThumbnail']['content']))
						$row['imageThumbnail']['content'] = "https://statics.lapeyre.fr/img/catalogue/" . $row['imageThumbnail']['content'];

					$catalog[$row['identifier']] = $row;
				}
			}
		}				
	}
	else
	{
		echo $file.PHP_EOL;
		$fileContent = json_decode(utf8_encode(file_get_contents($file)),1);
		
		if($fileContent && $fileContent["product"]["published"]["content"])
		{
			$row = array(
				"identifier"		=> $fileContent["product"]["partnumber"],
				"imageFull"			=> $fileContent["product"]["imageFull"],
				"label"				=> $fileContent["product"]["label"],
				"type"		 		=> $fileContent["product"]["type"],
				"shortDesc"			=> $fileContent["product"]["shortDesc"],
				"seo"				=> $fileContent["product"]["seo"],
				"longDesc"			=> $fileContent["product"]["longDesc"],
				"additionalDesc1"	=> $fileContent["product"]["additionalDesc1"],
				"imageThumbnail"	=> $fileContent["product"]["imageThumbnail"]
			);							

			if(isset($fileContent["product"]["categoriesParentList"]["categoryParent"]['categoryIdentifier']))
			{
				$row['parentIdentifier'][] = preg_replace('/([\S\s]*)(CCN|CCU|SCU)([0-9]+)/', '$2$3', $fileContent["product"]["categoriesParentList"]["categoryParent"]['categoryIdentifier']);
			}
			else
			{
				foreach($fileContent["product"]["categoriesParentList"]["categoryParent"] as $parent)
				{
					$row['parentIdentifier'][] = preg_replace('/([\S\s]*)(CCN|CCU|SCU)([0-9]+)/', '$2$3', $parent['categoryIdentifier']);
				}
			}

			if(isset($row['imageFull']['content']))
				$row['imageFull']['content'] = "https://statics.lapeyre.fr/img/catalogue/" . $row['imageFull']['content'];
			if(isset($row['imageThumbnail']['content']))
				$row['imageThumbnail']['content'] = "https://statics.lapeyre.fr/img/catalogue/" . $row['imageThumbnail']['content'];

			$catalog[$row['identifier']] = $row;
		}
	}
}
file_put_contents("src/data/log/creating-catalog.json", json_encode($catalog),JSON_PRETTY_PRINT);