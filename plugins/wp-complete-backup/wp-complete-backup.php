<?php
/*
Plugin Name: WP Complete Backup
Version: 3.0.5
Plugin URI: http://www.mycodetree.com
Donate link: http://mycodetree.com/donations/
Description: WP Backup Complete is a complete backup solution for WordPress. The plugin offers the ability to backup the database as well as make a complete file system backup. 
Author: MyCodeTree
Author URI: http://www.mycodetree.com/

Copyright 2011 mycodetree.com.  (email: support@mycodetree.com)
*/

//[BEGIN CODE BLOCK]
/*
 * CREATE SERVER STORAGE FOLDER IF IT DOES NOT EXIST
 */
$pp = dirname(__FILE__);
if (!file_exists($pp . "/storage/")) {
	mkdir($pp . "/storage/");
}
/*
 * SET THE MAX EXECUTIN TIME HIGH, IF THIS SERVER ALLOWS IT
 */
ini_set('max_execution_time', 120);
//[END CODE BLOCK]

//[BEGIN CODE BLOCK]
/*
 * SELF REFERENCING UPDATER TO SET LOCALHOST IP ADDRESS
 */
if ($_GET['updatelocalip'] == 'yes') {
	update_option('wpcomplete-remoteipaddress', $_SERVER['REMOTE_ADDR']);
	die($_SERVER['REMOTE_ADDR']);
}
//[END CODE BLOCK]

//[BEGIN CODE BLOCK]
/*
 * DETERMINS IF RENDER IS FROM POST AND IF SO, A REGISTRATION KEY AND THEN UPDATE
 */
if (isset($_POST['wpcomplete_registrationkey']) && $_POST['wpcomplete_registrationkey'] !='') {
    update_option('wpcomplete-backup-registrationkey', trim($_POST['wpcomplete_registrationkey']));
    $installedregistration = base64_decode(trim($_POST['wpcomplete_registrationkey']));
}
// [END CODE BLOCK]

/*
 * COMPARES A SUPPLIED VERSION NUMBER AGAINST THE CURRENT SERVER PHP VERSION.
 * RETURNS TRUE IF CURRENT VERSION IS EQUAL TO OR GREATER THAN SUPPLIED VERSION
 * NUMBER. RETURNS FALSE IF CURRENT VERSION IS LESS THAN SUPPLIED VERSION NUMBER.
 * @RETURN: BOOL
 * @@EXAMPLE: getPhpVersion()
 * 
 */
function wpcomplete_getphpversion($supportedVersion) {
    (function_exists('phpversion'))?$phpVer = explode('.', floatval(phpversion())):$phpVer=false;
    (is_array($phpVer) && $phpVer[0] >= $supportedVersion)?$phpVer=true:$phpVer=false;
    return $phpVer;
} 
/*
 * DUMP WORDPRESS DATABASE AS A .SQL FILE
 * BY DEFAULT THE .SQL FILE STORES IN THE 
 * PLUGIN FOLDER.
 * @RETURN: BOOL FALSE || SQL FILENAME
 * @EXAMPLE: wp_complete_tables(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, DUMP_FILENAME);
 */
function wpcomplete_tables($host,$user,$pass,$name,$dbbackup,$tables = '*') {
    $link = mysql_connect($host,$user,$pass);
    mysql_select_db($name,$link);
    if($tables == '*'){
        $tables = array();
        $result = mysql_query('SHOW TABLES');
        while($row = mysql_fetch_row($result)) {
            $tables[] = $row[0];
        }
    }
    else {
        $tables = is_array($tables) ? $tables : explode(',',$tables);
    }
    foreach($tables as $table){
        $result = mysql_query('SELECT * FROM '.$table);
        $num_fields = mysql_num_fields($result);
        $return.= 'DROP TABLE IF EXISTS '.$table.';';
        $row2 = mysql_fetch_row(mysql_query('SHOW CREATE TABLE '.$table));
        $return.= "\n\n".$row2[1].";\n\n";
        
        for ($i = 0; $i < $num_fields; $i++){
            while($row = mysql_fetch_row($result)){
                $return.= 'INSERT INTO '.$table.' VALUES(';
                for($j=0; $j<$num_fields; $j++) {
                    $row[$j] = addslashes($row[$j]);
                    $row[$j] = ereg_replace("\n","\\n",$row[$j]);
                    if (isset($row[$j])) { $return.= '"'.$row[$j].'"' ; } else { $return.= '""'; }
                    if ($j<($num_fields-1)) { $return.= ','; }
                }
                $return.= ");\n";
            }
        }
        $return.="\n\n\n";
    }
    $dbbackup .= "database.sql";
    if (fwrite(fopen($dbbackup,'w+'),$return)) {
    	return $dbbackup;
    	fclose($handle);
    }
    else {
    	return false;
    }
    
}
/*
 * LIST ALL FILES AND FOLDERS FOR A FOLDER PATH
 * @RETURN: GLOBAL ARRAY OF ALL FILES AND FOLDERS
 * @EXAMPLE: searchFolders(PATH_TO_SEARCH)
 */
