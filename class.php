<?php
!defined('EMLOG_ROOT') && exit('access deined!');

class MoeCDN {
    public static $options = array(
        'gravatar'    => '0',
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

    protected function hook() {
        addAction('admin_init', array('MoeCDN', 'options_init'));
        addAction('adm_siderbar_ext', array('MoeCDN', 'options_menu'));
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), array('MoeCDN', 'action_links'));

        addAction('init', array('MoeCDN', 'buffer_start'), 1);
        if (is_admin()) {
            addAction('in_admin_header', array('MoeCDN', 'buffer_end'), 99999);
            add_filter('get_avatar', array('MoeCDN', 'replace'));
            addAction('in_admin_footer', array('MoeCDN', 'buffer_start'), 1);
        } else {
            addAction('index_head', array('MoeCDN', 'buffer_end'), 99999);
            add_filter('get_avatar', array('MoeCDN', 'replace'));
            addAction('wp_footer', array('MoeCDN', 'buffer_start'), 1);
        }
        addAction('shutdown', array('MoeCDN', 'buffer_end'), 99999);
    }

    // 缓冲替换输出
    public static function buffer_start() {
        ob_start(array('MoeCDN', 'replace'));
    }
    public static function buffer_end() {
        ob_end_flush();
    }
    // 替换内容
    public static function replace($content) {
        if (self::$options['gravatar']) {
            $content = str_replace(array("//gravatar.com", "//www.gravatar.com", "//0.gravatar.com", "//1.gravatar.com", "//2.gravatar.com"), "//gravatar.moefont.com", $content);
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

        return $content;
    }

    //

    // 设置页面
    public static function action_links($links) {
        $links[] = '<a href="'. esc_url(get_admin_url(null, 'options-general.php?page=moecdn')) .'">' . __('Settings') . '</a>';
        $links[] = '<a href="http://cdn.moefont.com" target="_blank">支持</a>';
        return $links;
    }
    protected static function reset_options() {
        self::$options = array(
            'gravatar' => true,
            'googleapis' => true,
            'worg' => true,
            'wpcom' => true
        );
        update_option('moecdn_options', $options);
    }
    protected static function save_options() {
        self::$options = array(
            'gravatar' => $_POST['gravatar'],
            'googleapis' => $_POST['googleapis'],
            'worg' => $_POST['worg'],
            'wpcom' => $_POST['wpcom']);
        update_option('moecdn_options', $options);
    }
    public static function options_init() {
        if (isset($_POST['submit'])) {
            self::save_options();
            add_settings_error('moecdn_options', 'moecdn_options-updated', __('Settings saved.'), 'updated');
        } elseif (isset($_POST['reset'])) {
            self::reset_options();
            add_settings_error('moecdn_options', 'moecdn_options-reseted', __('Settings reseted.'), 'updated');
        }
    }
    public static function options_menu() {
        add_options_page('MoeCDN 设置', 'MoeCDN', 'manage_options', 'moecdn', array('MoeCDN', 'options_display'));
    }
    public static function options_display() {
        ?>



        <?php
    }
}