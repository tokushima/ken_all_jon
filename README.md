# Ken_all_Json

郵便局の住所の郵便番号（ローマ字・zip形式）を分割してjsonにしたファイル(〒1-3桁/〒4-5桁.json)を生成します。

郵便番号が重複していた場合、データ上最後の住所で上書きされます。

上書きされた住所はoverwrite.jsonに書き出されます。

http://www.post.japanpost.jp/zipcode/dl/roman-zip.html

## Sample
```
<html>
<head>
	<script type="text/javascript" src="https://code.jquery.com/jquery-2.1.4.min.js"></script>	
	<script type="text/javascript">
		function zip2addr(frm){
			zip1 = frm.zip.value.slice(0,3);
			zip2 = frm.zip.value.slice(3,5);
			zip3 = frm.zip.value.slice(5,7);
			
			$.ajax({
				type: 'GET',
				url: "./zip/" + zip1 + "/" + zip2 + ".json",
				dataType: 'json',
				cache: false,
				success: function(json){
					city = json["city"];
					addr = json["addr"];
					address2 = addr[zip3];
					
					if($.isArray(address2)){
						frm.state.value = address2[0];
						frm.address1.value = address2[1];
						frm.address2.value = address2[2];
					}else{
						frm.state.value = city[0];
						frm.address1.value = city[1];
						frm.address2.value = address2;
					}
				},
				error:function(data){					  
					console.log(data);
				}
			});
		}
	</script>
</head>
<body>
<form>
	<p>
		<input type="text" name="zip" /><input type="button" value="get" onclick="zip2addr(this.form)" />
	</p>
	<p>
		<input type="text" id="state" /><br />
		<input type="text" id="address1" /><br />
		<input type="text" id="address2" /><br />
	</p>
</form>
</body>
</html>

```
