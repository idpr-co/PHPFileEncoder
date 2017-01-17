<?
/**
*	@package	: jopesphClasses/securePHPFiles
*	@version  	: 1.2
*	@author 	: jafar rezaei
*	@email 		: bomber.man87@yahoo.com
*	@website 	: http://rezaei.ir
*
*	Secure Files class is made to make php files more secure and reliable to give
*	them to 3rd-party people , however this is not use any special extention
*	but we can imagine this not every body can edit our files and see every
*	thing about codes. This class uses base64 and encode files .
*/

namespace jopesphClasses;


error_reporting(E_ALL);
use \RecursiveIteratorIterator;
use \RecursiveDirectoryIterator;

/**
*	@class : securePHPFiles
*	Main class of secure files class
*/

class securePHPFiles {

	private $fileArrays 		= array();
	private $hashLength 		= 64;
	private $securedDirectory 	= "secure/";


	/**
	*	Function getFiles
	*	@param path = the path to get files
	*	@param ext 	= extention of files to load as default php
	*	@return = main class object
	*/
	private function getFiles($path , $ext){
		if(is_dir($path)){
			$dir_iterator = new RecursiveDirectoryIterator($path);
			$iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST);
			// could use CHILD_FIRST if you so wish
			
			foreach ($iterator as $file) {
			    if ($file->isFile() && pathinfo($file , PATHINFO_EXTENSION) == $ext ) {
			        $this->fileArrays[] = $file->getPathname() ;
			    }
			}
		}else{
			echo "Path not exist !";
		}
	}


	/**
	*	Function encodeFiles
	*	@return = Show message
	*/
	public function encodeFiles($path , $ext = "php"){
		$this->getFiles($path , $ext);
		foreach ($this->fileArrays as $file) {
			$mainFile = file_get_contents($file);

			$RandomEnc = $this->randomKey($this->hashLength);
			$RandomRep = $this->randomKey($this->hashLength, array(">", "?", "<", ",", ";") );

			$mainFile = strtr($mainFile , $RandomRep , $RandomEnc);
			$encode_and_replaced = base64_encode($mainFile);
			$replacePart = '$_X=base64_decode($_X);$_X=strtr($_X,"'.$RandomEnc.'","'.$RandomRep.'");eval("?> $_X");$_X=0;';
			$replacePart_Bottom = base64_encode($replacePart);

			$this->saveFile($file , array($encode_and_replaced , $replacePart_Bottom ));
		}
	}

	/**
	*	Function decodeFiles
	*	@return = Show message
	*/
	public function decodeFiles($path , $ext = "php"){

		$this->getFiles($path , $ext);

		if(count($this->fileArrays) > 0) {

			foreach ($this->fileArrays as $file) {
				$mainFile = file_get_contents($file);

				$RandomEnc = $this->randomKey($this->hashLength);
				$RandomRep = $this->randomKey($this->hashLength);

				$mainFile = strtr($mainFile , $RandomRep , $RandomEnc);
				$encode_and_replaced = base64_encode($mainFile);
				$replacePart = '$_X=base64_decode($_X);$_X=strtr($_X,"'.$RandomEnc.'","'.$RandomRep.'");
eval("?> $_X");$_X=0;';
				$replacePart_Bottom = base64_encode($replacePart);

				$this->saveFile($file , array($encode_and_replaced , $replacePart_Bottom ));

			}

		}else{
			echo "noFile Found";
		}

	}


	/**
	*	Function saveFile
	*	@param file = file address
	*	@param content = content of file
	*	@return = Show message
	*/
	private function saveFile($file , $content){

		$realPathName = $this->securedDirectory . $file;
		$realPath = substr($realPathName, 0 , strrpos($realPathName , "/"));


		if (!file_exists($realPathName) && !is_dir($realPath) && !mkdir($realPath, 0777, true)) {
			echo "Cannot create path";
		}

		$data = '<?php $_F=__FILE__;$_X = "'.$content[0].'";
eval(base64_decode("'.$content[1].'"));';

		if(file_put_contents($realPathName, $data)){
			echo "File : {$file}  Succesfuly Encoded ...<br/>";
		}else{
			echo "Error While Saving file : {$file} ";
		}
	}

	/**
	*	Function randomKey
	*	@param length = length of returned string
	*	@return = random string 
	*/
	private function randomKey($specialChars = array() ) {

		$key = "";

		$keys = array_merge(
			range(0, 9),
			range('a', 'z'),
			range('A', 'Z'),
			array(")", "(", "{", "}" ,"[" , "]" , "=" , "+" , "-" , "_" , "@" , "*" )
		);

		shuffle($keys);



		$specialCharsLengh = count($specialChars);
		if($specialCharsLengh > 0){
			shuffle($specialChars);
			for ($i=0; $i < $specialCharsLengh; $i++) { 
				$key .= $specialChars[$i];
			}
		}

		foreach ($keys as $k) {
			$key .= $k;
		}


		return $key;
	}

}

