<?php

/**
 * Plugin Name: Exclude noindex Pages From Search
 * Plugin URI: https://cameronjonesweb.com.au/wordpress-plugins/exclude-noindex-pages-from-search
 * Author: Cameron Jones
 * Author URI: https://cameronjonesweb.com.au
 * Description: 
 * Version: 0.1.0
 * License: GPLv2
 * 
 * Copyright 2017  Cameron Jones  (email : plugins@cameronjonesweb.com.au)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
 */

add_action( 'plugins_loaded', array( cameronjonesweb_exclude_noindex_pages_from_search::getInstance(), 'init' ) );

class cameronjonesweb_exclude_noindex_pages_from_search {

	// Variables
	protected static $instance = NULL;

    
    public static function getInstance() {
    
        NULL === self::$instance and self::$instance = new self;
        return self::$instance;
    
    }

	
	function init() {

		// Constants
		define( 'CJW_ENPFS_PLUGIN_VER', '0.1.0' );

		// Variables

		// Actions
		self::hook( 'action', 'pre_get_posts', null, 99 );
		self::hook( 'filter', 'register_post_type_args', null, 10, 2 );
		// Filters


	}


	public static function hook( $type, $hook, $function = null, $priority = 10, $args = 1 ) {

		$function = $function ? $function : $hook;

		if( $type === 'action' ) {

			add_action( $hook, array( self::getInstance(), $function ), $priority, $args );

		} else if( $type === 'filter' ) {

			add_filter( $hook, array( self::getInstance(), $function ), $priority, $args );

		}

	}


	function yoast_post_type_noindex() {

		$yoast_post_type_settings = get_option( 'wpseo_titles', true );
		$return = array();

		foreach( $yoast_post_type_settings as $key => $val ) {

			if( is_numeric( strpos( $key, 'noindex' ) ) && !is_numeric( strpos( $key, 'wpseo' ) ) && !is_numeric( strpos( $key, '-tax-' ) ) && $val ) {

				$post_type = str_replace( 'noindex-', '', $key );

				$return[] = $post_type;

			}

		}

		return $return;

	}


	function pre_get_posts( $query ) {

		if ( !is_admin() && $query->is_main_query() ) {

			if( $query->is_search() ) {

				$meta_query = $query->get( 'meta_query' );

				$meta_query[] = array(

					array(

						'key' => '_yoast_wpseo_meta-robots-noindex',
						'value' => '1',
						'compare' => '!='

					),

					'relation' => 'OR',

					array(

						'key' => '_yoast_wpseo_meta-robots-noindex',
						'compare' => 'NOT EXISTS'

					),

				);

				$query->set( 'meta_query', $meta_query );

			}

		}

	}


	function register_post_type_args( $args, $post_type ) {

		$yoast_post_type_noindex = self::yoast_post_type_noindex();

		if( in_array( $post_type, $yoast_post_type_noindex ) ) {

			$args['exclude_from_search'] = true;

		}

		return $args;

	}


}