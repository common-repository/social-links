<?php
/*
Plugin Name: Social Links
Plugin URI: http://blog.maybe5.com/?page_id=94
Description: Social Links is a sidebar widget that displays icon links to your profile pages on other social networking sites.
Author: Kareem Sultan
Version: 1.1.1
Author URI: http://blog.maybe5.com

/*  Copyright 2008  Kareem Sultan  (email : kareemsultan@gmail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

http://www.gnu.org/licenses/gpl.txt

*/

 define('sl_db_version','2.0');
		
 //$sl_db_version = "1.0";
 $PLUGIN_VERSION = 1.1;
 //$DB_VERSION = 2.0;
 $plugindir = get_settings('home').'/wp-content/plugins/'.dirname(plugin_basename(__FILE__));
 $pluginrelativedir = '/wp-content/plugins/'.dirname(plugin_basename(__FILE__));


   //Only used to upgrade from versions prior to 2.0
 $definitions = array(
	 array(0,'facebook.png','%userid%','Enter your complete Facebook profile URL','Facebook'),
	 array(1,'myspace.png','%userid%','Enter your complete MySpace URL.','MySpace'),
	 array(2,'linkedin.png','%userid%','Enter your complete LinkedIn URL.','LinkedIn'),
  	 array(3,'picasa.png','http://picasaweb.google.com/%userid%','Enter your Picasa(Google) username.','Picasa Web Album'),
	 array(4,'flickr.png','http://flickr.com/photos/%userid%','Enter your flickr username','Flickr'),
	 array(5,'youtube.png','http://www.youtube.com/%userid%','Enter your YouTube username','YouTube'),
	 array(6,'twitter.png','http://twitter.com/%userid%','Enter your Twitter username','Twitter'),
	 array(7,'pownce.png','http://pownce.com/%userid%','Enter your Pownce username','Pownce'),
	 array(8,'plurk.png','http://www.plurk.com/user/%userid%','Enter your Plurk username','Plurk'),
	 array(9,'digg.png','http://www.digg.com/users/%userid%','Enter your Digg username.','Digg'),
	 array(10,'delicious.png','http://delicious.com/%userid%','Enter your Delicious username','Delicious'),
	 array(11,'blogmarks.png','http://blogmarks.net/user/%userid%','Enter your BlogMarks username.','BlogMarks'),
	 array(12,'stumbleupon.png','http://%userid%.stumbleupon.com','Enter your Stumble Upon username','Stumble Upon'),
     array(13,'lastfm.png','http://www.last.fm/user/%userid%','Enter your Last.fm username','Last.fm'),
	 array(14,'amazon.png','%userid%','Enter your complete Amazon Wishlist URL','Amazon Wishlist'),
	 array(15,'blog.png','%userid%','Enter the complete blog URL.','Blog'),
	 array(16,'jeqq.png','http://www.jeqq.com/user/view/profile/%userid%','Enter your Jeqq username','Jeqq'),
   	 array(17,'dapx.png','%userid%','Enter your complete Dapx URL.','Dapx'),
	 array(18,'xing.jpg','%userid%','Enter your complete Xing URL.','Xing'),
	 array(19,'sixent.png','http://%userid%.sixent.com/','Enter your Sixent username','Sixent'),
	 array(20,'technorati.jpg','http://technorati.com/people/technorati/%userid%/','Enter your Technorati username.','Technorati'),
	 array(21,'friendfeed.png','http://friendfeed.com/%userid%','Enter your FriendFeed username.','FriendFeed')
   );


//call install function upon activation
register_activation_hook(__FILE__,'social_links_install');
register_deactivation_hook(__FILE__,'social_links_uninstall');


