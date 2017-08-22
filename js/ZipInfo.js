var ZipInfo = {};

ZipInfo.set = function(base_url,frm_zip,frm_prefecture,frm_city,frm_area){
	zip1 = frm_zip.value.slice(0,3);
	zip2 = frm_zip.value.slice(3,5);
	zip3 = frm_zip.value.slice(5,7);

	$.ajax({
		type: 'GET',
		url: base_url + '/' + zip1 + '/' + zip2 + '.json',
		dataType: 'json',
		cache: false,
		success: function(json){
			prefecture = json['prefecture'];
			addr = json['addr'];
			area = (zip3 in addr == true) ? addr[zip3] : '';
			
			if($.isArray(area)){
				frm_prefecture.value = area[0];
				frm_city.value = area[1];
				frm_area.value = area[2];
			}else{
				frm_prefecture.value = prefecture[0];
				frm_city.value = prefecture[1];
				frm_area.value = area;
			}
		},
		error:function(data){
			alert('not found');
			console.log(data);
		}
	});
};

