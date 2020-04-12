(function($){
	$('a.link-buy-package').on('click', function(){
		event.preventDefault();
		let package_id = $(this).data('package-id');
		let renew = $(this).data('renewal');
		if ( !renew ) {renew = false};
		let data = {package_id, renew};
		buyRetailerPackage(data);
	})

	function buyRetailerPackage(data){
	    $.ajax({
	      	url: window.location.origin+'/wp-admin/admin-ajax.php',
	      	dataType: 'HTML',
	      	method: 'POST',
	      	data: {
	        	data,
	        	action: 'buy_retailer_package'
	      	},
	      	success: function(res){
	      		console.log(res);
		        if (res == 'checkout') {
		          	window.location.assign('/checkout');
		        }
      		}
    	});
	}
})(jQuery)