<script type="text/javascript">var __namespace = '<?php echo $namespace; ?>';</script>
<div class="wrap <?php echo $namespace; ?>">
    <h2><?php echo $page_title; ?></h2>
    <form action="" method="post" id="<?php echo $namespace; ?>-form">
        <?php wp_nonce_field( $namespace . "-update-options" ); ?>
        <ul>
            <li>
                <label for="fve_max_width"><?php _e( 'Max width', $namespace ); ?></label>
                <input id="fve_max_width" name="data[fve_max_width]" size="3" value="<?php echo $this->fve_max_width; ?>">
                <p class="description"><?php echo sprintf( __( 'Set this to %1$s0%2$s for no max width. Otherwise, use pixels, or ems. (eg: %1$s500px%2$s or %1$s22em%2$s)', $namespace ), '<code>', '</code>' ) ?></p>
            </li>
            <li>
                <input type="submit" name="submit" class="button-primary" value="<?php _e( "Save Changes", $namespace ) ?>" />
            </li>
        </ul>
    </form>
</div>