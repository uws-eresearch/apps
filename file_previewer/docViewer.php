<?php

OCP\User::checkLoggedIn();
OCP\App::checkAppEnabled('file_previewer');

$filename = isset($_GET['fname']) ? $_GET['fname'] : '';
$sid = isset($_GET['sid']) ? $_GET['sid'] : '';
$fname = isset($_GET['fn']) ? $_GET['fn'] : '';

if(!empty($fname))
{
	$filename = $fname;
}

$user = OCP\User::getUser();


$config_file = \OC::$SERVERROOT.'/data/cr8it_config.json';
if(file_exists($config_file)) {
	$configs = json_decode(file_get_contents($config_file), true); // convert it to an array.
	$fascinator = $configs['fascinator'];
}
else {
	echo "No configuration file";
	return;
}

if(empty($sid)){
	if (\OC\Files\Filesystem::isReadable($filename)) {
		list($storage) = \OC\Files\Filesystem::resolvePath($filename);
		if ($storage instanceof \OC\Files\Storage\Local) {
			$full_path = \OC\Files\Filesystem::getLocalFile($filename);
			if(!file_exists(\OC::$SERVERROOT.'/data/fullpath.txt')){
				$fp = fopen(\OC::$SERVERROOT.'/data/fullpath.txt', 'w');
				fwrite($fp, $full_path);
				fclose($fp);
			}
		}
	} elseif (!\OC\Files\Filesystem::file_exists($filename)) {
		header("HTTP/1.0 404 Not Found");
		$tmpl = new OC_Template('', '404', 'guest');
		$tmpl->assign('file', $name);
		$tmpl->printPage();
	} else {
		header("HTTP/1.0 403 Forbidden");
		die('403 Forbidden');
	}
}

$path_parts = pathinfo($filename);
$extension = $path_parts['extension'];

if($extension === "doc" || $extension === "docx" || $extension === "xls" || $extension === "xlsx"
		|| $extension === "ppt" || $extension === "pptx" || $extension === "odt" || $extension === "odp"
	  	|| $extension === "ods") {
	//$full_path = '/data/'.$user.'/files'. $filename;
	//$revert = array('%21'=>'!', '%2A'=>'*', '%27'=>"'", '%28'=>'(', '%29'=>')');
	//$full_path = strtr(rawurlencode($full_path), $revert);
	$query = 'full_path:"'.md5($full_path).'"';
	$storage_id = \OCA\file_previewer\lib\Solr::getStorageId($query);
	$preview = $path_parts['filename'].'.htm';
	$url = $fascinator['downloadURL'].$storage_id.'/'.$preview;
	$url = str_replace(' ', '%20', $url);
	//OCP\Util::writeLog('file_previewer', 'Log message from Solr plugin - In latest method', 4);
}
else {
	$preview = '/'.basename($path_parts['dirname']).'/'.$path_parts['basename'];
	$url = $fascinator['downloadURL'].$sid.$preview;
	$url = str_replace(' ', '%20', $url);
}

try
{
	$cookie_file = '/tmp/cookie-session';
  	$ch = curl_init();
  	curl_setopt($ch,CURLOPT_URL,$url);
  	#curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
  	#curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
  	#curl_setopt($ch, CURLOPT_USERPWD, "admin:admin" );
  	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  	$content = curl_exec($ch);
  	
  	$result = curl_getinfo($ch);
  	curl_close($ch);
  	
  	//if error, send msg;
  	if(empty($content))
  	{
  		$content = "No previews available";
  	}
  	if(empty($sid)){
	  	//Find the source and alter the source
	  	$rgx = "/(<img [^>]*src=[\"\'])([^\"\']*)([\"\'][^>]*\/?>)/i";
	  	 
	  	$content = preg_replace($rgx, '$1$2?sid='.$storage_id.'$3', $content);
  	}
	
  	echo $content;
  	
}
catch (Exception $e)
{
	// in production you'd probably log or email this error to an admin
	// and then show a special message to the user but for this example
	// we're going to show the full exception
	die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
}

// $sourceDir = OC::$SERVERROOT.'/data/previews/'.$user.'/files'.$path_parts['dirname'];
// $outputFile = $sourceDir.'/'.$path_parts['basename'];

/*if (!(file_exists($outputFile) && (filemtime($outputFile) > filemtime($inputFile)))){
	// New file, create a preview and store in local file system
	$command = 'python /opt/jischtml5/tools/commandline/WordDownOO.py --dataURIs --epub '.escapeshellarg($inputFile).' '.escapeshellarg($outputDir);
	system($command, $retval);
}*/

/*switch ($extension){
	case "epub":
		//Download epub
		header("Content-type:application/epub+zip");
		header("Content-Type: application/force-download");
		header("Content-Disposition: attachment;filename=".$path_parts['basename']);
		readfile($outputFile);
	case "pdf":
		//TODO
		break;
	default:
		$content = file_get_contents($outputFile);
		print $content;
}*/
