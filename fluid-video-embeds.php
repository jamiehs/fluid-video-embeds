<?php
/*
Plugin Name: Fluid Video Embeds
Plugin URI: http://wordpress.org/extend/plugins/fluid-video-embeds/
Description: Makes your YouTube and Vimeo auto-embeds fluid/full width.
Author: jamie3d
Version: 1.0.3
Author URI: http://jamie3d.com
*/

class FluidVideoEmbed{
    static $available_providers = array(
        'youtube',
        'vimeo'
    );
    
    static $cache_duration = 2880;
    
    function __construct() {
        $this->cache_duration = self::$cache_duration;
        
        // A few constants...
        define( 'FVE_VERSION', '1.0.3' );
        // The directory the plugin resides in
        if( !defined( 'FVE_DIRNAME' ) ) define( 'FVE_DIRNAME', dirname( __FILE__ ) );
        
        // The URL path of this plugin
        if( !defined( 'FVE_URLPATH' ) ) define( 'FVE_URLPATH', ( is_ssl() ? str_replace( "http://", "https://", WP_PLUGIN_URL ) : WP_PLUGIN_URL ) . "/" . basename( FVE_DIRNAME ) );
        
        // The URL path of this plugin
        if( !defined( 'FVE_CACHE_PREFIX' ) ) define( 'FVE_CACHE_PREFIX', 'fve-cache-' );


        // Filter the oEmbed response
        add_filter('embed_oembed_html', array( &$this, 'filter_video_embed' ), 16, 3);
        // Register all JavaScript files used by this plugin
        add_action( 'init', array( &$this, 'wp_register_scripts' ), 1 );
        add_action( 'wp_print_scripts', array( &$this, 'wp_print_scripts' ) );
        add_action('wp_head', array( &$this, 'add_head_css' ) );
    }
    
    /**
     * Filter the Video Embeds
     * 
     * This filters Wordpress' built-in embeds and catches the URL if
     * it's one of the whitelisted providers. I'm only supporting YouTube and
     * Vimeo for now, but if demand is high, I might add more.
	 * 
	 * @uses $this->is_feed()
	 * 
	 * @return string filtered or unfiltered $html
     */
    function filter_video_embed($html, $url, $attr) {
    	/**
		 * If the content is being accessed via a RSS feed,
		 * let's just enforce the default behavior.
		 */
    	if( $this->is_feed() ) return $html;
		
        $this->provider_slug = $this->get_video_provider_slug_from_url( $url );
        
        if( in_array( $this->provider_slug, self::$available_providers ) ){
            $this->meta = $this->get_video_meta_from_url( $url );
            
            switch ( $this->provider_slug ) {
                case 'youtube':
                    /**
                     * YouTube doesn't seem to provide width and or height for
                     * their videos. They only provide a 'widescreen' property if the
                     * video is widescreen-ish. So this is likely the best we can do for now.
                     */
                    $padding = '75%';
                    if( $this->meta['aspect'] == 'widescreen' )
                        $padding = '56.25%';
                    
                    return '<div class="fve-video-wrapper ' . $this->provider_slug . '" style="padding-bottom:' . $padding . ';"><iframe class="youtube-player" type="text/html" width="100%" height="100%" src="http://www.youtube.com/embed/' . $this->meta['id'] . '?wmode=transparent&modestbranding=1&autohide=1&showinfo=0&rel=0" frameborder="0"></iframe></div>';
                break;
                case 'vimeo':
                    $padding = ( $this->meta['aspect'] * 100 ) . '%';
                    
                    return '<div class="fve-video-wrapper ' . $this->provider_slug . '" style="padding-bottom:' . $padding . ';"><iframe src="http://player.vimeo.com/video/' . $this->meta['id'] . '?portrait=0&byline=0&title=0" width="100%" height="100%" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe></div>';
                break;
            }
        }
        
        // Return the default embed.
        return $html;
    }
    
    /**
     * Adds the Style tag to the head of the page.
     * 
     * I'm trying it this way because it might be easier than loading an 
     * additional file for a small amount of CSS. We'll see...
     */
    function add_head_css() {
        echo '<!-- Start Fluid Video Embeds Style Tag -->' . "\n";
        echo '<style type="text/css">' . "\n";
        include( FVE_DIRNAME . '/stylesheets/main.css' );
        echo '</style>' . "\n";
        echo '<!-- End Fluid Video Embeds Style Tag -->' . "\n";
    }
    
    /**
     * I thought we might need a script or two, but it turns out
     * that we don't. I'll leave them here for now though.
     */
    function wp_register_scripts() {
        //wp_register_script( "fve-main-js", FVE_URLPATH . '/javascripts/main.js', array( 'jquery' ), FVE_VERSION, true );
    }
    
    function wp_print_scripts( ) {
        //wp_enqueue_script( 'jquery' );
        //wp_enqueue_script( 'fve-main-js' );
    }

