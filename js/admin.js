(function($){
	$('#save-rtp-meta').on('click', function(){
		event.preventDefault();
		let quantity = $.trim( $('input[name="quantity"]').val() );
		let price = $.trim( $('input[name="price"]').val() );
		let trial = $.trim( $('input[name="trial"]').val() );
		let postID = $('input[name="post_id"]').data('post-id');
		let data = { quantity, price, trial, postID };
		saveRetaileMeta(data);
	})

	function saveRetaileMeta(data){
	    $.ajax({
	      	url: window.location.origin+'/wp-admin/admin-ajax.php',
	      	dataType: 'json',
	      	method: 'POST',
	      	data: {
	        	data: data,
	        	action: 'update_retailer_package_meta'
	      	},
	      	success: function(res){
	      		$('input[name="quantity"]').val(res.quantity);
	      		$('input[name="price"]').val(res.price);
	      		$('input[name="trial"]').val(res.trial);
      		}
    	});
	}
})(jQuery)