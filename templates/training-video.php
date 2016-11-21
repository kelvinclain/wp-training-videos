<?php
global $post,$wptv_datas;
/* echo '<pre>';
print_r($wptv_datas);
echo '</pre>'; */
$wptv_video_default = get_option( 'wptv_video_default', '' );
$wptv_poster_default = get_option( 'wptv_poster_default', '' );
$videoUrl = (!empty($wptv_datas['wptv_datas']['video_url'])) ? $wptv_datas['wptv_datas']['video_url'] : $wptv_video_default;
$subtitles = $wptv_datas['wptv_datas']['subtitle'];
$deny_guest = $wptv_datas['wptv_datas']['deny_guest'];
$poster = (!empty($wptv_datas['wptv_datas']['poster_url'])) ? $wptv_datas['wptv_datas']['poster_url'] : $wptv_poster_default;
?>
<div id="video-course-wrapper">
	<div class="video-container">
		<video width="768" height="432" id="player<?php echo $post->ID; ?>" poster="<?php echo $poster; ?>" controls="controls" preload="none">
		<?php if($wptv_datas['wptv_datas']['video_mode'] == 'upload' && $deny_guest == 0): ?>
			<!-- MP4 source must come first for iOS -->
			<source type="video/mp4" src="<?php echo $videoUrl; ?>" />
			<!-- Fallback flash player for no-HTML5 browsers with JavaScript turned off -->
			<object width="768" height="432" type="application/x-shockwave-flash" data="<?php echo get_wptv_file_url('/assets/players/flashmediaelement.swf'); ?>">	
				<param name="movie" value="<?php echo get_wptv_file_url('/assets/players/flashmediaelement.swf'); ?>" /> 
				<param name="flashvars" value="controls=true&amp;file=<?php echo $videoUrl; ?>" /> 		
				<!-- Image fall back for non-HTML5 browser with JavaScript turned off and no Flash player installed -->
				<img src="<?php echo $poster; ?>" width="768" height="432" alt="" title="No video playback capabilities" />		
			</object>
		<?php elseif($wptv_datas['wptv_datas']['video_mode'] == 'youtube' && $deny_guest == 0): ?>
			<!-- Pseudo HTML5 -->
			<source type="video/youtube" src="https://www.youtube.com/watch?v=<?php echo $wptv_datas['wptv_datas']['youtube_url']; ?>" />
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
	</div>
	<div class="tab-container">
		<div id="wptv_tabs">
			<ul class="video-course-tabs">
				<li><a href="#wptv-subtitle">Traning Video</a></li>
				<li><a href="#wptv-download">Download</a></li>
				<li><a href="#wptv-faq">FAQ</a></li>
			</ul>
			<div id="wptv-subtitle">
				<div class="wptv-content">
					<div class="row transcript-items">
						<p>
						<?php if(!empty($subtitles)):
							$i = 0;
							foreach($subtitles as $subtitle):
								if(!empty($subtitle['time']) || $subtitle['time'] == 'none'):
						?>
							<span class="transcript subtitle ga-<?php echo $i; ?>" data-class="ga-<?php echo $i; ?>" data-duration="<?php echo $subtitle['time']; ?>"><?php echo $subtitle['text']; ?></span>
						<?php 	else:
									echo $subtitle['text'];
								endif;
							$i++; 						
							endforeach;
						endif; ?>
						</p>
					</div>
				</div>
			</div>
			<div id="wptv-download" class="hidden">
				<div class="wptv-content">
					<ul class="download-items">
					<?php if(!empty($wptv_datas['wptv_datas']['download_powerpoint_url'])): ?>
						<li class="download-item"><a href="<?php echo $wptv_datas['wptv_datas']['download_powerpoint_url']; ?>" target="_blank"><?php _e('Powerpoint Download','wptv'); ?></a></li>
					<?php endif; ?>
					<?php if(!empty($wptv_datas['wptv_datas']['download_script_url'])): ?>
						<li class="download-item"><a href="<?php echo $wptv_datas['wptv_datas']['download_script_url']; ?>" target="_blank"><?php _e('Script Download','wptv'); ?></a></li>
					<?php endif; ?>
					<?php if(!empty($wptv_datas['wptv_datas']['download_study_guide_url'])): ?>
						<li class="download-item"><a href="<?php echo $wptv_datas['wptv_datas']['download_study_guide_url']; ?>" target="_blank"><?php _e('Study Guide Download','wptv'); ?></a></li>
					<?php endif; ?>
					<?php if(!empty($wptv_datas['wptv_datas']['download_flyer_url'])): ?>
						<li class="download-item"><a href="<?php echo $wptv_datas['wptv_datas']['download_flyer_url']; ?>" target="_blank"><?php _e('Flyer Download','wptv'); ?></a></li>
					<?php endif; ?>
					</ul>
				</div>
			</div>
			<div id="wptv-faq">
				<div class="wptv-content">
					<?php if(!empty($wptv_datas['wptv_datas']['faq'])): 
						echo $wptv_datas['wptv_datas']['faq'];
					endif; ?>
				</div>
			</div>
		</div>
	</div>
</div>
