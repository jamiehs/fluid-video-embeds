<?php
/**
 * Constants used by this plugin
 *
 * @author jamie3d
 */

// The current version of this plugin
if( !defined( 'FLUID_VIDEO_EMBEDS_VERSION' ) ) define( 'FLUID_VIDEO_EMBEDS_VERSION', '1.2.9' );

// The cache prefix
if( !defined( 'FLUID_VIDEO_EMBEDS_CACHE_PREFIX' ) ) define( 'FLUID_VIDEO_EMBEDS_CACHE_PREFIX', 'fve' );

// YouTube API Key for this project
if( !defined( 'FLUID_VIDEO_EMBEDS_YOUTUBE_API_KEY' ) ) define( 'FLUID_VIDEO_EMBEDS_YOUTUBE_API_KEY', 'AIzaSyDt2tzsawMGWTDxRYd9LMEridJfCdKMJD0' );

// The directory the plugin resides in
if( !defined( 'FLUID_VIDEO_EMBEDS_DIRNAME' ) ) define( 'FLUID_VIDEO_EMBEDS_DIRNAME', dirname( dirname( __FILE__ ) ) );

// The URL path of this plugin
if( !defined( 'FLUID_VIDEO_EMBEDS_URLPATH' ) ) define( 'FLUID_VIDEO_EMBEDS_URLPATH', WP_PLUGIN_URL . "/" . plugin_basename( FLUID_VIDEO_EMBEDS_DIRNAME ) );

// Is the current request Ajax?
if( !defined( 'IS_AJAX_REQUEST' ) ) define( 'IS_AJAX_REQUEST', ( !empty( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) == 'xmlhttprequest' ) );
