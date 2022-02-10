$(document).ready(function(){
	$(document).find('#itg-enable-disable').on('change',function(){
		var value = false;
		var data_store = $(this).data('store');
		if( $(this).is(':checked') ){
			value = true;
		}
		var data = {
			value: value,
			url: data_store
		}	
		$(document).find('body').addClass('loading');
		$.ajax({
			type: "GET",
			url: "ajax/enable.php", 
			data: data,           
			dataType: "json",               
			success: function(response){
				console.log(response);
				$(document).find('body').removeClass('loading');
			},error:function(data){}
		});
	});
	$(document).find('#check-products').on('click',function(){
		var enable = $(this).data('enable');		
		var data_store = $(this).data('store');	
		if( enable == true ){
			var data = {
				url: data_store
			}			
			$(document).find('body').addClass('loading');
			$.ajax({
				type: "GET",
				url: "ajax/check_products.php", 
				data: data,           
				dataType: "json",               
				success: function(response){
					console.log(response);
					$(document).find('body').removeClass('loading');
				},error:function(data){}
			});
		}		
	});
});