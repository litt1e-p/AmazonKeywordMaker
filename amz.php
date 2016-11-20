<?php
ini_set("max_execution_time", "1800");
date_default_timezone_set("Asia/Shanghai");
class amzkw
{
	const DELIMITER = ' ';
	public $seedFilePath = "keyword_seeds.csv";
	private $genStr = '';

	function getSeeds($level = '') {
		$single = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
		$doubleA = ['a0', 'a1', 'a2', 'a3', 'a4', 'a5', 'a6', 'a7', 'a8', 'a9'];
		$doubleB = ['bx0', 'bx1', 'bx2', 'bx3', 'bx4', 'bx5', 'bx6', 'bx7', 'bx8', 'bx9'];
		return $level ? ( $level == 's' ? $single : ($level == 'a' ? $doubleA : $doubleB)) : array_merge($single, $doubleA, $doubleB);
	}

	function genarate_sample($seedsGroup, $charsLimit = 20) {
		$rs = [];
		$currentIndex = -1;
		while (strlen($this->genStr) <= $charsLimit) {
			// $currentIndex = $currentIndex + 1 < count($seedsGroup) ? ++$currentIndex : 0;
			$currentIndex = mt_rand(0, count($seedsGroup) - 1);
			$t = $this->randomFindInArr($seedsGroup[$currentIndex], $rs);
			if (!empty($t)) {
				// var_dump($t);
				// var_dump($seedsGroup);
				array_push($rs, $t);
				shuffle($rs);
				$this->genStr = implode(self :: DELIMITER, $rs);
			}
		}
		// var_dump($this->genStr);
		$this->cutArrIfneed($this->genStr, $charsLimit);
		// var_dump($this->genStr);
		// return $rs;
		$this->exportExcel($rs);
	}

	function randomFindInArr(&$seeds = []) {
		if (empty($seeds)) {
			return;
		}
		$index = mt_rand(0, count($seeds)-1);
		$temp = $seeds[$index];
		unset($seeds[$index]);
		$seeds = $this->rebuildArr($seeds);
		return $temp;		
	}

	function cutArrIfneed(&$str, $limit) {
		if (strlen($str) <= $limit) {
			return $str;
		}
		$arr = explode(self :: DELIMITER, $str);
		array_pop($arr);
		$str = implode(self :: DELIMITER, $arr);
		if (strlen($str) > $limit) {
			$this->cutArrIfneed($str, $limit);
		}
	}

	function rebuildArr($arr) {
		$rs = [];
		foreach ($arr as $v) {
			array_push($rs, $v);
		}
		return $rs;
	}

	function generate($date_workday, $fileSaveName = '') {
        $data = $this->readSeedsFromExcel($this->seedFilePath);
        // var_dump($data);
        if (!$data) exit('some error occured!');
        $duty = [];
        foreach ($data as $key => $val) {
            $duty[$key]['departName'] = $val[0];
            $duty[$key]['name'] = $val[1];
            $duty[$key]['printTime'] = $val[3];
            $duty[$key]['duty'] = self :: checkLate($val[3]);
        }
        $this->exportExcel(self :: dataSet($duty), array_merge(array('姓名'),$date_workday));
    }

	function readSeedsFromExcel($seedsFile) {
        header('Content-type: text/html; charset=UTF-8');
        $seedsFile = fopen($seedsFile,'r');
        $rs = array();
        while ($line=fgetcsv($seedsFile,1000,",")){
            foreach ($line as $key => $value) {
                $line[$key] = iconv('gb2312','utf-8',$value);
            }
            $rs[] = $line;
        }
        //remove title column in excel?
        if ($rs) {
            unset($rs[0]);
            return $rs;
        }
        return false;
    }

	public function exportExcel($data=array(),$title=array(),$filename='random_keywords'){
	        header("Content-type:application/octet-stream");
	        header("Accept-Ranges:bytes");
	        header("Content-type:application/vnd.ms-excel");
	        header("Content-Disposition:attachment;filename=".$filename.".xls");
	        header("Pragma: no-cache");
	        header("Expires: 0");
	        //start export excel
	        if (!empty($title)){
	            foreach ($title as $k => $v) {
	                $title[$k]=iconv("UTF-8", "GB2312",$v);
	            }
	            $title= implode("\t", $title);
	            echo "$title\n";
	        }
	        // if (!empty($data)){
	        //     foreach($data as $key=>$val){
	        //         foreach ($val as $ck => $cv) {
	        //             $data[$key][$ck]=iconv("UTF-8", "GB2312", $cv);
	        //         }
	        //         $data[$key]=implode("\t", $data[$key]);

	        //     }
	        //     echo implode("\n",$data);
	        // }
	        if (!empty($data)){
	            foreach($data as $key=>$val){
                    $data[$key]=iconv("UTF-8", "GB2312", $val);
	            }
                $data = implode("\t", $data);
	            echo $data;
	        }
	    }
}

$class = new amzkw();
$singleSeeds = $class->getSeeds('s');
$doubleASeeds = $class->getSeeds('b');
$doubleBSeeds = $class->getSeeds('a');
$seeds = [$singleSeeds, $doubleASeeds, $doubleBSeeds];
// var_dump(mt_rand(0, count($doubleASeeds)-1));
$class->genarate_sample($seeds, 100);
// var_dump('random_keywords.' . date('Y.m.d.H.i.s', time()) .'.csv');

?>