<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
class WPTV_Course 
{
	/**
     * Constructor.
     */
    public function __construct() {
		
        if ( is_admin() ) {
			// Register style sheet.
			add_action( 'admin_head', array($this, 'register_wptv_css' ));
            add_action( 'load-post.php',     array( $this, 'init_wptv_metabox' ) );
            add_action( 'load-post-new.php', array( $this, 'init_wptv_metabox' ) );
			add_action('admin_head', array( $this, 'register_wptv_scripts' ));
        } else {
			add_action('wp_head', array( $this, 'register_wptv_frontend_script' ));
			add_action('wp_head', array( $this, 'register_wptv_frontend_css' ));
		}
			add_shortcode( 'WPTV_Course', array($this,'WPTV_Course_shortcode' )); 
			add_action( 'wp_enqueue_scripts', array( $this, 'init_wptv_ajax' ) );
			add_action( 'wp_ajax_wptv_load_video', array( $this, 'wptv_load_video' ) ); 
			add_action( 'wp_ajax_nopriv_wptv_load_video', array( $this, 'wptv_load_video' ) );
    }
	
	/**
	 * Register and enqueue style sheet.
	 */
	public function register_wptv_css() {
		//echo 1;exit;
		wp_register_style( 'wptv-css', plugins_url( 'assets/css/wptv-styles.css', __FILE__) );
		wp_enqueue_style( 'wptv-css' );
	}
	
	public function register_wptv_frontend_css() {
		//echo 1;exit;
		wp_register_style( 'wptv-mediaelementplayer', plugins_url( 'assets/css/mediaelementplayer.css', __FILE__) );
		wp_enqueue_style( 'wptv-mediaelementplayer' );
		wp_register_style( 'wptv-frontend-css', plugins_url( 'assets/css/frontend-styles.css', __FILE__) );
		wp_enqueue_style( 'wptv-frontend-css' );
	}
	
	public function register_wptv_scripts() {
		wp_enqueue_script( 'wptv_script', plugins_url( 'assets/js/media.js' , __FILE__ ), array( 'jquery-ui-tabs' ) );

	}
	
	public function register_wptv_frontend_script() {
		wp_enqueue_script( 'wptv_mediaelement_and_player', plugins_url( 'assets/js/frontend/mediaelement-and-player.js' , __FILE__ ));
	}
	
	/**
     * Meta box initialization.
     */
    public function init_wptv_metabox() {
        add_action( 'add_meta_boxes', array( $this, 'add_wptv_metabox'  )        );
        add_action( 'save_post',      array( $this, 'save_wptv_metabox' ), 10, 2 );
    }
 
    /**
     * Adds the meta box.
     */
    public function add_wptv_metabox() {
		$post_types = get_option( 'wptv_post_types', array() );

		if(empty($post_types)) {
			$post_types = array('page');
		}
        add_meta_box(
            'wptv-meta-box',
            __( 'Video Course', 'wptv' ),
            array( $this, 'render_wptv_metabox' ),
            $post_types,
            'advanced',
            'default'
        );
 
    }
	
