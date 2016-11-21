function add_scripts(obj) {
		var urlField = jQuery(obj).attr("data-field-id");

		var file_frame;

			// If the media frame already exists, reopen it.
			if (file_frame) {
				file_frame.open();
				return;
			}

			// Create the media frame.
			file_frame = wp.media.frames.file_frame = wp.media({
				title: jQuery(this).data('uploader_title'),
				button: {
					text: jQuery(this).data('uploader_button_text'),
				},
				multiple: false // Set to true to allow multiple files to be selected
			});

			// When a file is selected, run a callback.
			file_frame.on('select', function(){
				// We set multiple to false so only get one image from the uploader
				attachment = file_frame.state().get('selection').first().toJSON();
				var url = attachment.url;

				jQuery('#' + urlField).val(url); //set which variable you want the field to have
			});

			// Finally, open the modal
			file_frame.open();
	 
		//return false;  
	}
jQuery(document).ready(function($){
	if($('video#player-default').length > 0){
		$('video#player-default').mediaelementplayer({
			//mode: 'shim',
			startVolume: 1
		});
	}
});