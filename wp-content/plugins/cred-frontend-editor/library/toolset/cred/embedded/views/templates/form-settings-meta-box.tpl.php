<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Security check' );
}

$settings = CRED_Helper::mergeArrays( array(
	'post' => array(
		'post_type' => 'post',
		'post_status' => 'draft',
	),
	'form' => array(
		'type' => 'new',
		'action' => 'form',
		'action_page' => '',
		'action_message' => '',
		'redirect_delay' => 0,
		'hide_comments' => 0,
		'theme' => 'minimal',
		'has_media_button' => 0,
		'include_wpml_scaffold' => 0,
		'include_captcha_scaffold' => 0,
	),
), (array) $settings );
?>

<script>
    var $__my_option = "<option value='original'><?php _e( "Keep original status", "wp-cred" ); ?></option>";
</script>

<fieldset class="cred-fieldset cred-no-bottom-margin">
	<?php wp_nonce_field( 'cred-admin-post-page-action', 'cred-admin-post-page-field' ); ?>

    <p class="cred_create_form-explain-text">
		<?php _e( "This form will:", "wp-cred" ); ?>
    </p>
    <p class="cred_form_input left-margin-second-child">
		<?php
		$form_types = apply_filters( 'cred_admin_form_type_options', array(
			"new" => __( 'Add new content', 'wp-cred' ),
			"edit" => __( 'Edit existing content', 'wp-cred' ),
		), $settings['type'], $form );
		$n = 1;
		foreach ( $form_types as $type => $label ) {
			$class = "";
			?>
            <label class="cred-label" style="display: inline;margin-right: 10px;">
                <input type="radio" name="_cred[form][type]" value="<?php echo esc_attr($type); ?>" <?php checked( ( empty( $settings['type'] ) && $n == 1 ) || ( $settings['type'] === $type ) ); ?> /><span><?php echo $label; ?></span>
            </label><?php
			$n ++;
		}
		?>
    </p>

    <p class='cred-label-holder cred_create_form-explain-text'>
        <label for="cred_post_type"><?php _e( 'Post Types to create/edit:', 'wp-cred' ); ?></label>
    </p>
    <p class="cred_form_input">
        <select id="cred_post_type" name="_cred[post][post_type]" class='cred_ajax_change'>
			<?php
			echo '<option value="" selected="selected">' . __( '-- Select Post Type --', 'wp-cred' ) . '</option>';
			foreach ( $post_types as $pt ) {
				if ( ! has_filter( 'cred_wpml_glue_is_translated_and_unique_post_type' ) || apply_filters( 'cred_wpml_glue_is_translated_and_unique_post_type', $pt['type'] ) ) {
					if ( $settings['post']['post_type'] == $pt['type']
						|| ( isset( $_GET['glue_post_type'] )
						&& $pt['type'] == $_GET['glue_post_type'] )
                    ) {
						echo '<option value="' . esc_attr($pt['type']) . '" selected="selected">' . $pt['name'] . '</option>';
					} else {
						echo '<option value="' . esc_attr($pt['type']) . '">' . $pt['name'] . '</option>';
					}
				}
			}
			?>
			<?php
			if ( ! empty( $repeating_fields_groups_post_types ) ) {
				?>
				<optgroup label="<?php echo esc_attr( __( 'Repeatable field groups', 'wp-cred' ) ); ?>">
				<?php
				foreach ( $repeating_fields_groups_post_types as $rfg_pt ) {
					echo '<option value="' . esc_attr( $rfg_pt->name ) . '"'
						. ' ' . selected( $settings['post']['post_type'], $rfg_pt->name, false )
						. '>' 
						. $rfg_pt->labels->name 
						. '</option>';
				}
				?>
				</optgroup>
				<?php
			}
			?>
        </select>
    </p>

    <p class="cred-label-holder cred_create_form-explain-text">
        <label for="cred_post_status"><?php _e( 'Status of the post after submitting the form:', 'wp-cred' ); ?></label>
    </p>
    <p class="cred_form_input">
        <select id="cred_post_status" name="_cred[post][post_status]" class='cred_ajax_change'>
            <option value='' <?php if ( ! isset( $settings['post']['post_status'] ) || empty( $settings['post']['post_status'] ) ) {
				echo 'selected="selected"';
			} ?>><?php _e( '-- Select status --', 'wp-cred' ); ?></option>
            <option value='original' <?php selected( $settings['post']['post_status'], 'original' ); ?>><?php _e( 'Keep original status', 'wp-cred' ); ?></option>
            <option value='draft' <?php selected( $settings['post']['post_status'], 'draft' ); ?>><?php _e( 'Draft', 'wp-cred' ); ?></option>
            <option value='pending' <?php selected( $settings['post']['post_status'], 'pending' ); ?>><?php _e( 'Pending Review', 'wp-cred' ); ?></option>
            <option value='private' <?php selected( $settings['post']['post_status'], 'private' ); ?>><?php _e( 'Private', 'wp-cred' ); ?></option>
            <option value='publish' <?php selected( $settings['post']['post_status'], 'publish' ); ?>><?php _e( 'Published', 'wp-cred' ); ?></option>
        </select>
    </p>

    <p class='cred_create_form-explain-text'>
		<?php _e( 'What to show after submitting the form:', 'wp-cred' ); ?>
    </p>
    <p class="cred_form_input">
        <select id="cred_form_success_action" name="_cred[form][action]">
			<?php
			$form_actions = apply_filters( 'cred_admin_submit_action_options', array(
				"form" => __( 'Keep displaying this form', 'wp-cred' ),
				"message" => __( 'Display a message instead of the form...', 'wp-cred' ),
				"post" => __( 'Display the post', 'wp-cred' ),
				"custom_post" => __( 'Go to a specific post...', 'wp-cred' ),
				"page" => __( 'Go to a page...', 'wp-cred' ),
			), $settings['action'], $form );
			?>
            <option value="" selected="selected"><?php echo __( '-- Select action --', 'wp-cred' ); ?></option>
            <?php
	        foreach ( $form_actions as $value => $label ) {
		        if ( isset( $settings['action'] )
			        && $settings['action'] == $value ) {
			        ?>
                    <option value="<?php echo esc_attr($value); ?>" selected="selected"><?php echo $label; ?></option>
                    <?php
		        } else {
			        ?>
                    <option value="<?php echo esc_attr($value); ?>"><?php echo $label; ?></option>
                    <?php
		        }
	        }
			?>
        </select>
    </p>

    <table width="100%" id="after_visitors_submit_this_form">
        <tr>
            <td width="40%" style="vertical-align: top;">
            </td>

            <td>
                <span data-cred-bind="{ action: 'show', condition: '_cred[form][action]=page' }">
                    <select id="cred_form_success_action_page" name="_cred[form][action_page]">
                        <?php echo $form_action_pages; ?>
                    </select>
                </span>

                <span data-cred-bind="{ action: 'show', condition: '_cred[form][action]=custom_post' }">
                    <select id="cred_form_action_post_type" name="action_post_type" data-placeholder="<?php echo esc_attr( $default_empty_action_post_type ); ?>">
                        <?php echo $form_post_types; ?>
                    </select>
                </span>

                <span data-cred-bind="{ action: 'show', condition: '_cred[form][action]=custom_post' }">
                    <div style="display: none;" id="cred_form_action_ajax_loader" class="cred_ajax_loader_small"></div>
                    <select id="cred_form_action_custom_post" name="_cred[form][action_post]" data-placeholder="<?php echo esc_attr( $default_empty_action_post ); ?>">
                        <?php echo $form_current_custom_post; ?>
                    </select>
                </span>

                <span data-cred-bind="{ action: 'show', condition: '_cred[form][action] in [post,custom_post,page]' }">
                    <?php _e( 'Redirect delay for: ', 'wp-cred' ); ?>
                    <input type='text' size='3' id='cred_form_redirect_delay' name='_cred[form][redirect_delay]' value='<?php echo esc_attr( $settings['redirect_delay'] ); ?>'/>
		            <?php _e( ' seconds.', 'wp-cred' ); ?>
                </span>
            </td>
        </tr>
    </table>

