=== Fluid Video Embeds ===
Contributors: jamie3d
Donate Link: http://goo.gl/JTYF2
Tags: video, youtube, vimeo, fluid, flexible, elastic, responsive, 100%, full width, embed, oEmbed
Requires at least: 3.3
Tested up to: 3.4.1
Stable tag: trunk
License: GPLv3

Make your "auto embedded" YouTube and Vimeo videos full width (100%) while maintaining their original aspect ratio

== Description ==

This plugin modifies the built-in Vimeo and YouTube oEmbed auto-embeds so they are full-width, and flexible while maintaining their original aspect ratio.

See a live demo here: [Fluid Video Embeds Demo](http://jamie3d.com/fluid-video-embeds-demo/)

**Requirements:** PHP5+, WordPress 3.3+

**Usage:** Install the plugin, activate it, then your YouTube and Vimeo embeds should start to ignore the setting in `Settings > Media > Maximum embed size`

= How It Works =
The Fluid Video Embeds plugin aims to cleanly display YouTube and Vimeo videos while allowing them to be fluid(elastic/felxible) as well. The technique
for doing this is not very new (and is outlined in the credits links below), however I've added a bit of "sugar" to the mix. Since Vimeo and YouTube have robust, 
open APIs, I'm requesting information about each video server side (which is then cached) and used to determine the optimal aspect ratio for the video container.

= Credits =
*   This plugin uses some code from functions in [SlideDeck 2 Lite](http://wordpress.org/extend/plugins/slidedeck2/) for handling the fetching, caching, and organizing of video meta from the aforementioned providers. Please check out SlideDeck if you need your videos in a sweet jQuery Slider.
*   The CSS used to create the 100% width effect was curated and posted by [Web Designer Wall](http://webdesignerwall.com) in their post about  [CSS: Elastic Videos](http://webdesignerwall.com/tutorials/css-elastic-videos).
*   The original CSS is credited to [TJK Design](http://www.tjkdesign.com/articles/how-to-resize-videos-on-the-fly.asp).
*   The above TJK article then in turn credits [A List Apart](http://www.alistapart.com/) for its article titled: [Creating Intrinsic Ratios for Video](http://www.alistapart.com/articles/creating-intrinsic-ratios-for-video/)

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
You can't get the video dimensions (and thus aspect ratio) without doing some sloppy JavaScript (maybe) or making an API call. The only thing that
makes this plugin special is that fact that it attempts to remove black bars from your video, thus necessitating API calls. The API requests are cached however, so it
should only have a minimal impact.

== Screenshots ==

1. Videos with varying aspect ratios in various sized containers are not a problem.
2. Because the fve plugin uses the iFrame method for embedding YouTube and Vimeo (along with the 100% width technique) the videos are naturally responsive for mobile devices.
3. It also handles different aspect ratio videos from Vimeo quite gracefully. The only circumstance where I've seem black bars is when YouTube serves a 320p or 480p video for mobile. 

== Changelog ==
= 1.0.1 =
Added `wmode=transparent&` to the YouTube embed URL. This prevents YouTube videos from covering things like lightboxes and other overlapping content.

= 1.0 =
Initial release

== Upgrade Notice ==
= 1.0.1 =
Added `wmode=transparent&` to the YouTube embed URL

= 1.0 =
Initial release
