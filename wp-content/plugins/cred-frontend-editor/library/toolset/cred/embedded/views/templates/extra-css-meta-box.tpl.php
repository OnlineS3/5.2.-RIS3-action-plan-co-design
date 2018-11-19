<?php if (!defined('ABSPATH'))  die('Security check'); ?>

<div id='cred_extra_settings_panel_container_css' class='cred_extra_settings_panel_container'>
    <div class='cred_extra_css_settings_panel' style='position:relative;'>
    <div class="cred-editor-wrap">
    <textarea id='cred-extra-css-editor' name='_cred[extra][css]' style="position:relative;overflow-y:auto;" class="cred-extra-css-editor<?php if ($css && !empty($css)) echo ' cred-always-open'; ?>"><?php if ($css && !empty($css)) echo $css; ?></textarea>
    <div class="cred-content-resize-handle" title="<?php _e('Resize', 'wp-cred'); ?>"><br></div>
    </div>
    </div>    
</div>