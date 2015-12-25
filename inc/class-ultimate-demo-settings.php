<?php
/**
 * This class holds all Ultimate Demo settings and create setting page
 *
 * @author Tan Nguyen <tan@binaty.org>
 */
class Ultimate_Demo_Settings
{
	/**
	 * Mark the setting page hook so other can hook into it
	 * 
	 * @var String
	 */
	public $page_hook;

	/**
	 * Initial method. All settings page actions and filters are defined here
	 * 
	 * @return  void
	 */
	public function __construct()
	{
		// Add menu page
		add_action( 'admin_menu', array( $this, 'add_menu' ) );

		// Register setting to WordPress
		add_action( 'admin_init', array( $this, 'register_setting' ) );
		
		// Interact with setting
		add_action( 'admin_init', array( $this, 'init' ) );
	}

	/**
	 * Add WP Ultimate Demo page under Settings
	 * Users should have manage_options or granted to see this menu
	 */
	public function add_menu()
	{
		if ( wud_user_editable() )
			$this->page_hook = add_options_page( 
				__( 'WP Ultimate Demo', 'wud' ), 
				__( 'WP Ultimate Demo', 'wud' ), 
				'manage_options', 
				'wp-ultimate-demo', 
				array( $this, 'show' ) 
			);		
	}

	/**
	 * Register `ultimate_demo` setting to WordPress
	 * 
	 * @return void
	 */
	public function register_setting()
	{
		register_setting( 'ultimate_demo', 'ultimate_demo_settings' );
	}

	/**
	 * Save settings to database and toggle demo mode if offline mode checkbox is checked
	 * 
	 * @return Redirect if success
	 */
	public function init()
	{
		global $wpdb;

		if ( empty( $_POST['_page_now'] ) )
			return;

		if ( ! current_user_can( 'manage_options' ) || wud_user_uneditable() )
			wp_die( 'Cheating???' );

		// Toggle demo if offline mode is checked
		Ultimate_Demo_File_System::toggle_demo();

		// Change event offset if cleanup offset is changed
		if ( $_POST['cleanup_offset'] != wud_setting( 'cleanup_offset' ) && is_numeric( $_POST['cleanup_offset'] ) )
			Ultimate_Demo_Data::setup_event( intval( $_POST['cleanup_offset'] ) );

		$settings = array();

		foreach ( wud_default_settings() as $field => $default )
		{
			$settings[$field] =  isset( $_POST[$field] ) ? stripslashes( $_POST[$field] ) : '';
		}

		$settings['only_show_for'] = get_current_user_id();

		update_option( 'ultimate_demo', $settings );
		$wpdb->query( "UPDATE wuddemo_options SET option_value = '" . serialize( $settings ) . "' WHERE option_name = 'ultimate_demo'" );


		// Redirect with success message
		$_POST['_wp_http_referer'] = add_query_arg( 'success', 'true', $_POST['_wp_http_referer'] );
		wp_redirect( $_POST['_wp_http_referer'] );
		exit;
	}

