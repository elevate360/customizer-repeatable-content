<?php
/**
 * Main Customiser functionality
 * Implement new customizer control
 */
function el_customizer_functionality( $wp_customize ){
	
	//add setting
	$wp_customize->add_setting('el_content_section', array());
	
	//add section
	$wp_customize->add_section('el_social_media',
		array(
			'title'			=> 'Social Media',
			'priority'		=> 51,
			'description'	=> __('Social media options for the theme. These are pulled into and used across parts of the sites templates', 'ycc')
		)
	);
	
	//Try new control
	$control_args = array(
		array(
			'field_id'	=> 'title',
			'type'		=> 'text'
		),
		array(
			'field_id'	=> 'content',
			'type'		=> 'text'
		),
	);
	
	$wp_customize->add_control(
		 new el_customizer_repeatable_sections(	
			$wp_customize,
			'el_content_section',
			array(
				'label'			=> __('Content sectoion','ycc'),
				'description'	=> __('Add your content row by row','ycc'),
				'section'		=> 'el_social_media',
				'control_args'	=> $control_args
			)
		)
	);
	
}
add_action('customize_register', 'el_customizer_functionality', 15);