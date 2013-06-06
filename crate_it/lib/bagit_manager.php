<?php
namespace OCA\crate_it\lib;

class BagItManager{
	
	private static $instance;
	
	var $base_dir; 
	var $bag_dir;
	var $crate_root;
	var $manifest;
	
	var $bag;
	var $user;
	
	private function __construct(){
		$this->user = \OCP\User::getUser();
	    $this->base_dir = \OC::$SERVERROOT.'/data/'.$this->user;
	    $this->preview_dir = \OC::$SERVERROOT.'/data/previews/'.$this->user.'/files/';
	    $this->crate_root =$this->base_dir.'/crate_it'; 
		
		if(!file_exists($this->crate_root)){
			mkdir($this->crate_root);
		}
		$this->bag_dir = $this->crate_root.'/crate';
		$this->bag = new \BagIt($this->bag_dir);
		
	    $data_dir = $this->bag->getDataDirectory();
	    $this->manifest = $data_dir.'/manifest.json';
		
		//create manifest file if it doesn't exist
		if(!file_exists($this->manifest)){
			$fp = fopen($this->manifest, 'x');
			fclose($fp);
			$this->bag->update();
		}
	}
	
	public static function getInstance(){
		if(!self::$instance){
			self::$instance = new BagItManager();
		}
		return self::$instance;
	}
	
	public function addToBag($dir, $file){
		
		$input_dir = $this->base_dir.'/files';
		$data_dir = 'data';
		$title = '';
		
		if(basename($dir) === 'Shared'){
			//TODO need to fetch the url from relevant location
		}
		else if(substr($dir, -1) === '/'){
			$input_dir .= '/';
			$data_dir .= '/';
			$title = $file;
		}
		else{
			$input_dir .= $dir.'/';
			$data_dir .= $dir.'/';
			$title = substr($dir, 1).'/'.$file;
		}
		if(is_dir($input_dir.'/'.$file)){
			return "Cannot add a directory";
		}
		
		//add the file urls to fetch.txt so when you package the bag,
		//you can populate the data dir with those files
		$fetch_items = $this->bag->fetch->getData();
		$file_exists = false;
		foreach ($fetch_items as $item) {
			if($item['url'] === $input_dir.$file) {
				$file_exists = true;
				break;
			}
		}
		if($file_exists) {
			return "File is already in crate";
		}
		else {
			$this->bag->fetch->add($input_dir.$file, $data_dir.$file);
			
			//add an entry to manifest as well
			//TODO id and title
			$id = hash_file('sha256', $input_dir.$file);
			
			$entry = array("titles" => array(array('id' => $id, 'title' => $title,
					'filename' => $input_dir.$file)));
			if(filesize($this->manifest) == 0) {
				$fp = fopen($this->manifest, 'w');
				fwrite($fp, json_encode($entry));
				fclose($fp);
			}
			else {
				$contents = json_decode(file_get_contents($this->manifest), true); // convert it to an array.
				$elements = $contents['titles'];
				array_push($elements, array('id' => $id, 'title' => $title,
				'filename' => $input_dir.$file));
				$contents['titles'] = $elements;
				$fp = fopen($this->manifest, 'w');
				fwrite($fp, json_encode($contents));
				fclose($fp);
			}
		}
		
		// update the hashes
		$this->bag->update();
		return "File added to crate";
	}
	
	public function clearBag(){
		$this->bag->fetch->clear();
		
		//clear the manifest as well
		$fp = fopen($this->manifest, 'w+');
		fclose($fp);
		$this->bag->update();
		
		if(file_exists($this->crate_root.'/packages/crate.zip')){
			unlink($this->crate_root.'/packages/crate.zip');
		}
	}
	
	public function updateOrder($neworder){
		$shuffledItems = array();
		//Get id and loop
		foreach ($neworder as $id) {
			foreach ($this->getItemList() as $item) {
				if($id === $item['id'])
				{
					array_push($shuffledItems, $item);
				}
			}
		}
		$newentry = array("titles" => $shuffledItems);
		$fp = fopen($this->manifest, 'w+');
		fwrite($fp, json_encode($newentry));
		fclose($fp);
		$this->bag->update();
	}
	
	//TODO
	public function editTitle($id, $newvalue){
		//edit title here
		$contents = json_decode(file_get_contents($this->manifest), true);
		$items = &$contents['titles'];
		foreach ($items as &$item) {
			if($item['id'] === $id){
				$item['title'] = $newvalue;
			}
		}
		$fp = fopen($this->manifest, 'w+');
		fwrite($fp, json_encode($contents));
		fclose($fp);
		//TODO handle exceptions and return suitable value
		return true;
	}
	
	public function createEpub(){
		//create temp html from manifest
		$pre_content = "<html><body><h1>Table of Contents</h1><p style='text-indent:0pt'>";
		
		foreach ($this->getItemList() as $value) {
			$path_parts = pathinfo($value['filename']);
			$html_file = $path_parts['filename'].'.html';
			$url = $this->preview_dir.$path_parts['basename'].'/'.$html_file;
			$pre_content .= "<a href='".$url."'>".$html_file."</a></br>";
		}
		$manifest_html = $pre_content."</p></body></html>";
		
		$tempfile = tempnam(sys_get_temp_dir(),'');
    	if (file_exists($tempfile)) { 
    		unlink($tempfile); 
    	}
    	mkdir($tempfile);
    	if (is_dir($tempfile)) {
    		$fp = fopen($tempfile.'/manifest.html', 'w+');
			fwrite($fp, $manifest_html);
			fclose($fp);
			//feed it to calibre
			$command = 'ebook-convert '.$tempfile.'/manifest.html '.$tempfile.'/temp.epub --level1-toc //h:h1 --level2-toc //h:h2 --level3-toc //h:h3';
			system($command, $retval);
    	}
		//send the epub to user
		return $tempfile.'/temp.epub';
		
	}
	
	private function getItemList(){
		$contents = json_decode(file_get_contents($this->manifest), true); // convert it to an array.
		return $contents['titles'];
	}
	
	public function createZip(){
		
		$bag_items = $this->bag->fetch->getData();
		if(count($bag_items) === 0)
		{
			return null;
		}
		$tmp = \OC_Helper::tmpFolder();
		\OC_Helper::copyr($this->bag_dir, $tmp);
		
		//create a bag at the outputDir
		$bag = new \BagIt($tmp);
		
		if(count($bag->getBagErrors(true)) == 0){
			//use the fetch file to add data to bag, but don't use $bag->fetch->download(), 
			//yea I know it's weird but have to do at this time
			$fetch_items = $bag->fetch->getData();
			foreach ($fetch_items as $item){
				$bag->addFile($item['url'], $item['filename']);
			}
			$bag->update();
		
			//TODO see if there's one already
			//check if it's latest, if so only create the package
			if(!file_exists($this->crate_root.'/packages')){
				mkdir($this->crate_root.'/packages');
			}
			$zip_file = $this->crate_root.'/packages/crate';
			$bag->package($zip_file, 'zip');
			
			return $zip_file.'.zip';
		}
		else
		{
			$err = $bag->getBagErrors(true);
			print $err;
		}		
	}
	
	public function getFetchData(){
		//read from manifest
		$fp = fopen($this->manifest, 'r');
		$contents = file_get_contents($this->manifest);
		$cont_array = json_decode($contents, true);
		return array_values($cont_array["titles"]);
	}
	
}