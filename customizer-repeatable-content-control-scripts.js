jQuery(document).ready(function($){
	
	
	function set_up_functionality(){

		//Functionality for customizer control
		$('.add-content-section').on('click', function(){
			
			var data = {};
			data.action = 'add_repeater_section';
			data.test = 'Woooo';
			
			var url = ajax_object.ajax_url;
			
			//do ajax function to get content
			$.ajax({
				type: 'POST',
				url: url,
				data: data,
				cache: false,
				success: function(response){
					var response = JSON.parse(response);
					var test = 6; 
				},
				error: function(error){
					var response = JSON.parse(response);
					var test = 6; 
				}
			}); 
		
		});
		
	}
	
	
	//TODO: Fix for later 
	setTimeout(set_up_functionality, 1000);
});
