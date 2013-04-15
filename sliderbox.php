<?php
/*
Plugin Name:3D Slider Slice Box
Plugin URI: http://wordpress.org/extend/plugins/3d-slider-slicebox/
Description: 3D Slider Slice Box is responsive 3d slider which enables you to create 3d slider without the use of flash.
Author: Ezhil
Version: 1.1
Author URI: http://profiles.wordpress.org/ezhil/
License: GPLv2 or later
*/
$site_url = get_option('siteurl');
   $temp_url = get_template_directory_uri();
   define('SITE_URL', $site_url );
   define('TEMP_URL', $temp_url );
   $site_name = parse_url(SITE_URL, PHP_URL_HOST);
   define('SITE_NAME', $site_name );

  // limit excerpt
function sb_excerpt($limit) {
      $excerpt = explode(' ', get_the_excerpt(), $limit);
      if (count($excerpt)>=$limit) {
        array_pop($excerpt);
        $excerpt = implode(" ",$excerpt).'...';
      } else {
        $excerpt = implode(" ",$excerpt);
      } 
      $excerpt = preg_replace('`\[[^\]]*\]`','',$excerpt);
      return $excerpt;
    }
/*
 * value to array converter
 */
function sb_arr_gen($val)
{
	$dataPoints = array();  
    $dataPoint = array();  
	for($i=1;$i<=$val;$i++)
	{
	$dataPoint = array(	$tock['atr'.$i] = array('link'.$i,'name'.$i,'perma'.$i));
	$dataPoints = array_merge($dataPoints,$dataPoint);	
	}
	return $dataPoints;
}
//var_dump(sb_arr_gen(5));
$thevalue = get_option( 'sb_controls', $sb_controls );
if(empty($thevalue['no_of_slides']))
{
	$exval ='5';
	
}else {$exval = $thevalue['no_of_slides'];}
//$sb_controls  =  sb_arr_gen($exval);   
    
/* 
 * declare option in array
 * $sb_controls in the only registered settings option
 * 
 */
$sb_controls =  array('no_of_slides' => '' ,'slide_type' => '','slide_category' => '','slide_excerpt' => '','slide_excerptlength' => '','slide_width' => '',
'slide_orientation' => '','slide_cuboidsRandom' => '','slide_cuboidsCount' => '','slide_speed' => '','slide_disperseFactor' => '','slide_autoplay' => '',
'slide_interval' => '','slide_perspective' => '','slide_option'=>'');
$sb_controls['slide_imgs'] = sb_arr_gen($exval);
/*
 * Register settings options
 */

if ( is_admin() ) : 
function sb_register_settings() {
   // Register settings and call sanitation functions
   register_setting( 'sb_slider_options', 'sb_controls', 'sb_slides_validate' );
}
add_action( 'admin_init', 'sb_register_settings' );

/*
 * function to add submenu page to settings
 */

add_action('admin_menu', 'sb_admin_menus');

    function sb_admin_menus() {  
        add_submenu_page('options-general.php','slicebox sliders', '3D Slider', 'manage_options',   
            'sboptions', 'sb_options_page');   
    }


