var ZipInfo = {};

ZipInfo.set = function(base_url,frm_zip,frm_prefecture,frm_address1,frm_address2){
console.log(frm_zip.value);	
	zip1 = frm_zip.value.slice(0,3);
	zip2 = frm_zip.value.slice(3,5);
	zip3 = frm_zip.value.slice(5,7);

	$.ajax({
		type: 'GET',
		url: base_url + "/" + zip1 + "/" + zip2 + ".json",
		dataType: 'json',
		cache: false,
		success: function(json){
			prefecture = json["prefecture"];
			addr = json["addr"];
			address2 = addr[zip3];
			
			if($.isArray(address2)){
				frm_prefecture.value = address2[0];
				frm_address1.value = address2[1];
				frm_address2.value = address2[2];
			}else{
				frm_prefecture.value = prefecture[0];
				frm_address1.value = prefecture[1];
				frm_address2.value = address2;
			}
		},
		error:function(data){
			alert('not found');
			console.log(data);
		}
	});
};

