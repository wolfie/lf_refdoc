<?php

function index($args) {
	keepCachesUpdated();
	
	$response = new Response();
	$response->template('pagecache/index.html');
	return $response;
}

function page($args) {
	keepCachesUpdated();
	$templateName = 'pagecache/'.$args['context']['section'].'/'.$args['context']['page'].'.html';

	$response = new Response();

	if (file_exists(LF_TEMPLATES_PATH.'/'.$templateName)){
		$response->template($templateName);
	} else {
		$response->redirect(LF_SITE_PATH);
	}
	
	return $response;
}

function section($args) {
	keepCachesUpdated();

	$response = new Response();
	$response->template('pagecache/'.$args['context']['section'].'/index.html');
	return $response;
}

function keepCachesUpdated() {
	$needsUpdating = false;
	$updateFileName = LF_PROJECT_PATH.'/cachesupdated';
	$pagesToBeUpdated = array();
	$newLatestTimestamp = 0;
	$latestTimestamp = 0;
	$index = array();

	if (file_exists($updateFileName)) {
		$latestTimestamp = (int) rtrim(file_get_contents($updateFileName));
	}
		
	foreach (glob(PAGE_SOURCE.'/*', GLOB_ONLYDIR) as $dir) {
		foreach (glob($dir.'/*') as $page) {
			$index[basename($dir)][] = basename($page);

			$filemtime = filemtime($page);

			if ($filemtime > $latestTimestamp) {
				if ($filemtime > $newLatestTimestamp) {
					$newLatestTimestamp = $filemtime;
				}

				$pagesToBeUpdated[] = $page;
				$needsUpdating = true;
			}
		}
	}

	if ($needsUpdating) {
		require_once MARKDOWN_FILE;

		// Fix the manual pages themselves
		foreach ($pagesToBeUpdated as $page) {
			$content = file_get_contents($page);;
			
			if ($content === false) {
				trigger_error('Can\'t read '.$page);
			}

			$html = Markdown($content);

			$templateString = '{% extends "/base.html" %}{% block body %}{% verbatim %}'.$html.'{% endverbatim %}{% endblock %}';
			$templateString .= '{% block nav %}'.buildIndex($html).'{% endblock %}';

			$pageDir = PAGE_CACHE.'/'.basename(dirname($page));
			$pageName = $pageDir.'/'.basename($page);

			if (!file_exists($pageDir)) {
				mkdir($pageDir);
			} elseif (!is_dir($pageDir)) {
				trigger_error($pageDir.' is not a directory');
			}

			$templateFile = fopen($pageName.'.html', 'w') or
				trigger_error('Can\'t write to '.$pageName);

			fwrite($templateFile, $templateString);
			fclose($templateFile);
		}

		// recreate the index
		$indexFile = fopen(PAGE_CACHE.'/index.html', 'w');
		fwrite($indexFile, '{% extends "/base.html" %}{% block body %}<ul>');

		foreach ($index as $section => $sectionArray) {
			$sectionFile = fopen(PAGE_CACHE.'/'.$section.'/index.html', 'w');
			fwrite($sectionFile, '{% extends "/base.html" %}{% block body %}<ul>');

			foreach ($sectionArray as $page) {
				fwrite($sectionFile, '<li><a href="{{ LF_SITE_PATH }}'.$page.'/">'.$page.'</a></li>'.PHP_EOL);
				fwrite($indexFile, '<li><a href="'.$section.'/'.$page.'/">'.$section.': '.$page.'</a></li>'.PHP_EOL);
			}

			fwrite($sectionFile, '</ul>{% endblock %}');
			fclose($sectionFile);
		}

		fwrite($indexFile, '</ul>{% endblock %}');
		fclose($indexFile);

		// update the updated-timestamp
		$timestampFile = fopen($updateFileName, 'w');
		
		fwrite($timestampFile, $newLatestTimestamp);
		fclose($timestampFile);
	}
}

function buildIndex($html) {
	preg_match_all('!<h([1-6])( id="(.+)")?>(.*)<(a|/h)!U', $html, $matches);

	$menu = '';
	$info = array();
	$resultSize = count($matches[0]);

	for ($i = 0; $i < $resultSize; $i++) {
		$info[$i]['depth'] = (int)$matches[1][$i];
		$info[$i]['id'] = $matches[3][$i];
		$info[$i]['title'] = $matches[4][$i];
	}

	if (count($info) > 0) {
		$menu = array();
		$uls = 0;

		foreach ($info as $row => $heading) {
			$menu[$row] = array($heading['title']);

			if ($heading['id']) {
				array_unshift($menu[$row], '<a href="#'.$heading['id'].'">');
				array_push($menu[$row], '</a>');
			}
		}
		
		for ($i=0; $i<count($menu); $i++) {
			if (isset($info[$i-1])
					&& $info[$i]['depth'] < $info[$i-1]['depth']) {
				array_push($menu[$i-1],'</ul>');
				$uls--;
			}
			
			if (isset($info[$i+1]) 
					&& $info[$i]['depth'] < $info[$i+1]['depth']) {
				array_push($menu[$i], '<ul>');
				$uls++;
			}
		}

		while ($uls-- > 0 ) {
			array_push($menu[count($menu)-1], '</ul>');
		}

		foreach ($menu as $i => $row) {
			array_unshift($row,'<li>');
			$lastElement = array_pop($row);
			
			if ($lastElement === '</ul>') {
				array_push($row, '</li>');
			}

			array_push($row, $lastElement);

			if ($lastElement !== '<ul>') {
				array_push($row, '</li>');
			}


			$menu[$i] = $row;
		}

		foreach ($menu as $i => $row) {
			$menu[$i] = implode('', $row);
		}

		$menu = '<ul>'.implode("\n", $menu).'</ul>';
	}

	return $menu;
}