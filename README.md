# Ken_all_Json

郵便局の住所の郵便番号（ローマ字・zip形式）を分割してjsonにしたファイル(〒1-3桁/〒4-5桁.json)を生成します。
郵便番号が重複していた場合、データ上最後の住所で上書きされます。


## 書き出し
```
 php cmdman.phar ZipInfo::publish --out ./out
```


## 使い方 ( sample.html )
```
<html>
<head>
	<script type="text/javascript" src="https://code.jquery.com/jquery-2.1.4.min.js"></script>	
	<script type="text/javascript" src="js/ZipInfo.js"></script>		
</head>
<body>
<form onsubmit="ZipInfo.set('./out',this.zip,this.state,this.address1,this.address2); return false;">
	<p>
		<input type="text" name="zip" placeholder="0000000" /><input type="submit" value="get" />
	</p>
	<p>
		<input type="text" id="state" placeholder="state" /><br />
		<input type="text" id="address1" placeholder="address1" /><br />
		<input type="text" id="address2" placeholder="address2" /><br />
	</p>
</form>
</body>
</html>

```
