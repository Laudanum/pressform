=== Plugin Name ===
Contributors: Ryan Huff, MyCodeTree.com
Donate link: http://mycodetree.com/donations/
Tags: mysql, database, backup, cron, codetree, restore, database, backup database, codetree backup
Requires at least: 3.0
Tested up to: 3.2.1
Stable tag: 3.0.5

WP Backup Complete is a complete backup solution for WordPress. The plugin will backup the Wordpress database as well as the file system.

== Description ==

WP Backup Complete is an easy to use, complete backup solution for WordPress. The plugin 
offers the ability to backup the database as well as make a complete file system backup.

Plugin features: 

* Automatic backup restore tool
* One-click button to clear all stored backups
* Option to delete backups individually
* Complete user guide in PDF format
* Access to community support forum
* Randomized server storage locations

== Installation ==

1. Upload the plugin archive file into Wordpress using the 'Add New' option under the plugin menu in the Administration area.
1. Alternatively, you can unzip the plugin archive and FTP the contents to the wp-content/plugin/ folder of Wordpress
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Navigate to 'WP Complete Backup' under the settings menu.

== Frequently Asked Questions ==

= I click the Run Backup Now button but it doesn't make a backup =

Like most backup plugins, WP Complete Backup depends on server conditions. Ensure that your
server has write permissions to the /wp-content/plugins/wp-complete-backup folder. 

Also, be mindful of server disk space. If you have a large site, the backup may exceed your 
available disk space. Try doing just a database backup first. If a database-only backup
works, it may be a sign that a combined backup exceeds your server's available disk space.

= Where is the database backup? =

The database backup is in SQL-Dump format and is located in /wp-content/plugins/wp-complete-backup/storage ... 
Inside the storage folder is another folder named after the UNIX timestamp of when the backup
was created. The database SQL file is located in that folder.

= Are other backups on the server, backed up when I run a new backup? =

No, each backup will only include the storage location for that backup. Previously stored
backups will not be included with new backups (this is so a backup file doesn't exponentially 
grow in size).

== Screenshots ==

1. Example of the plugin's settings menu.

== Changelog ==

= 2.0 =
* Added support for non-parent folder locations (www.yourdomain.com/your-folder)
* Fixed bug in registration that would not allow registration under certain circumstances
* May require that you click the 1-click registration button again.

= 3.0 =
* Automatic backup restore tool
* Remote backup API
* Complete user's guide
* Remote binding ability
* Display backup size and total storage size
* 32 non-critical bug fixes

= 3.0.5 =
* Fixed redirect link