function social_links_wrapper(){

   // This only works if the widget api is installed
   if ( !function_exists('register_sidebar_widget') || !function_exists('register_widget_control') )
   	return; // ...and if not, exit gracefully from the script.
		
		// Displays the icons in the sidebar
		function widget_social_links($args) {

			global $PLUGIN_VERSION;
			
			extract($args);

			$options = get_option('widget_social_links');
			$title = empty($options['title']) ? 'Social Links' : $options['title'];
			$width =  empty($options['width']) ? 20 : $options['width'];

				echo $before_widget;
				echo $before_title . $title . $after_title ;

			echo '<!-- Social Links Version: '. $PLUGIN_VERSION .' -->';
			echo "<div id='socialLinksContainer' style='width:$width"."px;'>";
			echo generateSocialLinksInnerHTML();
			echo '</div>';
			echo $after_widget;

		}

	//Config Panel
	function widget_social_links_control() {

		$options = get_option('widget_social_links');

        
         if ( $_POST['social-links-submit'] ) {


				// Clean up control form submission options
				$newoptions['title'] = strip_tags(stripslashes($_POST['social-links-title']));
				$newoptions['width'] = strip_tags(stripslashes($_POST['social-links-width']));
   	      $newoptions['openInNewWindow'] = $_POST['social-links-openInNewWindow'];




				if ( $options != $newoptions ) {
					$options = $newoptions;
					update_option('widget_social_links', $options);
				}
			}

			$title = empty($options['title']) ? 'Social Links' : $options['title'];
			$width = empty($options['width']) ? 100 : $options['width'];
			$openInNewWindow = empty($options['openInNewWindow']) ? false : true;


			?>

				<table>
					<tr><td>
						<label for="social-links-title">Widget title: <input type="text" id="social-links-title" name="social-links-title" value="<?php echo $title; ?>" /></label>
					</td></tr>
					<tr><td>
						<label for="social-links-width">Width: <input type="text" id="social-links-width" name="social-links-width" style="width:25px;" value="<?php echo $width; ?>" /> pixels</label>
					</td></tr>

             <tr><td>
               <label for="social-links-openInNewWindow">
                 <input type="checkbox" id="social-links-openInNewWindow" name="social-links-openInNewWindow" <?php if($openInNewWindow){echo "checked='true'";} ?>/>
               Open links in new window
               </label>
             </td></tr>

        </table>


				<input type="hidden" name="social-links-submit" id="social-links-submit" value="1" />

			<?php
		}//End of widget_social_links_control


		function wp_ajax_social_links_add_network(){
			// read submitted information
   		$icon = $_POST['icon'];
			$url = $_POST['url'];
			$displayname = $_POST['displayname'];
			$messageId = $_POST['responseDiv'];

			$result = insertNetwork($icon,$url,$displayname);
			if($result == 1)
		 		$result = 'Link added.';
		 	else
		 		$result = "There was a problem adding the link. Refresh the page and try again.";

		 	$innerHTML = generateSocialLinksPreviewInnerHTML('');
			die('
				$("message").innerHTML = "'.$result.'";
				$("message").className="updated fade";
				$("message").style.visibility = "visible";
				document.getElementById("displayDiv").innerHTML = "'.$innerHTML.'";
				createSortables();


			');

			//Add this line to the above javascript to show the complete table.
			//document.getElementById("editDiv").innerHTML = "' . generateSocialLinksEditInnerHTML(). '";
		}

		//TODO: Implement the add ajax process to send data and let the javascript add child elements
		//This is to avoid using innerHTML replacement and will then allow for more advanced client side effects
	/*	function wp_ajax_social_links_add_network_send_data(){
			// read submitted information
			global $definitions;

			$selectedIndex = $_POST['networkIndex'];
			$data = $_POST['value'];

			$result = insertNetwork($selectedIndex,$data);
			$data = generateSocialLinksData();
			//$result = 'fake insert';
			die("$result
				$('message').innerHTML = 'Database result is $result.';
				$('message').class='updated fade';
				$('message').style.visibility = 'visible';
				updateSocialLinks($data);
			");

		}
		*/
		function wp_ajax_social_links_delete_network(){
			global $wpdb;
		 	global $definitions;

			$linkId = $_POST['linkId'];
		 	social_links_log('deleting linkID='. $linkId,2);
		 	$table_name = $wpdb->prefix . "social_links";
		 	$sql = 'delete from ' .  $table_name . ' where id='.$linkId;
		 	$result = $wpdb->query($wpdb->prepare($sql));

		 	if($result == 1)
		 		$result = 'Removed link.';
		 	else
		 		$result = 'There was a problem deleting the link. Refresh the page and try again.'.$sql;
		 	social_links_log("delete network result: $result",0);
		 	die('
				$("message").innerHTML = "'.$result.'";
				$("message").className="updated fade";
				$("message").style.visibility = "visible";
			');
		}

		 function insertNetwork($icon,$url,$displayname){
		    social_links_log("Inserting new network. $displayname:($icon) -> $url",2);
		 	global $wpdb;

		 	$table_name = $wpdb->prefix . "social_links";
			$sql = "Insert into $table_name (icon,url,display_name,sort_order) VALUES ('".$icon."','".$url."','".$displayname."',1000)";
			social_links_log("SQL : $sql",0);

          $result = $wpdb->query($wpdb->prepare($sql));
          social_links_log("insert network result: $result",0);
		 	return $result;
		 }



		 function getSocialLinks(){
		 	global $wpdb;
		 	$table_name = $wpdb->prefix . "social_links";
		 	$sql = 'Select * from ' .  $table_name . ' order by sort_order';
		 	$results = $wpdb->get_results($sql,ARRAY_A);
		 	return $results;

		 }

		 function generateSocialLinksInnerHTML(){
		 	global $definitions;
		 	global $plugindir;

		 	$options = get_option('widget_social_links');
         $target = empty($options['openInNewWindow']) ? "_self" : "_new";
		 	$rows = getSocialLinks();
		 	if(count($rows)==0)
		 		return;
		 	
		 	foreach ($rows as $row) {

				$siteId = $row['id'];
				$icon = $row['icon'];
				$url = $row['url'];
				$displayname = $row['display_name'];
				
				$innerHTML = $innerHTML . "<a id='link_$siteId' target='$target'  href='$url'><img src='$plugindir/images/icons/$icon' title='".$displayname."' alt='".$displayname."'/></a>";
				if($row != $rows[count($rows)-1]){
					$innerHTML = $innerHTML."\n";
				}
			}

			return $innerHTML;
		 }

		 function generateSocialLinksPreviewInnerHTML($delimiter){
		 	global $definitions;
		 	global $plugindir;

		 	$rows = getSocialLinks();
		 	if(count($rows)==0)
		 		return;
		 	
		 	foreach ($rows as $row) {
		 		$siteId = $row['id'];
				$icon = $row['icon'];
				$url = $row['url'];
				$displayname = $row['display_name'];

		 		$innerHTML = $innerHTML . "<span id='link_$siteId' title='$displayname ($url)'><img style='margin:2px;cursor:move;' src='$plugindir/images/icons/$icon'/></span>";
				if($row != $rows[count($rows)-1]){
					$innerHTML = $innerHTML.$delimiter;
				}
			}

			return $innerHTML;
		 }

		 /*
		 function generateSocialLinksData(){
			global $definitions;

		 	$rows = getSocialLinks();
		 	if(count($rows)==0)
		 		return;
		 	////WPD_print("Found".count($rows)." networks.");
		 	$data = '';
		 	foreach ($rows as $row) {
		 		$linkInfoArray = $definitions[$row[2]];
		 		$data += "link_$row[0],$linkInfoArray[0],$linkInfoArray[1],$linkInfoArray[4]\n";
		 	}
		 	return $data;

		 }
		*/

      function getIconHTML(){
        global $pluginrelativedir;
        $dir_path =  dirname(realpath(__FILE__));
        $dir = $dir_path.'/images/icons';

            $html = '<div>';
           if(is_dir($dir)){

             if($handle = opendir($dir)){
               while(($file = readdir($handle)) !== false){
                  $ext = strrchr($file,'.');
                 if($ext == '.png' || $ext == '.gif' || $ext == '.jpg' || $ext == '.ico'){
                      $html = $html .  "<img src='$pluginrelativedir/images/icons/$file' alt='$file' style='margin:2px;cursor:pointer;' onclick='imageSelected(this)'/>";
                 }
               }
               closedir($handle);
             }
           }
           return $html . '</div>';
      }

		function social_links_admin_menu(){
			global $pluginrelativedir;
		    //add_options_page('Social Links Settings', 'Social Links', 8,$pluginrelativedir.'/edit-sociallinks.php');
		    add_management_page('Social Links Settings', 'Social Links', 8,__FILE__,'widget_social_links_settings');
      }

		function addHeaderCode(){
			
			global $plugindir;
			echo '<link type="text/css" rel="stylesheet" href="' . $plugindir . '/stylesheet.css" />' . "\n";

		}

 			global $plugindir;
			wp_enqueue_script('social-links', $plugindir . '/javascript.js',array('sack'));


 			if(strpos($_SERVER["REQUEST_URI"],'wp-admin')){
 				wp_enqueue_script('scriptaculous');
 			}
			add_action('wp_head','addHeaderCode');



			//Add action to load sub menu
			add_action('admin_menu', 'social_links_admin_menu');




			//Add ajax callback action called from client side javascript
			add_action('wp_ajax_social_links_add_network', 'wp_ajax_social_links_add_network' );
			add_action('wp_ajax_social_links_delete_network', 'wp_ajax_social_links_delete_network' );

			register_sidebar_widget('Social Links', 'widget_social_links');
			register_widget_control('Social Links', 'widget_social_links_control');

	}//End of SocialLinks class

	social_links_log('registering plugin',0);
	add_action('plugins_loaded','social_links_wrapper');

//todo handle auto db table update
function social_links_install(){
	
	global $wpdb;
	
	social_links_log("Installing Social Links Plugin",2);
	 //echo '<div>Activation social links</div>';
	 
	$table_name = $wpdb->prefix . "social_links";
	
  	$installed_ver = (get_option('SOCIAL_LINKS_DB_VERSION') == "") ? 0 : doubleval(get_option('SOCIAL_LINKS_DB_VERSION'));
  	$required_ver = doubleval(sl_db_version);
	//WPD_print('match: '.$match);
	//if($installed_ver < 2){
	//	migrateOldData();
	//}
	social_links_log("Installed db version: $installed_ver  Required db version:$required_ver",2);
	
	if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
		social_links_log('Installing database',2);
		$sql = "CREATE TABLE " . $table_name . " (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		url text,
		display_name varchar(55),
		sort_order int(11) not null DEFAULT 0,
		UNIQUE KEY id (id)
		);";
		
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		$result = dbDelta($sql);
		social_links_log("dbDelta results: <pre>{".print_r($result,true)."}</pre>",2);
		add_option("SOCIAL_LINKS_DB_VERSION", "2.0");

   }else if($installed_ver < $required_ver ) {
		social_links_log('Upgrading database',2);
		$sql = "CREATE TABLE " . $table_name . " (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		network_id int(11),
		user_info varchar(55),
		icon varchar(50),
		url text,
		display_name varchar(55),
		sort_order int(11) not null DEFAULT 0,
		UNIQUE KEY id (id)
		);";
		
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		$result = dbDelta($sql);
		social_links_log("dbDelta results: <pre>{".print_r($result,true)."}</pre>",2);
		update_option("SOCIAL_LINKS_DB_VERSION", strval($required_ver));

   }


 }