// Function to generate options page
function sb_options_page() {
   global $pagenow;

           sb_slide_options();
}
// Function to generate options page
function sb_slide_options() {
   global $sb_controls;
   if ( ! isset( $_REQUEST['updated'] ) )
       $_REQUEST['updated'] = false; // This checks whether the form has just been submitted. ?>
<div class="wrap">
<?php screen_icon(); echo "<h2>3D Slider options</h2>"; ?>
<?php if ( false !== $_REQUEST['updated'] ) : ?>
<div class="updated fade"><p><strong><?php _e( 'Options saved' ); ?></strong></p></div>
<?php endif; // If the form has just been submitted, this shows the notification ?>
<form method="post" action="options.php">
<?php $settings = get_option( 'sb_controls', $sb_controls )?>
<?php settings_fields( 'sb_slider_options' );?>
<style>
.imgon th,.imgon td{
background:#eee;padding:15px 25px;border-bottom:1px solid #ddd;
}
#imgoff th, #imgoff td, #imgoff1 th, #imgoff1 td, #imgoff2 th, #imgoff2 td{background:#eee;padding:15px 25px;border-bottom:1px solid #ddd;}
.imgon input{margin-bottom:5px;
    padding: 6px;
    width: 500px; }
</style>
<table class="form-table rctable"><!-- Grab a hot cup of coffee, yes we're using tables! -->
<!-- choose options -->
<tr valign="top"><th scope="row"><label for="sb_controls[slide_option]"><h3 style="margin: 0px;">Choose option</h3></label></th>
<td>
<select id="sb_controls[slide_option]"  name="sb_controls[slide_option]" onchange="graboption()">
  <option value="posts" <?php selected( $settings['slide_option'], posts ); ?>>From posts</option>
  <option value="imgs" <?php selected($settings['slide_option'], imgs ); ?>>From images</option>
</select>
<script type="text/javascript">

function graboption()
{
var curval = document.getElementById('sb_controls[slide_option]');
var curval1 = curval.options[curval.selectedIndex].value;
if(curval1 == 'imgs')
{
document.getElementById('sldtitle').innerHTML = 'Enter slide images';	
document.getElementById('imgoff').style.display = 'none';
<?php $imgval = count($sb_controls['slide_imgs']);for ($i=0;$i<=$imgval;$i++){ ?>
document.getElementsByClassName('imgon')[<?php echo $i; ?>].setAttribute('style','');
<?php } ?>
}else if(curval1 == 'posts')
{
	document.getElementById('sldtitle').innerHTML = 'Select slide category';
	document.getElementById('imgoff').setAttribute('style','');	
	<?php $imgval = count($sb_controls['slide_imgs']);for ($i=0;$i<=$imgval;$i++){ ?>
	document.getElementsByClassName('imgon')[<?php echo $i; ?>].style.display = 'none';
	<?php } ?>
}

}
</script>
</td>
</tr>
<!-- no of slides -->
<tr valign="top"><th scope="row"><label for="sb_controls[no_of_slides]"><h3 style="margin: 0px;">No of Slides</h3></label></th>
<td>
<input placeholder="enter only numbers" title="enter only numbers" pattern="[0-9]*" id="sb_controls[no_of_slides]" name="sb_controls[no_of_slides]" type="text" value="<?php  esc_attr_e($settings['no_of_slides']); ?>" /></td>
</tr>

<!-- entry title  -->
<tr  valign="top">
<th scope="row"><h2 id="sldtitle" style="width: 290px;">Enter the slide images</h2></th>
</tr>

<?php foreach ($sb_controls['slide_imgs'] as $i=> $lnval) { ?>
<tr style="<?php if ($settings['slide_option'] == 'posts'){echo "display:none;";} ?>" class="imgon"><th scope="row"><label for="sb_controls[slide_imgs]"><h3 style="margin: 0px;">Slide <?php echo $i;?></h3></label></th>
<td>
<input placeholder="Image Source : enter the link" id="<?php echo  $lnval[0]?>" name="sb_controls[<?php echo  $lnval[0]?>]" type="text" value="<?php  esc_attr_e($settings[$lnval[0]]); ?>" />
<br>
<input placeholder="Image Caption : enter the text" id="<?php echo  $lnval[1]?>" name="sb_controls[<?php echo  $lnval[1]?>]" type="text" value="<?php  esc_attr_e($settings[$lnval[1]]); ?>" />
<br>
<input placeholder="Link URL : enter the url" id="<?php echo  $lnval[2]?>" name="sb_controls[<?php echo  $lnval[2]?>]" type="text" value="<?php  esc_attr_e($settings[$lnval[2]]); ?>" />
</td>
</tr>
<?php } ?>
<!-- select category  -->
<tr style="<?php if (strip_tags($settings['slide_option']) == 'imgs'){echo "display:none;";} ?>" id="imgoff" valign="top"><th scope="row"><label for="sb_controls[slide_category]"><h3 style="margin: 0px;">Select post category</h3></label></th>
<td>
<select id="sb_controls[slide_category]"  name="sb_controls[slide_category]" >
<?php $slide_cat = get_categories();
foreach ($slide_cat as $i)
{?>
<option value="<?php echo $i->cat_ID; ?>" <?php selected( $settings['slide_category'], $i->cat_ID ); ?>><?php echo $i->name; ?></option>
<?php }
?>
</select>
</td>
</tr>

<!-- show excerpt  -->
<tr id="imgoff1" valign="top"><th scope="row"><label for="sb_controls[slide_excerpt]"><h3 style="margin: 0px;">Show excerpt</h3></label></th>
<td><input id="sb_controls[slide_excerpt]" name="sb_controls[slide_excerpt]" type="checkbox" value="show" <?php checked( show == $settings['slide_excerpt'] ); ?>" /></td>
</tr>
<!-- Excerpt length -->
<tr id="imgoff2" valign="top"><th scope="row"><label for="sb_controls[slide_excerptlength]"><h3 style="margin: 0px;">Excerpt length</h3></label></th>
<td>
<input placeholder="default is 30" title="no of words in description, works only when show excerpt in on" pattern="[0-9]*" id="sb_controls[slide_excerptlength]" name="sb_controls[slide_excerptlength]" type="text" value="<?php  esc_attr_e($settings['slide_excerptlength']); ?>" /></td>
</tr>

<!-- entry title  -->
<tr  valign="top">
<th scope="row"><h2 style="width: 290px;">Slider effects options</h2></th>
</tr>
<tr valign="top"><th scope="row"><label for="sb_controls[slide_width]"><h3 style="margin: 0px;">Slider width</h3></label></th>
<td>
<input placeholder="for eg:just 950 and not 950px" title="for eg:just 950 and not 950px" pattern="[0-9]*" id="sb_controls[slide_width]" name="sb_controls[slide_width]" type="text" value="<?php  esc_attr_e($settings['slide_width']); ?>" /></td>
</tr>
<!-- select type -->
<tr valign="top"><th scope="row"><label for="sb_controls[slide_type]"><h3 style="margin: 0px;">Slider Navigation</h3></label></th>
<td>
<select id="sb_controls[slide_type]"  name="sb_controls[slide_type]" >
  <option value="type1" <?php selected( $settings['slide_type'], type1 ); ?>>Type1 - Slideshow with dots</option>
  <option value="type2" <?php selected($settings['slide_type'], type2 ); ?>>Type2 - Slideshow with play & pause</option>
</select>
</td>
</tr>
<!-- random cuboids -->
<tr valign="top"><th scope="row"><label for="sb_controls[slide_cuboidsRandom]"><h3 style="margin: 0px;">Random Cuboids</h3></label></th>
<td><input id="sb_controls[slide_cuboidsRandom]" name="sb_controls[slide_cuboidsRandom]" type="checkbox" value="show" <?php checked( show == $settings['slide_cuboidsRandom'] ); ?>" /></td>
</tr>
<!-- cuboidsCount -->
<tr valign="top"><th scope="row"><label for="sb_controls[slide_cuboidsCount]"><h3 style="margin: 0px;">Cuboids Count</h3></label></th>
<td>
<input placeholder="default is 5:number of slices" title="random cuboids should not be selected, only numbers" pattern="[0-9]*" id="sb_controls[slide_cuboidsCount]" name="sb_controls[slide_cuboidsCount]" type="text" value="<?php  esc_attr_e($settings['slide_cuboidsCount']); ?>" /></td>
</tr>
<!-- auto play -->
<tr valign="top"><th scope="row"><label for="sb_controls[slide_autoplay]"><h3 style="margin: 0px;">Auto play</h3></label></th>
<td><input id="sb_controls[slide_autoplay]" name="sb_controls[slide_autoplay]" type="checkbox" value="show" <?php checked( show == $settings['slide_autoplay'] ); ?>" /></td>
</tr>
<!-- interval -->
<tr valign="top"><th scope="row"><label for="sb_controls[slide_interval]"><h3 style="margin: 0px;">Slide Interval</h3></label></th>
<td>
<input placeholder="1000 = 1sec" title="1000 = 1sec:works only when autoplay is on" pattern="[0-9]*" id="sb_controls[slide_interval]" name="sb_controls[slide_interval]" type="text" value="<?php  esc_attr_e($settings['slide_interval']); ?>" /></td>
</tr>
<!-- speed -->
<tr valign="top"><th scope="row"><label for="sb_controls[slide_speed]"><h3 style="margin: 0px;">Slide speed</h3></label></th>
<td>
<input placeholder="enter only numbers" title="speed of slide rotation" pattern="[0-9]*" id="sb_controls[slide_speed]" name="sb_controls[slide_speed]" type="text" value="<?php  esc_attr_e($settings['slide_speed']); ?>" /></td>
</tr>
<!-- disperseFactor -->
<tr valign="top"><th scope="row"><label for="sb_controls[slide_disperseFactor]"><h3 style="margin: 0px;">Disperse Factor</h3></label></th>
<td>
<input placeholder="default is 30" title="controls movements of the cubeboid;default is 30" pattern="[0-9]*" id="sb_controls[slide_disperseFactor]" name="sb_controls[slide_disperseFactor]" type="text" value="<?php  esc_attr_e($settings['slide_disperseFactor']); ?>" /></td>
</tr>
<!-- perspective -->
<tr valign="top"><th scope="row"><label for="sb_controls[slide_perspective]"><h3 style="margin: 0px;">Perspective</h3></label></th>
<td>
<input placeholder="default is 1200" title="enter only numbers" pattern="[0-9]*" id="sb_controls[slide_perspective]" name="sb_controls[slide_perspective]" type="text" value="<?php  esc_attr_e($settings['slide_perspective']); ?>" /></td>
</tr>
<!-- orientation -->
<tr valign="top"><th scope="row"><label for="sb_controls[slide_orientation]"><h3 style="margin: 0px;">Orientation</h3></label></th>
<td>
<select id="sb_controls[slide_orientation]"  name="sb_controls[slide_orientation]" >
  <option value="h" <?php selected( $settings['slide_orientation'], h ); ?>>Horizontal</option>
  <option value="v" <?php selected($settings['slide_orientation'], v ); ?>>Vertical</option>
  <option value="r" <?php selected($settings['slide_orientation'], r ); ?>>Random</option>
</select>
</td>
</tr>
</table>
<p class="submit"><input type="submit" class="button-primary" value="Save Options" /></p>
</form>
</div>
<?php
} 
function sb_slides_validate( $input ) {
   global $sb_controls;

   $settings = get_option( 'sb_controls', $sb_controls );
   
          //$input['atr1'] = wp_filter_nohtml_kses( $input['atr1'] );
      return $input;
}

