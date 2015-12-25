<?php
/**
 * There heart of WP Ultimate Demo plugin which load scripts, styles, setup permissions, authenication...
 * 
 * @author Tan Nguyen <tan@binaty.org>
 */
class Ultimate_Demo
{
	/**
	 * Constructor only contains actions and filters
	 *
	 * @return  void
	 */
	public function __construct()
	{
		// Add admin notices
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );

		// Enqueue scripts and styles to all pages
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );

		// Setup files permissions and cleanup demo temporary data when needed
		add_action( 'admin_init', array( $this, 'setup' ) );

		// Print countdown bar on footer
		add_action( 'admin_footer', array( $this, 'countdown_bar' ) );

		// Prefill user data when this setting is enabled
		add_action( 'login_footer', array( $this, 'prefill_user_data' ) );

		// Auto login when this setting is enabled
		add_action( 'init', array( $this, 'auto_login' ) );

		// Print login message when this setting is enabled
		add_filter( 'login_message', array( $this, 'login_message' ) );

		// Disallow disable this plugin if current user isn't activate this plugin
		add_filter( 'plugin_action_links', array( $this, 'disable_deactivation' ), 10, 4 );

		add_action( 'plugins_loaded', array( $this, 'i18n' ) );
	}

	public function setup()
	{
		// Disable file edit if is set
		if ( wud_setting('disable_file_editing' ) )
		{
			if ( ! defined( 'DISALLOW_FILE_EDIT' ) )
				define( 'DISALLOW_FILE_EDIT', true );

			if ( ! defined( 'DISALLOW_FILE_MODS' ) )
				define( 'DISALLOW_FILE_MODS', true );
		}

		if ( isset( $_GET['cleanup'] ) && $_GET['cleanup'] )
		{
			Ultimate_Demo_Data::cleanup();
		}
	}

	/**
	 * Load Text Domain for Translation
	 * 
	 * @return void
	 */
	public function i18n()
	{
		load_plugin_textdomain( 'wud', false, basename( WUD_DIR ) . '/lang/' );
	}

	/**
	 * Disable deactivation if admin set it
	 */
	public function disable_deactivation( $actions, $plugin_file, $plugin_data, $context ) 
	{
		if ( wud_user_editable() )
			return $actions;

		// Remove edit link for all
		if ( array_key_exists( 'edit', $actions ) )
			unset( $actions['edit'] );

		// Remove deactivate link for crucial plugins
		if ( array_key_exists( 'deactivate', $actions ) && in_array( $plugin_file, array(
			'wp-ultimate-demo/wp-ultimate-demo.php'
		) ) )
			unset( $actions['deactivate'] );
		
		return $actions;
	}

	/**
	 * Enqueue JS and CSS
	 * @return void
	 */
	public function enqueue()
	{
		$next_cleanup = wud_get_next_cleanup_time();

		$interval 	  = wud_setting( 'countdown_interval' );

		wp_register_script( 'ultimate-demo', WUD_JS_URL . 'ultimate-demo.js', array(), '1.0.0', true );
		wp_register_style( 'ultimate-demo', WUD_CSS_URL . 'ultimate-demo.css', array(), '1.0.0', 'all' );
		wp_localize_script( 'ultimate-demo', 'cleanup', compact('next_cleanup', 'interval') );
		wp_enqueue_script( 'ultimate-demo' );
		wp_enqueue_style( 'ultimate-demo' );
	}

	/**
	 * Print Countdown bar (in footer)
	 * 
	 * @return void
	 */
	public function countdown_bar()
	{
		$template = wud_setting( 'countdown_template' );

		if ( empty( $template ) || ! wud_is_demo_active() )
			return;

		$template = str_replace( '%time%', '<time id="countdown"></time>', $template );

		echo "<div id='countdown-wrapper'>{$template}</div>";
	}

	public function admin_notices()
	{
		//
	}

	public function prefill_user_data()
	{
		if ( wud_setting( 'auto_login' ) != 2 )
			return;

		$user_login = wud_setting( 'user_login' );
		$user_pass 	= wud_setting( 'user_pass' );

		?> 
		<script type="text/javascript">
			document.getElementById('user_login').value = '<?php echo $user_login ?>';
			document.getElementById('user_pass').value = '<?php echo $user_pass ?>';
		</script>
		<?php
	}

	/**
	 * Add login message in login form if is set
	 * 
	 * @return String Message to display
	 */
	public function login_message( $message )
	{
		// If login message is empty, do nothing
		if ( empty( wud_setting( 'login_message' ) ) )
			return $message;

		$login_message 	= htmlspecialchars_decode( stripslashes( wud_setting( 'login_message' ) ) );

		$message 		=  "<p class='message'>{$login_message}</p>";

		return $message;
	}

	/**
	 * Auto Login a user if admin enable this setting
	 * 
	 * @return void
	 */
	public function auto_login()
	{
		// If user already logged in, do nothing
		if ( is_user_logged_in() || wud_setting( 'auto_login' ) != 1 )
			return;

		// Get auto login user from setting
		$user_id 	= intval( wud_setting( 'auto_login_as' ) );
		$user 		= get_user_by( 'id', $user_id ); 
		
		// If not found, do nothing
		if ( ! $user ) 
			return;

		// Login user
	    wp_set_current_user( $user_id, $user->user_login );
	    wp_set_auth_cookie( $user_id, true );

	    do_action( 'wp_login', $user->user_login );
	}
}

new Ultimate_Demo;