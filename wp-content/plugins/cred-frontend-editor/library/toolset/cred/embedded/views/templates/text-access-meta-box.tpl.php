<?php if (!defined('ABSPATH')) die('Security check'); ?>
<table class="access-form-texts">
    <tbody>
    <div class="cred-notification <?php echo ($is_access_active ? "cred-info" :"cred-error"); ?>">
        <div class="<?php echo ($is_access_active ? "cred-info" :"cred-error"); ?>">
            <?php
            $txt_anchor = (isset($form_type) && $form_type == 'cred-user-form') ? "__CRED_CRED_USER_GROUP" : "__CRED_CRED_GROUP";
            if ($is_access_active) {
                ?>
                <p>
                    <i class="fa fa-info-circle"></i> 
                    <?php printf(
                            __('To control who can see and use this form, go to the %1$sAccess settings%2$s.', 'wp-cred'), 
                            '<a target="_parent" href="' 
                                . admin_url( 'admin.php?page=types_access&tab=cred-forms' ) 
                                . '">',
                            '</a>'
                            ); ?>
                </p>    
                <?php
            } else {
                ?>
                <p>
                    <i class="fa fa-warning"></i> 
                    <?php printf(
                            __('This Form will be accessible to everyone, including guest (not logged in). They will be able to submit/edit content using this form.<br>To control who can use the form, please install the %1$sAccess plugin%2$s.', 'wp-cred'), 
                            '<a target="_blank" href="https://toolset.com/home/toolset-components/#access">',
                            '</a>'
                        ); ?>
                </p>
                <?php
            }
            ?>
        </div>
    </div>
</tbody>
</table>