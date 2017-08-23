# Ken_all_Json

郵便局の住所の郵便番号（ローマ字・zip形式）を分割してjsonにしたファイル(〒1-3桁/〒4-5桁.json)を生成します。

## 書き出し
```
 php cmdman.phar ZipInfo::publish --out ./out
```


## Jsonの内容

```
{
    "〒下２桁": {
        "p": "都道府県",
        "c": "市区町村",
        "a": "地域・番地など",
        "f": "施設・建物名など"
    }
}
```

郵便番号が重複している場合は項目に`d`が追加され、全ての住所が列挙されます。



## 使い方
```
<html>
<head>
	<script type="text/javascript" src="https://code.jquery.com/jquery-2.1.4.min.js"></script>	
	<script type="text/javascript">
		function zipinfo(base_url,frm_zip,frm_prefecture,frm_city,frm_area,frm_facility){
			zip1 = frm_zip.value.slice(0,3);
			zip2 = frm_zip.value.slice(3,5);
			zip3 = frm_zip.value.slice(5,7);
	
			$.ajax({
				type: 'GET',
				url: base_url + '/' + zip1 + '/' + zip2 + '.json',
				dataType: 'json',
				cache: false,
				success: function(json){
					if(zip3 in json == true){
						var info = json[zip3];
						
						frm_prefecture.value = info['p'];
						frm_city.value = info['c'];
						frm_area.value = info['a'];
						frm_facility.value = info['f']						
					}else{
						alert('not found');
						console.log(data);
					}
				},
				error:function(data){
					alert('not found');
					console.log(data);
				}
			});
		}
	</script>
</head>
<body>
<form onsubmit="zipinfo('./out',this.zip,this.prefecture,this.city,this.area,this.facility); return false;">
	<p>
		<input type="text" name="zip" placeholder="0000000" /><input type="submit" value="get" />
	</p>
	<p>
		<input type="text" id="prefecture" placeholder="prefecture" /><br />
		<input type="text" id="city" placeholder="city" /><br />
		<input type="text" id="area" placeholder="area" /><br />
		<input type="text" id="facility" placeholder="facility" /><br />
	</p>
</form>
</body>
</html>
```
