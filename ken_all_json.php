<?php
$in_file = 'KEN_ALL_ROME.CSV';
$out_dir = 'zip';

$src = mb_convert_encoding(file_get_contents($in_file),'UTF-8','SJIS');
$src = mb_convert_kana($src,'as');
$src = str_replace(['（','）'],['(',')'],$src);
$src = str_replace(' ','',$src);

$overwrite_zip = [];
$addr = [];
$cnt = 0;
foreach(explode(PHP_EOL,$src) as $line){
	if(!empty($line)){
		list($zip,$state,$address1,$address2) = explode(',',$line);
		$cnt++;

		$zip1 = substr($zip,0,3);
		$zip2 = substr($zip,3,2);
		$zip3 = substr($zip,5);
		
		$address1 = str_replace(' ','',$address1);
		
		$address2 = preg_replace('/\(.+\)/','', $address2);
		$address2 = preg_replace('/\(.*$/','',$address2);
		$address2 = preg_replace('/^.*\).*$/','',$address2);

		if($address2 == '以下に掲載がない場合'){
			$address2 = '';
		}else if(strpos($address2,'次に番地がくる場合') !== false){
			$address2 = '';
		}else if(strpos($address2,'一円') !== false){
			$address2 = str_replace('一円','',$address2);
		}else if(strpos($address2,'、') !== false){
			$address2 = '';
		}
		
		if(!isset($addr[$zip1][$zip2]['city'])){
			$addr[$zip1][$zip2]['city'] = [$state,$address1];
		}		
		if(implode('',$addr[$zip1][$zip2]['city']) != $state.$address1){
			$address2 = [$state,$address1,$address2];
		}
		
		if(isset($addr[$zip1][$zip2]['addr'][$zip3])){
			if($addr[$zip1][$zip2]['city'] != [$state,$address1] || $addr[$zip1][$zip2]['addr'][$zip3] != $address2){
				$overwrite_zip[] = array_merge(
						[$zip],
						(is_array($addr[$zip1][$zip2]['addr'][$zip3]) ?
								$addr[$zip1][$zip2]['addr'][$zip3] :
								array_merge($addr[$zip1][$zip2]['city'],[$addr[$zip1][$zip2]['addr'][$zip3]])
						)
				);
			}
		}
		
		$addr[$zip1][$zip2]['addr'][$zip3] = $address2;
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

print('Written '.$output_cnt.' files.'.PHP_EOL);

if(!empty($overwrite_zip)){
	file_put_contents('overwrite.json',json_encode($overwrite_zip));
	print('Written overwrite.json'.PHP_EOL);
	print('Overwrite zipcode: '.sizeof($overwrite_zip).'/'.$cnt.PHP_EOL);
}

