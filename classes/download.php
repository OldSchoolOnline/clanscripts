<?php


include_once("basic.php");
include_once("downloadcategory.php");
include_once("btupload.php");

class Download extends Basic {
	
	protected $objUpload;
	protected $objDownloadCategory;
	protected $strMIMEType;
	protected $arrSplitFileNames;
	protected $intFileSize;
	
	function __construct($sqlConnection) {
				
		$this->MySQL = $sqlConnection;
		$this->strTableKey = "download_id";
		$this->strTableName = $this->MySQL->get_tablePrefix()."downloads";
		
		$this->objDownloadCategory = new DownloadCategory($sqlConnection);
		
		$this->arrSplitFileNames = array();
		
	}
    
	function setCategory($intCatID) {

		return $this->objDownloadCategory->select($intCatID);
		
	}
	
	function uploadFile($uploadfile, $fileloc, $downloadCatID, $outsidelink=false) {
		
		$returnVal = false;
		if($this->setCategory($downloadCatID)) {
			$this->intFileSize = 0;
		
			$allowableExt = $this->objDownloadCategory->getExtensions();
			
			$this->objUpload = new BTUpload($uploadfile, "", $fileloc, $allowableExt, 4, $outsidelink);
		
			if($this->objUpload->uploadFile() && $this->splitFile()) {
				
				$returnVal = true;
				
			}
			
		}
		
		return $returnVal;
	}

	// Split File for Downloads
	
	function splitFile()
	{
	
		$returnVal = false;
		$countErrors = 0;
		$fullFileName = $this->objUpload->getFileLoc().$this->objUpload->getUploadedFileName();

		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$this->strMIMEType = finfo_file($finfo, $fullFileName);
		
		$this->arrSplitFileNames = array();
		$handle = fopen($fullFileName, 'rb');
		if($handle) {
			$file_size = filesize($fullFileName);
			$this->intFileSize = $file_size;
			$parts_size = floor($file_size/2);
			$modulus=$file_size % 2;
			for($i=0;$i<2;$i++) {
				if($modulus!=0 && $i==1) {
					$parts[$i] = fread($handle,$parts_size+$modulus);
				}
				else {
					$parts[$i] = fread($handle,$parts_size);
				}
				
				if($parts[$i] === false) {
					$countErrors++;
				}
	
			}
	
			if(fclose($handle) && $countErrors == 0) {
	
				for($i=0;$i<2;$i++) {
					$filePrefix[$i] = uniqid(time());
					$this->arrSplitFileNames[] = "split_".$filePrefix[$i];
					$tempFileName = $this->objUpload->getFileLoc()."split_".$filePrefix[$i];
					$handle = fopen($tempFileName, 'wb');
					
					if(!$handle || fwrite($handle,$parts[$i]) === false) {
						$countErrors++;
					}
				}
	
				if(fclose($handle) && $countErrors == 0 && unlink($fullFileName)) {
					$returnVal = true;
				}
	
			}
		}
	
		return $returnVal;
	}
		
	
	public function getSplitNames() {
		
		return $this->arrSplitFileNames;
		
	}
	
	
	public function getMIMEType() {
		return $this->strMIMEType;	
	}
	
	public function getFileSize() {
		return $this->intFileSize;
	}
	
    
}
?>
