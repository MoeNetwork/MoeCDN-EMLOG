<?php
!defined('EMLOG_ROOT') && exit('access deined!');

class MoeCDN {
    protected static $options;

    public function __construct() {
        self::$options = get_option('moecdn_options');
        if (!is_array(self::$options))
            self::reset_options();
        self::hook();
    }

    protected static function hook() {
        add_action('admin_init', array('MoeCDN', 'options_init'));
        add_action('admin_menu', array('MoeCDN', 'options_menu'));
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), array('MoeCDN', 'action_links'));

        add_action('init', array('MoeCDN', 'buffer_start'), 1);
        if (is_admin()) {
            add_action('in_admin_header', array('MoeCDN', 'buffer_end'), 99999);
            add_filter('get_avatar', array('MoeCDN', 'replace'));
            add_action('in_admin_footer', array('MoeCDN', 'buffer_start'), 1);
        } else {
            add_action('wp_head', array('MoeCDN', 'buffer_end'), 99999);
            add_filter('get_avatar', array('MoeCDN', 'replace'));
            add_action('wp_footer', array('MoeCDN', 'buffer_start'), 1);
        }
        add_action('shutdown', array('MoeCDN', 'buffer_end'), 99999);
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

        <div class="wrap">
        <h2>MoeCDN 设置</h2>

        <form method="post" name="moecdn" id="moecdn">

            <table class="form-table">
                <tbody>
                <tr><th scope="row">Gravatar</th>
                    <td><label for="gravatar">
                            <input name="gravatar" type="hidden" value="0" />
                            <input name="gravatar" type="checkbox" id="gravatar" value="1" <?php checked(self::$options['gravatar']); ?>>
                            替换 Gravatar 服务器
                        </label></td>
                </tr>
                <tr><th scope="row">Google</th>
                    <td><label for="googleapis">
                            <input name="googleapis" type="hidden" value="0" />
                            <input name="googleapis" type="checkbox" id="googleapis" value="1" <?php checked(self::$options['googleapis']); ?>>
                            替换 Google Fonts 和 Google AJAX CDN 服务器
                        </label></td>
                </tr>
                <tr><th scope="row">WordPress</th>
                    <td><label for="worg">
                            <input name="worg" type="hidden" value="0" />
                            <input name="worg" type="checkbox" id="worg" value="1" <?php checked(self::$options['worg']); ?>>
                            替换 WordPress Emoji 图片服务器
                        </label></td>
                </tr>
                <tr><th scope="row">WP.COM</th>
                    <td><label for="wpcom">
                            <input name="wpcom" type="hidden" value="0" />
                            <input name="wpcom" type="checkbox" id="wpcom" value="1" <?php checked(self::$options['wpcom']); ?>>
                            替换 Jetpack 等 WordPress.com 静态资源服务器
                        </label></td>
                </tr>
                </tbody>
            </table>

            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button button-primary" value="保存更改">
                &nbsp;<input name="reset" type="submit" class="button button-secondary" value="重置设置" onclick="return confirm('你确定要重置所有的设置吗？');">
            </p>
        </form>

        <?php
    }
}