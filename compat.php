<?php
!defined('EMLOG_ROOT') && exit('access deined!');

if(!function_exists('get_option')) {
    function get_option($v) {
        return Option::get($v);
    }
}

if(!function_exists('add_action')) {
    function add_action($wp, $func, $other = '') {
        $wp = str_replace(array(''), '', $wp);
        if(!empty($wp)) {
            addAction($wp, $func);
        }
    }
}

if(!function_exists('is_admin')) {
    function is_admin() {
        if(ROLE == ROLE_ADMIN) {
            return true;
        } else {
            return false;
        }
    }
}

if(!function_exists('plugin_basename')) {
    function plugin_basename($path) {
        return 'moecdn';
    }
}