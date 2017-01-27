<?php
/**
 * Helper class to be used along side the el_customizer_repeatable_sections
 * Used to render the HTML output for the section
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
$el_customizer_helper = el_customizer_helper::getInstance();
 
 
//register new customiser control
function register_new_customizer_controls($wp_customize){
				
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
			$directory = get_stylesheet_directory_uri() . '/inc/customizer/';
					
			//enqueue the main ajax_url variable
			wp_enqueue_script('el_customizer_repeatable_sections_scripts', $directory . 'el_customizer_repeatable_sections_scripts.js', array('jquery','jquery-ui-sortable'));	
			wp_localize_script('el_customizer_repeatable_sections_scripts', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php'))); 
			wp_enqueue_style('el_customizer_repeatable_sections_styles', $directory . 'customizer_controls_styles.css');
			wp_enqueue_script('jquery-ui-sortable');
		}
			
	}
}
add_action('customize_register', 'register_new_customizer_controls', 5);

?>