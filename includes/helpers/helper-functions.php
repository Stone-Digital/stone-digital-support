<?php 

/**
 * Check cuurent user email address exists "stonedigital.com.au"
 * 
 * @since 1.1.0
 * @return boolean
 */
function check_user_email_domain() {
    if (is_user_logged_in()) {
        $user_email = wp_get_current_user()->user_email;
        return strpos($user_email, '@stonedigital.com.au') !== false;
    }
    return false;
}

/**
 * Check cuurent user - user role
 * 
 * @since 1.1.0
 * @return boolean
 */
function check_admin_or_editor() {
    if (is_user_logged_in()) {
        $user = wp_get_current_user();

        if (in_array('administrator', $user->roles) || in_array('editor', $user->roles)) {
            return true;
        }
    }
    return false;
}