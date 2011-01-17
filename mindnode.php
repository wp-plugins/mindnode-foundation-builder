<?php 

/*
Plugin Name: Mindnode Foundation Builder
Plugin URI: http://studio2108.com/mindnode-wordpress-plugin/
Description: Imports a MindMap .opml file, and creates pages based on the structure.  Helpful for setting up brand new sites. WARNING it will nuke ALL pages/posts (drops the wp_posts table) on your site, so only use this in the beginning! See www.mindnode.com for the Mind Node software.  Check out the screencast demo at http://studio2108.com/mindnode-wordpress-plugin/
Version: 0.5
Author: Neight Haskins
Author URI: http://studio2108.com/mindnode-wordpress-plugin/
License: GPLv2
*/

/*
This program is free software; you can redistribute it and/or modify 
it under the terms of the GNU General Public License as published by 
the Free Software Foundation; version 2 of the License.

This program is distributed in the hope that it will be useful, 
but WITHOUT ANY WARRANTY; without even the implied warranty of 
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the 
GNU General Public License for more details. 

You should have received a copy of the GNU General Public License 
along with this program; if not, write to the Free Software 
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA 
*/


// Hook for adding admin menus
add_action('admin_menu', 'dash_add_pages');

// action function for above hook
function dash_add_pages() {

	//top level menu item
    add_menu_page('html page title', 'Mindnode', 'manage_options', 'mindnode', 'build_page_function' );

 
}


// mt_toplevel_page() displays the page content for the custom Test Toplevel menu
function build_page_function()  {?>
    <h1>Mindnode foundation builder</h1>

    <p>So this is used to build a foundation for a new wordpress site.  This plugin is very powerful, 
    <br/>and it will <strong>obliterate</strong>
    any other page/post on the site, so use this before doing anything else.
    <br/><br/>It's advisable to <strong>turn off</strong> the plugin once you're done with it.</p>
    

	
	
	<form id="theForm" action="<?php bloginfo('url');?>/wp-admin/admin.php?page=mindnode" method="post" enctype="multipart/form-data">

	<div style="border: 1px solid #ccc;padding: 10px;">
		<label>Please select a .opml file from your computer:</label><br/> File: <input type="file" name="opml" id="opml"  size="40" />
	<br/><br/><input id="submitButton" type="submit" value="Build Foundation">	
	</div>
	<input type="hidden" name="process" value="1"><br/><br/>

	</form>
	
    <p>Example mind map.. follow this format for best results.  One central node, and a bunch of branches and children.  You can add as many branches as you need.</p>    
    <img style="border: 1px solid #ccc;padding: 10px;"src="<?php bloginfo('url') ?>/wp-content/plugins/mindnode-foundation-builder/example.png"/>   
 
    


<?php } 
 if($_POST['process'] == '1' && checkFileType() ){ 

		$xml = simplexml_load_file($_FILES['opml']['tmp_name']);
		

$q = "DROP TABLE wp_posts";
$r = mysql_query($q);
$q = "CREATE TABLE wp_posts (
  ID bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  post_author bigint(20) unsigned NOT NULL DEFAULT '0',
  post_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  post_date_gmt datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  post_content longtext NOT NULL,
  post_title text NOT NULL,
  post_excerpt text NOT NULL,
  post_status varchar(20) NOT NULL DEFAULT 'publish',
  comment_status varchar(20) NOT NULL DEFAULT 'open',
  ping_status varchar(20) NOT NULL DEFAULT 'open',
  post_password varchar(20) NOT NULL DEFAULT '',
  post_name varchar(200) NOT NULL DEFAULT '',
  to_ping text NOT NULL,
  pinged text NOT NULL,
  post_modified datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  post_modified_gmt datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  post_content_filtered text NOT NULL,
  post_parent bigint(20) unsigned NOT NULL DEFAULT '0',
  guid varchar(255) NOT NULL DEFAULT '',
  menu_order int(11) NOT NULL DEFAULT '0',
  post_type varchar(20) NOT NULL DEFAULT 'post',
  post_mime_type varchar(100) NOT NULL DEFAULT '',
  comment_count bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (ID),
  KEY post_name (post_name),
  KEY type_status_date (post_type,post_status,post_date,ID),
  KEY post_parent (post_parent),
  KEY post_author (post_author)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
$r = mysql_query($q);

foreach($xml->body->outline as $xx){	

	insert_page($xx[0]['text']);	
	$parent_id = mysql_insert_id();

	if($xx->outline){dig($xx, $parent_id);}

	}
echo 'done, oh the magic...  check your pages/navigation.';

} 


function insert_page($page, $parent_id){

	$menu_order = mysql_insert_id();
    $temp_text = '#mindnode Place holder text for '.$page;
    $post_name = sanitize_title_with_dashes($page);
	// $site_name = 'http://'.$xml->head->title.'?page_id='.mysql_insert_id();  not implimented, not reason yet.. also would have to resolve scope conflict on xml ref
    
    if(!$parent_id){
		
    	$q = "INSERT into wp_posts (post_title, post_content, post_excerpt, post_name, post_type, menu_order) VALUES ('$page', '$temp_text', '$temp_text', '$post_name', 'page', '$menu_order')";
    	$r = mysql_query($q);
    
    }

    else 
    
    {

	   	$q = "INSERT into wp_posts (post_title, post_content, post_excerpt, post_name, post_parent, post_type, menu_order) VALUES ('$page', '$temp_text', '$temp_text', '$post_name', '$parent_id', 'page', '$menu_order')";
    	$r = mysql_query($q);
    }
}	
function dig($xx, $parent_id){
	
	$top_parent = $parent_id;

	foreach($xx->outline as $xx){
				
		insert_page($xx[0]['text'], $top_parent);
		$top_id = mysql_insert_id();
		if($xx->outline){	
		
			$parent_id = mysql_insert_id();
			dig($xx, $parent_id);
		}
	}
}

function checkFileType(){

// begin Dave B's Q&D file upload security code 
 $allowedExtensions = array("opml"); 
 foreach ($_FILES as $file) { 
   if ($file['tmp_name'] > '') { 
     if (!in_array(end(explode(".", 
           strtolower($file['name']))), 
           $allowedExtensions)) { 
		return 0;
     } else {
     	return 1;
     }
   } 
 } 
}


?>