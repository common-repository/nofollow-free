<?php
/*
Plugin Name: NoFollow Free
Plugin URI: http://www.michelem.org/wordpress-plugin-nofollow-free/
Description: Remove the nofollow tag from your blog's comments with a lot of options customizable, per user type removal, per comments count removal etc... Supports multilingual and a Top Commenters sidebar Widget.
Version: 1.6.3
Author: Michele Marcucci
Author URI: http://www.michelem.org/

Copyright (c) 2007 Michele Marcucci
Released under the GNU General Public License (GPL)
http://www.gnu.org/licenses/gpl.txt
*/


// Get/Set some options
$siteurl =  get_option('siteurl');
$noffdata = array(
	'nofollow_reg_author' => "author_reg_yes",
	'nofollow_reg_text' => "comment_reg_yes",
	'nofollow_author' => "author_yes",
	'nofollow_text' => "",
	'display_band' => "yes",
	'position_band' => "right",
	'colour_band' => "red",
	'url_band' =>  "http://www.michelem.org/wordpress-plugin-nofollow-free/",
	'nofollow_limit' => "10",
	'nofollow_limit' => "5",
	'count_comments' => "no",
	'comment_link' => "yes",
	'noff_lang' => "en",
	'noff_widget_limit' => "10",
	'noff_widget_title' => "Top Commenters",
	'noff_widget_titlepre' => "h2",
	'noff_widget_ulclass' => "links",
	'noff_widget_exclude_user' => "1"
	);

$noff_settings = get_option('noff_settings');

//Add the actions/filters
add_filter('get_comment_author_link', 'remove_nofollow_author', 11);
add_filter('comment_text', 'remove_nofollow_text');
add_action('comment_form', 'addnofflink');
add_action("widgets_init", "add_authors_top_ten_init");

 // This function add the NFollow Free sentence to every posts
function addnofflink() {
global $noff_settings, $single, $feed, $post;
include(dirname(__FILE__).'/lang/lang-'.$noff_settings['noff_lang'].'.php');

        if(!$feed && $single ){
                if($noff_settings['comment_link'] == "yes") {
                        echo "<p style=\"margin-top: 15px;\">"._TEXTLINK."</p>";
		}
        }
}

