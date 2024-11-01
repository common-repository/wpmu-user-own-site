<div id="geweb_register_form_wrap">
    <form  id="geweb_register_form" method="post" action="">
        <fieldset>
            <?php wp_nonce_field( 'geweb-add-blog', '_wpnonce_add-blog' ) ?>
            <table class="form-table">
                <tr class="form-field">
                    <th scope="row"><label for="site-address"><?php _e( 'Site Address' ) ?></label></th>
                    <td>
                        <input name="blog[domain]" type="text" class="regular-text" id="site-address" />
                        <p><?php echo __( 'Only lowercase letters (a-z) and numbers are allowed.' ); ?></p>
                    </td>
                </tr>
                <tr class="form-field">
                    <th scope="row"><label for="site-title"><?php _e( 'Site Title' ) ?></label></th>
                    <td><input name="blog[title]" type="text" class="regular-text" id="site-title" /></td>
                </tr>
                <tr class="form-field">
                    <th scope="row"><label for="admin-email"><?php _e( 'Admin Email' ) ?></label></th>
                    <td><input name="blog[email]" type="email" class="regular-text" id="admin-email" /></td>
                </tr>
                <tr class="form-field">
                    <td>
                        <?php
                            $re_sitekey = get_option('re_sitekey');
                            if($re_sitekey):
                        ?><div class="g-recaptcha" data-sitekey="<?php echo $re_sitekey; ?>"></div>
                        <?php else: ?>
                            &nbsp;
                        <?php endif; ?>
                    </td>
                    <td><?php _e( 'A new user will be created if the above email address is not in the database.' ) ?><br /><?php _e( 'The username and password will be mailed to this email address.' ) ?></td>
                </tr>
            </table>
            <input type="submit" value="Send" />
            <input type="hidden" name="action" value="gewev_add_site" />
        </fieldset>
    </form>
</div>