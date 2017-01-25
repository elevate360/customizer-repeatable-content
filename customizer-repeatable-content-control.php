<?php
/**
* WordPress customizer control for creating content sections
*/
	//Include the base control class 
	$path = ABSPATH . 'wp-includes/class-wp-customize-control.php';
	include($path);

	class el_customizer_repeatable_sections extends WP_Customize_Control{
		
		private $custom_args = null;
		
		//called on initiation
		public function __construct($wp_customize, $setting_name, $args){
			
			//call parent
			parent::__construct($wp_customize, $setting_name, $args);
				
			if(isset($args['control_args'])){
				$this->custom_args = $args['control_args'];
			}
			
			
			add_action('customize_controls_enqueue_scripts', array($this, 'enqueue_customizer_control_admin_scripts'));
			
			//add our ajax functions
			add_action('wp_ajax_add_repeater_section', array($this, 'get_reapeater_field_markup'));
			add_action('wp_ajax_nopriv_add_repeater_section', array($this, 'get_reapeater_field_markup'));
		
		}

		//render field control
		public function render_content(){
			
			?>
			<label>
				<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>		
				<?php
				if(!empty($this->description)){?>
				<span class="description"><?php echo $this->description; ?></span>
				<?php }?>
				
				
				<input type="hidden" <?php $this->link(); ?> value="<?php $this->value(); ?>"/>
	
				<div class="button add-content-section">Add Row</div>
				<div class="content-repeater-section"></div>
			</label>
			<?php	
		}
		
		/**
		 * Enqueue scripts for customizer control
		 */
		public function enqueue_customizer_control_admin_scripts(){
			$directory = get_stylesheet_directory_uri() . '/inc/customizer/';
				
			//enqueue main script
			wp_enqueue_script('el_customizer_repeatable_sections_scripts', $directory . 'el_customizer_repeatable_sections_scripts.js', array('jquery'));	
			//enqueue the main ajax_url variable
			wp_localize_script('el_customizer_repeatable_sections_scripts', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php'))); 
		}
		
		/**
		 * Gets the markup for the reapeater field, called via AJAX
		 */
		public function get_reapeater_field_markup(){
			
			$html = '';
			$response = array();
			
			$html .= '<p>Hello world!</p>';
			
			$respose['status'] = 'success';
			$response['content'] = $html;
			
			
			
			echo json_encode($response);
			
			wp_die();
		}
			
	}


?>