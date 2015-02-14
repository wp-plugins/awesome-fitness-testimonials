//jQuery.noConflict();

jQuery(document).ready(function($){
	// Tooltips
	$('.hastip').tooltipsy( {offset: [10, 0]} );
	
	$.mask.definitions['1'] = "[1-9]";
	$("input[name='wpft[thumb_width]']").mask("19?9");
	$("input[name='wpft[thumb_height]']").mask("19?9");
	$("input[name='wpft_start_weight']").mask("19?9");
	$("input[name='wpft_current_weight']").mask("19?9");

	// "Short Testimonial" editor tab 
	var $bar = $('<div>' + wpftEditorText.excerpt_tip + '</div>');
        $bar.addClass('quicktags-toolbar');
        $wrap = $('#wpft_excerpt_wrap');
        $wrap.prepend($bar);
        $('#wp-content-editor-tools #content-html').before(
          '<a id="content-wpft" class="wp-switch-editor swith-wpft" onclick="switchEditors.switchto(this);">' + wpftEditorText.tab_name + '</a>'
        );
	
	$('input[name="wpft[layout_background_color]"]').iris({
		hide: false,
		change: function(e, ui) {
			$('.preview-container .wpft-wrap').css( 'background-color', ui.color.toString());
		}
	});
	
	$('input[name="wpft[layout_title_color]"]').iris({
		hide: false,
		change: function(e, ui) {
			$('.preview-container .t_quote').css( 'color', ui.color.toString());
		}
	});
	
	$('input[name="wpft[layout_maintext_color]"]').iris({
		hide: false,
		change: function(e, ui) {
			$('.preview-container .t_content, .preview-container .t_name, .preview-container .t_subtext').css( 'color', ui.color.toString());
		}
	});
	
	
	$('#content-wpft').click(function(e) {
		e.preventDefault();
		var id = 'content';
		var ed = tinyMCE.get(id);
		var dom = tinymce.DOM;
		$('#wp-content-editor-container, #post-status-info').hide();
		dom.removeClass('wp-content-wrap', 'html-active');
		dom.removeClass('wp-content-wrap', 'tmce-active');
		dom.addClass('wp-content-wrap', 'wpft-active');
		$('#wpft_excerpt_wrap').show();
	});
	
	$('#content-tmce').click(function(e) {
		e.preventDefault();
		$('#wp-content-wrap').removeClass('wpft-active').addClass('tmce-active');
		$('#wpft_excerpt_wrap').hide();
		$('#wp-content-editor-container, #post-status-info').show();
	});
	
	$('#content-html').click(function(e) {
		e.preventDefault();
		$('#wp-content-wrap').removeClass('wpft-active').addClass('html-active');
		$('#wpft_excerpt_wrap').hide();
		$('#wp-content-editor-container, #post-status-info').show();
	});
	
	$('.wpft_layout_group input[name="wpft_layout"]').click( function() {
		switch( $(this).val() ) {
			case '0': // auto
				$('.wpft_field_group').fadeIn(300);
				break;
			case '1': // text
				$('#wpft_img_1_group').fadeOut(100);
				$('#wpft_img_2_group').fadeOut(100);
				$('#wpft_start_weight_group').fadeOut(100);
				$('#wpft_curr_weight_group').fadeOut(100);
				break;
			case '2': // single
				$('.wpft_field_group').fadeIn(300);
				$('#wpft_img_1_group').fadeOut(100);
				$('#wpft_start_weight_group').fadeOut(100);
				$('#wpft_curr_weight_group').fadeOut(100);
				break;
			case '3': // double
				$('.wpft_field_group').fadeIn(300);
				break;
			case '4': // video
				$('#wpft_img_1_group').fadeOut(100);
				$('#wpft_img_2_group').fadeOut(100);
				$('#wpft_start_weight_group').fadeOut(100);
				$('#wpft_curr_weight_group').fadeOut(100);
				break;
			case '5':// alt 1
				$('.wpft_field_group').fadeIn(300);
				break;
			case '6': // alt 2
				$('.wpft_field_group').fadeIn(300);
				break;
		}
	});
	
	// Image Upload
    var custom_uploader;
    $('.wpwf-img-upload-button').click(function(e) {
 
        e.preventDefault();
		
		var target_field = $(this).parents('td:first').find('input[type=text]');
 
        //Extend the wp.media object
        custom_uploader = wp.media.frames.file_frame = wp.media({
            title: wpftEditorText.choose_image,
            button: {
                text: wpftEditorText.choose_image
            },
            multiple: false
        });
 
        //When a file is selected, grab the URL and set it as the text field's value
        custom_uploader.on('select', function() {
			attachment = custom_uploader.state().get('selection').first().toJSON();
            target_field.val(attachment.url);
        });
 
        //Open the uploader dialog
        custom_uploader.open(); 
    });
	
	// Enable ACE editor
	var aceAreaArray = {
		ace_area_css: 'ace-textarea-css',
		ace_area_noimage: 'ace-textarea-noimage',
		ace_area_single: 'ace-textarea-single',
		ace_area_double: 'ace-textarea-double',
		ace_area_video: 'ace-textarea-video',
		ace_area_alt1: 'ace-textarea-alt1',
		ace_area_alt2: 'ace-textarea-alt2'
		};
	
	var editors = [];
	var $textarea = [];
	for ( var key in aceAreaArray ) {
		if ( $('#' + key).length > 0 ) {
			editors[key] = ace.edit(key);
			
			if ( key == 'ace_area_css' ) {
				editors[key].setTheme('ace/theme/chrome');
				editors[key].getSession().setMode('ace/mode/css');
			} else {
				editors[key].setTheme('ace/theme/monokai');
				editors[key].getSession().setMode('ace/mode/html');
			}
			$textarea[key] = $('#'+aceAreaArray[key]).hide();
			editors[key].getSession().setValue($textarea[key].val());
			
			editors[key].getSession().setUseWrapMode(true);
			editors[key].getSession().setWrapLimitRange(null, null);
			editors[key].renderer.setShowPrintMargin(null);
			editors[key].session.setUseSoftTabs(null);
		}
	}
	
	if ( $('#ace_area_css').length ) {
		editors['ace_area_css'].getSession().on('change', function () { $textarea['ace_area_css'].val(editors['ace_area_css'].getSession().getValue()); });
		editors['ace_area_noimage'].getSession().on('change', function () { $textarea['ace_area_noimage'].val(editors['ace_area_noimage'].getSession().getValue()); });
		editors['ace_area_single'].getSession().on('change', function () { $textarea['ace_area_single'].val(editors['ace_area_single'].getSession().getValue()); });
		editors['ace_area_double'].getSession().on('change', function () { $textarea['ace_area_double'].val(editors['ace_area_double'].getSession().getValue()); });
		editors['ace_area_video'].getSession().on('change', function () { $textarea['ace_area_video'].val(editors['ace_area_video'].getSession().getValue()); });
		editors['ace_area_alt1'].getSession().on('change', function () { $textarea['ace_area_alt1'].val(editors['ace_area_alt1'].getSession().getValue()); });
		editors['ace_area_alt2'].getSession().on('change', function () { $textarea['ace_area_alt2'].val(editors['ace_area_alt2'].getSession().getValue()); });
	}
	$('#wpft-admintabs').tabs();
	
	$('#wpft-admintabs .wp-color-picker').wpColorPicker();

	//hover states on the static widgets
	$('#dialog_link, ul#icons li').hover(
	function() { $(this).addClass('ui-state-hover'); },
	function() { $(this).removeClass('ui-state-hover'); }
	);
});