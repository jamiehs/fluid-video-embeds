<script type="text/javascript">var __namespace = '<?php echo $namespace; ?>';</script>
<div class="wrap <?php echo $namespace; ?>">
    <h2><?php echo $page_title; ?></h2>
    <form action="" method="post" id="<?php echo $namespace; ?>-form">
        <?php wp_nonce_field( $namespace . "-update-options" ); ?>
        <ul>
            <li>
                <label for="fve_style"><?php _e( 'Embed Style', $namespace ); ?></label>
                <select id="fve_style" name="data[fve_style]">
                    <option<?php echo ( $fve_style == 'iframe' ) ? ' selected="selected"' : '' ; ?> value="iframe"><?php _e( 'iFrame Embed', $namespace ); ?></option>
                    <option<?php echo ( $fve_style == 'image' ) ? ' selected="selected"' : '' ; ?> value="image"><?php _e( 'Image (click to play)', $namespace ); ?></option>
                    <option<?php echo ( $fve_style == 'hyperlink' ) ? ' selected="selected"' : '' ; ?> value="hyperlink"><?php _e( 'Hyperlink', $namespace ); ?></option>
                </select>
            </li>
            <li>
                <input type="submit" name="submit" class="button-primary" value="<?php _e( "Save Changes", $namespace ) ?>" />
                <?php _e( "(Saves the above settings)", $namespace ) ?>
            </li>
        </ul>
    </form>
</div>