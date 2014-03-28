<?php
// Add a wrapping <div> if the max width is set.
if( $this->fve_max_width != 0 ) { ?>
<div class="fve-max-width-wrapper">
<?php } ?>

<div class="fve-video-wrapper fve-image-embed fve-thumbnail-image <?php echo $this->provider_slug; ?>" style="padding-bottom:<?php echo $wrapper_padding; ?>;">
    <?php echo $this->iframe_before_src . $iframe_url . $this->iframe_after_src; ?>
    <?php if( $this->fve_responsive_hyperlink ) { ?>
        <a class="hyperlink-image" target="_blank" href="<?php echo $permalink; ?>"><img src="<?php echo $thumbnail; ?>"><div class="fve-play-button" style="background-image: url('<?php echo FLUID_VIDEO_EMBEDS_URLPATH; ?>/images/play.svg');"></div></a>
    <?php } ?>
</div>

<?php
// Add a wrapping <div> if the max width is set.
if( $this->fve_max_width != 0 ) { ?>
</div>
<?php } ?>