// This is the Widget
function add_authors_top_ten_init() { 
	if (!function_exists('register_sidebar_widget')) 
		return;

	function add_authors_top_ten($args) {
		global $wpdb;
		$noff_settings = get_option('noff_settings');
	
		extract($args);
	
		if ($noff_settings['noff_widget_limit'] != "") $limit = $noff_settings['noff_widget_limit']; else $limit = 10;
		if ($noff_settings['noff_widget_title'] != "") $title = $noff_settings['noff_widget_title']; else $title = "Top Commenters";
		if ($noff_settings['noff_widget_ulclass'] != "") $ulclass = $noff_settings['noff_widget_ulclass']; else $ulclass = "links";
		if ($noff_settings['noff_widget_exclude_user'] == "1") $noff_where = "AND user_id=0";
			
		$querystr = "SELECT comment_author, comment_author_url, COUNT(*) as num_comments FROM ".$wpdb->comments." WHERE comment_approved!='spam' AND comment_type!='trackback' AND comment_type!='pingback' $noff_where GROUP BY comment_author_email ORDER BY num_comments DESC LIMIT $limit";
		$results = $wpdb->get_results($querystr);
	
	        print $before_widget;
		print $before_title . $title . $after_title;
		print "<ul class='$ulclass'>";
		foreach ($results as $row) {
			if ($row->comment_author_url != "") $url = "<a href='".$row->comment_author_url."'>".$row->comment_author."</a>"; else $url = $row->comment_author;
			print "<li>".$url." (".$row->num_comments.")</li>";
		}
		print "</ul>";
		print $after_widget;
	}
	
	// Widget admin control
	function add_authors_top_ten_control() {
		$noff_settings = get_option('noff_settings');
		
		if (isset($_POST['noff-submit']))
		{
			$noff_settings['noff_widget_limit'] = $_POST['noff_widget_limit'];
			$noff_settings['noff_widget_title'] = $_POST['noff_widget_title'];
			$noff_settings['noff_widget_ulclass'] = $_POST['noff_widget_ulclass'];
			$noff_settings['noff_widget_exclude_user'] = $_POST['noff_widget_exclude_user'];
			
			update_option('noff_settings', $noff_settings);
			$noff_settings = get_option('noff_settings');
		}
		
		if ($noff_settings['noff_lang'] == "") $noff_lang = "en"; else $noff_lang = $noff_settings['noff_lang'];
		include(dirname(__FILE__).'/lang/lang-'.$noff_lang.'.php');
	
		?>
		<div style="text-align:left">
	        <h3>NoFollow Free - Top Commenters Widget</h3>
	        <p style="text-align:left"><input type="text" name="noff_widget_limit" size="3" value="<?php echo $noff_settings['noff_widget_limit'] ?>" /> How many top commenters to show (default 10)</p>
	        <p style="text-align:left"><input type="text" name="noff_widget_title" size="20" value="<?php echo $noff_settings['noff_widget_title'] ?>" /> Title for the widget (default "Top Commenters")</p>
	        <p style="text-align:left"><input type="text" name="noff_widget_ulclass" size="3" value="<?php echo $noff_settings['noff_widget_ulclass'] ?>" /> What style sheet class for &lt;ul&gt; attribute (default "links")</p>
	        <p style="text-align:left"><input type="checkbox" name="noff_widget_exclude_user" value="1" <?php if ($noff_settings['noff_widget_exclude_user'] == 1) echo "CHECKED"; ?> /> Exclude registered users (default CHECKED)</p>
	        <p style="text-align:left"><input type="hidden" name="noff-submit" id="noff-submit" value="1" /> </p>
	        </div>
		<?php
	}

	if(function_exists('register_sidebar_widget')) {
		register_sidebar_widget(__('Top Ten Comments'), 'add_authors_top_ten'); 
		register_widget_control(array('Top Ten Comments', 'widgets'), 'add_authors_top_ten_control', 600, 200);
	}
} 

// This function remove the nofollow from author's link
function remove_nofollow_author($nofollow) {
global $noff_settings, $wpdb, $comment;
	if ($noff_settings['blacklist'] != '') {
		$words = explode(',', $noff_settings['blacklist']);
		foreach ($words as $word) {
			if (@eregi($word, $comment->comment_content) || @eregi($word, $comment->comment_author)) {
				$block = 1;
			}
		}
	}
	
	if ($noff_settings['blacklist_email'] != '') {
		$bemail = explode(',', $noff_settings['blacklist_email']);
		foreach ($bemail as $email) {
			if (@eregi($email, $comment->comment_author_email)) {
				$block = 1;
			}
		}
	}
	
	// Count comments per authors
	$queryString="SELECT COUNT(*) as comments FROM ".$wpdb->comments." WHERE comment_author='".$wpdb->escape($comment->comment_author)."'";
	$comments_count = $wpdb->get_var($queryString);
	// Show comments count per authors
	if ($noff_settings['count_comments'] == 'yes') {
		if ($comment->comment_type != "pingback" && $comment->comment_type != "trackback" && !is_admin()) {
			$nofollow .= " (".$comments_count." comments)";
		}
	}

	$queryStringUser="SELECT user_email FROM ".$wpdb->users." WHERE user_email='".$wpdb->escape($comment->comment_author_email)."'";
	$registered_user = $wpdb->get_var($queryStringUser);
	if ($comment->comment_author_email == "$registered_user" && $noff_settings['nofollow_reg_author'] == "author_reg_yes" && $block != 1) {
		if ($comments_count >= $noff_settings['nofollow_reg_limit'] || $noff_settings['nofollow_limit'] == 0) {
			//$nofollow = preg_replace("/rel='external nofollow'>/","rel='external'>", $nofollow);
			$nofollow = preg_replace("/rel=\'external nofollow/","rel='external", $nofollow);
			return $nofollow;
		} else {
			return $nofollow;
		}
	} else {
		if ($noff_settings['nofollow_author'] == 'author_yes' && $block != 1) {
			if ($comments_count >= $noff_settings['nofollow_limit'] || $noff_settings['nofollow_limit'] == 0) {
				$nofollow = preg_replace("/external nofollow/","external", $nofollow);
				return $nofollow;
			} else {
				return $nofollow;
			}
		} else {
			return $nofollow;
		}
	}
}