function wpcomplete_searchFolders($dir) {
	global $filenames;
    if ($handle = opendir($dir)) {
        while (false !== ($file = readdir($handle))) {
            if ($file != "." && $file != ".." && $file != "storage" && is_file($dir.'/'.$file)) {
            	$filenames[] = $dir.'/'.$file;
            }
            else if ($file != "." && $file != ".." && $file != "storage" && is_dir($dir.'/'.$file)) {
                wpcomplete_searchFolders($dir.'/'.$file);
            }
        }
        closedir($handle);
    }
    return $filenames;
}
/*
 * LIST ALL FILES AND FOLDERS IN THE BACKUP STORAGE
 * @RETURN: GLOBAL ARRAY OF ALL BACKUPS
 * @EXAMPLE: wpcomplete_searchBackups(PATH_TO_SEARCH)
 */
function wpcomplete_searchBackups($dir) {
	global $searchnames;
    if ($handle = opendir($dir)) {
        while (false !== ($file = readdir($handle))) {
            if ($file != "." && $file != ".." && $file != "index.php" && $file != 'database.sql' && $file != '.wpcmanifest' && is_file($dir.'/'.$file)) {
                $searchnames[] = $dir.'/'.$file;
            }
            else if ($file != "." && $file != ".." && $file != "index.php" && $file != 'database.sql' && $file != '.wpcmanifest' && is_dir($dir.'/'.$file)) {
               	wpcomplete_searchBackups($dir.'/'.$file);
            }
        }
        closedir($handle);
    }
    if (!is_array($searchnames)) {
    	$searchnames = array();
    }
    return $searchnames;
}
/*
 * CREATE FOLDER IN THE PLUGIN DIRECTORY FOR EITHER TEMP OR PERM BACKUP FILE STORAGE
 * @RETURN: STRING
 * @EXAMPLE: wpcomplete_createserverstoragefolder($temp)
 */
function wpcomplete_createserverstoragefolder() {
	$pathprefix = dirname(__FILE__);
	$foldername = time();
	if (file_exists($pathprefix . "/storage/" . $foldername)) {
		wpcomplete_createserverstoragefolder();
	}
	else {
		if (!mkdir($pathprefix . "/storage/" . $foldername)) {
			wpcomplete_createserverstoragefolder();
		}
		fwrite(fopen($pathprefix . "/storage/" . $foldername . "/index.php", "w+"), "");
		return $pathprefix . "/storage/" . $foldername . "/";
	}
}
/*
 * EXTRACT A BACKUP ZIP FILE TO SPECIFIED PATH
 * @RETURN: BOOL
 * @EXAMPLE: wpcomplete_zipextract($file, $extractPath)
 */
function wpcomplete_zipextract($file, $extractPath) {
    $zip = new ZipArchive;
    $res = @$zip->open($file);
    if (!@$index = $zip->locateName('.wpcmanifest')) {
    	return false;
    }
    else { 
	    if ($res === TRUE) {
	    	//REMOVE THE MANIFEST
	    	@$zip->deleteIndex($index);
	    	//EXTRACT THE ARCHIVE
	        @$zip->extractTo($extractPath);
	        //RE-ADD THE MANIFEST
	        $zip->addFromString('.wpcmanifest', time());
	        //CLOSE THE ZIP
	        @$zip->close();
	        return TRUE;
	    } else {
	        return FALSE;
	    }
    }
}
/*
 * EXTRACT A BACKUP ZIP FILE TO SPECIFIED PATH
 * @RETURN: STRING
 * @EXAMPLE: wpcomplete_randomstring(NULL)
 */
function wpcomplete_randomstring() {
    $length = 32;
    $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
    $string = NULL;    
    for ($p = 0; $p < $length; $p++) {
        $string .= $characters[mt_rand(0, strlen($characters))];
    }
    return $string;
}
/*
 * CREATE A WP-STYLE DIV BOX
 * @RETURN: NULL
 * @EXAMPLE: wpcomplete_postbox(ID, BOX_TITLE, BOX_CONTENTS)
 */
function wpcomplete_postbox($id, $title, $content) {  
	echo "<div id='$id' class='postbox'> 
    <div class='handlediv'' title='Click to toggle'><br /></div>
    <h3 class='hndle'><span>$title</span></h3>
    <div class='inside'> $content </div>
    </div>";    
}
/*
 * CREATE A TABLE WITH ROWS
 * @RETURN: STRING
 * @EXAMPLE: wp_complete_form_table(ROWS_OF_TABLE_DATA)
 */
