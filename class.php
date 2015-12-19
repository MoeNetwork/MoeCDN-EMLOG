<?php
!defined('EMLOG_ROOT') && exit('access deined!');

class MoeCDN {
    public static $options = array(
        'gravatar'    => '1',
        'googleapis'  => '1',
        'worg'        => '1'
    );

    public function __construct() {
        self::$options = array_merge(self::$options, $this->getAllOpt());
        if (!is_array(self::$options))
            self::reset_options();
    }

    public static function checked($val) {
        if(self::$options[$val] == '1') {
            echo ' checked="checked"';
        }
    }

    protected function getAllOpt() {
        $r = array(
            'gravatar'    => self::get('gravatar'),
            'googleapis'  => self::get('googleapis'),
            'worg'        => self::get('worg')
        );
        foreach($r as $key => $test) {
            if(is_null($test)) {
                $m = Database::getInstance();
                $m->query('INSERT INTO `'.DB_PREFIX."options` (`option_name`,`option_value`) VALUES ('moecdn_{$key}','".self::$options[$key]."');");
                $c = Cache::getInstance();
                $c->updateCache('options');
                unset($r[$key]);
            }
        }
        return $r;
    }

    public static function get($v) {
        return Option::get('moecdn_' . $v);
    }

    public static function set($n , $v) {
        option::updateOption('moecdn_' . $n , $v);
    }

    public function hook() {
        addAction('adm_siderbar_ext', array('MoeCDN', 'options_menu'));
        addAction('index_head', array('MoeCDN', 'bufferStart'));
        addAction('index_footer', array('MoeCDN', 'bufferEnd'));
        addAction('adm_head', array('MoeCDN', 'bufferStart'));
        addAction('adm_footer', array('MoeCDN', 'bufferEnd'));
    }

    // 缓冲替换输出
    public static function bufferStart() {
        ob_start(array('MoeCDN', 'replace'));
    }
    public static function bufferEnd() {
        ob_end_flush();
    }

    // 替换内容
    public static function replace($content) {
        if (self::$options['gravatar']) {
            $content = str_replace(array("//gravatar.com", "//www.gravatar.com", "//0.gravatar.com", "//1.gravatar.com", "//2.gravatar.com", "cn.gravatar.com"), "//gravatar.moefont.com", $content);
            $content = str_replace(array("//secure.gravatar.com"), "//gravatar-ssl.moefont.com", $content);
        }

        if (self::$options['googleapis']) {
            $content = str_replace(array("//fonts.googleapis.com"), "//cdn.moefont.com/fonts", $content);
            $content = str_replace(array("//ajax.googleapis.com"), "//cdn.moefont.com/ajax", $content);
        }

        if (self::$options['worg']) {
            $content = str_replace(array("\\/\\/s.w.org"), "\\/\\/cdn.moefont.com\\/worg", $content);
            $content = str_replace(array("//s.w.org"), "//cdn.moefont.com/worg", $content);
        }

        if (self::$options['wpcom']) {
            $content = str_replace(array("//s0.wp.com", "//s1.wp.com"), "//cdn.moefont.com/wpcom", $content);
        }

        if(ROLE == ROLE_ADMIN) {
            $content .= '<!--Thank you for using MoeCDN! (This message is only visiable for admin)-->';
        }
        return $content;
    }

    public static function options_menu() {
        echo '<div class="sidebarsubmenu" id="moecdn"><a href="./plugin.php?plugin=moecdn">MoeCDN</a></div>';
    }
}