endif;  // EndIf is_admin()
//for jquery load
function jq_sb_load()
{
wp_enqueue_script('jquery');
}
add_action('wp_enqueue_scripts','jq_sb_load'); 
//for right click
function sb_slides_display()
{
global $sb_controls;
$settings = get_option( 'sb_controls', $sb_controls );
	?>
<div class="sb_cov">
<link rel="stylesheet" type="text/css" href="<?php echo plugins_url(); ?>/3d-slider-slicebox/css/slicebox.css" />
<?php if ($settings['slide_width']){$slidewidth = strip_tags($settings['slide_width']);}else {$slidewidth = '950';}?>
<style>
.sb_cov{max-width:<?php echo $slidewidth.'px !important';?>}
</style>
<script type="text/javascript" src="<?php echo plugins_url(); ?>/3d-slider-slicebox/js/modernizr.custom.46884.js"></script>
<ul id="sb-slider" class="sb-slider">
<?php
if ($settings['no_of_slides'])
{
$slideno = strip_tags($settings['no_of_slides']);
}else {$slideno = '3';}
if (strip_tags($settings['slide_option']) == 'imgs'){
for($i=1;$i<=$slideno;$i++) {?>
					<li>
						<a href="<?php echo strip_tags($settings['perma'.$i]); ?>" target="_blank"><img src="<?php echo strip_tags($settings['link'.$i]); ?>" /></a>
						<div class="sb-description">
							<h3><?php echo strip_tags($settings['name'.$i]); ?></h3>
						</div>
					</li>
<?php } } else if (strip_tags($settings['slide_option']) == 'posts')
{
if ($settings['slide_category'])
{
$slidecat = strip_tags($settings['slide_category']);
}else {$slidecat = '1';}
global $post;
$args = array( 'numberposts' => $slideno, 'category' => $slidecat );
$myposts = get_posts( $args );
foreach( $myposts as $post ) :	setup_postdata($post);?>
					<li>
						<a href="<?php the_permalink(); ?>" target="_blank"><?php if ( has_post_thumbnail() ) {   the_post_thumbnail();}?></a>
						<div class="sb-description">
							<h3><?php the_title();?></h3>
							<span><?php if ($settings['slide_excerpt'])
                                           {
                                          if ($settings['slide_excerptlength']){echo sb_excerpt(strip_tags($settings['slide_excerptlength']));}else {echo sb_excerpt(20); }
                                           } ?>
                            </span>
						</div>
					</li>
<?php endforeach; 
} // for posts
?>
				</ul>
				<div id="shadow" class="shadow"></div>
				<div id="nav-arrows" class="nav-arrows">
					<a href="#">Next</a>
					<a href="#">Previous</a>
				</div>
<?php if ($settings['slide_type'] == 'type1'){?>
				<div id="nav-dots" class="nav-dots">
					<span class="nav-dot-current"></span>
					<?php for ($i=2;$i<=$slideno;$i++){ ?>
					<span></span>
					<?php }?>
				</div>
<?php } else if ($settings['slide_type'] == 'type2'){ ?>
<div id="nav-options" class="nav-options">
					<span id="navPlay">Play</span>
					<span id="navPause">Pause</span>
</div>
<?php }?>
		<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
		<script type="text/javascript" src="<?php echo plugins_url(); ?>/3d-slider-slicebox/js/jquery.slicebox.js"></script>
		<script type="text/javascript">
			$(function() {

				var Page = (function() {
					
					var $navArrows = $( '#nav-arrows' ).hide(),
					<?php if ($settings['slide_type'] == 'type1'){?>
					$navDots = $( '#nav-dots' ).hide(),$nav = $navDots.children( 'span' ),
		<?php } else if ($settings['slide_type'] == 'type2'){ ?>
		$navOptions = $( '#nav-options' ).hide(),
		<?php }?>
						$shadow = $( '#shadow' ).hide(),
						slicebox = $( '#sb-slider' ).slicebox( {
							onReady : function() {

								$navArrows.show();
								<?php if ($settings['slide_type'] == 'type1'){?>
								$navDots.show();
					<?php } else if ($settings['slide_type'] == 'type2'){ ?>
					$navOptions.show();
					<?php }?>
								$shadow.show();

							},
		<?php if ($settings['slide_orientation']){$orientation = strip_tags($settings['slide_orientation']);}else {$orientation = 'v';}?>
							orientation : '<?php echo $orientation; ?>',
							<?php if ($settings['slide_cuboidsRandom'] == 'show'){?> cuboidsRandom : true, <?php }?>
		<?php if ($settings['slide_cuboidsCount']){$cuboidsCount = strip_tags($settings['slide_cuboidsCount']);}else {$cuboidsCount = '3';}?>
		cuboidsCount : '<?php echo $cuboidsCount; ?>',
		<?php if ($settings['slide_speed']){$speed =  strip_tags($settings['slide_speed']);}else {$speed = '600';}?>
		speed : '<?php echo $speed; ?>',
		<?php if ($settings['slide_disperseFactor']){$disperseFactor =  strip_tags($settings['slide_disperseFactor']);}else {$disperseFactor = '30';}?>
		disperseFactor : '<?php echo $disperseFactor; ?>',	
		<?php if ($settings['slide_interval']){$interval =  strip_tags($settings['slide_interval']);}else {$interval = '3000';}?>
		interval : '<?php echo $interval; ?>',
		<?php if ($settings['slide_perspective']){$perspective =  strip_tags($settings['slide_perspective']);}else {$perspective = '1200';}?>
		perspective : '<?php echo $perspective; ?>',					
		<?php if ($settings['slide_autoplay'] == 'show'){?> autoplay : true, <?php }?>

							<?php if ($settings['slide_type'] == 'type1'){?>
							onBeforeChange : function( pos ) {
								$nav.removeClass( 'nav-dot-current' );
								$nav.eq( pos ).addClass( 'nav-dot-current' );
							}
				<?php } ?>
							
						} ),
						
						init = function() {

							initEvents();
							
						},
						initEvents = function() {

							// add navigation events
							$navArrows.children( ':first' ).on( 'click', function() {

								slicebox.next();
								return false;

							} );

							$navArrows.children( ':last' ).on( 'click', function() {
								
								slicebox.previous();
								return false;

							} );
							<?php if ($settings['slide_type'] == 'type1'){?>
							$nav.each( function( i ) {
								
								$( this ).on( 'click', function( event ) {
									
									var $dot = $( this );
									
									if( !slicebox.isActive() ) {

										$nav.removeClass( 'nav-dot-current' );
										$dot.addClass( 'nav-dot-current' );
									
									}
									
									slicebox.jump( i + 1 );
									return false;
								
								} );
								
							} );
				<?php } else if ($settings['slide_type'] == 'type2'){ ?>
				$( '#navPlay' ).on( 'click', function() {
					
					slicebox.play();
					return false;

				} );

				$( '#navPause' ).on( 'click', function() {
					
					slicebox.pause();
					return false;

				} );
				<?php }?>
							

						};

						return { init : init };

				})();

				Page.init();

			});
		</script>
</div>	
<?php }
?>