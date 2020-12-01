<?php

//if( basename(__FILE__) == basename( $_SERVER[ "SCRIPT_FILENAME" ] ) ) {
	/*static $wp_load; // Since this will be called twice, hold onto it.
	echo dirname( dirname( dirname( dirname(__FILE__) ) ) );
	if( ! file_exists( $wp_load = untrailingslashit( ABSPATH ) . "/wp-load.php" ) ) {
		$wp_load    = false;
		$dir        = __FILE__;
		while( '/' != ( $dir = dirname( $dir ) ) ) {
			if( file_exists( $wp_load = "{$dir}/wp-load.php" ) ) {
				break;
			}
		}
	}
	echo "File ";*/
	$file_path = dirname(__FILE__); // go two level up for directory from this file.
	require_once dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/wp-load.php';

	global $wpdb;

	/* $prefix      = str_replace( '_', '\_', $wpdb->prefix );
	$tables      = $wpdb->get_col( "SHOW TABLES LIKE '{$prefix}%'" ); */
	$drop_tables = array(
		'booking_history',
		'booking_order_history',
		'posts',
		'postmeta',
	);

	foreach ( $drop_tables as $table ) {
		$table = $wpdb->prefix . $table;
		$wpdb->query( "DROP TABLE $table" );
	}

	$restore_file = $file_path . '/tychesod_wp611.sql';
	$lines        = file( $restore_file );

	foreach ( $lines as $line ) {

		if ( substr( $line, 0, 2 ) == '--' || $line == '' ) // Skip it if it's a comment.
			continue;

		$templine .= $line; // Add this line to the current segment.

		if ( substr( trim( $line ), -1, 1) == ';' ) { // If it has a semicolon at the end, it's the end of the query.
			$wpdb->query( $templine );
			$templine = ''; // Reset temp variable to empty.
		}
	}

	$global_settings = get_option( 'woocommerce_booking_global_settings_backup' );
	update_option( 'woocommerce_booking_global_settings', $global_settings );

	echo ' Reset Successfully..!!';
	wp_mail('dhruvin@tychesoftwares.com','Demo Site Reset', 'Demo site got reset at ' . date( 'Y-m-d h:i:sa' ) );
//}
