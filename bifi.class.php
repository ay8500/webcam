<?php
/**
 * BigFile achive system by Levi
 * @author Levi
 *
 */
class BiFi {
	
	public $numFiles=0;	
	private $fileName="";
	private $fpData=false;
	private $fpIndex=false;
	private $index = array();
	
	public function open($fileName,$flags=null) {
		$this->fileName=$fileName;
		if (file_exists($fileName.".bfi") && file_exists($fileName.".bfd")) {
			$this->fpIndex=fopen($fileName.".bfi", "r");
			$this->index = (array)json_decode(stream_get_contents($this->fpIndex));
			$this->numFiles=sizeof($this->index);
			fclose($this->fpIndex);
			return true;
		} else {
			$this->fpData=false;
			unset($this->index);$this->index=array();
			return true;
		}
	}
	
	public function close() {
		if($this->fpData!==false ) {
			fclose($this->fpData);
			$this->fpData=false;
		}
		unset($this->index);$this->index=array();
	}
	
	public function addFile($fileName,$name) {
		if (file_exists($fileName)) {
			if($this->fpData===false ) {
				$this->fpData=fopen($this->fileName.".bfd", "a");
			}
			if($this->fpData!==false ) {
				if($fp=fopen($fileName, "r")) {
					if (isset($this->index[$name])) {
						unset($this->index[$name]);
					}
					$ret=array();
					$ret["d"]=filemtime($fileName);
					$ret["l"]=filesize($fileName);
					$ret["p"]=filesize($this->fileName.".bfd");
					$this->index[$name]=$ret;
					$this->numFiles=count($this->index);
					
					while (!feof($fp)) {
						fwrite($this->fpData,fread($fp, 32192));
					}
					fclose($fp);
						
					file_put_contents($this->fileName.".bfi", json_encode($this->index), LOCK_EX);
					return true;
				}
			}
		} 
		return false;
	}
	
	public function deleteName($name) {
		if (isset($this->index[$name])) {
			unset($this->index[$name]);
			$this->numFiles=count($this->index);
			file_put_contents($this->filename.".bfi", json_encode($this->index), LOCK_EX);
			return true;
		}
		return false;
	}
	
	public function getFromName($name) {
		if (isset($this->index[$name])) {
			$item=(object)$this->index[$name];
			$this->fpData=fopen($this->fileName.".bfd", "r");
			if($this->fpData!==false ) {
				fseek($this->fpData, (int)$item->p);
				return fread($this->fpData,(int)$item->l); 
			}
		}
		return null;
	}
	
	public function getInfo($name) {
		if (isset($this->index[$name])) {
			return $this->index[$name];
		}
		return null;
	}
	
	public function statIndex($index) {
		$arraykeys=array_keys($this->index);
		if (isset($arraykeys[$index])) {
			$ret=array();
			$ret["name"]=$arraykeys[$index];
			//$retx=$this->index[$arraykeys[$index]];
			return $ret;
		} 
		return null;
	}
	
	
}