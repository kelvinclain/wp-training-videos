<?php 
global $post, $wptv_datas;
$postId = $post->ID;
$post_type = get_post_type($postId);
$relatedVideos = $wptv_datas['wptv_datas']['related_videos'];
$args = array(
	'post_type' => $post_type,
	'post_status' => 'publish',
	'post_parent' => 0,
	'posts_per_page' => -1,
	'post__in' => $relatedVideos,
	'order' => 'DESC',
	'orderby' => 'menu_order'
);
$related_query = new WP_Query( $args ); 
$apiKey = get_option( 'wptv_youtube_api_key', '' );
?>
<div class="wptv-sidebar">
	<input type="hidden" id="ytb-apikey" value="<?php echo (!empty($apiKey)) ? $apiKey : 'AIzaSyCbr2ND-vsk7279RpDD2GEWBiNOBzLmxws'; ?>" />
	<div class="related-videos">
		<h3><?php _e('Related Training Resources', 'wptv'); ?></h3>
	<?php if ( $related_query->have_posts() ) : ?>
		<ul class="related-videos-list">
		<?php while ( $related_query->have_posts() ) : $related_query->the_post(); ?>
			<li class="video-item video-<?php the_ID(); ?>">
				<a href="<?php echo get_permalink() ?>" class="play-video">
				<?php
					$rId = get_the_ID();
					$wptv_related_datas = get_post_meta( $rId, 'wptv_datas', true );
					if ( has_post_thumbnail() ) {
						the_post_thumbnail( array(767,431) );
					}				
				?>
					<span class="video-overlay"><img src="<?php echo plugins_url( '../assets/images/play-btn.png',__FILE__); ?>" /></span>
				</a>
				<p class="video-name"><a href="<?php echo get_permalink() ?>"><?php the_title(); ?></a></p>
				<p class="video-info">
					<?php 
						$videoUrl = '';
						if(isset($wptv_related_datas['wptv_datas']) && !empty($wptv_related_datas['wptv_datas']) && $wptv_related_datas['wptv_datas']['video_mode'] == 'upload' && !empty($wptv_related_datas['wptv_datas']['video_url'])) {
							$videoUrl = $wptv_related_datas['wptv_datas']['video_url'];
							$length = wptv_length_formated($videoUrl);
							echo '<span class="video-duration"><i class="fa fa-clock-o" aria-hidden="true"></i> ' . $length .'</span> | <a href="'. get_permalink() .'"><span class="view-video"><i class="fa fa-eye" aria-hidden="true"></i> Views</span></a>';
						} elseif(isset($wptv_related_datas['wptv_datas']) && !empty($wptv_related_datas['wptv_datas']) && $wptv_related_datas['wptv_datas']['video_mode'] == 'youtube' && !empty($wptv_related_datas['wptv_datas']['youtube_url'])) {
							$video_id = $wptv_related_datas['wptv_datas']['youtube_url'];
							echo '<span class="youtube-duration video-duration" data-ytb-id="' . $video_id .'"></span> | <a href="'. get_permalink() .'"><span class="view-video"><i class="fa fa-eye" aria-hidden="true"></i> Views</span></a>';
						}
					?>
				</p>
				
			</li>
		<?php endwhile; ?>
		</ul>
	<?php endif;
	wp_reset_postdata(); ?>
	</div>
	<div class="wptv-presentation" id="wptv-presentation">
		<ul class="wptv-sidebar-tab">
			<li><a href="#wptv-content"><?php _e('Content', 'wptv'); ?></a></li>
			<li><a href="#wptv-notebook"><?php _e('notebook', 'wptv'); ?></a></li>
		</ul>
		<div id="wptv-content">
		<?php
			$categories = get_terms( $post_type , array(
							'post_type' => $post_type ,
							'fields' => 'all',
							'orderby' => 'term_id',
							'parent' => 0,
							'hide_empty' => 1,
							'order'	=> 'ASC'
						)); 
			if(!empty($categories)):
		?>
			<div id="accordion">			
			<?php foreach($categories as $category) { ?>
				<h3 class="content-title"><?php echo $category->name; ?></h3>
				<div class="wptv-category-content">
				<?php	$cargs = array(
							'post_type' => $post_type,
							'post_status' => 'publish',
							'post_parent' => $postId,
							'posts_per_page' => -1,
							'order' => 'DESC',
							'orderby' => 'menu_order',
							'tax_query' => array(
										array(
											'taxonomy' => $post_type,
											'field' => 'slug',
											'terms' => $category->slug
										)
									)
						);
					$contents = new WP_Query( $cargs ); ?>
					<ul class="content-lists">
					<?php if ( $contents->have_posts() ) :
						while ( $contents->have_posts() ) : $contents->the_post(); 
							$cId = get_the_ID();
							$wptv_content_datas = get_post_meta( $cId, 'wptv_datas', true );
							$deny_guest = $wptv_content_datas['wptv_datas']['deny_guest'];
						?>
							<li class="content-item">
								<a href="#" data-content-id="<?php the_ID(); ?>">
									<p><?php the_title(); ?></p>
									<p>
									<?php 
									if(isset($wptv_content_datas['wptv_datas']) && !empty($wptv_content_datas['wptv_datas']) && $wptv_content_datas['wptv_datas']['video_mode'] == 'upload' && !empty($wptv_content_datas['wptv_datas']['video_url'])) {
										$cUrl = $wptv_content_datas['wptv_datas']['video_url'];
										$length = wptv_length_formated($cUrl);
										echo '<span class="video-duration">' . $length .'</span>';
									} elseif(isset($wptv_content_datas['wptv_datas']) && !empty($wptv_content_datas['wptv_datas']) && $wptv_content_datas['wptv_datas']['video_mode'] == 'youtube' && !empty($wptv_content_datas['wptv_datas']['youtube_url'])) {
										$ytb_id = $wptv_content_datas['wptv_datas']['youtube_url'];
										echo '<span class="youtube-duration video-duration" data-ytb-id="' . $ytb_id .'"></span>';
									}
									?>
									</p>
									<?php echo($deny_guest == 1) ? '<i class="fa fa-lock" aria-hidden="true"></i>' : '<i class="fa fa-eye" aria-hidden="true"></i>'; ?>
								</a>
							</li>
						<?php 
						endwhile;
					endif; ?>
					</ul>
					<?php wp_reset_postdata();?>
				</div>
			<?php } ?>
			</div>
		<?php else: ?>
			<p><?php _e('No content.'); ?></p>
		<?php endif; ?>
		</div>
		<div id="wptv-notebook" class="hidden">
			<?php echo get_the_excerpt( $postId ) ?>
		</div>
	</div>
</div>