// JavaScript Document

function wpft_ajax_testimonials( term_id, ajaxurl, randomOption, excerptOption ) {
	
	randomOption = typeof randomOption !== 'undefined' ? randomOption : false;
	excerptOption = typeof excerptOption !== 'undefined' ? excerptOption : false;
	
	var data = {
		action: 'wpft_testimonials_get',
		group_id: term_id,
		random: randomOption,
		excerpt: excerptOption
	};
	jQuery.post( ajaxurl, data, function(response) {
	//console.log(data);
		if (response != -1) {
			jQuery('#testimonial-'+term_id).removeClass('wpft_loading').html(response);
		}
	});
}

jQuery(document).ready(function($){
	// jquery-justgage
	
	window.onload = function(){
		if( typeof(wpft_WeightResults) != 'undefined' ) {
			for (var key in wpft_WeightResults) {
				var gaugeID = "wpft-group-" + wpft_WeightResults[key].group_id + "-gauge-" + key;
				var g = {};
				
				console.log('#' + gaugeID);
				if( $('#' + gaugeID).length > 0 ) {
					// Use alt color for particular skin and even-numbered testimonials
					var newLabelColor = ( (typeof(wpft_WeightResults[key].use_alt_color) ) && ( parseFloat(key) && (key % 2 == 0) ) ) ? wpft_WeightResults[key].label_color_alt : wpft_WeightResults[key].label_color;
					
					// Generate gage
					g.key  = new JustGage({
						id: gaugeID, 
						value: wpft_WeightResults[key].start_weight,  
						min: 0,
						max: wpft_WeightResults[key].start_weight,
						title: wpft_WeightResults[key].weight_units[2] + " " + wpft_WeightResults[key].num_sign + wpft_WeightResults[key].difference + " " + wpft_WeightResults[key].weight_units[1],
						label: wpft_WeightResults[key].weight_units[0],
						valueFontColor: newLabelColor,
						titleFontColor: newLabelColor,
						labelFontColor: newLabelColor,
						gaugeWidthScale: 0.4,
						startAnimationTime: 1,
						refreshAnimationTime: 500
					});
					
					setTimeout( function(g, key) {g.key.refresh(wpft_WeightResults[key].current_weight) }, 1000, g, key);
				}

			}
		}
	};
});