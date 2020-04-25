(function($){
	$('a.link-buy-package').on('click', function(){
		event.preventDefault();
		let package_id = $(this).data('package-id');
		let renewal = $(this).data('renewal');
		let trial = $(this).data('trial');
		if ( !renewal ) {renewal = +false};
		if ( !trial ) {trial =+false};
		let data = {package_id, renewal, trial};
		console.log(data);
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
	      		// console.log(res);
		        if (res == 'checkout') {
		          	window.location.assign('/checkout');
		        }
		        if (res == 'thank-you-page') {
		          	window.location.assign('/thank-you-page');
		        }
      		}
    	});
	}
})(jQuery)