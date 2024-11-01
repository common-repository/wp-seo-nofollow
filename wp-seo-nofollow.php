<?php
/**
 * Plugin Name: WordPress SEO nofollow
 * Description: add rel="nofollow" html tag "a" on external domain link, comments popup link and more link. 给评论链接、阅读全文、站外域链接链接增加rel="nofollow"。
 * Version: 1.1
 * Author: iOpenV
 * Author URI: http://www.iopenv.com/
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

function nofollow_create_menu() {

	//create new option submenu
	add_options_page('WordPress SEO nofollow plugin Settings', 'WordPress SEO nofollow', 'manage_options',__FILE__,'nofollow_settings_page');

	//call register settings function
	add_action( 'admin_init', 'register_nofollowsettings' );
}

add_action('admin_menu', 'nofollow_create_menu');

function register_nofollowsettings() {
	//register our settings
	register_setting( 'nofollow-settings-group', 'wpsn_comments_popup_link' );
	register_setting( 'nofollow-settings-group', 'wpsn_more_link' );
	register_setting( 'nofollow-settings-group', 'wpsn_external' );
	register_setting( 'nofollow-settings-group', 'wpsn_deactivate' );
}

function nofollow_settings_page() {
?>
<div class="wrap">
	<?php //screen_icon(); ?>
	<h2><?php _e('WordPress SEO nofollow');?></h2>
	<form method="post" action="options.php" enctype="multipart/form-data">
		<?php settings_fields( 'nofollow-settings-group' ); ?>
		<?php do_settings_sections( 'nofollow-settings-group' ); ?>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php _e('评论链接'); ?></th>
				<td>
					<label>
						<input name="wpsn_comments_popup_link" type="radio" value="enable"<?php if (get_option('wpsn_comments_popup_link') == 'enable') { ?> checked="checked"<?php } ?> />
				<?php _e('开启'); ?>
					</label>
					<label>
						<input name="wpsn_comments_popup_link" type="radio" value="disable"<?php if (get_option('wpsn_comments_popup_link') == 'disable') { ?> checked="checked"<?php } ?> />
					<?php _e('关闭'); ?>
					</label>
					<p><?php _e('有多少条评论的链接会加上rel="nofollow"，推荐开启。');?></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('阅读全文'); ?></th>
				<td>
					<label>
						<input name="wpsn_more_link" type="radio" value="enable"<?php if (get_option('wpsn_more_link') == 'enable') { ?> checked="checked"<?php } ?> />
				<?php _e('开启'); ?>
					</label>
					<label>
						<input name="wpsn_more_link" type="radio" value="disable"<?php if (get_option('wpsn_more_link') == 'disable') { ?> checked="checked"<?php } ?> />
					<?php _e('关闭'); ?>
					</label>
					<p><?php _e('阅读全文链接加上rel="nofollow"，推荐开启。');?></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('站外域'); ?></th>
				<td>
					<label>
						<input name="wpsn_external" type="radio" value="enable"<?php if (get_option('wpsn_external') == 'enable') { ?> checked="checked"<?php } ?> />
				<?php _e('开启'); ?>
					</label>
					<label>
						<input name="wpsn_external" type="radio" value="disable"<?php if (get_option('wpsn_external') == 'disable') { ?> checked="checked"<?php } ?> />
					<?php _e('关闭'); ?>
					</label>
					<p><?php _e('在文章中站外的域名链接加上rel="external nofollow"，此功能开启导出链接将瞬间减少，建议SEO高手使用。');?></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<?php _e('删除设置'); ?>
				</th>
				<td>
					<label>
						<input type="checkbox" name="wpsn_deactivate" value="yes" <?php if(get_option("wpsn_deactivate")=='yes') echo 'checked="checked"'; ?> />
						<?php _e('卸载此插件时自动删除设置选项。'); ?>
					</label>
				</td>
			</tr>
		</table>
		<?php submit_button(); ?>
	</form>
	<h2><?php _e('友情提示');?></h2>
	<p><?php _e('插件只针对有SEO经验或专业人士使用。');?></p>
	<h2><?php _e('捐赠共勉');?></h2>
	<p><?php _e('支付宝：dexu.xie@gmail.com，多少随意。');?></p>
	<p><img src="<?php echo plugins_url('/images/qr-alipay.png', __FILE__);?>"></p>
</div>
<?php }?>
<?php
// 评论链接
if (get_option('wpsn_comments_popup_link') == 'enable') {
	function add_nofollow_to_comments_popup_link(){
		return ' rel="nofollow" ';
	}
	add_filter('comments_popup_link_attributes', 'add_nofollow_to_comments_popup_link');
}
// 阅读全文
if (get_option('wpsn_more_link') == 'enable') {
	function add_nofollow_to_link($link) {
		return str_replace('<a', '<a rel="nofollow" target="_blank"', $link);
	}
	add_filter('the_content_more_link','add_nofollow_to_link', 0);
}
// 站外域
if (get_option('wpsn_external') == 'enable') {
	add_filter('the_content','the_content_external',999);
	function the_content_external($content){
		preg_match_all('/href="(.*?)"/',$content,$matches);
		if($matches){
			foreach($matches[1] as $val){
				if( strpos($val,$_SERVER['HTTP_HOST'])===false ) $content=str_replace("href=\"$val\"", "href=\"$val\" rel=\"external nofollow\" class=\"external\" ",$content);
			}
		}
		return $content;
	}
}
// 删除设置
if(get_option("wpsn_deactivate")=='yes'){
	function wpsn_deactivate(){
		global $wpdb;
		$remove_options_sql = "DELETE FROM $wpdb->options WHERE $wpdb->options.option_name like 'wpsn_%'";
		$wpdb->query($remove_options_sql);
	}
	register_deactivation_hook(__FILE__,'wpsn_deactivate');
}
?>