// This function remove the nofollow from body comment's link
function remove_nofollow_text($nofollow = '') {
global $noff_settings, $wpdb, $comment;
	if ($noff_settings['blacklist'] != '') {
		$words = explode(',', $noff_settings['blacklist']);
		foreach ($words as $word) {
			if (@eregi($word, $comment->comment_content)) {
				$block_text = 1;
			}
		}
	}
	$queryString="SELECT COUNT(1) as comments FROM ".$wpdb->comments." WHERE comment_author_email='".$wpdb->escape($comment->comment_author_email)."'";
	$comments_count = $wpdb->get_var($queryString);
	$queryStringUser="SELECT user_email FROM ".$wpdb->users." WHERE user_email='".$wpdb->escape($comment->comment_author_email)."'";
	$registered_user = $wpdb->get_var($queryStringUser);
	if ($comment->comment_author_email == "$registered_user" && $noff_settings['nofollow_reg_text'] == "text_reg_yes" && $block != 1) {
		if ($comments_count >= $noff_settings['nofollow_reg_limit'] || $noff_settings['nofollow_limit'] == 0) {
			$nofollow = preg_replace("/(<a[^>]*[^\s])(\s*nofollow\s*)/i", "$1", $nofollow);
			$nofollow = preg_replace("/(<a[^>]*[^\s])(\s*rel=\"\s*\")/i", "$1", $nofollow);
			$pattern='/" rel="nofollow">/';
			$replacement='" >';
			$nofollow = preg_replace($pattern, $replacement,$nofollow);
		return $nofollow;
		} else {
			return $nofollow;
		}
	} else {
		if ($noff_settings['nofollow_text'] == 'text_yes' && $block_text != 1) {
			if ($comments_count >= $noff_settings['nofollow_limit'] || $noff_settings['nofollow_limit'] == 0) {
				$nofollow = preg_replace("/(<a[^>]*[^\s])(\s*nofollow\s*)/i", "$1", $nofollow);
				$nofollow = preg_replace("/(<a[^>]*[^\s])(\s*rel=\"\s*\")/i", "$1", $nofollow);
				$pattern='/" rel="nofollow">/';
				$replacement='" >';
				$nofollow = preg_replace($pattern, $replacement,$nofollow);
				return $nofollow;
			} else {
				return $nofollow;
			}
		} else {
			return $nofollow;
		}
	}
}

/* Load CSS into WP header */
add_action('wp_head', 'noff_css');

function noff_css() {
global $noff_settings;
	if (!is_feed() && !is_admin()) {
		$siteurl =  get_option('siteurl');
		$lastdir = get_last_dir(dirname(__FILE__));
		if ($noff_settings['display_band'] == 'yes') {
			$url_band = $noff_settings['url_band'];
			$position = $noff_settings['position_band'];
			$colour = $noff_settings['colour_band'];
			if ($noff_settings['position_band'] == 'left') {
				$noff_css = '
	<style type="text/css" media="screen">
	a#ribbon {
	position: absolute;
	top: 0px;
	left: 0px;
	display: block;
	width: 129px;
	height: 129px;
	background: transparent url("'.$siteurl.'/wp-content/plugins/'.$lastdir.'/images/css_nofollow_badge'.$colour.$position.'.gif") no-repeat top left;
	text-indent: -999em;
	text-decoration: none;
	z-index: 1000;
	}
	</style>';
			} else {
				$noff_css = '
	<style type="text/css" media="screen">
	a#ribbon {
	position: absolute;
	top: 0px;
	right: 0px;
	display: block;
	width: 129px;
	height: 129px;
	background: transparent url("'.$siteurl.'/wp-content/plugins/'.$lastdir.'/images/css_nofollow_badge'.$colour.$position.'.gif") no-repeat top left;
	text-indent: -999em;
	text-decoration: none;
	z-index: 1000;
	}
	</style>';
			}

		print $noff_css."\n";
		}
        }
}



