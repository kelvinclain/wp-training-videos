<?php
/*
Plugin Name: WP training videos
Plugin URI: mr.trung88tn@gmail.com
Description: WP training videos for post types
Version: 1.0.0
Author: Trungnv
Author URI: mr.trung88tn@gmail.com
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
include('wp-training-videos.php');

add_action( 'admin_menu', 'wp_training_menu' );
function wp_training_menu( ) {
    add_options_page( 'WPTV', 'WP training videos', 'manage_options', 'wptv-settings', 'wptv_options' );
}
function add_scripts_method() {
   wp_enqueue_script( 'add_scripts', plugins_url( 'assets/js/add_scripts.js', __FILE__ ), array( 'jquery' ));
   if(function_exists( 'wp_enqueue_media' )){
		wp_enqueue_media();
	}else{
		wp_enqueue_style('thickbox');
		wp_enqueue_script('media-upload');
		wp_enqueue_script('thickbox');
	}
}
add_action('admin_head', 'add_scripts_method');
function wptv_options() {
	if(isset($_POST['submit']) && !empty($_POST['wptv_post_types'])) {
		update_option('wptv_post_types', $_POST['wptv_post_types']);
		update_option('wptv_youtube_api_key', $_POST['wptv_youtube_api_key']);
		update_option('wptv_video_default', $_POST['wptv_video_default']);
		update_option('wptv_poster_default', $_POST['wptv_poster_default']);
	}
    $value = get_option( 'wptv_post_types', array() );
    $args = array(
		'public'   => true,
		'_builtin' => false
	);

	$output = 'names'; // names or objects, note names is the default
	$operator = 'or'; // 'and' or 'or'

	$post_types = get_post_types( $args, $output, $operator );
	$apiKey = get_option( 'wptv_youtube_api_key', '' );
	$wptv_video_default = get_option( 'wptv_video_default', '' );
	$wptv_poster_default = get_option( 'wptv_poster_default', '' );
?>
<div class="wrap">
	<h1>WPTV Settings</h1>
	<form novalidate="novalidate" action="options-general.php?page=wptv-settings" method="post">
		<input type="hidden" value="general" name="option_page">
		<input type="hidden" value="update" name="action">
		<input type="hidden" value="<?php wp_get_referer(); ?>" name="_wp_http_referer">
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row"><label for="wptv_post_types">Youtube API key:</label></th>
					<td>
						<input name="wptv_youtube_api_key" id="wptv_youtube_api_key" value="<?php echo (!empty($apiKey)) ? $apiKey : ''; ?>" />
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="wptv_post_types">Apply to post types:</label></th>
					<td>
						<select name="wptv_post_types[]" id="wptv_post_types" multiple>
							<?php foreach ( $post_types  as $post_type ) { 
							if($post_type != 'attachment' && $post_type != 'post') {
							?>
							<option value="<?php echo $post_type ?>" <?php if(in_array($post_type,$value)){echo 'selected="selected"';} ?>><?php echo $post_type; ?></option>
							<?php }} ?>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="wptv_post_types">Poster default:</label></th>
					<td>
						<input name="wptv_poster_default" id="wptv_poster_default" value="<?php echo (!empty($wptv_poster_default)) ? $wptv_poster_default : ''; ?>" />
						<input type="button" data-field-id="wptv_poster_default" class="button button_add_file" value="Choose File" onclick="add_scripts(this)" />
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="wptv_post_types">Video default:</label></th>
					<td>
						<input name="wptv_video_default" id="wptv_video_default" value="<?php echo (!empty($wptv_video_default)) ? $wptv_video_default : ''; ?>" />
						<input type="button" data-field-id="wptv_video_default" class="button button_add_file" value="Choose File" onclick="add_scripts(this)" />
						<br class="clear"/>
						<br class="clear"/>
						<?php if(!empty($wptv_video_default)): ?>
							<video width="640" height="360" id="player-default" poster="<?php echo $wptv_poster_default; ?>" controls="controls" preload="none">
								<!-- MP4 source must come first for iOS -->
								<source type="video/mp4" src="<?php echo $wptv_video_default; ?>" />
								<!-- Fallback flash player for no-HTML5 browsers with JavaScript turned off -->
								<object width="640" height="360" type="application/x-shockwave-flash" data="<?php echo get_wptv_file_url('/assets/players/flashmediaelement.swf'); ?>">	
									<param name="movie" value="<?php echo get_wptv_file_url('/assets/players/flashmediaelement.swf'); ?>" /> 
									<param name="flashvars" value="controls=true&amp;file=<?php echo $wptv_video_default; ?>" /> 		
									<!-- Image fall back for non-HTML5 browser with JavaScript turned off and no Flash player installed -->
									<img src="<?php echo $wptv_poster_default; ?>" width="640" height="360" alt="" title="No video playback capabilities" />		
								</object>
							</video>
						<?php endif; ?>
					</td>
				</tr>
			</tbody>
		</table>
		<p class="submit"><input type="submit" value="Save Changes" class="button button-primary" id="submit" name="submit"></p>
	</form>
</div>
<?php

	}
// retrieves the attachment ID from the file URL
function wptv_length_formated($attachment_url) {
	global $wpdb;
	$attachment = $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE guid='%s';", $attachment_url ));
	$time = '';
	if(!empty($attachment[0])){
		$video_meta = wp_get_attachment_metadata( $attachment[0] );		
		$length = $video_meta['length'];  
		$hours = floor($length / 3600);
		$minutes = floor(($length / 60) % 60);
		$seconds = $length % 60;		
		if($hours <= 0 && $minutes > 0) {
			$time = $minutes. 'm' . $seconds . 's';
		} elseif($hours <= 0 && $minutes <= 0){
			$time = $seconds . 's';
		} else {
			$time = $hours. 'h' .$minutes. 'm' . $seconds . 's';
		}
	}
	return $time; 
	
}

function get_wptv_file_url($path) {
	$url = plugins_url($path, __FILE__) ;
	return $url;
}