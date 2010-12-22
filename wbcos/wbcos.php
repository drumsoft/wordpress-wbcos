<?php
/*
Plugin Name: Wait Before Comment or Spam
Plugin URI: http://drumsoft.com/wp/wbcos
Description: Require users(or spammers) wait before each comments.
Version: 1.0
Author: Haruka Kataoka
Author URI: http://drumsoft.com/
*/
/*
about:
	a comment spam filter.
	This plugin makes users to wait N seconds to post comments after they page loads.
	NOTE: when this plugin is activated, Javascript required to post comments.
	
	Following reasons, this plugin is considered effective.
	1. Most spammer's comment spam tools support no Javascript.
	2. Spammers cannot wait N seconds, because they want to post a large number of spams.
	3. Normal users takes more than N seconds to write a comment.

usage:
	1.	Install the plugin.
	2.	Configure settings below.
		(wbcos_secret1 and wbcos_secret2 must be changed.)
	3.	Activate the plugin.
	4.	Add a tag below in your "wp-content/themes/YOURTHEMENAME/comments.php".
		<?php wbcos_echo_keys_field() ?>
		The tag must be in comment post form.
	5.	Post test comments.


Licence: GPL (2 or later)

Wait Before Comment or Spam - A wordpress plugin require users(or spammers) wait before each comments.
Copyright (C) 2010 Haruka Kataoka

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/

// ---------------- settings
$wbcos_secret1 = '508|=j7tJtl@`c-Q'; // replace this with your random key string
$wbcos_secret2 = '}Uw0a&..xzlv ~v!'; // replace this with your random key string
$wbcos_span = 12 * 60 * 60; // TTL of keys (seconds)
$wbcos_sleep = 5; // required time to users wait (seconds)
$wbcos_waitmessage = "wait a moment.."; 


// ---------------- 

$wbcos_callasresponder = array_key_exists('wbcos_request_challenge', $_GET);

if (!$wbcos_callasresponder) {
	$wbcos_url = get_settings('siteurl') . '/wp-content/plugins/wbcos/'; //the url of this plugin
	$wbcos_js  = $wbcos_url . 'wbcos.js';
	$wbcos_php = $wbcos_url . 'wbcos.php';
}

$wbcos_now = time();
$wbcos_first = true;

/*<?php wbcos_echo_keys_field() ?>*/
function wbcos_echo_keys_field() {
	global $wbcos_now;
	global $wbcos_first;
	global $wbcos_js;
	global $wbcos_php;
	global $wbcos_waitmessage;
	echo '<input type="hidden" name="wbcos_comment_time" value="' . $wbcos_now . '">';
	echo '<input type="hidden" name="wbcos_comment_challenge" value="' . wbcos_get_challenge($wbcos_now) . '">';
	echo '<input type="hidden" name="wbcos_comment_response" value="">';
	echo '<input type="hidden" name="wbcos_comment_requesturi" value="' . $wbcos_php . '">';
	echo '<input type="hidden" name="wbcos_comment_waitmessage" value="' . $wbcos_waitmessage . '">';
	if ($wbcos_first) {
		echo '<script type="text/javascript" src="' . $wbcos_js . '"></script>';
		$wbcos_first = false;
	}
}

function wbcos_get_challenge($tm) {
	global $wbcos_secret1;
	return sha1($wbcos_secret1 . $tm);
}

function wbcos_get_response($challenge) {
	global $wbcos_secret2;
	return sha1($wbcos_secret2 . $challenge);
}

function wbcos_test_keys($tm, $challenge, $response) {
	global $wbcos_span;
	global $wbcos_now;
	return	$challenge == wbcos_get_challenge($tm) 
		and	$response == wbcos_get_response($challenge)
		and	$tm < $wbcos_now and $wbcos_now - $tm < $wbcos_span;
}

function wbcos_hook_post_comment($id) {
	if (!wbcos_test_keys($_POST['wbcos_comment_time'], $_POST['wbcos_comment_challenge'], $_POST['wbcos_comment_response'])){
		wp_die('Comment post failed. Please set Javascript enable.', 'Comment post failed.');
	}
}

if ( $wbcos_callasresponder ) {
	$tm = $_GET['wbcos_request_time'];
	$challenge = $_GET['wbcos_request_challenge'];
	if ($challenge == wbcos_get_challenge($tm)) {
		sleep($wbcos_sleep);
		echo wbcos_get_response($challenge);
	}else{
		header("HTTP/1.0 400 Bad Request", FALSE);
		echo 'request not supported';
	}
}else{
	add_action ('pre_comment_on_post', 'wbcos_hook_post_comment');
}
?>
