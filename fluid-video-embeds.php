<?php
/*
Plugin Name: Fluid Video Embeds
Plugin URI: http://wordpress.org/extend/plugins/fluid-video-embeds/
Description: Makes your YouTube and Vimeo auto-embeds fluid/full width.
Author: jamie3d
Version: 1.2.9
Author URI: http://jamie3d.com
Text Domain: fluid-video-embeds
Domain Path: /languages
*/

// Include constants file
require_once( dirname( __FILE__ ) . '/lib/constants.php' );

class FluidVideoEmbed{
    static $available_providers = array(
        'youtube',
        'vimeo'
    );

    function __construct() {
        $this->namespace = 'fluid-video-embeds';
        $this->friendly_name = 'Fluid Video Embeds';
        $this->cache_duration = 2880;
        $this->try_to_get_youtube_max_image = false;

        // Name of the option_value to store plugin options in
        $this->option_name = '_' . $this->namespace . '--options';

        // Set default options
        $this->defaults = array(
            'fve_style' => 'iframe',
            'fve_max_width' => '0',
            'fve_alignment' => 'left',
            'fve_responsive_hyperlink' => false,
            'fve_force_youtube_16_9' => false,
            'fve_force_vimeo_16_9' => false,
            'fve_disable_css' => false,
            'fve_responsive_hyperlink_mq' => '@media screen and (max-device-width: 768px)',
            );

        // Autoload the Max Width option
        $this->fve_max_width = (string) $this->get_option( 'fve_max_width' );
        if ( empty( $this->fve_max_width ) ) {
            $this->fve_max_width = $this->defaults['fve_max_width'];
        }

        // Autoload the Alignment option
        $this->fve_alignment = (string) $this->get_option( 'fve_alignment' );
        if ( empty( $this->fve_alignment ) ) {
            $this->fve_alignment = $this->defaults['fve_alignment'];
        }

        // Autoload the Responsive Hyperlink options
        $this->fve_responsive_hyperlink = (bool) $this->get_option( 'fve_responsive_hyperlink' );
        if ( empty( $this->fve_responsive_hyperlink ) ) {
            $this->fve_responsive_hyperlink = $this->defaults['fve_responsive_hyperlink'];
        }
        $this->fve_responsive_hyperlink_mq = (string) $this->get_option( 'fve_responsive_hyperlink_mq' );
        if ( empty( $this->fve_responsive_hyperlink_mq ) ) {
            $this->fve_responsive_hyperlink_mq = $this->defaults['fve_responsive_hyperlink_mq'];
        }

        // Autoload 16:9 options
        $this->fve_force_youtube_16_9 = (bool) $this->get_option( 'fve_force_youtube_16_9' );
        if ( empty( $this->fve_force_youtube_16_9 ) ) {
            $this->fve_force_youtube_16_9 = $this->defaults['fve_force_youtube_16_9'];
        }
        $this->fve_force_vimeo_16_9 = (bool) $this->get_option( 'fve_force_vimeo_16_9' );
        if ( empty( $this->fve_force_vimeo_16_9 ) ) {
            $this->fve_force_vimeo_16_9 = $this->defaults['fve_force_vimeo_16_9'];
        }

        // Autoload disable CSS option
        $this->fve_disable_css = (bool) $this->get_option( 'fve_disable_css' );
        if ( empty( $this->fve_disable_css ) ) {
            $this->fve_disable_css = $this->defaults['fve_disable_css'];
        }

        $this->iframe_before_src = '<iframe src="';
        $this->iframe_after_src = '" width="100%" height="100%" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>';

        load_plugin_textdomain( 'fluid-video-embeds', false, dirname(plugin_basename(__FILE__)) . '/languages/');

        $this->_add_hooks();
    }

