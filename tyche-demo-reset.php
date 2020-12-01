<?php 
/**
 * Plugin Name: Demo Data Reset plugin for Tyche Softwares Demo Sites
 * Plugin URI: http://www.tychesoftwares.com/
 * Description: This plugin will reset demo data after some predined fixed time
 * Version: 1.0.0
 * Author: Tyche Softwares
 * Author URI: http://www.tychesoftwares.com/
 * Requires PHP: 5.6
 */

if ( ! class_exists( 'Tyche_Demo_Reset' ) ) {

	/**
	 * Main class for demo data reset.
	 */
	class Tyche_Demo_Reset {

		function __construct() {

			//add_action( 'admin_init', array( &$this, 'tdr_init' ) );

			add_filter( 'cron_schedules', array( &$this, 'tdr_cron_schedules' ) );
			//wp_clear_scheduled_hook('tdr_schedule_hook');
			if(!wp_get_schedule('tdr_schedule_hook')){
				//wp_schedule_event(time(), '3hours', 'tdr_schedule_hook' );
			}

			add_action( 'tdr_schedule_hook', array( __CLASS__ , 'tdr_schedule_callback' ) );
		}

		function tdr_cron_schedules( $schedules ){
			if(!isset($schedules["3hours"])){
				$schedules["3hours"] = array(
					'interval' => 3*60*60,
					'display' => __('Once every 3 Hours'));
			}
			return $schedules;
		}

		public function tdr_schedule_callback() {

			global $wpdb;

			$prefix = str_replace( '_', '\_', $wpdb->prefix );
			$tables = $wpdb->get_col( "SHOW TABLES LIKE '{$prefix}%'" );
			foreach ( $tables as $table ) {
				$wpdb->query( "DROP TABLE $table" );
			}

			$restore_file  = plugin_dir_path( __FILE__ ) . "woo_demo.sql";

			$lines = file( $restore_file );

			foreach ( $lines as $line ) {
				// Skip it if it's a comment
				if (substr($line, 0, 2) == '--' || $line == '')
					continue;

				// Add this line to the current segment
				$templine .= $line;
				// If it has a semicolon at the end, it's the end of the query
				if (substr(trim($line), -1, 1) == ';') {
					// Perform the query
					$wpdb->query($templine);
					// Reset temp variable to empty
					$templine = '';
				}
			}

			wp_mail('dhruvin@tychesoftwares.com','Demo Site Reset','Demo site got reset at ' . date("Y/m/d h:i:sa") );
		}
	}
}
//$tyche_demo_reset = new Tyche_Demo_Reset();

/* Adding Submenu Bookings-> Reset Demo */
function reset_menu () {

	$page = add_submenu_page(
		'edit.php?post_type=bkap_booking',
		__( 'Reset Demo', 'woocommerce-booking' ),
		__( 'Reset Demo', 'woocommerce-booking' ),
		'manage_options',
		'demo_reset_page',
		'bkap_reset_function'
	);
}
add_action( 'bkap_add_submenu', 'reset_menu' );

/**
 * This callback function will add the Menu page at Bookings-> Reset Demo submenu that has the form to Apply Changes.
 *
 * Changed Global Settings will be backeduped and used during the reset script.
 */
function bkap_reset_function() {

	if ( isset( $_POST['bkap_reset_demo'] )
		&& 'Apply Changes' === $_POST['bkap_reset_demo'] 
		&& isset( $_POST['bkap_reset_demo_field'] )
		|| wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['bkap_reset_demo_field'] ) ), 'bkap_reset_demo_nonce' )
	) {
		// do stuffs here.
		$global_settings = get_option( 'woocommerce_booking_global_settings' );
		update_option( 'woocommerce_booking_global_settings_backup', $global_settings );
	}
	?>
	<div class="wrap">
		<form method="post" action="">
			<?php
				wp_nonce_field( 'bkap_reset_demo_nonce', 'bkap_reset_demo_field' );
				submit_button( 'Apply Changes', 'primary', 'bkap_reset_demo', true, $other_attributes );
			?>
		</form>
	<?php
}