</fieldset>

<fieldset class="cred-fieldset">
    <div data-cred-bind="{ action: 'toggle', condition: '_cred[form][action]=message' }">

        <table width="100%">
            <tr>
                <td width="40%" style="vertical-align: top;">
                </td>
                <td>
                    <i><?php _e( 'Enter the message to display instead of the form. You can use HTML and shortcodes. (but no Toolset Forms)', 'wp-cred' ); ?></i>
					<?php echo CRED_Helper::getRichEditor( 'credformactionmessage', '_cred[form][action_message]', $settings['action_message'], array(
						'wpautop' => true,
						'teeny' => true,
						'editor_height' => 100,
						'editor_class' => 'wpcf-wysiwyg',
						'quicktags' => array( 'buttons' => '' ),
					) ); ?>
                </td>
            </tr>
        </table>

    </div>
</fieldset>

<?php
do_action( 'cred_ext_cred_form_settings', $form, $settings );
?>

<fieldset class="cred-fieldset">

    <table width="100%">
        <tr>
            <td width="40%" style="vertical-align: top;">
                <label class="cred_create_form-explain-text"><?php _e( 'Other settings:', 'wp-cred' ); ?></label>
            </td>
            <td>
                <label class='cred-label-chk'>
                    <input type='checkbox' class='cred-checkbox-10' name='_cred[form][use_ajax]' id='cred_content_has_media_button' value='1' <?php if ( isset( $settings['use_ajax'] ) && $settings['use_ajax'] == '1' ) {
						echo 'checked="checked"';
					} ?> /><span class='cred-checkbox-replace'></span>
                    <span><?php _e( 'AJAX submission', 'wp-cred' ); ?></span>
                </label>

                <label class='cred-label-chk'>
                    <input type='checkbox' class='cred-checkbox-10' name='_cred[form][hide_comments]' id='cred_form_hide_comments' value='1' <?php if ( $settings['hide_comments'] ) {
						echo 'checked="checked"';
					} ?> />
                    <span><?php _e( 'Hide comments when displaying this form', 'wp-cred' ); ?></span>
                </label>

                <label class='cred-label-chk'>
                    <input type='checkbox' class='cred-checkbox-10' name='_cred[form][has_media_button]' id='cred_content_has_media_button' value='1' <?php if ( $settings['form']['has_media_button'] ) {
						echo 'checked="checked"';
					} ?> /><span class='cred-checkbox-replace'></span>
                    <span><?php _e( 'Allow Media Insert button in Post Content Rich Text Editor', 'wp-cred' ); ?></span>
                </label>

            </td>
        </tr>
    </table>

</fieldset>