    /**
     * Add in various hooks
     *
     * Place all add_action, add_filter, add_shortcode hook-ins here
     */
    private function _add_hooks() {
        // Filter the oEmbed response
        add_filter('embed_oembed_html', array( &$this, 'filter_video_embed' ), 16, 3);

        // Add the Fluid Video Embeds Stylesheets
        if( !$this->fve_disable_css ) {
            add_action('wp_head', array( &$this, 'add_head_css' ) );
        }

        // Options page for configuration
        add_action( 'admin_menu', array( &$this, 'admin_menu' ) );

        // Output the styles for the admin area.
        add_action( 'admin_menu', array( &$this, 'admin_print_styles' ) );

        // Register admin JavaScripts for this plugin
        add_action( 'admin_menu', array( &$this, 'wp_register_admin_scripts' ), 1 );

        // Route requests for form processing
        add_action( 'init', array( &$this, 'route' ) );

        // Enqueue the public scripts
        add_action( 'init', array( &$this, 'enqueue_public_scripts' ) );

        // Add a settings link next to the "Deactivate" link on the plugin listing page.
        add_filter( 'plugin_row_meta', array( &$this, 'plugin_action_links' ), 10, 2 );

        // Register all JavaScripts for this plugin
        add_action( 'init', array( &$this, 'wp_register_scripts' ), 1 );

        // Register all Stylesheets for this plugin
        add_action( 'init', array( &$this, 'wp_register_styles' ), 1 );

        // Add the default stylesheet to the editor
        add_action( 'after_setup_theme', array( &$this, 'add_editor_styles' ) );

        // Admin 'Ajax' view for showing calculated CSS.
        add_action( 'wp_ajax_fve_show_css', array( &$this, 'show_css' ) );

        // Add the fve shortcode
        add_shortcode( 'fve', array( &$this, 'shortcode' ) );
    }

    /**
     * Adds the default FVE stylesheet to the
     * WYSIWYG editor so the shortcode executes correctly.
     */
    function add_editor_styles() {
        add_editor_style( FLUID_VIDEO_EMBEDS_URLPATH . "/stylesheets/main.css" );
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
        $this->head_css();
        echo '</style>' . "\n";
        echo '<!-- End Fluid Video Embeds Style Tag -->' . "\n";
    }

    /**
     * Echoes the generated CSS for the plugin
     */
    function head_css() {
        include( FLUID_VIDEO_EMBEDS_DIRNAME . '/stylesheets/main.css' );

        // Additional styles for maximum width
        if( $this->fve_max_width != '0' ) {
            echo '.fve-max-width-wrapper{' . "\n";
            echo '    max-width: ' . $this->fve_max_width . ';' . "\n";
            // Additional styles for alignment
            switch( $this->fve_alignment ) {
                case 'left':
                echo '    margin-left: 0;' . "\n";
                echo '    margin-right: auto;' . "\n";
                break;
                case 'center':
                echo '    margin-left: auto;' . "\n";
                echo '    margin-right: auto;' . "\n";
                break;
                case 'right':
                echo '    margin-left: auto;' . "\n";
                echo '    margin-right: 0;' . "\n";
                break;
            }
            echo "}" . "\n";
        }

        // Additional styles for responsive hyperlink
        if( $this->fve_responsive_hyperlink ) {
            echo $this->fve_responsive_hyperlink_mq . ' {' . "\n";
            echo '    .fve-video-wrapper iframe, .fve-video-wrapper object, .fve-video-wrapper embed { display: none; }' . "\n";
            echo '    .fve-video-wrapper a.hyperlink-image { display: block; }' . "\n";
            echo '}' . "\n";
        }
    }

    /**
     * Process update page form submissions
     *
     * @uses self::sanitize()
     * @uses wp_redirect()
     * @uses wp_verify_nonce()
     */
    private function _admin_options_update() {
        // Verify submission for processing using wp_nonce
        if( wp_verify_nonce( $_REQUEST['_wpnonce'], "{$this->namespace}-update-options" ) ) {
            $data = array();
            /**
             * Loop through each POSTed value and sanitize it to protect against malicious code. Please
             * note that rich text (or full HTML fields) should not be processed by this function and
             * dealt with directly.
             */
            foreach( $_POST['data'] as $key => $val ) {
                $data[$key] = $this->_sanitize( $val );
            }

            // Add a dimension if the user forgot
            if( !empty( $data['fve_max_width'] ) && !preg_match( '/px|em|%/i', $data['fve_max_width'] ) ) {
                $data['fve_max_width'] .= 'px';
            }

            // Update the options value with the data submitted
            update_option( $this->option_name, $data );

            // Redirect back to the options page with the message flag to show the saved message
            wp_safe_redirect( $_REQUEST['_wp_http_referer'] );
            exit;
        }
    }

