<?php
/**
 * BigFile achive system by Maierlabs
 * @author Levi
 * @version 2.0
 */
class BiFi {

    const BIFI_DATA_EXTENSION = '.bfd';
    const BIFI_INDEX_EXTENSION = '.bfi';

    const BIFI_MAX_INDEX_LENGTH_IN_MEMORY=68000;

    public $numFiles=0;
	public $numDeletedFiles=0;
    private $fileName="";

    /**
     * opens a bifi archive
     * @param string $fileName file including the path
     * @param not used $flags
     */
    public function open($fileName,$flags=null) {
        $this->fileName=$fileName;
        if (file_exists($fileName.self::BIFI_INDEX_EXTENSION)) {
            $this->numFiles = $this->getArchiveFileCount();
        } else {
            $this->numFiles = 0;
        }
        return true;
    }

    /**
     * close the bifi archive
     */
    public function close() {
        $this->numFiles=null;
        $this->fileName=null;
    }

    /**
     * Adds a file to archive
     * @param string $fileName The path to the file to add.
     * @param string $name This is the local name inside the archive
     * @return boolean
     */
    public function addFile($fileName,$name) {
        if (!file_exists($fileName))
            return false;

        $fpData=fopen($this->fileName.self::BIFI_DATA_EXTENSION, "a");
        if($fpData===false )
            return false;

        $fp=fopen($fileName, "r");
        if ($fp===false)
            return false;

        $ret=array();
        $ret["d"]=filemtime($fileName);
        $ret["l"]=filesize($fileName);
        $ret["p"]=filesize($this->fileName.self::BIFI_DATA_EXTENSION);
        $index=array();
        $index[$name]=$ret;

        if (!$this->addIndex($index))
            return false;

        $this->numFiles++;
        while (!feof($fp)) {
            fwrite($fpData,fread($fp, 64382));
        }
        fclose($fp);
        fclose($fpData);

        return true;
    }

    /**
     * delete element from archive
     * important: will not free the data in the datafile, only the first char of name will be asterixed.
     * to do this please use the reorganise function
     * @param string $name
     * @return boolean
     */
    public function deleteName($name) {
        if ($name==null || $name=="")
            return false;

        $fp=fopen($this->fileName.self::BIFI_INDEX_EXTENSION,"r");
        if ($fp===false)
            return false;

        $ret=array();
        $filePos=0;
        $index=0;
        $found=false;
        $fileContent='';
        while (!feof($fp)) {
            $fileContent.=fread($fp, self::BIFI_MAX_INDEX_LENGTH_IN_MEMORY);
            $fcArray=explode('}', $fileContent);
            $l=sizeof($fcArray)-1;
            foreach ($fcArray as $i=>$item) {
                if ($i<$l) {
                    $json=ltrim($item,",{");
                    $ret=json_decode("{".$json."}}",true);
                    if (key($ret)===$name) {
                        $found=true;
                        break;
                    }
                    $filePos += strlen($item)+1;
                    $index++;
                } else  {
                    $fileContent=$item;
                }
            }
        }
        fclose($fp);

        if ($found) {
            $fp=fopen($this->fileName.self::BIFI_INDEX_EXTENSION,"r+");
            if ($fp===false)
                return false;

            if (-1==fseek($fp,$filePos+2))
                return false;

            if (!fwrite($fp,"*")) {
                return false;
            }
            fclose($fp);
            $this->numFiles--;
            return true;
        }

        return false;
    }

    /**
     * get string from archive by name
     * @param string $name
     * @return data | null
     */
    public function getFromName($name) {
        $item = $this->getFileInfo("name",$name);
        $item =$item[$name];
        if ($item===null || (int)$item["l"]==0)
            return null;


        $fpData=fopen($this->fileName.self::BIFI_DATA_EXTENSION, "r");
        if($fpData===false )
            return null;

        if(-1==fseek($fpData, (int)$item["p"]))
            return null;
        $ret = fread($fpData,(int)$item["l"]);
        fclose($fpData);
        return $ret;
    }


    /**
     * The function obtains information about the entry defined by its index.
     * @param int $index Index of the entry
     * @return array containing the entry details or FALSE on failure.
     */
    public function statIndex($index) {
        if ($index+1>$this->numFiles)
            return false;
        $info= $this->getFileInfo("index",$index);
        if($info==null) {
            return false;
        } else {
            return $info;
        }
        return false;
    }

    /**
     * Returns the count of elements in the archise spezified by the file name.
     * This dunction does'nt open the archive
     * @param string $fileName
     * @return number
     */
    public function getArchiveFileCount ($filter="",$returnArray=false) {
        if (!file_exists($this->fileName.self::BIFI_INDEX_EXTENSION))
            return 0;

        $fp=fopen($this->fileName.self::BIFI_INDEX_EXTENSION,"r");
        if ($fp===false)
            return 0;

		if($returnArray) {
			$archArray=array();
			$idx=0;
		}

        $count=0;
        $this->numDeletedFiles=0;
        $fileContent='';
        while (!feof($fp)) {
            $fileContent.=fread($fp, self::BIFI_MAX_INDEX_LENGTH_IN_MEMORY);
            $fileContent= str_replace("}}","}",$fileContent);
            $fcArray=explode('}', $fileContent);
            $l=sizeof($fcArray)-1;
            foreach ($fcArray as $i=>$item) {
                if ($i<$l) {
                    if (strstr($item,"*")===false) {               //elements that contains * are "deleted"
                        if (""==$filter || strstr($item,$filter)) {  //filter
                            $count++;
                            if ($returnArray) {
                                $json = "{" . ltrim($item, ",{") . "}}";
                                $itemArray = json_decode($json, true);
                                $archArray[$idx++] = key($itemArray);
                            }
                        }
					} else {
                        $this->numDeletedFiles++;
                    }
                } else  {
                    $fileContent=$item;
                }
            }
        }
        fclose($fp);
		if($returnArray) {
			return $archArray;
		} else {
			return $count;
		}
    }

