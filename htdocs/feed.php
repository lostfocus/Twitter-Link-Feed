<?php
require_once('../includes/twitteroauth.php');
require_once('../config/config.php');

$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, ACCESS_TOKEN, ACCESS_SECRET);

$params = array('include_entities' => 'true','count' => 200);
$content = $connection->get('statuses/home_timeline',$params);

// TODO: Fail more gracefully
if(!is_array($content)) die();
header("Content-Type: application/atom+xml; charset=UTF-8");
echo '<?xml version="1.0" encoding="utf-8"?>'."\n";
?>
<feed xmlns="http://www.w3.org/2005/Atom">
	<title>Links from your Twitter account.</title>
	<link href="https://twitter.com/" />
	<id>https://twitter.com/#<?php echo md5(CONSUMER_KEY . CONSUMER_SECRET) ?></id>
	<updated><?php echo date("c") ?></updated>
	<author>
		<name>Dominik Schwind</name>
		<email>dschwind@lostfocus.de</email>
	</author>
<?php
foreach($content as $tweet){
	if(count($tweet->entities->urls) > 0){
		$tweet->text = str_replace("<","&lt;",str_replace("&","&amp;",$tweet->text));
		$content = 		"<h1><a href='https://twitter.com/%s'>%s</a></h1>\n";
		$content .= 	"<img src='%s' alt='%s' /><br />\n";
		$content .=		"%s<br />\n";
		$content = sprintf($content,
			$tweet->user->screen_name, $tweet->user->name,
			$tweet->user->profile_image_url, $tweet->user->name,
			$tweet->text
			);
		foreach($tweet->entities->urls as $url){
			$expanded_url = $url->url;
			$display_url = $url->url;
			if(isset($url->expanded_url) && ($url->expanded_url != NULL)) $expanded_url = $url->expanded_url;
			if(isset($url->display_url) && ($url->display_url != NULL)) $display_url = $url->display_url;
			$content .= sprintf("<a href='%s'>%s</a><br />\n",$expanded_url, $display_url);
		}
		
	?>
		<entry>
			<title><?php echo $tweet->user->screen_name; ?> (<?php echo $tweet->user->name; ?>)</title>
			<link href="<?php printf("https://twitter.com/%s/status/%s",$tweet->user->screen_name,$tweet->id_str); ?>" />
			<id><?php printf("https://twitter.com/%s/status/%s",$tweet->user->screen_name,$tweet->id_str); ?></id>
			<updated><?php echo date("c",strtotime($tweet->created_at)) ?></updated>
			<content type="xhtml">
				<div xmlns="http://www.w3.org/1999/xhtml">
					<?php echo $content; ?>
				</div>
			</content>
			<summary><?php echo strip_tags($content); ?></summary>
		</entry>
<?php
	}
}
?>
</feed>