    /**
     * Sanitize data
     *
     * @param mixed $str The data to be sanitized
     *
     * @uses wp_kses()
     *
     * @return mixed The sanitized version of the data
     */
    private function _sanitize( $str ) {
        if ( !function_exists( 'wp_kses' ) ) {
            require_once( ABSPATH . 'wp-includes/kses.php' );
        }
        global $allowedposttags;
        global $allowedprotocols;

        if ( is_string( $str ) ) {
            $str = wp_kses( $str, $allowedposttags, $allowedprotocols );
        } elseif( is_array( $str ) ) {
            $arr = array();
            foreach( (array) $str as $key => $val ) {
                $arr[$key] = $this->_sanitize( $val );
            }
            $str = $arr;
        }

        return $str;
    }

    /**
     * Define the admin menu options for this plugin
     *
     * @uses add_action()
     * @uses add_options_page()
     */
    function admin_menu() {
        $page_hook = add_options_page( $this->friendly_name, $this->friendly_name, 'administrator', $this->namespace, array( &$this, 'admin_options_page' ) );

        // Add print scripts and styles action based off the option page hook
        add_action( 'admin_print_scripts-' . $page_hook, array( &$this, 'admin_print_scripts' ) );
    }

    /**
     * The admin section options page rendering method
     *
     * @uses current_user_can()
     * @uses wp_die()
     */
    function admin_options_page() {
        if( !current_user_can( 'manage_options' ) ) {
            wp_die( 'You do not have sufficient permissions to access this page' );
        }

        $namespace = $this->namespace;
        $page_title = $this->friendly_name . ' ' . __( 'Settings', 'fluid-video-embeds' );
        $fve_style = $this->get_option( 'fve_style' );

        include( FLUID_VIDEO_EMBEDS_DIRNAME . "/views/options.php" );
    }

    /**
     * Load JavaScript for the admin options page
     *
     * @uses wp_enqueue_script()
     */
    function admin_print_scripts() {
        wp_enqueue_script( "{$this->namespace}-admin" );
    }

    /**
     * Load Stylesheet for the admin options page
     *
     * @uses wp_enqueue_style()
     */
    function admin_print_styles() {
        wp_enqueue_style( "{$this->namespace}-admin" );
        wp_enqueue_style( "{$this->namespace}-main" );
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
        $name = FLUID_VIDEO_EMBEDS_CACHE_PREFIX . md5( $name );
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
        $name = FLUID_VIDEO_EMBEDS_CACHE_PREFIX . md5( $name );
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
        delete_transient( FLUID_VIDEO_EMBEDS_CACHE_PREFIX . $name );
    }

    /**
     * [fve] shortcode for embedding in a template
     */
    function shortcode( $atts, $content = '' ) {
        extract( shortcode_atts( array(
            'nothing' => 'here yet',
            ), $atts ) );

        // If the embed is supported it returns HTML, if not, false.
        $supported_embed = $this->fluid_video_embed_from_url( $content );
        if( $supported_embed ) {
            return $supported_embed;
        }

        return "";
    }

    /**
     * Runs a simple MySQL query that clears any option from the wp_options table
     * that contains '_fve-cache-'
     */
    static function clear_caches() {
        global $wpdb;

        // Delete all the fve transients that contain '_fve-cache-'
        $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->options WHERE option_name LIKE %s AND option_name LIKE %s", '%_fve-cache-%', '%_transient_%' ) );
    }

    /**
     * Enqueue public scripts used by this plugin for enqueuing elsewhere
     *
     * @uses wp_register_script()
     */
    function enqueue_public_scripts() {
        // Admin JavaScript
        wp_enqueue_script( "{$this->namespace}-public" );
    }

