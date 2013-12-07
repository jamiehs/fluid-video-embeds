<script type="text/javascript">var __namespace = '<?php echo $namespace; ?>';</script>
<div class="wrap <?php echo $namespace; ?>">
    <h2><?php echo $page_title; ?></h2>
    <form action="" method="post" id="<?php echo $namespace; ?>-form">
        <?php wp_nonce_field( $namespace . "-update-options" ); ?>
        <ul>
            <li class="fve_max_width">
                <h3><?php _e( 'Maximum Width' ); ?></h3>
                <label for="fve_max_width"><?php _e( 'Max width:', $namespace ); ?></label>
                <input id="fve_max_width" name="data[fve_max_width]" size="3" value="<?php echo $this->fve_max_width; ?>">
                <p class="description"><?php echo sprintf( __( 'Set this to %1$s0%2$s for no max width. Otherwise, use pixels, or ems. (eg: %1$s500px%2$s or %1$s22em%2$s)', $namespace ), '<code>', '</code>' ); ?></p>
            </li>
            <li class="fve_alignment">
                <h3><?php _e( 'Container Alignment' ); ?></h3>
                <input id="fve_alignment_left" name="data[fve_alignment]" type="radio" size="3" value="left"<?php echo ( $this->fve_alignment == 'left' ) ? ' checked="checked"' : ''; ?>>
                <label for="fve_alignment_left"><?php _e( 'Left', $namespace ); ?></label>
                <input id="fve_alignment_center" name="data[fve_alignment]" type="radio" size="3" value="center"<?php echo ( $this->fve_alignment == 'center' ) ? ' checked="checked"' : ''; ?>>
                <label for="fve_alignment_center"><?php _e( 'Center', $namespace ); ?></label>
                <input id="fve_alignment_right" name="data[fve_alignment]" type="radio" size="3" value="right"<?php echo ( $this->fve_alignment == 'right' ) ? ' checked="checked"' : ''; ?>>
                <label for="fve_alignment_right"><?php _e( 'Right', $namespace ); ?></label>
                <p class="description"><?php _e( 'If the width is smaller, how should the video be aligned?'); ?></p>
            </li>
            <li class="fve_responsive_hyperlink">
                <h3><?php _e( 'Responsive Hyperlink Options' ); ?></h3>
                <input id="fve_responsive_hyperlink" name="data[fve_responsive_hyperlink]" type="checkbox" size="3" value="yes"<?php echo ( $this->fve_responsive_hyperlink == true ) ? ' checked="checked"' : ''; ?>>
                <label for="fve_responsive_hyperlink"><?php _e( 'Use Responsive Hyperlink?', $namespace ); ?></label>
            </li>
            <li>
                <label for="fve_responsive_hyperlink_breakpoint"><?php _e( 'Breakpoint:', $namespace ); ?></label>
                <input id="fve_responsive_hyperlink_breakpoint" name="data[fve_responsive_hyperlink_breakpoint]" size="3" value="<?php echo $this->fve_responsive_hyperlink_breakpoint; ?>">

                <p class="description"><?php _e( 'The Responsive Hyperlink option will swap the video iFrame with a simple hyperlink to the video URL (if the screen is smaller than the specified breakpoint). On mobile and handheld devices, it is often a better experience to watch the video in the native application or player. This option should enable that better experience.'); ?></p>
            </li>
            <li>
                <input type="submit" name="submit" class="button-primary" value="<?php _e( "Save Changes", $namespace ) ?>" />
            </li>
        </ul>
    </form>
</div>