function social_links_uninstall(){
	social_links_log("deleting option 'SOCIAL_LINKS_DB_VERSION'",2);
	delete_option('SOCIAL_LINKS_DB_VERSION');
}

function saveSortOrder(){
global $wpdb;
global $message;
global $messageClass;

$sortDataOrder = !empty($_POST['sortOrderData']) ? $_POST['sortOrderData'] : '';
if(!empty($sortDataOrder))
{
	social_links_log("Saving order",2);
	parse_str($sortDataOrder,$newSortorderArray);
	if(count($newSortorderArray) != 0){
		
		$table_name = $wpdb->prefix . "social_links";
		foreach($newSortorderArray["displayDiv"] as $order => $id){
			social_links_log('Order: '.$order.' Value: '.$id,0);
			
			$sql = 'Update ' .  $table_name . ' Set sort_order='.$order.' where id='.$id;
			$result = $wpdb->query($wpdb->prepare($sql));
			social_links_log('Result for '.$sql.' is '.$result,0);
				
		}
		$message = 'Saved links\' order.';
	}
	else{
		$message = 'No items to save.';
	}
	$messageClass = 'updated fade';
}
}

function migrateOldData(){
	global $definitions;
	global $wpdb;
	social_links_log("Migrating old links",2);
  	$installed_ver = doubleval(get_option('SOCIAL_LINKS_DB_VERSION'));
	$required_ver = doubleval(sl_db_version);
	social_links_log("Current version: $installed_ver Required version: $DB_VERSION",2);
	if($installed_ver != $required_ver){
		return -2;
	}
	//$wpdb->show_errors();
	$table_name = $wpdb->prefix . "social_links";
	$rows = getSocialLinks();
			if(count($rows)==0)
				return -1;
			$rowCount = count($rows);
			$updatedCount = 0;
		 	social_links_log("Found".$rowCount." networks.",2);
		   define('KEY_SITE_ID',0);
		   define('KEY_IMAGE',1);
		   define('KEY_URL_TEMPLATE',2);
		   define('KEY_INSTRUCTION',3);
		   define('KEY_DISPLAY_NAME',4);

			foreach ($rows as $row) {

				$network_id = $row['network_id'];
				if($network_id != null){
					$linkInfoArray = $definitions[$network_id ];
					$url = str_replace("%userid%",$row['user_info'],$linkInfoArray[KEY_URL_TEMPLATE]);
					$id = $row['id'];
					$icon = $linkInfoArray[KEY_IMAGE];
					$display_name = $linkInfoArray[KEY_DISPLAY_NAME];
					social_links_log("Old values: id:$id / icon:$icon / display_name:$display_name url:$url",0);
					$sql = "UPDATE $table_name set url=\"".$url."\",icon=\"".$icon."\",display_name=\"".$display_name."\" where id=$id";
					social_links_log("SQL $sql",0);
					social_links_log("prepared SQL".$wpdb->prepare($sql),0);
					$result = $wpdb->query($wpdb->prepare($sql));
					social_links_log('result '.$result,0);
					$updatedCount = $updatedCount + $result;
				}
				
		  }
		  return $updatedCount;

	 //dropFields();
   }
   function dropFields(){

	   /*WPD_print("dropping old version of table");
		$table_name = $wpdb->prefix . "social_links";
		$sql = "ALTER TABLE $table_name DROP COLUMN network_id;";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		$result = $wpdb->query($sql);
		WPD_print("Drop column result: $result");
		*/
   }

   
 /*
	* 0 = Debug: should only be used during development
	* 1 = Notice: for verbosely logging generic and recurring events (e.g. a user has logged in)
	* 2 = Important: events that change something in the system, but are usual (e.g. a post is published)
	* 3 = Warning: events that indicate an exceptional or abnormal behaviour (e.g. a file could not be uploaded or something very important was changed)
	* 4 = Error: events that indicate a fatal abnormal behaviour (e.g. a database error)
	* 5 = Panic: events that indicate a serious damage of the system itself (e.g. cannot connect to the database)
*/
   function social_links_log($message,$level){
	   if (function_exists('wpsyslog'))
		wpsyslog('social-links', $message, $level);
   }
