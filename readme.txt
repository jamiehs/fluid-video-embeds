=== Fluid Video Embeds ===
Contributors: jamie3d
Donate Link: http://goo.gl/JTYF2
Tags: video, youtube, vimeo, fluid, flexible, elastic, responsive, 100%, full width, embed, oEmbed
Requires at least: 3.3
Tested up to: 4.3.1
Stable tag: trunk
License: GPLv3

Make your "auto embedded" YouTube and Vimeo videos full width (100%) while maintaining their original aspect ratio

== Description ==

This plugin modifies the built-in Vimeo and YouTube oEmbed auto-embeds so they are full-width, and flexible while maintaining their original aspect ratio.

As of version `1.2.2` it contains English, Portuguese (BR), and Spanish translations.

See a live demo here: [Fluid Video Embeds Demo](http://jamie3d.com/fluid-video-embeds-demo/)

**Requirements:** PHP5+, WordPress 3.3+

**Usage:** Install the plugin, activate it, then your YouTube and Vimeo embeds should start to ignore the setting in `Settings > Media > Maximum embed size` You don't need to use embed code or a shortcode, you can simply paste the YouTube or Vimeo URL into your post and it should work.

You can also use the `[fve]` shortcode:

    [fve]http://youtu.be/oHg5SJYRHA0[/fve]

If you want to use the Fluid Video Embeds method in a php template file in your theme, you can use the do_shortcode method:

    <?php echo do_shortcode('[fve]http://youtu.be/oHg5SJYRHA0[/fve]'); ?>

You can filter the YouTube and or Vimeo URLs like this if you want to customize them (like explicitly specifying the https scheme):

    // Filter the iframe URL for Vimeo
    add_filter( 'fve_vimeo_iframe_url', 'fve_vimeo_iframe_url', 10, 2 );
    function fve_vimeo_iframe_url( $vimeo_iframe_url, $video_meta ) {
      return 'https://player.vimeo.com/video/' . $video_meta['id'] . '?portrait=0&byline=0&title=0';
    }

Check the source to see all of the filters by searching for `apply_filters(`


= How It Works =
The Fluid Video Embeds plugin aims to cleanly display YouTube and Vimeo videos while allowing them to be fluid(elastic/felxible) as well. The technique for doing this is not very new (and is outlined in the credits links below), however I've added a bit of "sugar" to the mix. Since Vimeo and YouTube have robust, open APIs, I'm requesting information about each video server side (which is then cached) and used to determine the optimal aspect ratio for the video container.

= Credits =
* This plugin uses some code from functions in [SlideDeck 2 Lite](http://wordpress.org/extend/plugins/slidedeck2/) for handling the fetching, caching, and organizing of video meta from the aforementioned providers. Please check out SlideDeck if you need your videos in a sweet jQuery Slider.
* The CSS used to create the 100% width effect was curated and posted by [Web Designer Wall](http://webdesignerwall.com) in their post about  [CSS: Elastic Videos](http://webdesignerwall.com/tutorials/css-elastic-videos).
* The original CSS is credited to [TJK Design](http://www.tjkdesign.com/articles/how-to-resize-videos-on-the-fly.asp).
* The above TJK article then in turn credits [A List Apart](http://www.alistapart.com/) for its article titled: [Creating Intrinsic Ratios for Video](http://www.alistapart.com/articles/creating-intrinsic-ratios-for-video/)

== Installation ==

1. Upload the `fluid-video-embeds` folder and all its contents to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. ???
4. Enjoy/Profit.

== Frequently Asked Questions ==

= Why does the plugin only support YouTube and Vimeo? =
I believe that these are the two most popular video platforms (for my current audience) and I coded them first because I am the most familiar with them.
I am not opposed to adding video-provider X if their API allows me to.

= Why do you need to make API calls? =
You can't get the video dimensions (and thus aspect ratio) without doing some sloppy JavaScript (maybe) or making an API call. The only thing that makes this plugin special is that fact that it attempts to remove black bars from your video, thus necessitating API calls. The API requests are cached however, so it should only have a minimal impact.

== Screenshots ==

1. Settings page.
2. Videos with varying aspect ratios in various sized containers are not a problem.
3. Because the fve plugin uses the iFrame method for embedding YouTube and Vimeo (along with the 100% width technique) the videos are naturally responsive for mobile devices.
4. It also handles different aspect ratio videos from Vimeo quite gracefully. The only circumstance where I've seen black bars is when YouTube serves a 320p or 480p video for mobile.

== Changelog ==
= 1.2.9 =
* You can now filter the video URLs and permalinks with `fve_youtube_iframe_url`, `fve_youtube_permalink`, etc.

= 1.2.8 =
* Updating translation text domain
* Updating translations for es_ES and pt_BR

= 1.2.7 =
* Added https:// to certain endpoint and asset URLs in the plugin (better https support).
* Added 16:9 override for Vimeo videos. (It seems some servers are unable to reach Vimeo's API).
* Added option to disable CSS output. This was breaking minification for some users. There is now an accompanying URL that shows the generated CSS for manual inclusion into the theme if desired.

= 1.2.6 =
* Removes the scheme (`http://`) from the iframe URLs for better `https://` support. Thanks to NicholasCook for the fix.

= 1.2.5 =
* Adds an editor stylesheet for TinyMCE

= 1.2.4 =
* Fixes a few PHP notices due to empty API results.
* Adds a new YouTube option that allows you to force the 16:9 ratio.

= 1.2.3 =
* Upgraded to version 3 of the YouTube API.
* Fixes a previous issue that was introduced around version 1.0.3 when the YouTube version 2 API became unreliable.

= 1.2.2 =
* Added Portuguese translation thanks to Eduardo http://www.linkedin.com/in/eduardotoledano
* Added a max-width to the settings page.

= 1.2.1 =
* Added full i18n (internationalization) support.
* Added Spanish translation thanks to Andrew at http://www.webhostinghub.com/

= 1.2.0 =
* Added a max-width option to the new settings screen.
* Now has an alignment option if the max-width option is used.
* New hyperlink mode for mobile devices with a customizable media query.

= 1.1.1 =
Changed the way that YouTube widescreen videos are determined. It looks like they changed an API without letting us know.

= 1.1.0 =
Adding a [fve] shortcode for use in your theme like this: `<?php echo do_shortcode('[fve]http://www.youtube.com/watch?v=oHg5SJYRHA0[/fve]'); ?>`

= 1.0.3 =
Fixing an error (Warning/Notice) that was being thrown if the YouTube API did not return an aspect ratio property.

= 1.0.2 =
Adding a feed detection function that reverts to the default functionality for oEmbeds if the post is being viewed in a feed.

= 1.0.1 =
Added `wmode=transparent&` to the YouTube embed URL. This prevents YouTube videos from covering things like lightboxes and other overlapping content.

= 1.0 =
Initial release


== Upgrade Notice ==
= 1.2.9 =
* Adds filters for the YouTube and Vimeo URLs

= 1.2.8 =
* Updating translations for es_ES and pt_BR

== Upgrade Notice ==
= 1.2.7 =
* Vimeo 16:9 override, more https fixes

= 1.2.6 =
* Fixes https issue

= 1.2.5 =
* Adds an editor stylesheet for TinyMCE

= 1.2.4 =
* Adds a new YouTube option that allows you to force the 16:9 ratio.

= 1.2.3 =
* Fixes bug with 4:3 YouTube aspect ratio (SD) videos.

= 1.2.2 =
* Adds Portuguese translation.

= 1.2.1 =
* Adds Spanish translation & i18n support.

= 1.2.0 =
* Added a max-width option to the new settings screen.

= 1.1.0 =
Adding a shortcode option for use in template files

= 1.0.1 =
Added `wmode=transparent&` to the YouTube embed URL

= 1.0 =
Initial release
