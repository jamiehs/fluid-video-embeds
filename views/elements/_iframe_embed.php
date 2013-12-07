<?php 
// Add a wrapping <div> if the max width is set.
if( $this->fve_max_width != 0 ) { ?>
<div class="fve-max-width-wrapper">
<?php } ?>
<div class="fve-video-wrapper fve-image-embed fve-thumbnail-image <?php echo $this->provider_slug; ?>" style="padding-bottom:<?php echo $wrapper_padding; ?>;">
    <?php echo $this->iframe_before_src . $iframe_url . $this->iframe_after_src; ?>
</div>
<?php 
// Add a wrapping <div> if the max width is set.
if( $this->fve_max_width != 0 ) { ?>
</div>
<?php } ?>