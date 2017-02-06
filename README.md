# Customizable Repeatable Control
Custom WordPress Cutomizer control. Used to create a dynamic control that prompts users to enter information about a collection of data

##How to use?
Create a function first to hook into the right place e.g `add_action('customize_register', 'register_new_customizer_elements', 5);`

Then inside this function, hooked to `customize_register` set up the new control by supplying data as such

```
//Define control arguments here 
$control_args = array(
	array(
		'field_id'			=> 'title', //id used for each field
		'field_label'		=> 'Header Title', //label dispayed next to the field
		'field_type'		=> 'text', //type: currently only 'text' supported
		'field_control_id'	=> 'repeater_section' //ID of the control that will be using this control
	),
	array(
		'field_id'			=> 'content',
		'field_label'		=> 'Main Content',
		'field_type'		=> 'text',
		'field_control_id'	=> 'repeater_section'
	),
);

//Add the control, here's where the magic happens!
$wp_customize->add_control(
	 new el_customizer_repeatable_sections(	
		$wp_customize,
		'repeater_section',
		array(
			'label'			=> __('Content Section','namespace here'),
			'description'	=> __('Add your content row by row','namespace here'),
			'section'		=> 'section_id_goes_here',
			'control_args'	=> $control_args
		)
	)
);

```

This will give you a repeaterable control section. 

More info to come soon