    /**
     * Returns the index object
     * @param string $by 'index', 'name'
     * @param string $value the index or name value
     * @return array | null
     */
    public function getFileInfo($by,$value) {
        if($by!=="index" && $by!=="name")
            return null;

        if (!file_exists($this->fileName.self::BIFI_INDEX_EXTENSION))
            return null;

        $fp=fopen($this->fileName.self::BIFI_INDEX_EXTENSION,"r");
        if ($fp===false)
            return null;

        $ret=array();
        $index=0;
        $fileContent='';
        while (!feof($fp)) {
            $fileContent.=fread($fp, self::BIFI_MAX_INDEX_LENGTH_IN_MEMORY);
            $fcArray=explode('}', $fileContent);
            $l=sizeof($fcArray)-1;
            foreach ($fcArray as $i=>$item) {
                if ($i<$l) {
                    $json="{".ltrim($item,",{")."}}";
                    $ret=json_decode($json,true);
                    if ($by==="index") {
                        if (strstr(key($ret),"*")===false) { //elements that contains * are "deleted"
                            if ($index === $value)
                                return $ret;
                            $index++;
                        }
                    } else {

                        if (key($ret)===$value)
                            return $ret;
                    }
                } else  {
                    $fileContent=$item;
                }
            }
        }
        fclose($fp);
        return null;
    }


    /**
     * reorganise file,  release the data holes due delete files in the data file
     * first check if there are anny holes in the data file, deleted entrys are marked with an asterix in the index file
     * @param string $fileName
     * @param bool $onlyCheck just check if any holes exists
     * @return array
     */
    public function reorganize($onlyCheck=false) {
        $ret=$this->reorganizeCheck(true);
        if ($ret["freeSize"]!==0 && !$onlyCheck) {
            return $this->reorganizeCheck(false);
        }
        return $ret;
    }

    /**
     * reorganise file,  release the data holes due delete files in the data file
     * @param string $fileName
     * @param bool $onlyCheck just check if any holes exists
     * @return array
     */
    private function reorganizeCheck($onlyCheck=false) {
        $ret = array();
        if (!$onlyCheck) {
            $fpd=fopen($this->fileName.self::BIFI_DATA_EXTENSION,"r");
            $fileTempName=$this->fileName.'.'.(100+rand(1,899));
            $fpti=fopen($fileTempName.self::BIFI_INDEX_EXTENSION, "w");
            $fptd=fopen($fileTempName.self::BIFI_DATA_EXTENSION, "w");

            if ($fpti==false || $fptd==false || $fpd==false)
                return false;
        }


        $calculatedSize=0;$count=0;$error=false;

        for ($i=0; $i<$this->numFiles; $i++) {
            $idx= $this->statIndex($i);
            $oldPosition = $idx[key($idx)]["p"];
            $idx[key($idx)]["p"]=$calculatedSize;   //the new position of content in the data file
            $calculatedSize += $idx[key($idx)]["l"];
            $count++;
            if (!$onlyCheck) {
                $separator = ($i == 0 ? "" : ",");
                $json = trim(json_encode($idx), "{}") . "}";
                if (!fwrite($fpti, $separator . $json))
                    return false;
                if (-1==fseek($fpd, $oldPosition) )
                    return false;
                if (!fwrite($fptd, fread($fpd, $idx[key($idx)]["l"])))
                    return false;
            }
        }
        if (!$onlyCheck) {
            fclose($fpti);fclose($fptd);fclose($fpd);
        }
        $ret['count']=$count;
        $ret['oldSize']=filesize($this->fileName.self::BIFI_DATA_EXTENSION);
        $ret['calcSize']=$calculatedSize;
        $ret['freeSize']=$ret['oldSize']-$ret['calcSize'];
        if (!$onlyCheck && $error===false) {
            $ret['newSize']=filesize($fileTempName.self::BIFI_DATA_EXTENSION);
            unlink($this->fileName.self::BIFI_DATA_EXTENSION);
            rename($fileTempName.self::BIFI_DATA_EXTENSION, $this->fileName.self::BIFI_DATA_EXTENSION);
            unlink($this->fileName.self::BIFI_INDEX_EXTENSION);
            rename($fileTempName.self::BIFI_INDEX_EXTENSION, $this->fileName.self::BIFI_INDEX_EXTENSION);
        }
        return $ret;
    }

    /**
     * Add an item to the index.
     * @param unknown $item
     */
    public function addIndex($item) {

        $fpIndex=fopen($this->fileName.self::BIFI_INDEX_EXTENSION, "a");
        if($fpIndex===false )
            return false;

        $elementSeparator="";
        if ($this->numFiles>0)
            $elementSeparator=',';

        $json=json_encode($item,JSON_NUMERIC_CHECK);
        $json=trim($json,"{}")."}";
        if (!fwrite($fpIndex, $elementSeparator.$json))
            return false;

        $this->numFiles++;

        fclose($fpIndex);
        return true;
    }

    /**
     * Delete the two files from the archive
     * @return bool
     */
    function deleteAchive() {

        if(file_exists($this->fileName.self::BIFI_INDEX_EXTENSION))
            $i=unlink($this->fileName.self::BIFI_INDEX_EXTENSION);
        else
            $i=true;

        if(file_exists($this->fileName.self::BIFI_DATA_EXTENSION))
            $d=unlink($this->fileName.self::BIFI_DATA_EXTENSION);
        else
            $d=true;

        $this->numFiles = 0;

        return $i && $d;
    }

}