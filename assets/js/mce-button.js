(function() {
	tinymce.PluginManager.add('wpft_shortcode_button', function( editor, url ) {
		var groupsArray = [];
		wpft_no_group = false;
		
		if( typeof(wpft_plugin_groups) != 'undefined' ) {
			for (var key in wpft_plugin_groups) {
				var group = {text: wpft_plugin_groups[key], value: key};
				groupsArray.push(group) ;
			}
		}
		
		if ( groupsArray.length < 1 ) {
			wpft_no_group = true; // No active group found
		}
		
		editor.addButton( 'wpft_shortcode_button', {
            title: wpftTexts.button_title,
            icon: 'icon wpftshortcode-own-icon',
            //icon: 'icon dashicons-format-chat',
			
			onclick: function() {
				
				editor.windowManager.open( {
					title: wpftTexts.window_title,
					body: [{
						type: 'listbox', 
						name: 'wpft_group', 
						label: wpftTexts.label_group, 
						'values': groupsArray
					},{
						type: 'listbox', 
						name: 'wpft_limit', 
						label: wpftTexts.label_limit, 
						'values': [ { text: wpftTexts.not_set, value: '0'}, { text: '1', value: '1'}, { text: '2', value: '2'}, { text: '3', value: '3'}, { text: '4', value: '4'}, { text: '5', value: '5'}, { text: '6', value: '6'}, { text: '7', value: '7'}, { text: '8', value: '8'}, { text: '9', value: '9'}, { text: '10', value: '10'} ]
					},
					{
						type: 'checkbox',
						name: 'wpft_random',
						label: wpftTexts.label_random
					},
					{
						type: 'checkbox',
						name: 'wpft_excerpt',
						label: wpftTexts.label_excerpt
					},
					{
						type: 'checkbox',
						name: 'wpft_javascript',
						label: wpftTexts.label_javascript
					}],
					onsubmit: function( e ) {
						if( wpft_no_group )
							alert( wpftTexts.alert_message );
						
						var $shortcode = 'fitness-testimonials group="' + wpft_plugin_groups[e.data.wpft_group] + '" id="' + e.data.wpft_group + '"';
						
						if ( e.data.wpft_limit != '0' ) {
							$shortcode = $shortcode + ' limit="' + e.data.wpft_limit + '"';
						}
						if ( e.data.wpft_random == true ) {
							$shortcode = $shortcode + ' random="true"';
						}
						if ( e.data.wpft_excerpt == true ) {
							$shortcode = $shortcode + ' excerpt="true"';
						}
						if ( e.data.wpft_javascript == true ) {
							$shortcode = $shortcode + ' javascript="true"';
						}
						
						editor.insertContent( '[' + $shortcode + ']' );
					}
				});
			}
        });
    });
})();