    /**
     * Sets a WordPress Transient. Returns a boolean value of the success of the write.
     * 
     * @param string $name The name (key) for the file cache
     * @param mixed $content The content to store for the file cache
     * @param string $time_from_now time in minutes from now when the cache should expire
     * 
     * @uses set_transient()
     * 
     * @return boolean
     */
    function cache_write( $name = "", $content = "", $time_from_now = 30 ) {
        $duration = $time_from_now * 60;
        $name = FVE_CACHE_PREFIX . md5( $name );
        return set_transient( $name, $content, $duration );
    }
    
    /**
     * Reads a file cache value and returns the content stored, 
     * or returns boolean(false)
     * 
     * @param string $name The name (key) for the transient
     * 
     * @uses get_transient()
     * 
     * @return mixed
     */
    function cache_read( $name = "" ) {
        $name = FVE_CACHE_PREFIX . md5( $name );
        return get_transient( $name );
    }
    
    /**
     * Deletes a WordPress Transient Cache
     * 
     * @param string $name The name (key) for the file cache
     * 
     * @uses delete_transient()
     */
    function cache_clear( $name = "" ) {
        delete_transient( FVE_CACHE_PREFIX . $name );
    }
    
    /**
     * Get Video Provider Slug From URl
     * 
     * @param string $url of a (standard) video from YouTube, Dailymotion or Vimeo
     * 
     * @return string The slug of the video service.
     */
    function get_video_provider_slug_from_url( $url ){
        // Return a youtube reference for a youtu.be URL
        if( preg_match( '/(youtu\.be)/i', $url ) ){
            return 'youtube';
        }
        
        // Detect the dotcoms normally.
        preg_match( '/((youtube|vimeo|dailymotion)\.com)/i', $url, $matches );
        
        // If nothing was detected...
        if( !isset( $matches[2] ) )
            return false;
        
        $domain = $matches[2];
        return $domain;
    }
    
    /**
     * Get a video's thumbnail
     * 
     * Extract's a video's ID and provider from the URL and retrieves the URL for the
     * thumbnail of the video from its video service's thumbnail service.
     * 
     * @param string $video_url The URL of the video being queried
     * 
     * @uses is_wp_error()
     * @uses cache_read()
     * @uses cache_write()
     * @uses get_video_id_from_url()
     * @uses get_video_provider_slug_from_url()
     * @uses wp_remote_get()
     * 
     * @return string
     */
    function get_video_thumbnail( $video_url ){
        $video_id = $this->get_video_id_from_url( $video_url );
        $video_provider = $this->get_video_provider_slug_from_url( $video_url );
        
        $thumbnail_url = '';
        
        switch( $video_provider ){
            case 'youtube':
                $thumbnail_url = 'http://img.youtube.com/vi/' . $video_id . '/2.jpg';
            break;
            
            case 'dailymotion':
                $thumbnail_url = 'http://www.dailymotion.com/thumbnail/160x120/video/' . $video_id;
            break;
            
            case 'vimeo':
                // Create a cache key
                $cache_key = $video_provider . $video_id . 'vimeo-thumbs';
                
                // Attempt to read the cache
                $_thumbnail_url = $this->cache_read( $cache_key );
                
                // if cache doesn't exist
                if( !$_thumbnail_url ){
                    $response = wp_remote_get( 'http://vimeo.com/api/v2/video/' . $video_id . '.json' );
                    if( !is_wp_error( $response ) ) {
                        $response_json = json_decode( $response['body'] );
                        $video = reset( $response_json );
                        $thumbnail_url = $video->thumbnail_small;
                        
                        // Write the cache
                        $this->cache_write( $cache_key, $thumbnail_url, $this->cache_duration );
                    }
                }
            break;
        }

        return $thumbnail_url;
    }

    /**
     * Get Video ID From URL
     * 
     * @param string $url of a (standard) video from YouTube, Dailymotion or Vimeo
     * 
     * @return string The ID of the video for the service detected.
     */
    function get_video_id_from_url( $url ){
        preg_match( '/(youtube\.com|youtu\.be|vimeo\.com|dailymotion\.com)/i', $url, $matches );
        $domain = $matches[1];
        $video_id = "";
        
        switch( $domain ){
            case 'youtube.com':
                if( preg_match( '/^[^v]+v.(.{11}).*/i', $url, $youtube_matches ) ) {
                    $video_id = $youtube_matches[1];
                } elseif( preg_match( '/youtube.com\/user\/(.*)\/(.*)$/i', $url, $youtube_matches ) ) {
                    $video_id = $youtube_matches[2];
                }
            break;
            
            case 'youtu.be':
                if( preg_match( '/youtu.be\/(.*)$/i', $url, $youtube_matches ) ) {
                    $video_id = $youtube_matches[1];
                }
            break;
            
            case 'vimeo.com':
                preg_match( '/(clip\:)?(\d+).*$/i', $url, $vimeo_matches );
                $video_id = $vimeo_matches[2];
            break;
            
        }

        return $video_id;
    }