function wpcomplete_form_table($rows) {
    $content = '<table class="form-table" width="100%">';
    foreach ($rows as $row) {
        $content .= '<tr><th valign="top" scope="row" style="width:50%">';
        if (isset($row['id']) && $row['id'] != '')
            $content .= '<label for="'.$row['id'].'" style="font-weight:bold;">'.$row['label'].':</label>';
        else
            $content .= $row['label'];
        if (isset($row['desc']) && $row['desc'] != '')
            $content .= '<br/><small>'.$row['desc'].'</small>';
        $content .= '</th><td valign="top">';
        $content .= $row['content'];
        $content .= '</td></tr>'; 
    }
    $content .= '</table>';
    return $content;
}
/*
 * RETURN A MORE READABLE SIZE BASED ON BYTES
 * @RETURN: MIXED
 * @EXAMPLE: wpcomplete_formatbyte($bytes)
 */
function wpcomplete_formatbyte($bytes) {
    if ($bytes < 1024) {
        return $bytes .' Bytes';
    } elseif ($bytes < 1048576) {
        return round($bytes / 1024, 2) .'KB';
    } elseif ($bytes < 1073741824) {
        return round($bytes / 1048576, 2) . 'MB';
    } elseif ($bytes < 1099511627776) {
        return round($bytes / 1073741824, 2) . 'GB';
    } elseif ($bytes < 1125899906842624) {
        return round($bytes / 1099511627776, 2) .'TB';
    } elseif ($bytes < 1152921504606846976) {
        return round($bytes / 1125899906842624, 2) .'PB';
    } elseif ($bytes < 1180591620717411303424) {
        return round($bytes / 1152921504606846976, 2) .'EB';
    } elseif ($bytes < 1208925819614629174706176) {
        return round($bytes / 1180591620717411303424, 2) .'ZB';
    } else {
        return round($bytes / 1208925819614629174706176, 2) .'YB';
    }
}
/*
 * GET THE STRING VALUE BETWEEN TWO STRING POINTS
 * @RETURN: STRING
 * @EXAMPLE: wpcomplete_get_target(LEFT_POINT,RIGHT_POINT,SUBJECT)
 */
function wpcomplete_get_target($string, $start, $end){ 
    $string = " ".$string; 
    $ini = strpos($string,$start); 
    if ($ini == 0) return ""; 
    $ini += strlen($start); 
    $len = strpos($string,$end,$ini) - $ini; 
    return substr($string,$ini,$len); 
} 
/*
 * SET THE LINKS THAT SHOW UP FOR WP COMPLETE BACKUP IN THE WORDPRESS PLUGIN LIST
 * @RETURN: ARRAY
 * @EXAMPLE: set_plugin_links(NULL)
 */
function set_plugin_links($links, $file) {
	$plugin = plugin_basename(__FILE__);
	// create link
	if ($file == $plugin) {
		$links[] = "<a href='http://mycodetree.com/forums/' title='MyCodeTree Support Community' target='_blank'>Support Forum</a>";
		$links[] = "<a href='http://twitter.com/mycodetree/' title='Follow @MyCodeTree on Twitter' target='_blank'>@MyCodeTree</a>";
		$links[] = "<a href='http://www.facebook.com/pages/MyCodeTree/145101265500968' title='MyCodeTree facebook page' target='_blank'>facebook</a>";
	}
	return $links;
}

//[BEGIN CODE BLOCK]
/*
 * BASIC METHODS: ADD LINK TO THE SETTINGS MENU AND CREATE A "SETTINGS PAGE"
 */
