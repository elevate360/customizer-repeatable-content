jQuery(document).ready(function($){
	
	
	function set_up_functionality(){

		//Functionality for customizer control
		$('.add-content-section').on('click', function(){
			
			var control_container = $(this).siblings('.content-repeater-section');
			
			//set the data section and extract field settings
			var data = {action: 'add_repeater_section'};
			var control_args = $(this).siblings('.control-args').attr('data-control-args');
			if(control_args.length != 0){
				control_args = JSON.parse(control_args);
				data.fields = control_args;
			}
			
			var url = ajax_object.ajax_url;
			
			//do ajax function to get content
			$.ajax({
				type: 'POST',
				url: url,
				data: data,
				cache: false,
				success: function(response){
					var response = JSON.parse(response);
					if(response.status == "success"){
						control_container.append(response.content);
					}			
				},
				error: function(error){
					
				}
			}); 
		
		});
		
		//On save, json encode all required fields so it can be saved
		$('body').on('click','.save-content-blocks', function(){
			
			var control_value = $(this).siblings('.control-value');
			var control_id = $(this).siblings('.control-args').attr('data-control-id');
			var content_sections  = $(this).siblings('.content-repeater-section').find('.field-section');
			
			var value = '';
			//if we have sections of data
			
			//Json encode values in prep to be saved!
			var final_value_string = '';
			if(content_sections.length != 0){
				final_data = [];
				
				//loop through each section to get fields
				content_sections.each(function(){
					
					var section_data = []; 
					
					var fields = $(this).find('.field');
					//go through all fields in section
					fields.each(function(){
						
						var field_name = $(this).attr('name');
						var field_value = $(this).val();
						var field_control_id = control_id;
						var field_type = 'text';
						var field_label = $(this).siblings('label').text();
						
						
						section_data.push({
							field_id : field_name, 
							field_value: field_value, 
							field_control_id: field_control_id, 
							field_type: field_type,
							field_label: field_label});
					});
					
					final_data.push(section_data);
				});
				
				//Encode object to srtring
				if(final_data.length != 0){
					final_value_string = JSON.stringify(final_data);
				}
				
				alert(final_value_string);
				
				
			}
			
			//save value back to hidden field
			control_value.val(final_value_string);
			
			//use internal wp customize to trigger setting save
			wp.customize(control_id, function(obj){
				obj.set(final_value_string);
			});
			
			
		});
		
		
		//Deleting a block!
		$('body').on('click','.remove-section', function(){
			
			var field_section = $(this).parents('.field-section').remove();
			
		});
		
		//set up sortables
		function set_up_sortable_sections(){
			
			//moving a block up and down with jquery draggable
			$('.content-repeater-section').sortable({
				items: '.field-section',
				opacity: 0.7,
				revert: true,
				axis: 'Y'
			});
		}
		set_up_sortable_sections();
			
		
	}
	
	
	//TODO: Fix for later 
	setTimeout(set_up_functionality, 1000);
});
