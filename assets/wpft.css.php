<?php
/*
 * DO NOT EDIT THIS DIRECTLY. INSTEAD, USE CUSTOM STYLESHEET BOX UNDER FITNESS TESTIMONIALS > SETTINGS
*/
header( 'Content-type: text/css; charset= UTF-8' ) ;
header( 'Cache-control: must-revalidate' );

$abs_path = explode( 'wp-content', $_SERVER['SCRIPT_FILENAME'] );
require_once( $abs_path[0] . 'wp-load.php' );

$settings = get_option( 'wpft');

echo "@import url(" . WPFT_PLUGIN_URL . "/skins/{$settings['skin']}/{$settings['skin']}.css);";
?>

.wpft_loading {
	min-height: 35px;
	min-width: 45px;
	background: url(images/testimonials_loading.gif) no-repeat;
}

.like_button {
	margin: 10px 0;
}

.wpft_in_between {
	margin: 25px 0;
}

.weight_result {
	font-size: 20px;
	weight: bold;
}

.weight_result span {
	display: block;
	text-align: center;
	font-size :30px;
}

.wpft-gauge {
	display: inline-block;
	width: 220px;
	height: 176px;
	margin: 10px 0;
	float: right;
}

.wpft_rating {
  unicode-bidi: bidi-override;
  direction: rtl;
}

.wpft_rating > span {
  display: inline-block;
  position: relative;
  width: 1.1em;
}
.wpft_rating > span:hover:before,
.wpft_rating > span:hover ~ span:before {
   content: "\2605";
   position: absolute;
}

.wpft_edit_link { font-size:12px; padding:10px 0; }

.testimonial_box_single .t_image_wrap {width: <?php echo $wpft_options['thumb_width']; ?>px; height: <?php echo $wpft_options['thumb_width']; ?>px; overflow: hidden; -webkit-border-radius: 50%; -moz-border-radius: 50%; border-radius: 50%; border: 4px solid #fff; -webkit-box-shadow: 0 3px 5px rgba(0, 0, 0, .1); -moz-box-shadow: 0 3px 5px rgba(0, 0, 0, .1); }

.testimonial_box_single .t_image_wrap img {margin:0; position: relative; top: 50%; -webkit-transform: translate(0, -50%); -moz-transform: translate(0, -50%); transform: translate(0, -50%);}

@media screen and (max-width: 782px) {
	.wpft-gauge {float: none;}
	.testimonial_box_single .t_image_wrap {margin: 0 auto;}
	.testimonial_box .t_image_1, .testimonial_box .t_image_2 {margin: 0 auto;}
}

<?php 
if( $settings ) {
		
if( $settings['thumb_rotate'] ) { ?>
/* Rotation */
.testimonial_box_double .before-text,
.testimonial_box_double .t_image_1 {
	transform: rotate(-5deg);
}
.testimonial_box_double .after-text, 
.testimonial_box_double .t_image_2 {
	transform: rotate(5deg);
}
.testimonial_box_double .t_image_container {
	margin-bottom: 50px;
}
.wpft-wrap.item-even .t_image_1, 
.wpft-wrap.item-even .t_image_2 {
	-webkit-box-shadow: 0 0 0 10px #fdfdfd, 0 30px 0 10px #fdfdfd, 0 41px 14px rgba(0,0,0,.25);
	-moz-box-shadow: 0 0 0 10px #fdfdfd, 0 30px 0 10px #fdfdfd, 0 41px 14px rgba(0,0,0,.25);
	box-shadow: 0 0 0 10px #fdfdfd, 0 30px 0 10px #fdfdfd, 0 41px 14px rgba(0,0,0,.25);
}
.testimonial_box .t_image_box_2::before {
	content: " ";
	display: block;
	position: absolute;
	top: -49px;
	left: -60px;
	width: 143px;
	height: 84px;
	background: url(images/transformation_arrow.png) no-repeat;
	z-index: 1;
}
<?php } // End if $settings['thumb_rotate']

if( $settings['thumb_alternate'] ) { ?>
/* Image Alternation */
.wpft-wrap.item-even .t_image_container {
	float: left;
	margin-right: 10px;
}
<?php } // End if $settings['thumb_alternate']

if( $settings['layout_background_color'] ) { echo '.wpft-wrap {background-color:' . $settings['layout_background_color'] . ';}' . "\n"; }

if( $settings['layout_maintext_color'] ) { echo '.testimonial_box .t_content, .testimonial_box .t_name, .testimonial_box .t_subtext {color:' . $settings['layout_maintext_color'] . ' }' . "\n"; }

if( $settings['layout_title_color'] ) { echo '.testimonial_box .t_quote {color:' . $settings['layout_title_color'] . ' }' . "\n"; }

if( $settings['layout_custom_css'] ) { echo $settings['layout_custom_css']; }

} // if $settings
?>