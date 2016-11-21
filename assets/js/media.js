function add_file(obj) {
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

            // here are some of the variables you could use for the attachment;
            //var all = JSON.stringify( attachment );      
            //var id = attachment.id;
            var title = attachment.title;
            //var filename = attachment.filename;
            var url = attachment.url;
            //var link = attachment.link;
            //var alt = attachment.alt;
            //var author = attachment.author;
            //var description = attachment.description;
            //var caption = attachment.caption;
            //var name = attachment.name;
            //var status = attachment.status;
            //var uploadedTo = attachment.uploadedTo;
            //var date = attachment.date;
            //var modified = attachment.modified;
            //var type = attachment.type;
            //var subtype = attachment.subtype;
            //var icon = attachment.icon;
            //var dateFormatted = attachment.dateFormatted;
            //var editLink = attachment.editLink;
            //var fileLength = attachment.fileLength;

            jQuery('#' + urlField).val(url); //set which variable you want the field to have
        });

        // Finally, open the modal
        file_frame.open();
 
    //return false;  
}

 
function remove_row(obj) {
    var parent=jQuery(obj).closest('.form_field');
    //console.log(parent)
    parent.remove();
}
 
function add_subtitle_row() {
    var row = '<div class="form_field"><ul><li><input type="text" name="wptv_datas[subtitle][time][]" class="input-text subtitle-time" value=""  /></li><li><input type="text" name="wptv_datas[subtitle][text][]" class="input-text subtitle-text" value="" /></li><li><input class="button button_remove" type="button" value="Remove row" onclick="remove_row(this)" /></li></ul></div>';
    jQuery(row).appendTo('#subtitle-lists');
}

jQuery(document).ready(function($) {
    $("#wptv_tabs .hidden").removeClass('hidden');
    $("#wptv_tabs").tabs();
	
	$('#video_mode').change(function(e){
		e.preventDefault();
		var mode = $(this).find("option:selected").val();
		
		if(mode == 'upload'){
			$('#video_url').css("display", "inline-block");
			$('.button_add_video').css("display", "inline-block");
			$('#youtube_url').css("display", "none");
			
		} else if(mode == 'youtube'){
			$('#video_url').css("display", "none");
			$('.button_add_video').css("display", "none");
			$('#youtube_url').css("display", "inline-block");
		} else {
			$('#video_url').css("display", "none");
			$('.button_add_video').css("display", "none");
			$('#youtube_url').css("display", "none");
		}
	});
});