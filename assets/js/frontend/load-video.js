jQuery(document).ready(function($) 
{
    $('.content-lists .content-item a').click(function(e) 
    {
        e.preventDefault();
		var contentId = $(this).attr('data-content-id');
        var datas = {
            action: 'wptv_load_video',
            nonce: wptv_load_video_ajax.nonce,
			video: contentId
        };
		
		$.ajax({
			cache: false,
			timeout: 8000,
			url: wptv_load_video_ajax.url,
			type: "POST",
			data: datas,
			//dataType : "html",
			beforeSend: function() {
				$( '#loading' ).show();
			},

			success: function( response, textStatus, jqXHR ){
				//var ajax_response = $( response );
				if(response.data.success == 1) {
					var courseHtml = response.data.course;
					$('#video-course-wrapper .video-container').html();
					
					$('#video-course-wrapper .video-container').html( courseHtml );

					setTimeout(function(){
						$('body').find('video').mediaelementplayer({
							startVolume: 1, 
							success: function(mediaElement, domObject) {
								var currentClass = '';
								media = mediaElement; // make it available for other functions
								//console.log(media.pluginType);
								media.addEventListener('timeupdate', function(e) {
									var k = 0;
									//Update subtitle
									var seconds = media.currentTime,
											timeSliderText = mejs.i18n.t('mejs.time-slider'),
											time = mejs.Utility.secondsToTimeCode(seconds, media.options),
											duration = media.duration;
									var sec = hmsToSecondsOnly(time);
									
									k = closest(sec,subtitles,'duration');
									//console.log(k);
									if(k != -1){
										currentClass =  subtitles[k]['class'];
										$('.transcript-items .transcript').each(function(){
											var itemClass = $(this).attr('data-class');
											if(itemClass == currentClass){
												$(this).addClass('current');
											} else {
												$(this).removeClass('current');
											}
										});
									}
									
								}, false);
							}
						});
					},500);
				} else {
					alert(response.data.error);
				}
			},

			error: function( jqXHR, textStatus, errorThrown ){
				console.log( 'The following error occured: ' + textStatus, errorThrown );   
			},

			complete: function( jqXHR, textStatus ){
				$( '#loading' ).hide();
			}

		});

    });
});