(function($){
	$('a.link-buy-package').on('click', function(){
		event.preventDefault();
		let package_id = $(this).data('package-id');
		buyRetailerPackage(package_id);
		console.log(package_id);
	})

	function buyRetailerPackage(package_id){
	    $.ajax({
	      	url: window.location.origin+'/wp-admin/admin-ajax.php',
	      	dataType: 'HTML',
	      	method: 'POST',
	      	data: {
	        	data: package_id,
	        	action: 'buy_retailer_package'
	      	},
	      	success: function(res){
	      		console.log(res);
		        if (res == 'checkout') {
		          	window.location.assign('/checkout');
		        }
		        // else {
		        //   	// window.location.assign('/thank-you-for-you-reservation-buss?reservation='+res);
		        // }
      		}
    	});
	}
})(jQuery)