//CHECK FOR IP RESTRICTIONS ON REMOTE EXECUTION
$remoteip = strtolower(get_option('wpcomplete-remoteipaddress'));
$passip = true;
if ($remoteip != 'any') {
	if ($remoteip != $_SERVER['REMOTE_ADDR']) {
		$passip = false;
	}
}
//GET THE URI FOR REMOTE EXECUTION
$remotexcution = $_SERVER['REQUEST_URI'];
$uriparts = explode("/", $remotexcution);
array_shift($uriparts);
$bittest = array_shift($uriparts);
if ($bittest == "wp-complete-backup") {
	if ($passip) {
		$api = explode("-", $uriparts[0]);
		if ($api[1] == get_option('wpcomplete_remoteapi')) {
			//VALID API
			//unset($filenames);
			$stop = false;
	        $savelocation = wpcomplete_createserverstoragefolder();
	        $fpoint = 0;
	        $dpoint = 0;
			$type = explode("-", $uriparts[1]);
			switch ($type[1]) {
				case 'database' : 
					wpcomplete_tables(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, $savelocation); 
					$manifest[] = $savelocation . "database.sql"; 
					$dpoint = 4; 
					break;
				case 'filesystem' : 
					$manifest = wpcomplete_searchFolders(rtrim(ABSPATH,"/")); 
					$fpoint = 3; 
					break; 
				case 'both' : 
					$manifest = wpcomplete_searchFolders(rtrim(ABSPATH,"/")); 
					$fpoint = 3;
					wpcomplete_tables(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, $savelocation); 
					$manifest[] = $savelocation . "database.sql"; 
					$dpoint = 4;
					break;
				default : $stop = true;
			}
			if (!$stop) {
		        //ZIP THE MANIFEST
		        $points = $fpoint + $dpoint;
			    $zipfile = $savelocation . 'mycodetreebu-' . $points . '-' . time() . '.zip';    
			    $zip = new ZipArchive();
			    $zip->open($zipfile, ZIPARCHIVE::CREATE);
			    foreach ($manifest as $item) {
			        $zip->addFile($item,$item);
			    }
			    //CREATE HIDDEN MANIFEST FILE AND ADD IT TO THE ZIP
			    @fwrite(fopen($savelocation . '.wpcmanifest','w+'), time());
			    $zip->addFile($savelocation . '.wpcmanifest','.wpcmanifest');
			    $zip->close();
				//CLEAR THE WAYWARD DATABASE FILE IF IT IS THERE
				if (file_exists($savelocation . "database.sql")) {
				   	unlink($savelocation . "database.sql");
				}
				//CLEAR THE WAYWARD MANIFEST FILE IF IT IS THERE
				if (file_exists($savelocation . '.wpcmanifest')) {
					unlink($savelocation . '.wpcmanifest');
				}
				//SUCCESS
				die("<response>0</response>");
			}
			//BAD TYPE
			die("<response>1</response>");
		}
		//BAD API
		die("<response>2</response>");
	}
	//IP RESTRICTION
	die("<response>3</response>");
}
//ADDED LINKS SEEM TO POPULATE ALL PLUGINS, NOT JUST MINE
add_filter( 'plugin_row_meta', 'set_plugin_links', 10, 2 );
//ADD THE ACTIONS
add_action('admin_menu', 'wpcomplete_backup');
add_filter( 'plugin_action_links', 'wpcomplete_backup_add_action_link', 10, 2 );
function wpcomplete_backup() {
    add_options_page('WP Complete Backup Options', 'WP Complete Backup', 'administrator', 'wpcomplete_backup_options', 'wpcomplete_backup_options');  
}
function wpcomplete_backup_add_action_link( $links, $file ) {
    static $this_plugin;
     if( ! $this_plugin ) $this_plugin = plugin_basename(__FILE__);
    if ( $file == $this_plugin ) {
        $settings_link = '<a href="' . admin_url( 'options-general.php?page=wpcomplete_backup_options' ) . '">' . __('Settings') . '</a>';
        array_unshift( $links, $settings_link ); // before other links
    }
    return $links;
}
function wpcomplete_backup_options() {
	//SET DEFAULT API KEY IF NEEDED
	$apikey = get_option('wpcomplete_remoteapi');
	if (empty($apikey) OR $apikey == '') {
		update_option('wpcomplete_remoteapi', wpcomplete_randomstring());
	}
	//SET DEFAULT REMOTE IP ADDRESS IF NEEDED
	$remoteipaddress = get_option('wpcomplete-remoteipaddress');
	if (empty($remoteipaddress) OR $remoteipaddress == '') {
		$remoteipaddress = @file_get_contents(site_url() . "/wp-admin/options-general.php?page=wpcomplete_backup_options&updatelocalip=yes");
		
	}
    if (isset($_POST['applydomain'])) {
    	if (empty($_POST['applyemail']) OR empty($_POST['applyfirstname']) OR empty($_POST['applylastname'])) {
    		echo "<div id=\"message\" class=\"updated\">Sorry buckaroo, we have to have your first name, last name and email address.</div>";
    	}
    	else {    		
	    	$getReg = file_get_contents("http://mycodetree.com/backendTasks/utilities.php?dir=pluginkey&opts=" . $_POST['applyfirstname'] . "," . $_POST['applylastname'] . "," . $_POST['applyemail'] . "," . $_POST['applydomain'] . ",WPCompleteBackup");	
    		update_option('wpcomplete-backup-registrationkey', "YES");
    		echo "<div id=\"message\" class=\"updated\">The Registration Key has been saved.</div>";
	    	
    	}
    }
    echo "<div class='wrap'>
    <h2>WP Complete Backup for WordPress</h2> 
    <div class='postbox-container' style='width:70%;'>
    <div class='metabox-holder'>    
    <div class='meta-box-sortables'>";
  if (class_exists('ZipArchive')) {  	
    if (wpcomplete_getphpversion(5)) {
    	$regName = NULL;
        if (get_option('wpcomplete-backup-registrationkey') != "YES") {
        	$registrationTest = "INVALID REGISTRATION";
        	echo "<div id=\"message\" class=\"updated\">CONFLICT: This domain has not been registered yet or you registered with an email address already in use (one registration per email address and domain). Please use the <em>1-Click Registration</em> form below to get your FREE registration for this domain.</div>";
        }           
        if ($registrationTest != "INVALID REGISTRATION") { 
        	$regName = "<br /><p style='font-size: 9px;color: maroon;font-style: Italic;'>Registration For:" . wpcomplete_get_target($registrationTest, "<FIRST>", "</FIRST>") . " " . wpcomplete_get_target($registrationTest, "<LAST>", "</LAST>") . " on " . wpcomplete_get_target($registrationTest, "<DOMAIN>", "</DOMAIN>") . "</p>"; 
            $registrationTest = true;
        } 
        else { 
        	$registrationTest = false; 
        }
        
        if ($registrationTest) {
        	//[BEGIN CODE BLOCK]
        	/*TRIGGERED IF BACKUP OPTION IS FIRED*/
        	if ($_POST['wpcomplete-backupmodefile'] == "filesystem" OR $_POST['wpcomplete-backupmodedatabase'] == "database") {
        		//CREATE FOLDER LOCATION
        		$savelocation = wpcomplete_createserverstoragefolder();
        		$fpoint = 0;
        		$dpoint = 0;
        		if ($_POST['wpcomplete-backupmodefile'] == 'filesystem') {
	        		//MANIFEST THE FILESYSTEM
	        		$manifest = wpcomplete_searchFolders(rtrim(ABSPATH,"/"));
	        		$fpoint = 3;        			        		
        		}
        	    if ($_POST['wpcomplete-backupmodedatabase'] == 'database') {
	        		//DUMP DATABASE
	        		wpcomplete_tables(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, $savelocation);
	        		$manifest[] = $savelocation . "database.sql";
	        		$dpoint = 4;
        		}
        		//ZIP THE MANIFEST
        		$points = $fpoint + $dpoint;
			    $zipfile = $savelocation . 'mycodetreebu-' . $points . '-' . time() . '.zip';    
			    $zip = new ZipArchive();
			    $zip->open($zipfile, ZIPARCHIVE::CREATE);
			    foreach ($manifest as $item) {
			        $zip->addFile($item,$item);
			    }
			    //CREATE HIDDEN MANIFEST FILE AND ADD IT TO THE ZIP
			    @fwrite(fopen($savelocation . '.wpcmanifest','w+'), time());
			    $zip->addFile($savelocation . '.wpcmanifest','.wpcmanifest');
			    $zip->close();
				//CLEAR THE WAYWARD DATABASE FILE IF IT IS THERE
				if (file_exists($savelocation . "database.sql")) {
				   	unlink($savelocation . "database.sql");
				}
				//CLEAR THE WAYWARD MANIFEST FILE IF IT IS THERE
				if (file_exists($savelocation . '.wpcmanifest')) {
					unlink($savelocation . '.wpcmanifest');
				}	
        	}
        	//[END CODE BLOCK]
        	/*************************************************************/
        	//[BEGIN CODE BLOCK]
        	/*TRIGGERED IF RESTORE OPTION IS FIRED*/
        	if (isset($_POST['wpcomplete_backuppath']) OR isset($_FILES['wpcomplete_backupfile'])) {
        		if (isset($_FILES['wpcomplete_backupfile']['name']) && !empty($_FILES['wpcomplete_backupfile']['name'])) {
        			if (!wpcomplete_zipextract($_FILES['wpcomplete_backupfile']['tmp_name'], '/')) {
        				die("<h2>Restoration not successful!</h2><p style='text-align: center; font-weight: bold; font-size: 14px;'>That is not a valid backup file.</p>");
        			}
        		}
        		else {
        			if (isset($_POST['wpcomplete_backuppath'])) {
        				if (!wpcomplete_zipextract($_POST['wpcomplete_backuppath'], '/')) {
        					die("<h2>Restoration not successful!</h2><p style='text-align: center; font-weight: bold; font-size: 14px;'>That is not a valid backup file.</p>");
        				}
        			}
        		}
        		echo "<h2>Restoration is successful!</h2><p style='text-align: center; font-weight: bold; font-size: 14px;'>In order to complete the restoration, you must logout of Wordpress and then log back in.</p><p style='text-align: center;'><input type='button' name='logout' id='logout' class='button-primary' value='Logout Now' onclick=\"location.href='" . site_url() . "/wp-login.php?loggedout=true';\"></p><p style='text-align: center;'><span style='color: maroon;'>***WARNING!***</span> Continuing to use Wordpress after restoration <u>WITHOUT logging out first</u>, may produce unexpected results.</p>";
        	}
        	else {
        	//[END CODE BLOCK]
        	/*************************************************************/
        	//[BEGIN CODE BLOCK]
        	/*DELETE ANY SPECIFIC BACKUPS*/
        	if ($_GET['wpcbackup'] == 'delsp') {
        		if (!empty($_GET['delitem'])) {
        			$fldpath = dirname(__FILE__) . '/storage/' . $_GET['delitem'];
					if ($handle = opendir($fldpath)) {
					    while (false !== ($file = readdir($handle))) {
					        if ($file != "." && $file != "..") {
					            @unlink($fldpath . '/' . $file);
					        }
					    }
					    closedir($handle);
					}
					@rmdir($fldpath);
        		}
        	}
        	//[END CODE BLOCK]
        	/*************************************************************/
        	//[BEGIN CODE BLOCK]
        	/*MAKE A NEW RANDOMIZED API KEY*/
			if ($_GET['wpcbackup'] == 'generatenew') {
				update_option('wpcomplete_remoteapi', wpcomplete_randomstring());
			}
        	//[END CODE BLOCK]
        	/*************************************************************/
        	//[BEGIN CODE BLOCK]
        	/*SAVE NEW REMOTE IP ADDRESS*/
			if ($_GET['wpcbackup'] == 'saveipaddress') {
				update_option('wpcomplete-remoteipaddress', $_GET['address']);
			}
        	//[END CODE BLOCK]
        	/*************************************************************/
        	//[BEGIN CODE BLOCK]
            //GET ALL STORED BACKUPS
		    $opts = NULL;
		    $backups = NULL;
		    unset($searchnames);
		    $backups = wpcomplete_searchBackups(dirname(__FILE__) . "/storage");
		    $items = NULL;
		    foreach ($backups as $backup) {
		    	$fn = basename($backup);
		    	$pathparts = explode("/",$backup);
		    	array_pop($pathparts);
		    	$fld = array_pop($pathparts);
	        	//[END CODE BLOCK]
	        	/*************************************************************/
	        	//[BEGIN CODE BLOCK]
		        if ($_GET['wpcbackup'] == 'clear') {
		        	//NO GUARANTEES LOL
		        	@unlink($backup);
		        	@unlink(implode("/",$pathparts) . "/$fld/index.php");
		        	@rmdir(implode("/",$pathparts) . "/$fld");
		        }
	        	//[END CODE BLOCK]
	        	/*************************************************************/
	        	//[BEGIN CODE BLOCK]
		    	if ($fn != "index.php") {
		    		$parts = explode("-", $fn);
		    		switch ($parts[1]) {
		    			case 4 : $ans = "database only"; break;
		    			case 3 : $ans = "files only"; break;
		    			case 7 : $ans = "database and files"; break;
		    		}
		    		$bytes = wpcomplete_formatbyte(@filesize($backup));
			    	$opts .= "<option value='$backup'>Backup: [$bytes] " . date("m/d/Y - H:i", $parts[2]) . " ($ans)</option>";
			    	$items .= "<li><a href='" . site_url() . "/wp-content/plugins/wp-complete-backup/storage/$fld/$fn' target='_self'><strong>[<span style='color: black;'>$bytes</span>]</strong> " . date("m/d/Y - H:i", $parts[2]) . " ($ans)</a>&nbsp;[<a href='#' target='_self' onclick=\"if (confirm('Are you sure you want to delete " . date("m/d/Y - H:i", $parts[2]) . "? This cannot be undone.')) { location.href='" . site_url() . "/wp-admin/options-general.php?page=wpcomplete_backup_options&wpcbackup=delsp&delitem=" . urlencode($fld) . "'; }\"><span style='color: Maroon;'><strong>Delete This Backup</strong></span></a>]</li>";
		    	}
		    	$totalsize += @filesize($backup);
		    }
		    if ($totalsize == 0 OR is_null($totalsize)) {
		    	$totalsize = 0;
		    }
		    if (is_null($items)) {
		    	$items = "<li>No backups yet! Why don't you make some?</li>";
		    }
        	//[END CODE BLOCK]
        	/*************************************************************/
        	//[BEGIN CODE BLOCK]
        	if ($_GET['wpcbackup'] == 'clear') {
        		echo "<script>location.href='" . site_url() . "/wp-admin/options-general.php?page=wpcomplete_backup_options';</script>";
        	}
        	//[END CODE BLOCK]
        	/*************************************************************/
        	//[BEGIN CODE BLOCK]
        	echo "<div><p>Welcome to the <u>easy to use <em><strong>WP Complete Backup</strong></em></u> utility. You can make a complete backup of your Wordpress database AND all Wordpress files (base installation,plugins,themes,media .... the whole sha' bang); or you can make a backup of just the database or just the files - the choice is yours.</p><p>Keep in mind that these backups are stored on your web server - so be mindful of disk space! The backup files that you make will always be located in a randomized folder at <em>/wp-content/plugins/wp-complete-backup/storage/...</em></p></div>";
			echo "<div style='text-align: center;'><a href='http://www.facebook.com/pages/MyCodeTree/145101265500968' target='_blank' title='MyCodeTree on facebook'><img src='" . plugins_url() . "/wp-complete-backup/Social-Truck_fb1.png' width='72' border='0'></a><a href='http://twitter.com/mycodetree' target='_blank' title='Follow @mycodetree on Twitter'><img src='" . plugins_url() . "/wp-complete-backup/Social-Truck_twi.png' width='72' border='0'></a><a href='http://mycodetree.com/feed' target='_blank' title='MyCodeTree RSS Feed'><img src='" . plugins_url() . "/wp-complete-backup/Social-Truck_rss.png' width='72' border='0'></a><a href='" . plugins_url() . "/wp-complete-backup/userguide.pdf' target='_blank' title='WP Complete Backup User Guide'><img src='" . plugins_url() . "/wp-complete-backup/Green.png' width='72' border='0'></a><a href='http://mycodetree.com/forums' target='_blank' title='MyCodeTree Support Community'><img src='" . plugins_url() . "/wp-complete-backup/forum.png' width='72' border='0'></a></div>";
        	echo "<form id='postbackup' action='" . site_url() . "/wp-admin/options-general.php?page=wpcomplete_backup_options' method='post' style='margin: 0px;'>";
      		if ( function_exists('wp_nonce_field') ) wp_nonce_field('wpcomplete-update-options');
                if (isset($_POST['wpcomplete-backup-registrationkey']) && !empty($_POST['wpcomplete-backup-registrationkey'])) {
                	$lkey = trim($_POST['wpcomplete-backup-registrationkey']); 
                	update_option('wpcomplete-backup-registrationkey', $lkey);             
                }
                
                $rows[] = array(
                        'id' => 'wpcomplete-backupmode',
                        'label' => 'WP Complete Backup',
                        'desc' => 'Choose to backup the entire Wordpress database, all the Wordpress files (base installation, themes, plugnins, media ... the whole sha\' bang) or both. After the backup is finished, you\'ll have access to it. Each backup has a random name and is stored in a unique folder location.',
                        'content' => "Backup Database: <input type='checkbox' name='wpcomplete-backupmodedatabase' id='wpcomplete-backupmodedatabase' checked='checked' value='database'>&nbsp;Backup Files: <input type='checkbox' name='wpcomplete-backupmodefile' checked='checked' id='wpcomplete-backupmodefile' value='filesystem'>"
                ); 
                $rows[] = array(
                        'id' => 'wpcomplete-backupdownload',
                        'label' => 'Download Backups',
                        'desc' => 'Download a backup by clicking the appropriate backup link in the list to the right.',
                        'content' => "Backup Storage <span style='color: black;'><strong>[" . wpcomplete_formatbyte($totalsize) . "]</strong></span>: <ul style='margin: 0px;margin-top: 5px;'>$items</ul>"
                ); 
                $rows[] = array(
                        'id' => 'wpcomplete-backupclear',
                        'label' => 'Clear Stored Backups',
                        'desc' => 'Clear all stored backups from your server.',
                        'content' => "<input type='button' class='button-primary' name='clearbackups' onclick='if (confirm(\"Are you sure you want to clear all saved backups? This cannot be undone.\")) { location.href=\"" . site_url() . "/wp-admin/options-general.php?page=wpcomplete_backup_options&wpcbackup=clear\"; }' value='Clear All Backups Now' />"
                );
                $rows[] = array(
                        'id' => 'wpcomplete-remoteipaddress',
                        'label' => 'Bind IP Address',
                        'desc' => 'The IP address that is allowed to remotley execute backups. If you use the word <strong><em>any</em></strong>, all IP addresses will be allowed to execute backups remotely.',
                        'content' => "<input type='text' name='remoteipaddress' id='remoteipaddress' value='$remoteipaddress' />&nbsp;<input type='button' class='button-primary' name='saveipaddress' id='saveipaddress' value='Save IP Address' onclick=\"location.href='" . site_url() . "/wp-admin/options-general.php?page=wpcomplete_backup_options&wpcbackup=saveipaddress&address='+encodeURI(getElementById('remoteipaddress').value);\">"
                );
                $api = get_option('wpcomplete_remoteapi'); 
                $rows[] = array(
                        'id' => 'wpcomplete-backuprandom',
                        'label' => 'Random API Key',
                        'desc' => 'Generate randomized API key to use for remote backup execution.',
                        'content' => "<input type='text' name='randomapi' id='randomapi' size='38' value='$api'/>&nbsp;<input type='button' class='button-primary' name='generatenewrandom' id='generatenewrandom' value='Generate API Key' onclick=\"location.href='" . site_url() . "/wp-admin/options-general.php?page=wpcomplete_backup_options&wpcbackup=generatenew';\"><br />Remote Execution URL: " . site_url() . "/wp-complete-backup/api-$api/type-method (method = database, filesystem, both)"
                ); 
	              wpcomplete_postbox('wpcompletesettings','WP Complete Backup', wpcomplete_form_table($rows));
	              echo "<input type='submit' class='button-primary' name='save' value='Run Backup Now'/> 
	              </form><br />";
        	//[END CODE BLOCK]
        	/*************************************************************/
        	//[BEGIN CODE BLOCK]
		    //RESTORE SECTION
		    //RADIO BUTTON MAGIC HERE (STATICALLY SETTING DD AS ALWAYS ON FOR NOW)
		    $ee = "checked='checked'";
		    $od = NULL;
		    $of = NULL;

	        echo "<form id='postrestore' action='" . site_url() . "/wp-admin/options-general.php?page=wpcomplete_backup_options' method='post' style='margin: 0px;' enctype='multipart/form-data'>";
                $secondrows[] = array(
                        'id' => 'wpcomplete_backupfile',
                        'label' => 'Restore Backup From Upload',
                        'desc' => 'Select a backup to restore. Backup must have been created with <em>WP Complete Backup</em> in order to restore.',
                        'content' => "<input type='file' name='wpcomplete_backupfile' id='wpcomplete_backupfile' /><br /><small><b>(restoring from an upload will override restoring a backup for the server)</b></small>"
                ); 
                $secondrows[] = array(
                        'id' => 'wpcomplete_backuppath',
                        'label' => 'Restore Backup From Server',
                        'desc' => 'Select a backup stored on your server to restore.',
                        'content' => "<select name='wpcomplete_backuppath' id='wpcomplete_backuppath'><option value='0'>Please Select...</option>$opts</select>"
                );
			echo "<input type='hidden' name='action' value='update' />
	              <input type='hidden' name='action' value='update' />";
                  wpcomplete_postbox('wpcompleterestore','Backup Restore', wpcomplete_form_table($secondrows));
	              echo "<input type='submit' class='button-primary' name='save' value='Restore Backup'/> 
	              </form>";
	        echo "</div></div></div></div>";
        	}
        }
        else {
        	global $current_user;
        	get_currentuserinfo();
            echo file_get_contents("http://mycodetree.com/pluginbranding.php?pluginname=wpcompletebackup") . "<p>Registration is <b>FREE</b>! Use the form below to get your <b>FREE</b> registration key. Even if you have a registration and misplaced it, you can use the form below to restore your <b>FREE</b> registration for this domain! Only one registration per domain/email address, each domain will need a unique registration key.</p>";
            $applydata = "<div style='margin: 5px;'>
            <form name='applyforreg' action='" . site_url() . "/wp-admin/options-general.php?page=wpcomplete_backup_options' method='post' style='margin: 0px;'>
            <p>We want to develop better stuff for you. After you evaluate the plugin, would you take a moment and tell us what you like and don't like about the plugin? What can we do better for you?</p>
            <p>First Name:&nbsp;<input type=\"text\" name=\"applyfirstname\" id=\"applyfirstname\" size=\"25\" value=\"" . $current_user->user_firstname . "\">&nbsp;Last Name:&nbsp;<input type=\"text\" name=\"applylastname\" id=\"applylastname\" size=\"25\" value=\"" . $current_user->user_lastname . "\"><br />
            Email Address:&nbsp;<input type=\"text\" name=\"applyemail\" id=\"applyemail\" size=\"25\" value=\"" . $current_user->user_email . "\"></p>
            <input type=\"hidden\" name=\"applydomain\" id=\"applydomain\" value=\"" . $_SERVER['HTTP_HOST'] . "\">
            <input type=\"submit\" class='button-primary' name='save' value='Get your FREE registration key HERE!'/>
            </form>
            </div>";
        	echo "<div class='wrap'><div class='postbox-container' style='width:70%;'><div class='metabox-holder'><div class='meta-box-sortables'>";
    		wpcomplete_postbox('wpcompleteapply','Get your FREE Registration Key with our <em>1-Click</em> Registration!', $applydata);
    		echo "</div></div></div></div>";
        }
    }
    else {
    	echo "<p>The minimum required PHP Version for The WP Complete Backup plugin to function is <strong><em>PHP Version 5.0</em</strong> and we've detected that your server is using <strong><em>PHP Version "; if (function_exists('phpversion')) { echo floatval(phpversion()); } echo "</em></strong>. please upgrade your PHP version or talk to your web host. If you consider moving your website to a different web host, MyCodeTree recomends <a href='http://secure.hostgator.com/~affiliat/cgi-bin/affiliates/clickthru.cgi?id=rthcon' target='_blank'>HostGator.com</a>.</p><p>If you would like further assistance please feel free to contact MyCodeTree at <a href='mailto:support@mycodetree.com?subject=PHP Version with WP Complete Backup Plugin'>support@mycodetree.com</a>.</p>";   
    }
  }
  else {
  	echo "<div id=\"message\" class=\"updated\"><strong>*WARNING*</strong> <em>WP Complete Backup</em> could not find PHP <em>ZipArchive</em> support on your server. <em>WP Complete Backup</em> requires ZipArchive support. Please contact your web host to resolve this issue. If you consider switching to a web host provider that does support PHP ZipArchive, we recommend <a href='http://secure.hostgator.com/~affiliat/cgi-bin/affiliates/clickthru.cgi?id=rthcon' target='_blank'>HostGator.com</a></div>";
  }
}
/*END OF BASIC METHODS*/
//[END CODE BLOCK]
?>