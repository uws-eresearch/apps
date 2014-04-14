<?php
OCP\User::checkLoggedIn();
OCP\App::checkAppEnabled('file_previewer');

$file = isset($_GET['fname']) ? $_GET['fname'] : '';

$user = OCP\User::getUser();

$p_parts = pathinfo($file);
$basename = $p_parts["basename"]; 

if (\OC\Files\Filesystem::isReadable($file)) {
	list($storage) = \OC\Files\Filesystem::resolvePath($file);
	if ($storage instanceof \OC\Files\Storage\Local) {
		$full_path = \OC\Files\Filesystem::getLocalFile($file);
		$current_content = file_get_contents($full_path);
		$inject = '<script type="text/javascript" src="/owncloud/apps/file_previewer/js/j5slide_embed.js"></script>';
		
		$val = preg_match('/<head>.*<\/head>/s',$current_content, $matches);
		if ($val) {
			$pattern = '/(<head>)(.*)(<\/head>)/s';
			$replacement = '$1$2' . $inject . '$3';
			$current_content = preg_replace($pattern, $replacement, $current_content);
		}
		echo $current_content;
		return;
	}
} elseif (!\OC\Files\Filesystem::file_exists($file)) {
	header("HTTP/1.0 404 Not Found");
	$tmpl = new OC_Template('', '404', 'guest');
	$tmpl->assign('file', $name);
	$tmpl->printPage();
} else {
	header("HTTP/1.0 403 Forbidden");
	die('403 Forbidden');
}