function get_last_dir($path){
    $path = str_replace('\\', '/', $path); 
    $path = preg_replace('/\/+$/', '', $path);
    $path = explode('/', $path);
    $l = count($path)-1;
    return isset($path[$l]) ? $path[$l] : '';
}

// Add the image top band
function noff() {
global $noff_settings;
include(dirname(__FILE__).'/lang/lang-'.$noff_settings['noff_lang'].'.php');

	if (!is_feed() && !is_admin()) {
		$siteurl =  get_option('siteurl');
		if ($noff_settings['display_band'] == 'yes') {
			$url_band = $noff_settings['url_band'];
			$position = $noff_settings['position_band'];
			$colour = $noff_settings['colour_band'];
			if ($noff_settings['position_band'] == 'left') {
				$noff = '<a id="ribbon" href="'.$url_band.'" title="'._BANDTXT.'">'._BANDTXT.'</a>';
			} else {
				$noff = '<a id="ribbon" href="'.$url_band.'" title="'._BANDTXT.'">'._BANDTXT.'</a>';
			}

		print $noff."\n";
		}
        }
}

//add_action('get_footer', 'noff');

add_option('noff_settings', $noffdata, 'Options for NoFollowFree');

add_action('admin_menu', 'add_noff_options_page');

function add_noff_options_page()
{
	if (function_exists('add_options_page'))
	{
		add_options_page('NoFollowFree', 'NOFF', 8, basename(__FILE__), 'noff_options_subpanel');
	}
}