    /**
     * Creates the fulid video embed from a URL
     *
     * @param string $url the video URL
     *
     * @return string the fluid video embed
     */
    function fluid_video_embed_from_url( $url ) {
        $iframe_url = '';

        // Get the provider slug and see if it's supported...
        $this->provider_slug = $this->get_video_provider_slug_from_url( $url );

        // If it is, then this is the point of processing.
        if( in_array( $this->provider_slug, self::$available_providers ) ){
            // Get the meta for the video (this is cached)
            $this->meta = $this->get_video_meta_from_url( $url );

            switch ( $this->provider_slug ) {
                case 'youtube':
                    /**
                     * YouTube doesn't seem to provide width and or height for
                     * their videos. They only provide a 'widescreen' property if the
                     * video is widescreen-ish. So this is likely the best we can do for now.
                     */
                    $wrapper_padding = '75%';
                    if( isset( $this->meta['aspect'] ) && $this->meta['aspect'] == 'widescreen' ) {
                        $wrapper_padding = '56.25%';
                    }

                    $iframe_url = '//www.youtube.com/embed/' . $this->meta['id'] . '?wmode=transparent&modestbranding=1&autohide=1&showinfo=0&rel=0';
                    $iframe_url = apply_filters( 'fve_youtube_iframe_url', $iframe_url, $this->meta );
                    $permalink = '//www.youtube.com/watch?v=' . $this->meta['id'];
                    $permalink = apply_filters( 'fve_youtube_permalink', $permalink, $this->meta );
                    $thumbnail = isset( $this->meta['full_image'] ) ? $this->meta['full_image'] : '';
                    break;
                    case 'vimeo':
                    $wrapper_padding = ( $this->meta['aspect'] * 100 ) . '%';

                    $iframe_url = '//player.vimeo.com/video/' . $this->meta['id'] . '?portrait=0&byline=0&title=0';
                    $iframe_url = apply_filters( 'fve_vimeo_iframe_url', $iframe_url, $this->meta );
                    $permalink = '//vimeo.com/' . $this->meta['id'];
                    $permalink = apply_filters( 'fve_vimeo_permalink', $permalink, $this->meta );
                    $thumbnail = isset( $this->meta['full_image'] ) ? $this->meta['full_image'] : '';
                    break;
                }

                ob_start();
                include( FLUID_VIDEO_EMBEDS_DIRNAME . '/views/elements/_iframe_embed.php' );
                $output = ob_get_contents();
                ob_end_clean();

                return $output;
            }

            return false;
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

        // If the embed is supported it returns HTML, if not, false.
        $supported_embed = $this->fluid_video_embed_from_url( $url );
        if( $supported_embed ) {
            return $supported_embed;
        }

        // Return the default embed.
        return $html;
    }

