<?php
/**
 * 郵便番号情報生成
 * @param $out 生成されたJsonファイルの書き出し先 @['require'=>true]
 * @param $work 作業ディレクトリ
 */
$out_dir = $out;
$work_dir = $work;
$ken_all_url = 'http://www.post.japanpost.jp/zipcode/dl/roman/ken_all_rome.zip';
$jigyosyo_url = 'http://www.post.japanpost.jp/zipcode/dl/jigyosyo/zip/jigyosyo.zip';

if(empty($work)){
	if(class_exists('\ebi\WorkingStorage')){
		$work_dir = \ebi\WorkingStorage::path('ZipInfo');
	}else{
		$work_dir = getcwd().'/work/ZipInfo';
	}
}

ini_set('memory_limit','-1');

if(!is_dir($work_dir)){
	mkdir($work_dir,0777,true);
}
if(!is_dir($out_dir)){
	mkdir($out_dir,0777,true);
}

$download_func = function($url,$csv_filename) use($work_dir){	
	$basename = basename($url);
	file_put_contents($work_dir.'/'.$basename,file_get_contents($url));
	
	$zip = new \ZipArchive();
	$zip->open($work_dir.'/'.$basename);
	$zip->extractTo($work_dir);
	$zip->close();
	
	unlink($work_dir.'/'.$basename);
	
	$src = mb_convert_encoding(file_get_contents($work_dir.'/'.$csv_filename),'UTF-8','SJIS');
	$src = str_replace(' ',' ',$src);
	$src = str_replace('"','',$src);
	unlink($work_dir.'/'.$csv_filename);	
	return $src;
};
$parse_ken_all_func = function($line){
// 	list(,,$zip,,,,$pref,$city,$area) = explode(',',$line);
	list($zip,$pref,$city,$area) = explode(',',$line); 
	$facility = '';
	
	if(strpos($pref,'　') !== false){
		list($pref,$facility) = explode('　',$pref,2);
	}
	return [$zip,$pref,$city,$area,$facility];
};
$parse_jigyosyo_func = function($line){
	list(,,$facility,$pref,$city,$area1,$area2,$zip) = explode(',',$line);
	return [$zip,$pref,$city,$area1.$area2,$facility];
};
$parse_area_func = function($str){
	if(!empty($str)){
		if(preg_match('/\（.+$/',$str,$m)){
			if(mb_substr($m[0],-2) !== '階）'){
				$str = str_replace($m[0],'',$str);
			}
		}
		if(
			mb_strpos($str,'以下に掲載がない場合') !== false ||
			mb_strpos($str,'次に番地がくる場合') !== false ||
			mb_strpos($str,'一円') !== false ||
			mb_strpos($str,'、') !== false ||
			mb_strpos($str,'〜') !== false
		){
			$str = '';
		}
	}
	return str_replace('　','',$str);
};


$addr = [];
$cnt = 0;

foreach([
	$ken_all_url=>[$parse_ken_all_func,'KEN_ALL_ROME.CSV',1],
	$jigyosyo_url=>[$parse_jigyosyo_func,'JIGYOSYO.CSV',2]
] as $url => $datainfo){
	
	foreach(explode(PHP_EOL,$download_func($url,$datainfo[1])) as $line){
		if(!empty($line)){
			list($zip,$pref,$city,$area,$facility) = $datainfo[0]($line);
			$cnt++;
	
			$zip1 = substr($zip,0,3);
			$zip2 = substr($zip,3,2);
			$zip3 = substr($zip,5);
			
			$city = str_replace('　','',$city);
			$area = $parse_area_func($area);
			$facility = $parse_area_func($facility);
			
			if(isset($addr[$zip1][$zip2][$zip3])){
				if(!isset($addr[$zip1][$zip2][$zip3]['d'])){
					$addr[$zip1][$zip2][$zip3]['d'] = [
						$addr[$zip1][$zip2][$zip3]
					];
				}
				
				$addr[$zip1][$zip2][$zip3]['d'][] = [
					'p'=>$pref,
					'c'=>$city,
					'a'=>$area,
					'f'=>$facility,
				];
			}else{
				$addr[$zip1][$zip2][$zip3] = [
					'p'=>$pref,
					'c'=>$city,
					'a'=>$area,
					'f'=>$facility,
				];
			}
		}

	}
}

$output_cnt = 0;
if(!is_dir($out_dir)){
	mkdir($out_dir);
}

foreach($addr as $k1 => $v1){
	foreach($v1 as $k2 => $v2){
		if(!is_dir($out_dir.'/'.$k1)){
			mkdir($out_dir.'/'.$k1,0777,true);
		}
		file_put_contents($out_dir.'/'.$k1.'/'.$k2.'.json',json_encode($v2));
		$output_cnt++;
	}
}

print(sprintf('%d files',$output_cnt).PHP_EOL);


