<?php
/*
Plugin Name: sitemap
Version: 1.0
Plugin URL: http://www.qiyuuu.com/for-emlog/emlog-plugin-sitemap
Description: 生成sitemap，供搜索引擎抓取
Author: 奇遇
Author Email: qiyuuu@gmail.com
Author URL: http://www.qiyuuu.com
*/
!defined('EMLOG_ROOT') && exit('access deined!');

function plugin_setting_view() {
?>
	<div class=containertitle>
	<?php if(isset($_GET['setting'])):?><span class="actived">插件设置完成</span><?php endif;?>
	<?php if(isset($_GET['error'])):?><span class="error">插件设置失败</span><?php endif;?>
	</div>
	<div class=line></div>
	<h2>MoeCDN 设置</h2>

	<form method="post" action="plugin.php?plugin=moecdn&action=setting">

		<table class="form-table">
			<tbody>
			<tr><th scope="row">Gravatar</th>
				<td><label for="gravatar">
						<input name="gravatar" type="hidden" value="0" />
						<input name="gravatar" type="checkbox" name="gravatar" value="1" <?php MoeCDN::checked('gravatar') ?>>
						替换 Gravatar 服务器
					</label></td>
			</tr>
			<tr><th scope="row">Google</th>
				<td><label for="googleapis">
						<input name="googleapis" type="hidden" value="0" />
						<input name="googleapis" type="checkbox" name="googleapis" value="1" <?php MoeCDN::checked('googleapis') ?>>
						替换 Google Fonts 和 Google AJAX CDN 服务器
					</label></td>
			</tr>
			<tr><th scope="row">Advanced</th>
				<td><label for="worg">
						<input name="worg" type="hidden" value="0" />
						<input name="worg" type="checkbox" name="worg" value="1" <?php MoeCDN::checked('worg'); ?>>
						替换 Emoji 图片服务器
					</label></td>
			</tr>
			</tbody>
		</table>

		<p class="submit">
			<input type="submit" name="submit" id="submit" class="btn btn-primary" value="保存更改">
		</p>
	</form>
<?php
}
function plugin_setting() {
    !empty($_POST['googleapis']) ? MoeCDN::set('googleapis','1') : MoeCDN::set('googleapis','0');
    !empty($_POST['gravatar'])   ? MoeCDN::set('gravatar','1')   : MoeCDN::set('gravatar','0');
    !empty($_POST['worg'])       ? MoeCDN::set('worg','1')       : MoeCDN::set('worg','0');
    $c = Cache::getInstance();
    $c->updateCache('options');
	return true;
}