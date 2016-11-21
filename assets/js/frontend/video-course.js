jQuery(document).ready(function($){
	$("#wptv_tabs .hidden").removeClass('hidden');
    $("#wptv_tabs").tabs();
	$("#wptv-presentation .hidden").removeClass('hidden');
    $("#wptv-presentation").tabs();
	/* $( "#wptv_tabs a").click(function(e){
		$(window).unbind('ScrollSpy');
	});
	$( "#wptv-presentation a").click(function(e){
		$(window).unbind('ScrollSpy');
	}); */
	var subtitles = [];
	$('.transcript-items .transcript').each(function(){
		var item_duration = $(this).attr('data-duration');
		var item_class = $(this).attr('data-class');
		var durations = hmsToSecondsOnly(item_duration);
		if(durations > 0){
			subtitles.push({'class' : item_class, 'duration' : durations});
		}
	});
	//console.log(subtitles);
	$('audio,video').mediaelementplayer({
		//mode: 'shim',
		alwaysShowControls: true,
		startVolume: 1, 
		success: function(mediaElement, domObject) {
			getMediaElementPlay(mediaElement, subtitles);
		}
	});
	$('body').on('click', '.transcript-items .transcript', function(e){
		e.preventDefault();
		var item_duration = $(this).attr('data-duration');
		var sec = hmsToSecondsOnly(item_duration);
		if(parseInt(sec)){
			$('html, body').animate({
				scrollTop: $("#wptv-course").offset().top
			}, 500);
			media.setCurrentTime(sec); // set starting time
			media.play();
		}
    });
	$('.related-videos-list .video-item p span.youtube-duration').each(function(){
		var t = $(this);
		if(t.length > 0) {
			var api_key = $('#ytb-apikey').val();
			var videoId = $(this).attr('data-ytb-id');
			if(api_key != '' && videoId != ''){
				$.ajax({
						cache: false,
						data: $.extend({
							key: api_key,
							id: videoId
						}, {}),
						dataType: 'json',
						type: 'GET',
						timeout: 5000,
						url: 'https://www.googleapis.com/youtube/v3/videos?part=snippet,contentDetails,statistics,status&fields=items(id,snippet,statistics,contentDetails,status)'
				})
				.done(function(data) {
					var items = data.items[0];
					ytbtime = YTDurationToSeconds(items.contentDetails.duration);
					t.html('<i class="fa fa-clock-o" aria-hidden="true"></i> ' + ytbtime);
				});
			}
		}
	});
	$('.content-lists .content-item p span.youtube-duration').each(function(){
		var t = $(this);
		if(t.length > 0) {
			var api_key = $('#ytb-apikey').val();
			var videoId = $(this).attr('data-ytb-id');
			if(api_key != '' && videoId != ''){
				$.ajax({
						cache: false,
						data: $.extend({
							key: api_key,
							id: videoId
						}, {}),
						dataType: 'json',
						type: 'GET',
						timeout: 5000,
						url: 'https://www.googleapis.com/youtube/v3/videos?part=snippet,contentDetails,statistics,status&fields=items(id,snippet,statistics,contentDetails,status)'
				})
				.done(function(data) {
					var items = data.items[0];
					ytbtime = YTDurationToSeconds(items.contentDetails.duration);
					t.html(ytbtime);
				});
			}
		}
	});
	//Accordion sidebar

	$( "#accordion" ).accordion({
		heightStyle: "content",
		collapsible: true
    });
	
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
				$('#overlay').css("visibility", "visible");
				$( '#loading' ).show();
			},

			success: function( response, textStatus, jqXHR ){
				//var ajax_response = $( response );
				if(response.data.success == 1) {
					var courseHtml = response.data.course;
					$('#video-course-wrapper .video-container').html();					
					$('#video-course-wrapper .video-container').html( courseHtml );
					$('#wptv-subtitle .wptv-content .transcript-items').html();
					$('#wptv-subtitle .wptv-content .transcript-items').html(response.data.subtitles);
					$('#wptv-download .wptv-content .download-items').html();
					$('#wptv-download .wptv-content .download-items').html(response.data.download);
					$('#wptv-faq .wptv-content').html();
					$('#wptv-faq .wptv-content').html(response.data.faq);
					
					setTimeout(function(){
						var ajaxSubtitles = [];
						$('body #wptv-subtitle .wptv-content .transcript-items').find('.transcript').each(function(){
							var item_duration = $(this).attr('data-duration');
							var item_class = $(this).attr('data-class');
							var durations = hmsToSecondsOnly(item_duration);
							if(durations > 0){
								ajaxSubtitles.push({'class' : item_class, 'duration' : durations});
							}
						});
						$('body #video-course-wrapper .video-container').find('video').mediaelementplayer({
							startVolume: 1, 
							success: function(mediaElement, domObject) {
								getMediaElementPlay(mediaElement, ajaxSubtitles);
								mediaElement.play();
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
				$('#overlay').css("visibility", "hidden");
				$( '#loading' ).hide();
				$//('#wptv-course').focus();
				$('html, body').animate({
                    scrollTop: $("#wptv-course").offset().top
                }, 1000);
			}

		});

    });

});

function getMediaElementPlay(mediaElement, subtitles) {
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
		
		//k = closest(sec,subtitles,'duration');
		k = findIn2dArray(subtitles, 'duration' , sec);
		console.log(k);
		if(k != -1){
			currentClass =  subtitles[k]['class'];
			jQuery('.transcript-items .transcript').each(function(){
				var itemClass = jQuery(this).attr('data-class');
				if(itemClass == currentClass){
					jQuery(this).addClass('current');
				} else {
					jQuery(this).removeClass('current');
				}
			});
		}
		
	}, false);
}

function YTDurationToSeconds(duration) {
  var formattedTime = duration.replace("PT","").replace("H","h").replace("M","m").replace("S","s");

  return formattedTime;
}

function hmsToSecondsOnly(str) {
    var p = str.split(':'),
        s = 0, m = 1;

    while (p.length > 0) {
        s += m * parseInt(p.pop(), 10);
        m *= 60;
    }

    return s;
}

function findIn2dArray(arr_2d, key , val){
    var index=-1;

	for(var i = 0, len = arr_2d.length; i < len; i++){
		if(arr_2d[i][key] === val){
			index = i;
			break;
		}
	}
	return index;
}

