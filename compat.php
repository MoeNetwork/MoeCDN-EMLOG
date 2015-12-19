<?php
!defined('EMLOG_ROOT') && exit('access deined!');

if(!function_exists('get_option')) {
    function get_option($v) {
        return Option::get($v);
    }
}

if(!function_exists('add_action')) {
    function add_action($wp) {

    }
}