function noff_options_subpanel()
{
	global $_POST;
	$noff_settings = get_option('noff_settings');
	
	if (isset($_POST['submit']))
	{
		$noff_settings['nofollow_reg_author'] = $_POST['nofollow_reg_author'];
		$noff_settings['nofollow_reg_text'] = $_POST['nofollow_reg_text'];
		$noff_settings['nofollow_author'] = $_POST['nofollow_author'];
		$noff_settings['nofollow_text'] = $_POST['nofollow_text'];
		$noff_settings['display_band'] = $_POST['display_band'];
		$noff_settings['position_band'] = $_POST['position_band'];
		$noff_settings['colour_band'] = $_POST['colour_band'];
		$noff_settings['url_band'] = $_POST['url_band'];
		$noff_settings['nofollow_limit'] = $_POST['nofollow_limit'];
		$noff_settings['nofollow_reg_limit'] = $_POST['nofollow_reg_limit'];
		$noff_settings['count_comments'] = $_POST['count_comments'];
		$noff_settings['blacklist'] = $_POST['blacklist']; 
		$noff_settings['blacklist_email'] = $_POST['blacklist_email']; 
		$noff_settings['comment_link'] = $_POST['comment_link'];
		$noff_settings['noff_lang'] = $_POST['noff_lang'];
		
		update_option('noff_settings', $noff_settings);
	}
	
	if ($noff_settings['noff_lang'] == "") $noff_lang = "en"; else $noff_lang = $noff_settings['noff_lang'];
	include(dirname(__FILE__).'/lang/lang-'.$noff_lang.'.php');

	?>
	<div class="wrap">
        <h2>NoFollow Free</h2>
	<p>If you would like to use the Top Commenters widget you can find it in <a href="/wp-admin/widgets.php">Presentation -> Widgets</a> menu</p>
        <form action="" method="post">
	<h4><?php print _SELLANG; ?></h4>
	<select name="noff_lang">
	<option value="ar" <?php if ($noff_settings['noff_lang'] == "ar") print "SELECTED"; ?>>Arabic</option>
	<option value="by" <?php if ($noff_settings['noff_lang'] == "by") print "SELECTED"; ?>>Belarusian</option>
	<option value="cn" <?php if ($noff_settings['noff_lang'] == "cn") print "SELECTED"; ?>>Chinese</option>
	<option value="hr" <?php if ($noff_settings['noff_lang'] == "hr") print "SELECTED"; ?>>Croatian</option>
	<option value="cz" <?php if ($noff_settings['noff_lang'] == "cz") print "SELECTED"; ?>>Czech</option>
	<option value="da" <?php if ($noff_settings['noff_lang'] == "da") print "SELECTED"; ?>>Danish</option>
	<option value="nl" <?php if ($noff_settings['noff_lang'] == "nl") print "SELECTED"; ?>>Dutch</option>
	<option value="en" <?php if ($noff_settings['noff_lang'] == "en") print "SELECTED"; ?>>English</option>
	<option value="fi" <?php if ($noff_settings['noff_lang'] == "fi") print "SELECTED"; ?>>Finnish</option>
	<option value="fr" <?php if ($noff_settings['noff_lang'] == "fr") print "SELECTED"; ?>>French</option>
	<option value="de" <?php if ($noff_settings['noff_lang'] == "de") print "SELECTED"; ?>>German</option>
	<option value="hu" <?php if ($noff_settings['noff_lang'] == "hu") print "SELECTED"; ?>>Hungarian</option>
	<option value="jp" <?php if ($noff_settings['noff_lang'] == "jp") print "SELECTED"; ?>>Japanese</option>
	<option value="id" <?php if ($noff_settings['noff_lang'] == "id") print "SELECTED"; ?>>Indonesian</option>
	<option value="it" <?php if ($noff_settings['noff_lang'] == "it") print "SELECTED"; ?>>Italian</option>
	<option value="lv" <?php if ($noff_settings['noff_lang'] == "lv") print "SELECTED"; ?>>Latvian</option>
	<option value="mt" <?php if ($noff_settings['noff_lang'] == "mt") print "SELECTED"; ?>>Maltese</option>
	<option value="no" <?php if ($noff_settings['noff_lang'] == "no") print "SELECTED"; ?>>Norwegian</option>
	<option value="pl" <?php if ($noff_settings['noff_lang'] == "pl") print "SELECTED"; ?>>Polish</option>
	<option value="pt" <?php if ($noff_settings['noff_lang'] == "pt") print "SELECTED"; ?>>Portuguese</option>
	<option value="ro" <?php if ($noff_settings['noff_lang'] == "ro") print "SELECTED"; ?>>Romanian</option>
	<option value="ru" <?php if ($noff_settings['noff_lang'] == "ru") print "SELECTED"; ?>>Russian</option>
	<option value="sr" <?php if ($noff_settings['noff_lang'] == "sr") print "SELECTED"; ?>>Serbian</option>
	<option value="es" <?php if ($noff_settings['noff_lang'] == "es") print "SELECTED"; ?>>Spanish</option>
	<option value="se" <?php if ($noff_settings['noff_lang'] == "se") print "SELECTED"; ?>>Swedish</option>
	<option value="tr" <?php if ($noff_settings['noff_lang'] == "tr") print "SELECTED"; ?>>Turkish</option>
	<option value="uk" <?php if ($noff_settings['noff_lang'] == "uk") print "SELECTED"; ?>>Ukrainian</option>
	</select>
	<p><?php print _OPTREMOVE; ?></p>
        <h4><?php print _OPTFROM; ?></h4>
        <p><input type="checkbox" name="nofollow_author" value="author_yes" <?php if ($noff_settings['nofollow_author'] == 'author_yes') echo 'checked="checked"'; ?> /> <?php print _AUTHORLINK; ?></p>
        <p><input type="checkbox" name="nofollow_text" value="text_yes" <?php if ($noff_settings['nofollow_text'] == 'text_yes') echo 'checked="checked"'; ?> /> <?php print _COMTEXTLINK; ?></p>
        <p><input type="checkbox" name="nofollow_reg_author" value="author_reg_yes" <?php if ($noff_settings['nofollow_reg_author'] == 'author_reg_yes') echo 'checked="checked"'; ?> /> <?php print _REGAUTHORLINK; ?></p>
        <p><input type="checkbox" name="nofollow_reg_text" value="text_reg_yes" <?php if ($noff_settings['nofollow_reg_text'] == 'text_reg_yes') echo 'checked="checked"'; ?> /> <?php print _REGCOMTEXTLINK; ?></p>
        <h4><?php print _SETNUMAUTH; ?></h4>
	<p><?php print _SETNUMZERO; ?> <input type="text" name="nofollow_limit" size="3" value="<?php echo $noff_settings['nofollow_limit'] ?>" /></p>
        <h4><?php print _SETNUMREGAUTH; ?></h4>
	<p><?php print _SETNUMZERO; ?> <input type="text" name="nofollow_reg_limit" size="3" value="<?php echo $noff_settings['nofollow_reg_limit'] ?>" /></p>
        <h4><?php print _SETBLACKLIST; ?></h4>
	<textarea rows="4" cols="80" name="blacklist"><?php echo htmlentities($noff_settings['blacklist']); ?></textarea>
		<h4><?php print _SETBLACKLIST_EMAIL; ?></h4>
	<textarea rows="4" cols="80" name="blacklist_email"><?php echo htmlentities($noff_settings['blacklist_email']); ?></textarea>
        <h4><?php print _SETNUMCOM; ?></h4>
        <p><input type="radio" name="count_comments" value="yes" <?php if ($noff_settings['count_comments'] == 'yes') echo 'checked="checked"'; ?> /> <?php print _YES; ?></p>
        <p><input type="radio" name="count_comments" value="no" <?php if ($noff_settings['count_comments'] == 'no') echo 'checked="checked"'; ?> /> <?php print _NO; ?></p>
        <h4><?php print _DONATELINK; ?></h4>
        <p><input type="radio" name="comment_link" value="yes" <?php if ($noff_settings['comment_link'] == 'yes') echo 'checked="checked"'; ?> /> <?php print _YES; ?></p>
        <p><input type="radio" name="comment_link" value="no" <?php if ($noff_settings['comment_link'] == 'no') echo 'checked="checked"'; ?> /> <?php print _NO2; ?><p>

	<hr>
	<p><?php print _INSERTFUNC; ?></p>
	<blockquote>
	<p>&lt;body&gt;<br />
	<strong>&lt;?php if (function_exists(noff())) noff(); ?&gt;</strong><br />
	...
	</p>
	</blockquote>
	<p><?php print _OPTBAND; ?></p>
        <h4><?php print _TOPBAND; ?></h4>
        <p><input type="radio" name="display_band" value="yes" <?php if ($noff_settings['display_band'] == 'yes') echo 'checked="checked"'; ?> /> <?php print _YES; ?></p>
        <p><input type="radio" name="display_band" value="no" <?php if ($noff_settings['display_band'] == 'no') echo 'checked="checked"'; ?> /> <?php print _NO; ?></p>
	<?php if ($noff_settings['display_band'] == 'yes') { 
		if ($noff_settings['position_band'] == '') {
			$noff_settings['position_band'] = 'right';
		}
		if ($noff_settings['colour_band'] == '') {
			$noff_settings['colour_band'] = 'green';
		}
	?>
        <h4><?php print _POSBAND; ?></h4>
        <p><input type="radio" name="position_band" value="right" <?php if ($noff_settings['position_band'] == 'right') echo 'checked="checked"'; ?> /> <?php print _RIGHT; ?></p>
        <p><input type="radio" name="position_band" value="left" <?php if ($noff_settings['position_band'] == 'left') echo 'checked="checked"'; ?> /> <?php print _LEFT; ?></p>
        <h4><?php print _COLBAND; ?></h4>
        <p><input type="radio" name="colour_band" value="green" <?php if ($noff_settings['colour_band'] == 'green') echo 'checked="checked"'; ?> /> <?php print _GREEN; ?></p>
        <p><input type="radio" name="colour_band" value="red" <?php if ($noff_settings['colour_band'] == 'red') echo 'checked="checked"'; ?> /> <?php print _RED; ?></p>
        <p><input type="radio" name="colour_band" value="orange" <?php if ($noff_settings['colour_band'] == 'orange') echo 'checked="checked"'; ?> /> <?php print _ORANGE; ?></p>
        <p><input type="radio" name="colour_band" value="blue" <?php if ($noff_settings['colour_band'] == 'blue') echo 'checked="checked"'; ?> /> <?php print _BLUE; ?></p>
        <h4><?php print _URLBAND; ?></h4>
        <p><input type="text" name="url_band" size="60" value="<?php echo $noff_settings['url_band'] ?>" /></p>
	<?php } ?>
        <p><input type="submit" name="submit" value="Save Settings" /></p>
        </form>
	<hr>
	<p><?php print _CHECKUP; ?></p>
        </div>
	<?php
}
?>