	public function render_wptv_metabox( $post ) {
		// Add nonce for security and authentication.
		global $post;
		$wptv_datas = get_post_meta( $post->ID, 'wptv_datas', true );
		$videos = array();
		if(isset($wptv_datas['wptv_datas']['related_videos']) && !empty($wptv_datas['wptv_datas']['related_videos'])) {
			$videos = $wptv_datas['wptv_datas']['related_videos'];
		}
		$id = $post->ID;
        wp_nonce_field( 'wptv_nonce_action', 'wptv_nonce' ); ?>		
			<div id="wptv_form">
				<div id="wptv_wrap">
					<p>Copy this shortcode to content <span style="color:#ff9900;">[WPTV_Course id=<?php echo $post->ID; ?>]</span></p>
					<div class="wptv-info">
						<div id="wptv_tabs">
							<ul class="category-tabs">
								<li><a href="#wptv-subtitle">Traning Video</a></li>
								<li><a href="#wptv-download">Download</a></li>
								<li><a href="#wptv-faq">FAQ</a></li>
								<li><a href="#wptv-related">Related Training Resources</a></li>
							</ul>
							<div id="wptv-subtitle">
								<div class="form_field">									
									<input type="checkbox" id="deny_guest" name="wptv_datas[deny_guest]" class="deny_guest" value="1" <?php echo (isset($wptv_datas['wptv_datas']['deny_guest']) && $wptv_datas['wptv_datas']['deny_guest'] > 0) ? 'checked="checked"' : ''; ?> />
									<label for="deny_guest"><?php _e('Lock guest','wptv'); ?></label>
								</div>
								<div class="form_field">
									<h3><?php _e('Video mode','wptv'); ?></h3>
									<select name="wptv_datas[video_mode]" id="video_mode" class="">
										<option value=""><?php _e('Video type'); ?></option>
										<option value="upload" <?php echo (isset($wptv_datas['wptv_datas']['video_mode']) && $wptv_datas['wptv_datas']['video_mode'] == 'upload') ? 'selected="selected"' : ''; ?>><?php _e('Upload video'); ?></option>
										<option value="youtube" <?php echo (isset($wptv_datas['wptv_datas']['video_mode']) && $wptv_datas['wptv_datas']['video_mode'] == 'youtube') ?  'selected="selected"' : ''; ?>><?php _e('Youtube link'); ?></option>
									</select>
								</div>
								<div class="form_field">
									<input class="video_url" id="video_url" value="<?php (isset($wptv_datas['wptv_datas']['video_url'])) ? esc_html_e( $wptv_datas['wptv_datas']['video_url'] ) : ''; ?>" placeholder="<?php _e('Video','wptv') ?>" title="<?php _e('Video','wptv') ?>" type="text" name="wptv_datas[video_url]" style="display:<?php echo (isset($wptv_datas['wptv_datas']['video_mode']) && $wptv_datas['wptv_datas']['video_mode'] == 'upload') ? 'inline-block' : 'none';?>" />
									<input type="button" data-field-id="video_url" class="button button_add_file button_add_video" value="Choose video" onclick="add_file(this)" style="display:<?php echo (isset($wptv_datas['wptv_datas']['video_mode']) && $wptv_datas['wptv_datas']['video_mode'] == 'upload') ? 'inline-block' : 'none';?>" />
									<input class="youtube_url" id="youtube_url" value="<?php (isset($wptv_datas['wptv_datas']['youtube_url'])) ? esc_html_e( $wptv_datas['wptv_datas']['youtube_url'] ) : ''; ?>" placeholder="<?php _e('Youtube link','wptv') ?>" title="<?php _e('Youtube link','wptv') ?>" type="text" name="wptv_datas[youtube_url]" style="display:<?php echo (isset($wptv_datas['wptv_datas']['video_mode']) && $wptv_datas['wptv_datas']['video_mode'] == 'youtube') ? 'inline-block' : 'none';?>" />
								</div>
								<div class="form_field">
									<input class="poster_url" id="poster_url" value="<?php (isset($wptv_datas['wptv_datas']['poster_url'])) ? esc_html_e( $wptv_datas['wptv_datas']['poster_url'] ) : ''; ?>" placeholder="<?php _e('Poster','wptv') ?>" title="<?php _e('Poster','wptv') ?>" type="text" name="wptv_datas[poster_url]" />
									<input type="button" data-field-id="poster_url" class="button button_add_file" value="Choose image" onclick="add_file(this)" />
								</div>
								<br class="clear" />
								<h3><?php _e('Subtitles','wptv'); ?></h3>
								<div class="form_field">
									<ul>
										<li><label><strong><?php _e('Time'); ?></strong></label></li>
										<li><label><strong><?php _e('Text'); ?></strong></label></li>
										<li><label><strong><?php _e('Action'); ?></strong></label></li>
									</ul>
								</div>
								<div id="subtitle-lists">
								<?php if(isset($wptv_datas['wptv_datas']['subtitle']) && !empty($wptv_datas['wptv_datas']['subtitle'])): 
									foreach($wptv_datas['wptv_datas']['subtitle'] as $key => $value){
								?>
									<div class="form_field">
										<ul>
											<li><input type="text" name="wptv_datas[subtitle][time][]" class="input-text subtitle-time" value="<?php echo $value['time'] ?>"  /></li>
											<li><input type="text" name="wptv_datas[subtitle][text][]" class="input-text subtitle-text" value="<?php echo $value['text'] ?>" /></li>
											<li><input class="button button_remove" type="button" value="Remove row" onclick="remove_row(this)" /></li>
										</ul>
									</div>
								<?php }
									endif; ?>
								</div>
								<div class="form_field">
									<input class="button" type="button" value="Add subtitle row" onclick="add_subtitle_row();" />
								</div>
							</div>
							<div class="hidden" id="wptv-download">
								<ul class="wptv-download-list">
									<li>
										<h3><?php _e('Powerpoint Download','wptv'); ?></h3>
										<div class="download-item">										
											<div class="form_field">
												<input class="download_url" id="download_powerpoint_url" value="<?php (isset($wptv_datas['wptv_datas']['download_powerpoint_url'])) ? esc_html_e( $wptv_datas['wptv_datas']['download_powerpoint_url'] ) : ''; ?>" placeholder="<?php _e('Powerpoint url','wptv') ?>" title="<?php _e('Powerpoint url','wptv') ?>" type="text" name="wptv_datas[download_powerpoint_url]" />
												<input type="button" data-field-id="download_powerpoint_url" class="button button_add_file" value="Choose File" onclick="add_file(this)" />
											</div>
										</div>
									</li>
									<li>
										<h3><?php _e('Script Download','wptv'); ?></h3>
										<div class="download-item">										
											<div class="form_field">
												<input class="download_url" id="download_script_url" value="<?php (isset($wptv_datas['wptv_datas']['download_script_url'])) ? esc_html_e( $wptv_datas['wptv_datas']['download_script_url'] ) : ''; ?>" placeholder="<?php _e('Script Download','wptv') ?>" title="<?php _e('Script Download','wptv') ?>" type="text" name="wptv_datas[download_script_url]" />
												<input type="button" data-field-id="download_script_url" class="button button_add_file" value="Choose File" onclick="add_file(this)" />
											</div>
										</div>
									</li>
									<li>
										<h3><?php _e('Study Guide Download','wptv'); ?></h3>
										<div class="download-item">										
											<div class="form_field">
												<input class="download_url" id="download_study_guide_url" value="<?php (isset($wptv_datas['wptv_datas']['download_study_guide_url'])) ? esc_html_e( $wptv_datas['wptv_datas']['download_study_guide_url'] ) : ''; ?>" placeholder="<?php _e('Study Guide Download','wptv') ?>" title="<?php _e('Study Guide Download','wptv') ?>" type="text" name="wptv_datas[download_study_guide_url]" />
												<input type="button" data-field-id="download_study_guide_url" class="button button_add_file" value="Choose File" onclick="add_file(this)" />
											</div>
										</div>
									</li>
									<li>
										<h3><?php _e('Flyer Download','wptv'); ?></h3>
										<div class="download-item">										
											<div class="form_field">
												<input class="download_url" id="download_flyer_url" value="<?php (isset($wptv_datas['wptv_datas']['download_flyer_url'])) ? esc_html_e( $wptv_datas['wptv_datas']['download_flyer_url'] ) : ''; ?>" placeholder="<?php _e('Flyer Download','wptv') ?>" title="<?php _e('Flyer Download','wptv') ?>" type="text" name="wptv_datas[download_flyer_url]" />
												<input type="button" data-field-id="download_flyer_url" class="button button_add_file" value="Choose File" onclick="add_file(this)" />
											</div>											
										</div>
									</li>
								</ul>
							</div>
							<div class="hidden" id="wptv-faq">
								<?php
									$editor_id = 'faq-content';
									$content = (isset($wptv_datas['wptv_datas']['faq'])) ? $wptv_datas['wptv_datas']['faq'] : '';
									wp_editor( $content, $editor_id, array("teeny" => true, "textarea_name" => "wptv_datas[faq]", "textarea_rows" => 25, 'tinymce' => array("height" => 200)) );
								?>
							</div>
							<div id="wptv-related">
								<div class="form_field">
									<?php echo $this->getRelatedVideos($id, $videos); ?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
	<?php }
	