    /**
     * Get video meta from a video source URL
     * 
     * Parses a video URL and extracts its associated id, service and API meta data
     * 
     * @param string $url The video source URL
     * 
     * @uses is_wp_error()
     * @uses cache_read()
     * @uses cache_write()
     * @uses get_video_provider_slug_from_url()
     * @uses get_video_id_from_url()
     * @uses wp_remote_get()
     * 
     * @return array
     */
    function get_video_meta_from_url( $url ) {
        $service = $this->get_video_provider_slug_from_url( $url );
        $video_id = $this->get_video_id_from_url( $url );
        
        $video_meta = array(
            'id' => $video_id,
            'service' => $service
        );
        
        // Create a cache key
        $cache_key = "video-meta-{$service}{$video_id}";
        
        // Attempt to read the cache for the response
        $response = $this->cache_read( $cache_key );
        
        if( !$response ) {
            switch( $service ) {
                case "youtube":
                    $url = 'http://gdata.youtube.com/feeds/api/videos/' . $video_id . '?v=2&alt=json';
                break;
                
                case "vimeo":
                    $url = 'http://vimeo.com/api/v2/video/' . $video_id . '.json';
                break;
            }
            
            $response = wp_remote_get( $url, array( 'sslverify' => false ) );
            
            // Only update the cache if this is not an error
            if( !is_wp_error( $response ) ) {
                $this->cache_write( $cache_key, $response, $this->cache_duration );
            }
        }
        
        if( !is_wp_error( $response ) ) {
            $response_json = json_decode( $response['body'] );
            
            if( !empty( $response_json ) ) {
                switch( $service ){
                    case 'youtube':
                        $video_meta['title'] = $response_json->entry->title->{'$t'};
                        $video_meta['permalink'] = 'http://www.youtube.com/watch?v=' . $video_id;
                        $video_meta['description'] = $response_json->entry->{'media$group'}->{'media$description'}->{'$t'};
                        $video_meta['thumbnail'] = 'http://img.youtube.com/vi/' . $video_id . '/mqdefault.jpg';
                        $video_meta['full_image'] = 'http://img.youtube.com/vi/' . $video_id . '/0.jpg';
                        $video_meta['created_at'] = strtotime( $response_json->entry->published->{'$t'} );
						$video_meta['aspect'] = 'standard';
						if( isset( $response_json->entry->{'media$group'}->{'yt$aspectRatio'} ) ) {
	                        $video_meta['aspect'] = ( $response_json->entry->{'media$group'}->{'yt$aspectRatio'}->{'$t'} == 'widescreen' ) ? 'widescreen' : 'standard';
						}
                        $video_meta['duration'] = $response_json->entry->{'media$group'}->{'yt$duration'}->{'seconds'};
                        
                        if( isset( $response_json->entry->author ) ) {
                            $author = reset( $response_json->entry->author );
                            $video_meta['author_name'] = $author->name->{'$t'};
                            $video_meta['author_url'] = "http://www.youtube.com/user/" . $author->name->{'$t'};
                        }
                    break;
                    
                    case 'vimeo':
                        $video = reset( $response_json );
                        $video_meta['title'] = $video->title;
                        $video_meta['permalink'] = 'http://vimeo.com/' . $video_id;
                        $video_meta['description'] =  $video->description;
                        $video_meta['thumbnail'] = $video->thumbnail_medium;
                        $video_meta['full_image'] = $video->thumbnail_large;
                        $video_meta['author_name'] = $video->user_name;
                        $video_meta['author_url'] = $video->user_url;
                        $video_meta['author_avatar'] = $video->user_portrait_small;
                        $video_meta['aspect'] = $video->height / $video->width;
                        $video_meta['duration'] = $video->duration;
                    break;
                }
            }
        }

        return $video_meta;
    }

	/**
	 * Is Feed?
	 * 
	 * An extension of the is_feed() function.
	 * We first check WWordPress' built in method and if it passes,
	 * then we say yes this is a feed. If it fails, we try to detect FeedBurner
	 * 
	 * @return boolean
	 */
	function is_feed(){
		if( is_feed() ){
			return true;
		}elseif( preg_match( '/feedburner/', strtolower( $_SERVER['HTTP_USER_AGENT'] ) ) ){
			return true;
		}
		return false;
	}
    
    /**
     * Runs a simple MySQL query that clears any option from the wp_options table
     * that contains '_fve-cache-'
     */
    static function clear_caches(){
        global $wpdb;
        
        // Delete all the fve transients that contain '_fve-cache-'
        $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->options WHERE option_name LIKE %s AND option_name LIKE %s", '%_fve-cache-%', '%_transient_%' ) );
    }

    static function activate() {
        self::clear_caches();
    }
    static function deactivate() {
        self::clear_caches();
    }
}

$fve = new FluidVideoEmbed();

register_activation_hook( __FILE__, array('FluidVideoEmbed', 'activate') );
register_deactivation_hook( __FILE__, array('FluidVideoEmbed', 'deactivate') );

?>