    /**
     * Retrieve the stored plugin option or the default if no user specified value is defined
     *
     * @param string $option_name The name of the option you wish to retrieve
     *
     * @uses get_option()
     *
     * @return mixed Returns the option value or false(boolean) if the option is not found
     */
    function get_option( $option_name, $reload = false ) {
        // If reload is true, kill the existing options value so it gets fetched fresh.
        if( $reload )
            $this->options = null;

        // Load option values if they haven't been loaded already
        if( !isset( $this->options ) || empty( $this->options ) ) {
            $this->options = get_option( $this->option_name, $this->defaults );
        }

        if( isset( $this->options[$option_name] ) ) {
            return $this->options[$option_name];    // Return user's specified option value
        } elseif( isset( $this->defaults[$option_name] ) ) {
            return $this->defaults[$option_name];   // Return default option value
        }
        return false;
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

        $domain = (string) $matches[2];
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
            $thumbnail_url = 'https://img.youtube.com/vi/' . $video_id . '/mqdefault.jpg';
            break;

            case 'dailymotion':
            $thumbnail_url = 'https://www.dailymotion.com/thumbnail/160x120/video/' . $video_id;
            break;

            case 'vimeo':
                // Create a cache key
            $cache_key = $video_provider . $video_id . 'vimeo-thumbs';

                // Attempt to read the cache
            $_thumbnail_url = $this->cache_read( $cache_key );

                // if cache doesn't exist
            if( !$_thumbnail_url ){
                $response = wp_remote_get( 'https://vimeo.com/api/v2/video/' . $video_id . '.json' );
                if( !is_wp_error( $response ) ) {
                    $response_json = json_decode( $response['body'] );
                    $video = reset( $response_json );
                    $thumbnail_url = $video->thumbnail_medium;

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
                    $url = 'https://www.googleapis.com/youtube/v3/videos';
                    $url.= '?key=' . FLUID_VIDEO_EMBEDS_YOUTUBE_API_KEY;
                    $url.= '&part=contentDetails,snippet';
                    $url.= '&userIp=' . (isset( $_SERVER['SERVER_ADDR'] ) ? $_SERVER['SERVER_ADDR'] : '0.0.0.0') ;
                    $url.= '&id=' . $video_id;
                break;

                case "vimeo":
                    $url = 'https://vimeo.com/api/v2/video/' . $video_id . '.json';
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

                    if( $response_json->pageInfo->totalResults == 0 ) break;

                    $video_meta['title'] = $response_json->items[0]->snippet->title;
                    $video_meta['permalink'] = 'https://www.youtube.com/watch?v=' . $video_id;
                    $video_meta['description'] = $response_json->items[0]->snippet->title;
                    $video_meta['thumbnail'] = 'https://img.youtube.com/vi/' . $video_id . '/mqdefault.jpg';
                    $video_meta['full_image'] = $this->get_youtube_max_thumbnail( $video_id );
                    $video_meta['created_at'] = strtotime( $response_json->items[0]->snippet->publishedAt );
                    $video_meta['aspect'] = 'widescreen';
                    if( isset( $response_json->items[0]->contentDetails->definition ) ) {
                        $video_meta['aspect'] = ( $response_json->items[0]->contentDetails->definition === 'hd' ) ? 'widescreen' : 'standard';
                    }

                    // Allow the widescreen option to be overriden
                    if( $this->fve_force_youtube_16_9 ) {
                        $video_meta['aspect'] = 'widescreen';
                    }
                    $video_meta['duration'] = $response_json->items[0]->contentDetails->duration;

                    if( isset( $response_json->items[0]->snippet->channelTitle ) ) {
                        $video_meta['author_name'] = $response_json->items[0]->snippet->channelTitle;
                        $video_meta['author_url'] = "https://www.youtube.com/channel/" . $response_json->items[0]->snippet->channelId;
                    }
                    break;

                    case 'vimeo':
                    $video = reset( $response_json );
                    $video_meta['title'] = $video->title;
                    $video_meta['permalink'] = 'https://vimeo.com/' . $video_id;
                    $video_meta['description'] =  $video->description;
                    $video_meta['thumbnail'] = $video->thumbnail_medium;
                    $video_meta['full_image'] = $video->thumbnail_large;
                    $video_meta['author_name'] = $video->user_name;
                    $video_meta['author_url'] = $video->user_url;
                    $video_meta['author_avatar'] = $video->user_portrait_small;
                    $video_meta['aspect'] = $video->height / $video->width;
                    $video_meta['duration'] = $video->duration;
                    // Allow the widescreen option to be overriden
                    if( $this->fve_force_vimeo_16_9 ) {
                        $video_meta['aspect'] = 1080/1920; // 16:9
                    }
                    break;
                }
            }
        }