	//Get all video
	
	public function getRelatedVideos($id, $videos) {
		$post_types = get_post_type($id);

		if(empty($post_types)) {
			$post_types = 'page';
		}
		
		$html = '';
		
		$args = array(
			'post_type' => $post_types,
			'post_status' => 'publish',
			'post_parent' => 0,
			'posts_per_page' => -1,
			'post__not_in' => array($id),
			'order' => 'DESC',
			'orderby' => 'menu_order'
		);
		$related_query = new WP_Query( $args ); 
		$html .= '<select name="wptv_datas[related_videos][]" id="related_videos" multiple>
			<option value="">Select videos</option>';
		// The Loop		
		if ( $related_query->have_posts() ) :
			while ( $related_query->have_posts() ) : $related_query->the_post(); 
				$videoId = get_the_ID();
				$related = (isset($videos) && !empty($videos) && in_array($videoId, $videos)) ? 'selected="selected"' : '';
				$html .= '<option value="'. $videoId .'" '. $related .'>'. get_the_title() .'</option>';
			endwhile;		
		endif;
		$html .= '</select>';
		wp_reset_postdata();
		return $html;
	}
	
	public function save_wptv_metabox( $post_id, $post ) {
        // Add nonce for security and authentication.
        $nonce_name   = isset( $_POST['wptv_nonce'] ) ? $_POST['wptv_nonce'] : '';
        $nonce_action = 'wptv_nonce_action';		

        // Check if nonce is set.
        if ( ! isset( $nonce_name ) ) {
            return;
        }

        // Check if nonce is valid.
        if ( ! wp_verify_nonce( $nonce_name, $nonce_action ) ) {
            return;
        }
 
        // Check if user has permissions to save data.
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
 
        // Check if not an autosave.
        if ( wp_is_post_autosave( $post_id ) ) {
            return;
        }
 
        // Check if not a revision.
        if ( wp_is_post_revision( $post_id ) ) {
            return;
        }
		
		if (isset($_POST['wptv_datas']) && isset($nonce_name))  
		{
			// Build array for saving post meta

			$wptv_datas = array();
			if(!empty($_POST['wptv_datas']['video_mode']) && (!empty($_POST['wptv_datas']['video_url']) || !empty($_POST['wptv_datas']['youtube_url'])) && isset($_POST['wptv_datas']['subtitle'])){
				$wptv_datas['wptv_datas']['video_mode'] = $_POST['wptv_datas']['video_mode'];
				if(isset($_POST['wptv_datas']['deny_guest'])) {
					$wptv_datas['wptv_datas']['deny_guest'] = 1;
				} else {
					$wptv_datas['wptv_datas']['deny_guest'] = 0;
				}				
				$wptv_datas['wptv_datas']['related_videos'] = $_POST['wptv_datas']['related_videos'];
				$wptv_datas['wptv_datas']['video_url'] = $_POST['wptv_datas']['video_url'];
				$wptv_datas['wptv_datas']['youtube_url'] = $_POST['wptv_datas']['youtube_url'];
				$wptv_datas['wptv_datas']['poster_url'] = $_POST['wptv_datas']['poster_url'];
				$wptv_datas['wptv_datas']['download_powerpoint_url'] = $_POST['wptv_datas']['download_powerpoint_url'];
				$wptv_datas['wptv_datas']['download_script_url'] = $_POST['wptv_datas']['download_script_url'];
				$wptv_datas['wptv_datas']['download_study_guide_url'] = $_POST['wptv_datas']['download_study_guide_url'];
				$wptv_datas['wptv_datas']['download_flyer_url'] = $_POST['wptv_datas']['download_flyer_url'];
				$wptv_datas['wptv_datas']['faq'] = $_POST['wptv_datas']['faq'];
				for($i=0;$i<count($_POST['wptv_datas']['subtitle']['time']);$i++){
					if(!empty($_POST['wptv_datas']['subtitle']['time'][$i]) && !empty($_POST['wptv_datas']['subtitle']['text'][$i])) {
						$wptv_datas['wptv_datas']['subtitle'][$i] = array('time' => $_POST['wptv_datas']['subtitle']['time'][$i],
															'text' => $_POST['wptv_datas']['subtitle']['text'][$i]
															);
					} else {
						$wptv_datas['wptv_datas']['subtitle'][$i] = array('time' => 'none',
															'text' => '<br/>'
															);
					}
				}
			}
			/* echo '<pre>';
			print_r($wptv_datas);
			echo '</pre>';
			exit; */
			if ( $wptv_datas ) 
				update_post_meta( $post_id, 'wptv_datas', $wptv_datas );
			else 
				delete_post_meta( $post_id, 'wptv_datas' );
		} 
		// Nothing received, all fields are empty, delete option
		else 
		{
			delete_post_meta( $post_id, 'wptv_datas' );
		}
		
	}
	
