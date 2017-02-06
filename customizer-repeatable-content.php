<?php
/**
 * Plugin Name
 *
 * @author      Simon Codrington
 * @copyright   2016 Simon Codrington \ Elevate 360
 * @license     GPL-2.0+
 *
 * Plugin Name: Repeatable Customizer Content Sections
 * Plugin URI:  https://simoncodrington.com.au
 * Description: Creates a new customizer control class to leverage in your theme. This control lets you create arbitrary sections of content to be used in
 * the front end of your designs.  
 * Version:     1.0.0
 * Author:      Simon Codrington
 * Author URI:  https://simoncodrington.com.au
 * Text Domain: customizer-repeatable-content
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */
 
 
 class customizer_repeatable_content{
 	
	/**
	 * Constructor, called on plugin activate
	 */
	public function __construct(){
		add_action('customize_register', array($this, 'register_new_customizer_class'), 5);
		add_action('customize_register', array($this, 'register_new_customiser_elements'), 11);
		
		//get instance of the helper, to ensure ajax hooks are added for the customizer control
		$el_customizer_helper = el_customizer_helper::getInstance();
	}
	
	/**
	 * Registeres a new customizer control (class) for use
	 * 
	 * Allows the creation of a repeatable content section that can be dynamically created by providing arguments as to what 
	 * data to collect, useful for when you need a customizer control to collect sets of data for display on the front end
	 */
	function register_new_customizer_class($wp_customize){
					
		/**
		 * Customizer control, used to create repeatable content sections in theme cutomizer
		 */
		class el_customizer_repeatable_sections extends WP_Customize_Control{
			
			private $custom_args = null;
			
			//called on initiation
			public function __construct($wp_customize, $setting_name, $args){
				
				//call parent
				parent::__construct($wp_customize, $setting_name, $args);
					
				if(isset($args['control_args'])){
					$this->custom_args['control_id'] = $this->id;
					$this->custom_args['fields'] = $args['control_args'];
				}
	
				//enqueue scripts used just by this class
				add_action('customize_controls_enqueue_scripts', array($this, 'enqueue_customizer_control_admin_scripts'));
	
			}
	
			//render field control
			public function render_content(){
				
				//TODO: Output arguments here so they can be sucked into JS
				?>
				<label>
					<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>		
					<?php
					if(!empty($this->description)){?>
					<span class="description"><?php echo $this->description; ?></span>
					<?php }?>
					
					
					<input type="hidden" class="control-value" <?php $this->link(); ?> value="<?php $this->value(); ?>" />
					<input type="hidden" class="control-args" data-control-args='<?php echo json_encode($this->custom_args); ?>' data-control-id="<?php echo $this->id; ?>"/>
					
				
					<div class="button add-content-section">Add Row</div>
					<div class="button save-content-blocks">Save Content</div>
					<div class="content-repeater-section">
						<?php
						$html = '';
						
						
						$instance = el_customizer_helper::getInstance();
						
						if(!empty($this->value())){
							
							$saved_values = json_decode($this->value());
							
							foreach($saved_values as $field_section){
								
								//each section with one or many fields
								$html .= '<div class="field-section">';
								foreach($field_section as $field){
									
									
									$html .= $instance->get_single_field_html($field);
									
								}
								//remove button
								$html .= '<div class="button remove-section">Remove</div>';
								$html .= '</div>';
								
							}
							echo $html;
						}
						
						
						?>
					</div>
				</label>
				<?php	
			}
	
		
			
			/**
			 * Enqueue scripts for customizer control
			 */
			public function enqueue_customizer_control_admin_scripts(){
				$directory = plugin_dir_path( __FILE__ );
						
				//enqueue the main ajax_url variable
				wp_enqueue_script('el_customizer_repeatable_sections_scripts', $directory . 'el_customizer_repeatable_sections_scripts.js', array('jquery','jquery-ui-sortable'));	
				wp_localize_script('el_customizer_repeatable_sections_scripts', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php'))); 
				wp_enqueue_style('el_customizer_repeatable_sections_styles', $directory . 'customizer_controls_styles.css');
				wp_enqueue_script('jquery-ui-sortable');
			}
				
		}
	}
	
	/**
	 * Adds new elements to the customizer, using the new class
	 * 
	 * Uses custom arguments to be passed into the new customizer class, this class determines how the repeater will function, it can be passed an array of
	 * arguments for each data you want to collect
	 */
	function register_new_customiser_elements( $wp_customize ){
		
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
				'field_id'			=> 'title',
				'field_label'		=> 'Header Title',
				'field_type'		=> 'text',
				'field_control_id'	=> 'el_content_section'
			),
			array(
				'field_id'			=> 'content',
				'field_label'		=> 'Main Content',
				'field_type'		=> 'text',
				'field_control_id'	=> 'el_content_section'
			),
		);
		
		//Add the control, here's where the magic happens!
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
	
	
	
 }
 
 /**
 * Helper class to be used along side the el_customizer_repeatable_sections
 * 
 * This class is used to render the output of the control. Separated into it's own class and loaded outside of the customizer class,
 * to allow ajax functionality to be registered
 */		
class el_customizer_helper{
	
	public static $instance = null;
	
	public function __construct(){
		
		//adding ajax functionality 
		add_action('wp_ajax_add_repeater_section', array($this, 'get_reapeater_field_markup'));
		add_action('wp_ajax_nopriv_add_repeater_section', array($this, 'get_reapeater_field_markup'));
		
	}
	/**
	 * Gets singleton of instance
	 */
	public static function getInstance(){
		
		if(is_null(self::$instance)){
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	/**
	 * Gets the markup for the reapeater field, called via AJAX
	 */
	public function get_reapeater_field_markup(){
		
		//dynamic function based on passed fields
	
		$html = '';
		$response = array();
		
		//if we have fields to set up
		if(isset($_POST['fields'])){
			
			$response['status'] = 'success';
			$fields = $_POST['fields']['fields'];

			$html .= '<div class="field-section">';
			foreach($fields as $field){
				
				$field_obj = new stdClass();
				foreach($field as $key => $value){
					$field_obj->$key = $value;
				}
				
				//get single HTML
				$html .= $this->get_single_field_html($field_obj);

			}
			$html .= '<div class="button remove-section">Remove</div>';
			$html .= '</div>';
			
		}
		
		
		
		$response['content'] = $html;
		echo json_encode($response);	
		wp_die();
	}

	public function display_test(){
		
	}

	/**
	 * Given a single field object, used to render the single field element
	 */
	public function get_single_field_html($field){
		
		$html = '';
		
		switch($field->field_type){
			
			
			
			//standard text area
			case 'text':

				$html .= '<div class="field-wrap">';
					$html .= '<label for="' . $field->field_control_id . '-' . $field->field_id . '">' . $field->field_label . '</label>';
					if(!empty($field->field_value)){
						$html .= '<input class="field" type="text" name="' . $field->field_control_id . '-' . $field->field_id  . '" id="' . $field->field_control_id . '-' . $field->field_id . '" value="' .  $field->field_value . '">'; 
					}else{
						$html .= '<input class="field" type="text" name="' . $field->field_control_id . '-' . $field->field_id  . '" id="' . $field->field_control_id . '-' . $field->field_id . '"/>'; 
					}
					
				$html .= '</div>';
			
			break;

		}
		
		return $html;
		
	}

	
}
 
 
 