        return $video_meta;
    }

    /**
     * Get Maximum YouTube Thumbnail
     *
     * YouTube maked it both easy and difficult to
     * get the highest resolution image for their videos.
     * Here we try to get the max resolution thumbnail and
     * if it returns a 404, then we simply serve the
     * medium quality version.
     *
     * @param string $video_id
     *
     * @return string The largest image we can get for this video
     */
    function get_youtube_max_thumbnail( $video_id ) {
        if( $this->try_to_get_youtube_max_image ) {
            // The URL of the maximum resolution YouTube thumbnail
            $max_res_url = 'https://img.youtube.com/vi/' . $video_id . '/maxresdefault.jpg';
            $cache_key = $max_res_url . 'max_res_test';
            $cache_duration = 60 * 60 * 24 * 2; // Two days

            // Attempt to read the cache for the response.
            $response_code = $this->cache_read( $cache_key );

            if( !$response_code ) {
                // Ask YouTube for the maximum resolution image.
                $response = wp_remote_get( $max_res_url, array( 'sslverify' => false ) );

                // If the response is good, cache the response code.
                if( !is_wp_error( $response ) ) {
                    if( isset( $response['response']['code'] ) ) {
                        $this->cache_write( $cache_key, (string) $response['response']['code'], $cache_duration );
                    }
                }
            }

            // If the response code is not 404
            if( $response_code != '404' ) {
                return $max_res_url;
            }
        }

        return 'https://img.youtube.com/vi/' . $video_id . '/mqdefault.jpg';
    }

    /**
     * Initialization function to hook into the WordPress init action
     *
     * Instantiates the class on a global variable and sets the class, actions
     * etc. up for use.
     */
    static function instance() {
        global $fve;

        // Only instantiate the Class if it hasn't been already
        if( !isset( $fve ) ) $fve = new FluidVideoEmbed();
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
     * Hook into plugin_action_links filter
     *
     * Adds a "Settings" link next to the "Deactivate" link in the plugin listing page
     * when the plugin is active.
     *
     * @param object $links An array of the links to show, this will be the modified variable
     * @param string $file The name of the file being processed in the filter
     */
    function plugin_action_links( $links, $file ) {
        if( $file == plugin_basename( FLUID_VIDEO_EMBEDS_DIRNAME . '/' . basename( __FILE__ ) ) ) {
            $old_links = $links;
            $new_links = array(
                "settings" => '<a href="options-general.php?page=' . $this->namespace . '">' . __( 'Settings', $this->namespace ) . '</a>'
                );
            $links = array_merge( $old_links, $new_links );
        }

        return $links;
    }

    /**
     * Route the user based off of environment conditions
     *
     * This function will handling routing of form submissions to the appropriate
     * form processor.
     *
     * @uses self::_admin_options_update()
     */
    function route() {
        $uri = $_SERVER['REQUEST_URI'];
        $protocol = isset( $_SERVER['HTTPS'] ) ? 'https' : 'http';
        $hostname = $_SERVER['HTTP_HOST'];
        $url = "{$protocol}://{$hostname}{$uri}";
        $is_post = (bool) ( strtoupper( $_SERVER['REQUEST_METHOD'] ) == "POST" );

        // Check if a nonce was passed in the request
        if( isset( $_REQUEST['_wpnonce'] ) ) {
            $nonce = $_REQUEST['_wpnonce'];

            // Handle POST requests
            if( $is_post ) {
                if( wp_verify_nonce( $nonce, "{$this->namespace}-update-options" ) ) {
                    $this->_admin_options_update();
                }
            }
            // Handle GET requests
            else {
                // Nothing here yet...
            }
        }
    }

    /**
     * Admin Ajax function to simply show the generated CSS
     *
     * @uses head_css()
     * @uses wp_die()
     */
    function show_css() {
        header('Content-Type: text/plain');
        $this->head_css();
        wp_die();
    }

    /**
     * Register admin scripts used by this plugin for enqueuing elsewhere
     *
     * @uses wp_register_script()
     */
    function wp_register_admin_scripts() {
        // Admin JavaScript
        // We may need this later on
        //wp_register_script( "{$this->namespace}-admin", FLUID_VIDEO_EMBEDS_URLPATH . "/javascripts/admin.js", array( 'jquery' ), FLUID_VIDEO_EMBEDS_VERSION, true );
    }

    /**
     * Register scripts used by this plugin for enqueuing elsewhere
     *
     * @uses wp_register_script()
     */
    function wp_register_scripts() {
        // Admin JavaScript
        // We may need this later on
        //wp_register_script( "{$this->namespace}-public", FLUID_VIDEO_EMBEDS_URLPATH . "/javascripts/public.js", array( 'jquery' ), FLUID_VIDEO_EMBEDS_VERSION, true );
    }

    /**
     * Register styles used by this plugin for enqueuing elsewhere
     *
     * @uses wp_register_style()
     */
    function wp_register_styles() {
        // Admin Stylesheet
        wp_register_style( "{$this->namespace}-admin", FLUID_VIDEO_EMBEDS_URLPATH . "/stylesheets/admin.css", array(), FLUID_VIDEO_EMBEDS_VERSION, 'screen' );

        // Default Stylesheet
        wp_register_style( "{$this->namespace}-main", FLUID_VIDEO_EMBEDS_URLPATH . "/stylesheets/main.css", array(), FLUID_VIDEO_EMBEDS_VERSION, 'screen' );
    }

    /***********************************************************************
    ******************** Activation and De-Activation **********************
    ***********************************************************************/
    /**
     * Static WordPress activation function.
     * (Do not depend on this being fired when upgrading)
     */
    static function activate() {
        self::clear_caches();
    }

    /**
     * Static WordPress de-activation function.
     * (Do not depend on this being fired when upgrading)
     */
    static function deactivate() {
        self::clear_caches();
    }
}

if( !isset( $fve ) ) {
    FluidVideoEmbed::instance();
}

register_activation_hook( __FILE__, array('FluidVideoEmbed', 'activate') );
register_deactivation_hook( __FILE__, array('FluidVideoEmbed', 'deactivate') );

?>