//Administration page
$message;
$messageClass;
function widget_social_links_settings(){
	global $definitions;
	global $message;
	global $messageClass;
	global $plugindir;
	
	//social_links_install();
	if (isset($_POST['saveorder']))
	{
		saveSortOrder();
	}else if (isset($_POST['migrate']))
	{
		$updated = migrateOldData();
		if($updated == -1){
			$message = "There were no existing links to migrate.";
			$messageClass = "success";
		}
		else if($updated == -2){
			$message = "The current social-links database has not been updated. Deactivate and reactivate the plugin and try again.";
			$messageClass = "error";
		}else{
			$message = "$updated links were migrated successfully.";
			$messageClass = "success";
		}
	}
 
	$installed_ver = doubleval(get_option('SOCIAL_LINKS_DB_VERSION'));
  	$required_ver = doubleval(sl_db_version);
	
	$visibility = 'hidden';
	if(!empty($messageClass))
		$visibility = 'visible';
	
	$width = empty($options['width']) ? 100 : $options['width'];

	?>
<!--<span>Current Version: <?php echo $installed_ver?> Required Version: <?php echo $required_ver?></span>-->
<p id="message" class="<?php echo $messageClass;  ?>" style="visibility:<?php echo $visibility;  ?>;width:550px;"><?php echo $message;  ?></p>
<div style="padding:0 20px 10px 20px;">
   <h2>Social Links</h2>
   <form method="post" onSubmit="social_links_ajax_saveOrder()" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
<br/>  
  <div id="addLinkDiv" style="float:left;width:310px;">
         <h3>Add New Social Link</h3>
   
		 <div id="Icons" style="background-color:#E7F4FC;border:solid 1px #D3DEE4;padding:10px;-moz-border-radius:3px;">
            <? echo getIconHTML(); ?>
     	   </div>
		  <br/>
         <label for="selectedIcon" style="width:8em;font-weight:bold;">Icon:</label>
         <img id="selectedIcon" name="selectedIcon" style="visibility:hidden;"/><br/>
         <label for="display" style="font-weight:bold;">Friendly Name:</label><br/>
         <input type="text" id="display" style="margin-bottom:1em;width:300px;" onkeydown="if(event.keyCode == 13){social_links_ajax_addNetwork();}"><br/>
         <label for="url" style="font-weight:bold;">URL:</label><br/>
         <input type="text" id="url" style="margin-bottom:1em;width:300px;" value="http://" onkeydown="if(event.keyCode == 13){social_links_ajax_addNetwork();}">
			<br/><input type="button" class="button" id="addButton" value="Add Social Link" onclick="social_links_ajax_addNetwork();" />
  	   </div>

     <div id="previewDiv" style="margin-left:350px;width:<?php echo ($width + 100);?>px;">
		<h3>Preview</h3>
        <div style="background-color:#E7F4FC;border:solid 1px #D3DEE4;padding:10px;-moz-border-radius:3px;">
			 <div  id="displayDiv" style="width:<?php echo $width;?>px;float:left;" class="drop_target">
				   <?php echo generateSocialLinksPreviewInnerHTML("\n");  ?>
			 </div>
			<div id="trash" class="drop_target" style="margin-left:<?php echo ($width+10);?>px;padding-left:20px;border-left:solid 1px #D3DEE4;">
               <img src="<?php echo $plugindir ?>/images/trash.jpg" height="40px"/>
            </div>
		</div>
        <div align="center">
			<input type="submit" class="button" id="saveOrderButton" name="saveorder" value="Save Order" style="margin-top:20px;"/>
       			<input type="hidden" name="sortOrderData" id="sortOrderData"/>
				<input type="hidden" name="callBackUrl" id="callBackUrl" value="<?php echo $plugindir ?>"/>
        </div>
     </div>
     <div style="clear: both;"> </div>
      <div id="instructionsDiv">
         <h3>Instructions</h3>
         <ul>
            <li>To add a new link select the network icon, fill in the network name and URL and click 'Add'.<br/></li>
            <li>To change the order they appear, rearrange the icons in the preview and click 'Save Order'. <br/></li>
            <li>To delete a link, simply drag it to the trash can.</li>
         </ul>
		
		<div>Migrate link information from versions of Social-Links prior to 2.0 <input type="submit" class="button" id="migrateButton" name="migrate" value="Migrate" /></div>
      </div>

   </form>
</div>
<script language="JavaScript">
	createSortables();
</script>
<?php
	}//End of widget_social_links_settings
?>