	// Add Shortcode
	function WPTV_Course_shortcode( $atts ) {
		global $post, $wptv_datas;
		$post_id = $atts['id'];
		$post_types = get_option( 'wptv_post_types', array() );
		$post_type = get_post_type($post_id);
		$content = '';
		if(in_array($post_type,$post_types)){			
			$args = array(
				'post_parent' => $post_id,
				'post_type'   => $post_type, 
				'numberposts' => -1,
				'post_status' => 'public' 
			); 
			$childrens = get_children( $args);
			$wptv_datas = get_post_meta( $post_id, 'wptv_datas', true );
			$image_url = plugins_url( '/assets/images/ajax-loading.gif',__FILE__);
			ob_start(); ?>
			<div id="overlay"></div>
			<div id="wptv-course"><div id="loading" style="display:none;"><img src="<?php echo $image_url; ?>" /></div><div class="wptv-course">
			<?php if(isset($wptv_datas['wptv_datas']) && (!empty($wptv_datas['wptv_datas']['video_url']) || !empty($wptv_datas['wptv_datas']['youtube_url']))) {
				if ( $training_template = locate_template( 'training-video.php' ) ) {
				   // locate_template() returns path to file
				   // if either the child theme or the parent theme have overridden the template
				   load_template( $training_template );
				} else {
				   // If neither the child nor parent theme have overridden the template,
				   // we load the template from the 'templates' sub-directory of the directory this file is in
				   load_template( dirname( __FILE__ ) . '/templates/training-video.php' );
				}
			} else {
				if ( $empty_template = locate_template( 'empty-video.php' ) ) {
				   // locate_template() returns path to file
				   // if either the child theme or the parent theme have overridden the template
				   load_template( $empty_template );
				} else {
				   // If neither the child nor parent theme have overridden the template,
				   // we load the template from the 'templates' sub-directory of the directory this file is in
				   load_template( dirname( __FILE__ ) . '/templates/empty-video.php' );
				}
			} ?>
			</div>
			<?php if(isset($wptv_datas['wptv_datas']) && (!empty($wptv_datas['wptv_datas']['video_url']) || !empty($wptv_datas['wptv_datas']['youtube_url'])) && (!empty($wptv_datas['wptv_datas']['related_videos']) || !empty($childrens))) {	?>
			<div id="wptv-sidebar">
				<?php if ( $sidebar_template = locate_template( 'sidebar-video.php' ) ) {
				   // locate_template() returns path to file
				   // if either the child theme or the parent theme have overridden the template
				   load_template( $sidebar_template );
				} else {
				   // If neither the child nor parent theme have overridden the template,
				   // we load the template from the 'templates' sub-directory of the directory this file is in
				   load_template( dirname( __FILE__ ) . '/templates/sidebar-video.php' );
				} ?>
			</div>
			<?php } ?>
			</div>
			<?php $content .= ob_get_clean();
		}
		return $content;
	}
	