	public function show()
	{
	?>
		<div class="wrap">
		<h2><?php _e( 'WP Ultimate Demo', 'wud' ); ?></h2>
		
		<?php 
		// Display success message when settings saved
		if ( isset( $_GET['success'] ) ) : ?>
		<div id="message" class="updated notice is-dismissible">
			<p><?php _e( 'Settings <strong>saved</strong>.', 'wud' ); ?></p>
			<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
		</div>
		<?php endif; ?>

		<form method="post" action="options.php" id="poststuff">
		    <?php settings_fields( 'ultimate_demo' ); ?>
			
			<div class="meta-box-sortables">
              	<div class="postbox">
                	<div class="handlediv" title="Click to toggle"> <br></div>
                  	<h3 class="hndle ui-sortable-handle"><?php _e( 'General', 'wud' ); ?></h3>
                  	<div class="inside">
                    	<table class="form-table">
                    		<tr valign="top">
                    			<th><?php _e( 'Offline Mode', 'wud' ); ?></th>
                    			<td>
                    				<label>
                    					<?php $this->checkbox( 'offline_mode', ! wud_is_demo_active() ); ?>
                    					<?php _e( 'Disable demo, unfreeze data, only admin can access and make changes', 'wud' ); ?>
                    				</label>
                    				<p class="description">
                    					<?php _e( "Uncheck this when you're ready to publish your demo<br>
                    					Notice: You'll probably get logged out after change this setting", 'wud' ); ?>
                    				</p>
                    			</td>
                    		</tr>

                    		<?php if ( wud_is_demo_active() ) : ?>
							
							<tr class="alert">
								<td colspan="2">
									<?php _e( 'All demo options are hidden in online mode. Switch to <strong>Offline mode</strong> to change demo settings.', 'wud' ); ?>
								</td>
							</tr>
			
                    		<?php endif; ?>

                    		<?php if ( ! wud_is_demo_active() ) : ?>
                    		<tr valign="top">
                    			<th><?php _e( 'Security', 'wud' ); ?></th>
                    			<td>
                    				<p>
                    				<label>
                    					<?php $this->checkbox( 'disable_file_editing' ); ?>
                    					<?php _e( "Don't let users modify your files, upgrade website, themes, and plugins <code>Recommended</code>", 'wud' ); ?>
                    				</label>
                    				</p>

                    				<p>
                    				<label>
                    					<?php $this->checkbox( 'hide_from_anyone' ) ?>
                    					<?php _e( 'Hide this plugin from any one except me <code>Recommended</code>', 'wud' ); ?>
                    				</label>
                    				</p>
                    			</td>
                    		</tr>

                    		<tr valign="top">
                    			<th><?php _e( 'Data Cleanup', 'wud' ); ?></th>
                    			<td>
                    				<?php $this->input( 'number', 'cleanup_offset', null, array('min' => 1, 'max' => 99) ); ?>
                    				<p class="description"><?php _e( 'Auto cleanup data after (hours)' ); ?></p>
                    			</td>
                    		</tr>


                    		<tr valign="top">
                    			<th><?php _e( 'Countdown', 'wud' ); ?></th>
                    			<td>
                    				<label>
                    					<?php $this->checkbox( 'show_countdown' ); ?>
                    					<?php _e( 'Show countdown bar when session is going to expired', 'wud' ); ?> 
                    					<div role="conditional-logic" id="show-countdown-child-condition"> <?php _e( 'in', 'wud' ); ?>
										<?php $this->input( 'number', 'countdown_interval' ); ?>
                    					<?php _e( 'seconds', 'wud' ); ?>	
                    				</label>
									<br><br>
									<label>
										<p class="description"><?php _e( 'With Template', 'wud' ); ?></p> <br>
										<?php $this->textarea( 'countdown_template', null, array(
											'rows' => 3,
											'cols' => 90
										) ); ?>
									</label>
									</div>
                    			</td>
                    		</tr>
                    	<?php endif; ?>
                    	</table>
                  	</div><!--.inside-->
              	</div><!--.postbox-->
            </div><!--.meta-box-sortables-->
			
			<?php if ( ! wud_is_demo_active() ) : ?>
            <div class="meta-box-sortables">
              	<div class="postbox">
                	<div class="handlediv" title="Click to toggle"> <br></div>
                  	<h3 class="hndle ui-sortable-handle"><?php _e( 'Login', 'wud' ); ?></h3>
                  	<div class="inside">
                    	<table class="form-table">
                    		<tr valign="top">
                    			<th><?php _e( 'Auto Login', 'wud' ); ?></th>
                    			<td>
                    				<label>
                    					<?php 
                    						$this->select( 'auto_login', array( 
	                    						__( 'Disable', 'wud' ), 
	                    						__( 'Enable', 'wud' ), 
	                    						__( 'Prefill', 'wud' )
                    						) ); 
                    					?>
                    				</label>
                    				<p class="description">
                    				<?php _e( "If <b>enable</b>, any one can bypass login form. <br> If you choose <b>prefill</b>, the login form is prefilled, users don't have to enter user name and password", 'wud' ); ?>
                    				</p>
                    			</td>
                    		</tr>
							
                    		<tr valign="top" id="auto-login-as">
                    			<th><?php _e( 'Auto Login with User', 'wud' ); ?></th>
                    			<td>
                    				<?php 
                    					wp_dropdown_users( array(
                    						'name'		=> 'auto_login_as',
                    						'selected' 	=> wud_setting( 'auto_login_as' )
                    					) ); 
                    				?>
                    			</td>
                    		</tr>

                    		<tr valign="top" id="prefill-settings">
                    			<th><?php _e( 'Prefill Settings', 'wud' ); ?></th>
                    			<td>
                    				<?php $this->text( 'user_login', null, array( 'placeholder' => __( 'User Login', 'wud' ) ) ); ?>
                    				<?php $this->text( 'user_pass', null, array( 'placeholder' => __( 'User Password', 'wud' ) ) ); ?>
                    			</td>
                    		</tr>
							
							<tr valign="top" id="login-message">
                    			<th><?php _e( 'Login Message', 'wud' ); ?></th>
                    			<td>
                    				<?php $this->textarea( 'login_message', null, array(
                    					'rows' => 3,
                    					'cols' => 90
                    				) ); ?>
                    				<p class="description">
                    					<?php _e( 'Print login message on the top of login form. Leaves blank to disable this feature' ); ?>
                    				</p>
                    			</td>
                    		</tr>
                    	</table>
                  	</div><!--.inside-->
              	</div><!--.postbox-->
            </div><!--.meta-box-sortables-->

            <div class="meta-box-sortables">
              	<div class="postbox">
                	<div class="handlediv" title="Click to toggle"> <br></div>
                  	<h3 class="hndle ui-sortable-handle"><?php _e( 'Manual Cleanup Data', 'wud' ); ?></h3>
                  	<div class="inside">
                    	<table class="form-table">
                    		<tr valign="top">
                    			<th></th>
                    			<td>
                    				<a href="<?php echo esc_url( add_query_arg('cleanup', 1) ); ?>" class="button"><?php _e( 'Cleanup', 'wud' ); ?></a>
                    				<p class="description"><?php _e( 'Cleanup all user entered demo data. Take website back to your last modified time.', 'wud' ); ?></p>
                    			</td>
                    		</tr>
                    	</table>
                  	</div><!--.inside-->
              	</div><!--.postbox-->
            </div><!--.meta-box-sortables-->
			
			<?php endif; ?>
			
			<input type="hidden" name="_page_now" value="ultimate-demo">
		    <?php submit_button(); ?>
		</form>
		</div>
		<?php
	}

	/**
	 * Generate <input> and <textarea> tags
	 * 
	 * @param  String $type  Input type
	 * @param  String $name  Input name, which also saved in settings
	 * @param  String $value (Optional) Default value
	 * @param  array  $attrs (Optional) Html attributes
	 * 
	 * @return void
	 */
	protected function input( $type, $name, $value = null, $attrs = array() )
	{
		$attributes = '';

		if ( ! empty( $attrs ) )
			$attributes = $this->make_attributes( $attrs );

		if ( $type !== 'checkbox' && is_null( $value ) )
			$value = is_null( $value ) ? wud_setting( $name ) : '';

		$html = "<input type='{$type}' name='{$name}' id='{$name}' value='{$value}' {$attributes}>";
	
		if ( $type === 'textarea' )
			$html = "<textarea type='{$type}' name='{$name}' {$attributes}>{$value}</textarea>";

		echo $html;
	}

	/**
	 * Shortcut to $this->input with type=text
	 * 
	 * @see input method
	 */
	protected function text( $name, $value = null, $attrs = array() )
	{
		$this->input( 'text', $name, $value, $attrs );
	}

	/**
	 * Shortcut to $this->input with type=textarea
	 * 
	 * @see  input method
	 */
	protected function textarea( $name, $value = null, $attrs = array() )
	{
		return $this->input('textarea', $name, $value, $attrs);
	}

	/**
	 * Generate <select> tag
	 * 
	 * @param  String $name 	Field name
	 * @param  Array $options  Options
	 * @param  Mixed $selected (Optional) Selected value
	 * @param  array  $attrs    (Optional) Html Attributes
	 * 
	 * @return String output html
	 */
	protected function select( $name, $options, $selected = null, $attrs = array() )
	{
		if ( ! empty( $attrs ) )
			$attributes = $this->make_attributes($attrs);
		
		$selected = is_null( $selected ) ? wud_setting( $name ) : $selected;

		$html = "<select name='{$name}' id='{$name}'>";

		foreach ( $options as $value => $label )
		{
			$select = $selected == $value ? 'selected' : '';

			$html .= "<option value='{$value}' {$select}>{$label}</option>";
		}

		$html .= "</select>";

		echo $html;
	}

	/**
	 * Generate input[type=checkbox]
	 * 
	 * @param  String  $name    Field Name
	 * @param  boolean $checked (Optional) Checked 
	 * @param  array   $attrs   Html attributes
	 * 
	 * @return String  Html output
	 */
	protected function checkbox( $name, $checked = null, $attrs = array() )
	{
		$checked = is_null( $checked ) ? wud_setting( $name ) : $checked;

		if ( $checked )
			$attrs['checked'] = 'checked';

		return $this->input( 'checkbox', $name, 1, $attrs );
	}

	/**
	 * Convert $attrs from array to string
	 * 
	 * @param  array  $attrs Html attributes
	 * 
	 * @return String Html output
	 */
	protected function make_attributes( $attrs = array() )
	{
		$output = '';

		foreach ( $attrs as $key => $value )
		{
			$output .= " {$key}='{$value}'";
		}

		return $output;
	}
}

new Ultimate_Demo_Settings;