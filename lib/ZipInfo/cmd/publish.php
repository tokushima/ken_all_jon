<?php
/**
 * 郵便番号情報生成
 * @param $out 生成されたJsonファイルの書き出し先 @['require'=>true]
 * @param $work 作業ディレクトリ
 */
$out_dir = $out;
$work_dir = $work;
$ken_all_url = 'http://www.post.japanpost.jp/zipcode/dl/kogaki/zip/ken_all.zip';
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
	list(,,$zip,,,,$pref,$city,$area) = explode(',',$line);
	return [$zip,$pref,$city,$area];
};
$parse_jigyosyo_func = function($line){
	list(,,$com,$pref,$city,$area1,$area2,$zip) = explode(',',$line);
	return [$zip,$pref,$city,$area1.$area2.'　'.$com];
};



$overwrite_zip = [];
$addr = [];
$cnt = 0;
$zipcnt = 0;

foreach([
	$ken_all_url=>[$parse_ken_all_func,'KEN_ALL.CSV'],
	$jigyosyo_url=>[$parse_jigyosyo_func,'JIGYOSYO.CSV']
] as $url => $datainfo){
	
	foreach(explode(PHP_EOL,$download_func($url,$datainfo[1])) as $line){
		if(!empty($line)){
			list($zip,$pref,$city,$area) = $datainfo[0]($line);
			$cnt++;
	
			$zip1 = substr($zip,0,3);
			$zip2 = substr($zip,3,2);
			$zip3 = substr($zip,5);
			
			$city = str_replace(' ','',$city);
			
			
			if(preg_match('/\（.+$/',$area,$m)){
				if(mb_substr($m[0],-2) !== '階）'){
					$area = str_replace($m[0],'',$area);
				}
			}
			if(
				mb_strpos($area,'以下に掲載がない場合') !== false ||
				mb_strpos($area,'次に番地がくる場合') !== false ||
				mb_strpos($area,'一円') !== false ||
				mb_strpos($area,'、') !== false ||
				mb_strpos($area,'〜') !== false
			){
				$area = '';
			}
			
			if(!isset($addr[$zip1][$zip2]['prefecture'])){
				$addr[$zip1][$zip2]['prefecture'] = [$pref,$city];
			}		
			if(implode('',$addr[$zip1][$zip2]['prefecture']) != $pref.$city){
				$area = [$pref,$city,$area];
			}
			
			if(isset($addr[$zip1][$zip2]['addr'][$zip3])){
				if($addr[$zip1][$zip2]['prefecture'] != [$pref,$city] || $addr[$zip1][$zip2]['addr'][$zip3] != $area){
					$overwrite_zip[] = array_merge(
						[$zip],
						(is_array($addr[$zip1][$zip2]['addr'][$zip3]) ?
							$addr[$zip1][$zip2]['addr'][$zip3] :
							array_merge($addr[$zip1][$zip2]['prefecture'],[$addr[$zip1][$zip2]['addr'][$zip3]])
						)
					);
					$zipcnt--;
				}
			}
			
			$addr[$zip1][$zip2]['addr'][$zip3] = $area;
			$zipcnt++;
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
			mkdir($out_dir.'/'.$k1);
		}
		file_put_contents($out_dir.'/'.$k1.'/'.$k2.'.json',json_encode($v2));
		$output_cnt++;
	}
}

print(sprintf('%d files, zip code: %d',$output_cnt,$zipcnt));

$log = [
	'date'=>date('YmdHis'),
	'ken_all_url'=>$ken_all_url,
	'output_files'=>$output_cnt,
	'output_zip_code'=>$zipcnt,
];
if(!empty($overwrite_zip)){
	$log['duplicate_zip_code'] = sizeof($overwrite_zip);
	print(', Duplicate zip code: '.sizeof($overwrite_zip));
}
print(PHP_EOL);

file_put_contents($out_dir.'/output.log.json',json_encode($log));
print('Written '.$out_dir.'/output.log.json'.PHP_EOL);