	public function init_wptv_ajax()
    {
        wp_enqueue_script( 
            'ajax_script', 
            plugins_url( 'assets/js/frontend/video-course.js' , __FILE__ ), 
            array('jquery-ui-core', 'jquery-ui-accordion','jquery-ui-tabs' ),
            TRUE 
        );
        wp_localize_script( 
            'ajax_script', 
            'wptv_load_video_ajax', 
            array(
                'url'   => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce( "wptv_load_video_nonce" ),
            )
        );
    }

    public function wptv_load_video()
    {
        check_ajax_referer( 'wptv_load_video_nonce', 'nonce' );
		$content = array();
		
        if( true ) {
			$videoId = $_POST['video'];
			if(isset($videoId)) {
				$wptv_datas_ajax = get_post_meta( $videoId, 'wptv_datas', true );
				$wptv_video_default = get_option( 'wptv_video_default', '' );
				$wptv_poster_default = get_option( 'wptv_poster_default', '' );
				$videoUrl = (!empty($wptv_datas_ajax['wptv_datas']['video_url'])) ? $wptv_datas_ajax['wptv_datas']['video_url'] : $wptv_video_default;
				$subtitles = $wptv_datas_ajax['wptv_datas']['subtitle'];
				$deny_guest = $wptv_datas_ajax['wptv_datas']['deny_guest'];
				$poster = (!empty($wptv_datas_ajax['wptv_datas']['poster_url'])) ? $wptv_datas_ajax['wptv_datas']['poster_url'] : $wptv_poster_default;
				$subtitlesData = '';
				if(!empty($subtitles)):
					$subtitlesData .= '<p>';
					$i = 0;
					foreach($subtitles as $subtitle):
						if(!empty($subtitle['time']) || $subtitle['time'] == 'none'):
							$subtitlesData .= '<span class="transcript subtitle ga-'. $i .'" data-class="ga-'. $i .'" data-duration="'. $subtitle['time'] .'">'. $subtitle['text'] .'</span>';
						else:
							$subtitlesData .= $subtitle['text'];
						endif;
					$i++;										
					endforeach;
					$subtitlesData .= '</p>';
				endif;
				//Download data
				$downloadDatas = '';
				if(!empty($wptv_datas_ajax['wptv_datas']['download_powerpoint_url'])):
					$downloadDatas .= '<li class="download-item"><a href="'. $wptv_datas_ajax['wptv_datas']['download_powerpoint_url'] .'" target="_blank">Powerpoint Download</a></li>';
				endif;
				if(!empty($wptv_datas_ajax['wptv_datas']['download_script_url'])):
					$downloadDatas .= '<li class="download-item"><a href="'. $wptv_datas_ajax['wptv_datas']['download_script_url'] .'" target="_blank">Script Download</a></li>';
				endif;
				if(!empty($wptv_datas_ajax['wptv_datas']['download_study_guide_url'])):
					$downloadDatas .= '<li class="download-item"><a href="'. $wptv_datas_ajax['wptv_datas']['download_study_guide_url'] .'" target="_blank">Study Guide Download</a></li>';
				endif;
				if(!empty($wptv_datas_ajax['wptv_datas']['download_flyer_url'])):
					$downloadDatas .= '<li class="download-item"><a href="'. $wptv_datas_ajax['wptv_datas']['download_flyer_url'] .'" target="_blank">Flyer Download</a></li>';
				endif;
				//Faq data
				$faqData = '';
				if(!empty($wptv_datas_ajax['wptv_datas']['faq'])): 
					$faqData .= $wptv_datas_ajax['wptv_datas']['faq'];
				endif;
				ob_start(); ?>
				<video width="768" height="432" id="player<?php echo $videoId; ?>" poster="<?php echo $poster; ?>" controls="controls" preload="none">
				<?php if($wptv_datas_ajax['wptv_datas']['video_mode'] == 'upload' && $deny_guest == 0): ?>
					<!-- MP4 source must come first for iOS -->
					<source type="video/mp4" src="<?php echo $videoUrl; ?>" />
					<!-- Fallback flash player for no-HTML5 browsers with JavaScript turned off -->
					<object width="768" height="432" type="application/x-shockwave-flash" data="<?php echo get_wptv_file_url('/assets/players/flashmediaelement.swf'); ?>">	
						<param name="movie" value="<?php echo get_wptv_file_url('/assets/players/flashmediaelement.swf'); ?>" /> 
						<param name="flashvars" value="controls=true&amp;file=<?php echo $videoUrl; ?>" /> 		
						<!-- Image fall back for non-HTML5 browser with JavaScript turned off and no Flash player installed -->
						<img src="<?php echo $poster; ?>" width="768" height="432" alt="" title="No video playback capabilities" />		
					</object>
				<?php elseif($wptv_datas_ajax['wptv_datas']['video_mode'] == 'youtube' && $deny_guest == 0): ?>
					<!-- Pseudo HTML5 -->
					<source type="video/youtube" src="https://www.youtube.com/watch?v=<?php echo $wptv_datas_ajax['wptv_datas']['youtube_url']; ?>" />
				<?php else: ?>
					<!-- MP4 source must come first for iOS -->
					<source type="video/mp4" src="<?php echo $wptv_video_default; ?>" />
					<!-- Fallback flash player for no-HTML5 browsers with JavaScript turned off -->
					<object width="768" height="432" type="application/x-shockwave-flash" data="<?php echo get_wptv_file_url('/assets/players/flashmediaelement.swf'); ?>">	
						<param name="movie" value="<?php echo get_wptv_file_url('/assets/players/flashmediaelement.swf'); ?>" /> 
						<param name="flashvars" value="controls=true&amp;file=<?php echo $wptv_video_default; ?>" /> 		
						<!-- Image fall back for non-HTML5 browser with JavaScript turned off and no Flash player installed -->	
						<img src="<?php echo $poster; ?>" width="768" height="432" alt="" title="No video playback capabilities" />					
					</object>
				<?php endif; ?>
				</video>			
				<?php 
				$content['course'] = ob_get_clean();
				$content['video_id'] = '#player'.$videoId;
				$content['subtitles'] = $subtitlesData;
				$content['download'] = $downloadDatas;
				$content['faq'] = $faqData;
				$content['success'] = 1;
				
				wp_send_json_success( $content );
			}
		}
        else {
            wp_send_json_error( array( 'error' => 'Could not load video.' ) );
		}
		//exit;
    }

}
new WPTV_Course();