# phpMyAdmin MySQL-Dump
# version 2.3.2
# http://www.phpmyadmin.net/ (download page)
#
# Host: localhost
# Generation Time: Feb 09, 2003 at 03:35 PM
# Server version: 3.23.54
# PHP Version: 4.2.3
# Database : `hlbook`
# --------------------------------------------------------

#
# Table structure for table `auth_Acclevels`
#

DROP TABLE IF EXISTS auth_Acclevels;
CREATE TABLE auth_Acclevels (
  acclevel mediumint(9) NOT NULL default '0',
  accname varchar(16) NOT NULL default '',
  plugin varchar(32) NOT NULL default '0',
  title varchar(32) NOT NULL default '',
  description text
) TYPE=MyISAM;

#
# Dumping data for table `auth_Acclevels`
#

INSERT INTO auth_Acclevels (acclevel, accname, plugin, title, description) VALUES (0, 'anon', 'base', 'Anonymous User', NULL);
INSERT INTO auth_Acclevels (acclevel, accname, plugin, title, description) VALUES (1, 'reg', 'base', 'Registered User', NULL);
INSERT INTO auth_Acclevels (acclevel, accname, plugin, title, description) VALUES (2, 'hlbook', 'hlbook', 'Bookable Server User', NULL);
INSERT INTO auth_Acclevels (acclevel, accname, plugin, title, description) VALUES (4, 'hladmin', 'hlbook', 'HLBookings Admin', NULL);
INSERT INTO auth_Acclevels (acclevel, accname, plugin, title, description) VALUES (8, '', '0', 'Unset', NULL);
INSERT INTO auth_Acclevels (acclevel, accname, plugin, title, description) VALUES (16, '', '0', 'Unset', NULL);
INSERT INTO auth_Acclevels (acclevel, accname, plugin, title, description) VALUES (32, '', '0', 'Unset', NULL);
INSERT INTO auth_Acclevels (acclevel, accname, plugin, title, description) VALUES (64, '', '0', 'Unset', NULL);
INSERT INTO auth_Acclevels (acclevel, accname, plugin, title, description) VALUES (128, '', '0', 'Unset', NULL);
INSERT INTO auth_Acclevels (acclevel, accname, plugin, title, description) VALUES (256, '', '0', 'Unset', NULL);
INSERT INTO auth_Acclevels (acclevel, accname, plugin, title, description) VALUES (512, '', '0', 'Unset', NULL);
INSERT INTO auth_Acclevels (acclevel, accname, plugin, title, description) VALUES (1024, '', '0', 'Unset', NULL);
INSERT INTO auth_Acclevels (acclevel, accname, plugin, title, description) VALUES (2048, '', '0', 'Unset', NULL);
INSERT INTO auth_Acclevels (acclevel, accname, plugin, title, description) VALUES (4096, '', '0', 'Unset', NULL);
INSERT INTO auth_Acclevels (acclevel, accname, plugin, title, description) VALUES (8192, '', '0', 'Unset', NULL);
INSERT INTO auth_Acclevels (acclevel, accname, plugin, title, description) VALUES (16384, '', '0', 'Unset', NULL);
INSERT INTO auth_Acclevels (acclevel, accname, plugin, title, description) VALUES (32768, '', '0', 'Unset', NULL);
INSERT INTO auth_Acclevels (acclevel, accname, plugin, title, description) VALUES (65536, '', 'base', 'Site Administrator', NULL);
# --------------------------------------------------------

#
# Table structure for table `auth_Sessions`
#

DROP TABLE IF EXISTS auth_Sessions;
CREATE TABLE auth_Sessions (
  sessionid varchar(32) NOT NULL default '',
  ipaddr varchar(15) default NULL,
  userid mediumint(8) unsigned NOT NULL default '0',
  lastactive int(10) unsigned NOT NULL default '0',
  data text NOT NULL,
  PRIMARY KEY  (sessionid),
  KEY userid (userid)
) TYPE=MyISAM;

#
# Dumping data for table `auth_Sessions`
#

# --------------------------------------------------------

#
# Table structure for table `auth_Users`
#

DROP TABLE IF EXISTS auth_Users;
CREATE TABLE auth_Users (
  userid mediumint(8) unsigned NOT NULL auto_increment,
  username varchar(32) binary NOT NULL default '',
  password varchar(34) binary NOT NULL default '',
  displayname varchar(32) NOT NULL default 'n00b',
  acclevel mediumint(8) unsigned NOT NULL default '1',
  disabled char(1) NOT NULL default '0',
  toffset varchar(4) NOT NULL default '0',
  logins mediumint(8) unsigned NOT NULL default '0',
  lastlogin int(10) unsigned NOT NULL default '0',
  email varchar(128) NOT NULL default '',
  massmail char(1) NOT NULL default '1',
  PRIMARY KEY  (userid),
  KEY username (username)
) TYPE=MyISAM;

#
# Dumping data for table `auth_Users`
#

INSERT INTO auth_Users (userid, username, password, displayname, acclevel, disabled, toffset, logins, lastlogin, email, massmail) VALUES (1, 'admin', 'Zs97VuYX284a6', 'Admin', 7, '0', '0', 0, 1044821638, 'sqlboy@playway.net', '1');
# --------------------------------------------------------

#
# Table structure for table `base_Options`
#

DROP TABLE IF EXISTS base_Options;
CREATE TABLE base_Options (
  plugin varchar(32) NOT NULL default '',
  keyname varchar(32) NOT NULL default '',
  value text NOT NULL,
  PRIMARY KEY  (keyname),
  KEY plugin (plugin)
) TYPE=MyISAM;

#
# Dumping data for table `base_Options`
#

INSERT INTO base_Options (plugin, keyname, value) VALUES ('base', 'page_defaultmode', 'hlbook');
INSERT INTO base_Options (plugin, keyname, value) VALUES ('base', 'page_title', 'Playway.net Half-Life Bookable Servers');
INSERT INTO base_Options (plugin, keyname, value) VALUES ('base', 'page_contact_name', 'SQLBoy');
INSERT INTO base_Options (plugin, keyname, value) VALUES ('base', 'page_contact_email', 'sqlboy@playway.net');
INSERT INTO base_Options (plugin, keyname, value) VALUES ('base', 'page_log_plugin_views', '0');
INSERT INTO base_Options (plugin, keyname, value) VALUES ('base', 'page_log_task_views', '0');
INSERT INTO base_Options (plugin, keyname, value) VALUES ('base', 'page_log_admin_views', '0');
INSERT INTO base_Options (plugin, keyname, value) VALUES ('base', 'page_template', 'default.xml');
INSERT INTO base_Options (plugin, keyname, value) VALUES ('base', 'page_additional_headers', '');
INSERT INTO base_Options (plugin, keyname, value) VALUES ('base', 'page_style', 'orange');
INSERT INTO base_Options (plugin, keyname, value) VALUES ('auth', 'session_expire', '24');
INSERT INTO base_Options (plugin, keyname, value) VALUES ('auth', 'session_verify_ip', '1');
INSERT INTO base_Options (plugin, keyname, value) VALUES ('auth', 'session_cleanup', '25');
INSERT INTO base_Options (plugin, keyname, value) VALUES ('auth', 'page_allow_signup', '0');
INSERT INTO base_Options (plugin, keyname, value) VALUES ('auth', 'auth_crypt', '0');
INSERT INTO base_Options (plugin, keyname, value) VALUES ('auth', 'session_track_userid', '1');
INSERT INTO base_Options (plugin, keyname, value) VALUES ('auth', 'session_log_user_agent', '0');
INSERT INTO base_Options (plugin, keyname, value) VALUES ('auth', 'session_log_user_os', '0');
INSERT INTO base_Options (plugin, keyname, value) VALUES ('hlbook', 'prebook', '14');
INSERT INTO base_Options (plugin, keyname, value) VALUES ('hlbook', 'scrn_allow_uploads', '1');
INSERT INTO base_Options (plugin, keyname, value) VALUES ('hlbook', 'scrn_max_bytes', '2000000');
INSERT INTO base_Options (plugin, keyname, value) VALUES ('hlbook', 'scrn_max_size', '1280x1024');
INSERT INTO base_Options (plugin, keyname, value) VALUES ('hlbook', 'scrn_full_size', '1024x768');
INSERT INTO base_Options (plugin, keyname, value) VALUES ('hlbook', 'scrn_thumb_size', '150x112');
INSERT INTO base_Options (plugin, keyname, value) VALUES ('hlbook', 'scrn_mimetype', 'all');
INSERT INTO base_Options (plugin, keyname, value) VALUES ('hlbook', 'scrn_format', 'jpg');
INSERT INTO base_Options (plugin, keyname, value) VALUES ('hlbook', 'scrn_upload_count', '2');
INSERT INTO base_Options (plugin, keyname, value) VALUES ('hlbook', 'scrn_full_path', '/home/user/hlbook/webdocs/hlbook/full');
INSERT INTO base_Options (plugin, keyname, value) VALUES ('hlbook', 'scrn_thumb_path', '/home/usr/hlbook/webdocs/hlbook/thumbs');
INSERT INTO base_Options (plugin, keyname, value) VALUES ('hlbook', 'scrn_full_url', '/hlbook/full/');
INSERT INTO base_Options (plugin, keyname, value) VALUES ('hlbook', 'scrn_thumb_url', '/hlbook/thumbs/');
INSERT INTO base_Options (plugin, keyname, value) VALUES ('hlbook', 'logstore', 'local');
INSERT INTO base_Options (plugin, keyname, value) VALUES ('base', 'times_in', 'local');
INSERT INTO base_Options (plugin, keyname, value) VALUES ('hlbook', 'logpath', '/home/user/hlbook/webdocs/hlbook/logs');
INSERT INTO base_Options (plugin, keyname, value) VALUES ('hlbook', 'logurl', '/hlbook/webdocs/logs/');
INSERT INTO base_Options (plugin, keyname, value) VALUES ('hlbook', 'email_allow', '1');
INSERT INTO base_Options (plugin, keyname, value) VALUES ('hlbook', 'email_matchtmpl', '/home/user/hlbook/conf/match.tmpl');
INSERT INTO base_Options (plugin, keyname, value) VALUES ('hlbook', 'email_subject', 'Booking Notofication from Playway.net');
# --------------------------------------------------------

#
# Table structure for table `base_Plugins`
#

DROP TABLE IF EXISTS base_Plugins;
CREATE TABLE base_Plugins (
  plugin varchar(24) NOT NULL default '',
  login char(1) NOT NULL default '0',
  acclevel mediumint(8) unsigned NOT NULL default '0',
  disabled char(1) NOT NULL default '0',
  pageviews bigint(20) unsigned NOT NULL default '0',
  deftask varchar(32) NOT NULL default 'index',
  layout varchar(32) default NULL,
  userconfigtable varchar(32) NOT NULL default '',
  title varchar(64) NOT NULL default '',
  author varchar(64) NOT NULL default '',
  version varchar(12) NOT NULL default '1.0',
  description text,
  PRIMARY KEY  (plugin),
  KEY disabled (disabled)
) TYPE=MyISAM;

#
# Dumping data for table `base_Plugins`
#

INSERT INTO base_Plugins (plugin, login, acclevel, disabled, pageviews, deftask, layout, userconfigtable, title, author, version, description) VALUES ('auth', '0', 0, '0', 0, 'login', '0', '0', 'Auth', 'Matt Chambers', '0.4.6', 'Incorporates user login/sessions management and support applictions.');
INSERT INTO base_Plugins (plugin, login, acclevel, disabled, pageviews, deftask, layout, userconfigtable, title, author, version, description) VALUES ('base', '0', 0, '0', 0, 'index', '0', '0', 'PHPStep Base', 'Matt Chambers', '0.4.6', 'Plugin/Page Registry administration.');
INSERT INTO base_Plugins (plugin, login, acclevel, disabled, pageviews, deftask, layout, userconfigtable, title, author, version, description) VALUES ('hlbook', '0', 0, '0', 0, 'book_servers', NULL, 'hlbook_Users', 'Half-Life Server Bookings', 'Matt Chambers', '0.99b1', 'Game Server Booking');
# --------------------------------------------------------

#
# Table structure for table `base_Registry`
#

DROP TABLE IF EXISTS base_Registry;
CREATE TABLE base_Registry (
  id int(10) unsigned NOT NULL auto_increment,
  page varchar(32) NOT NULL default '',
  plugin varchar(24) NOT NULL default '',
  login char(1) NOT NULL default '0',
  acclevel mediumint(8) unsigned NOT NULL default '0',
  disabled char(1) NOT NULL default '0',
  pageviews mediumint(8) unsigned NOT NULL default '0',
  layout varchar(32) default NULL,
  redirect char(1) NOT NULL default '0',
  title varchar(32) NOT NULL default '',
  description text,
  PRIMARY KEY  (id),
  KEY page (page),
  KEY parent (plugin),
  KEY disabled (disabled)
) TYPE=MyISAM;

#
# Dumping data for table `base_Registry`
#

INSERT INTO base_Registry (id, page, plugin, login, acclevel, disabled, pageviews, layout, redirect, title, description) VALUES (1, 'index', 'hlbook', '0', 0, '0', 0, '0', '0', 'Half-Life Match Bookings', NULL);
INSERT INTO base_Registry (id, page, plugin, login, acclevel, disabled, pageviews, layout, redirect, title, description) VALUES (2, 'search_past', 'hlbook', '0', 0, '0', 0, 'results.xml', '0', 'Patch Match History', NULL);
INSERT INTO base_Registry (id, page, plugin, login, acclevel, disabled, pageviews, layout, redirect, title, description) VALUES (3, 'book_servers', 'hlbook', '0', 0, '0', 0, 'servers.xml', '0', 'Available Servers', NULL);
INSERT INTO base_Registry (id, page, plugin, login, acclevel, disabled, pageviews, layout, redirect, title, description) VALUES (4, 'admin_stats', 'hlbook', '1', 2, '0', 0, 'adminstats.xml', '0', 'Admin', NULL);
INSERT INTO base_Registry (id, page, plugin, login, acclevel, disabled, pageviews, layout, redirect, title, description) VALUES (5, 'book_date', 'hlbook', '0', 0, '0', 0, 'servers.xml', '0', 'Select a Date', NULL);
INSERT INTO base_Registry (id, page, plugin, login, acclevel, disabled, pageviews, layout, redirect, title, description) VALUES (6, 'book_time', 'hlbook', '0', 0, '0', 0, 'servers.xml', '0', 'Select Time', NULL);
INSERT INTO base_Registry (id, page, plugin, login, acclevel, disabled, pageviews, layout, redirect, title, description) VALUES (7, 'login', 'auth', '0', 0, '0', 0, '0', '0', 'Login Page', NULL);
INSERT INTO base_Registry (id, page, plugin, login, acclevel, disabled, pageviews, layout, redirect, title, description) VALUES (8, 'logout', 'auth', '1', 0, '0', 0, '0', '1', 'Log Out', NULL);
INSERT INTO base_Registry (id, page, plugin, login, acclevel, disabled, pageviews, layout, redirect, title, description) VALUES (9, 'book_reserve', 'hlbook', '1', 2, '0', 0, 'servers.xml', '0', 'Reserve Server', NULL);
INSERT INTO base_Registry (id, page, plugin, login, acclevel, disabled, pageviews, layout, redirect, title, description) VALUES (10, 'owner_editbook', 'hlbook', '1', 2, '0', 0, 'clbookedit.xml', '0', 'Edit Booking', NULL);
INSERT INTO base_Registry (id, page, plugin, login, acclevel, disabled, pageviews, layout, redirect, title, description) VALUES (11, 'showbook', 'hlbook', '0', 0, '0', 0, 'servers.xml', '0', 'Show Booking', NULL);
INSERT INTO base_Registry (id, page, plugin, login, acclevel, disabled, pageviews, layout, redirect, title, description) VALUES (13, 'bookselect', 'hlbook', '0', 0, '0', 0, '0', '1', '', NULL);
INSERT INTO base_Registry (id, page, plugin, login, acclevel, disabled, pageviews, layout, redirect, title, description) VALUES (14, 'owner_viewclosed', 'hlbook', '1', 0, '0', 0, 'clbookowner.xml', '0', 'View Closed Booking', 'The owner may view his closed booking, edit it, upload shots, download logs, etc,etc.');
INSERT INTO base_Registry (id, page, plugin, login, acclevel, disabled, pageviews, layout, redirect, title, description) VALUES (15, 'owner_ulshots', 'hlbook', '1', 0, '0', 0, 'clbookowner.xml', '0', 'Upload Screenshots', NULL);
INSERT INTO base_Registry (id, page, plugin, login, acclevel, disabled, pageviews, layout, redirect, title, description) VALUES (16, 'owner_editplstats', 'hlbook', '1', 0, '0', 0, 'clbookowner.xml', '0', 'Edit Player Stats', NULL);
INSERT INTO base_Registry (id, page, plugin, login, acclevel, disabled, pageviews, layout, redirect, title, description) VALUES (51, 'owner_serverpanel', 'hlbook', '1', 2, '0', 0, 'ownersmon.xml', '0', 'Server Control Panel', NULL);
INSERT INTO base_Registry (id, page, plugin, login, acclevel, disabled, pageviews, layout, redirect, title, description) VALUES (18, 'owner_servermon', 'hlbook', '1', 0, '0', 0, 'ownersmon.xml', '0', 'ServerMon', NULL);
INSERT INTO base_Registry (id, page, plugin, login, acclevel, disabled, pageviews, layout, redirect, title, description) VALUES (19, 'user_viewclosed', 'hlbook', '0', 0, '0', 0, 'clbookuser.xml', '0', 'View Closed Booking', NULL);
INSERT INTO base_Registry (id, page, plugin, login, acclevel, disabled, pageviews, layout, redirect, title, description) VALUES (20, 'owner_viewshots', 'hlbook', '1', 2, '0', 0, 'clbookowner.xml', '0', 'View Screen Shots', NULL);
INSERT INTO base_Registry (id, page, plugin, login, acclevel, disabled, pageviews, layout, redirect, title, description) VALUES (21, 'user_viewshot', 'hlbook', '0', 0, '0', 0, 'screenshot.xml', '0', 'View Screen Shot', NULL);
INSERT INTO base_Registry (id, page, plugin, login, acclevel, disabled, pageviews, layout, redirect, title, description) VALUES (22, 'user_viewshots', 'hlbook', '0', 0, '0', 0, 'clbookuser.xml', '0', 'View All Screen Shots', NULL);
INSERT INTO base_Registry (id, page, plugin, login, acclevel, disabled, pageviews, layout, redirect, title, description) VALUES (23, 'user_viewbooked', 'hlbook', '0', 0, '0', 0, '0', '0', 'View Booked Match', NULL);
INSERT INTO base_Registry (id, page, plugin, login, acclevel, disabled, pageviews, layout, redirect, title, description) VALUES (24, 'pagenotfound', 'base', '0', 0, '0', 0, 'default.xml', '0', 'Sorry, There was an Error!', NULL);
INSERT INTO base_Registry (id, page, plugin, login, acclevel, disabled, pageviews, layout, redirect, title, description) VALUES (25, 'admin_users', 'hlbook', '1', 4, '0', 0, 'admin_users.xml', '0', 'User Administration', NULL);
INSERT INTO base_Registry (id, page, plugin, login, acclevel, disabled, pageviews, layout, redirect, title, description) VALUES (26, 'admin_usersearch', 'hlbook', '1', 4, '0', 0, 'adminusers.xml', '0', 'Users', NULL);
INSERT INTO base_Registry (id, page, plugin, login, acclevel, disabled, pageviews, layout, redirect, title, description) VALUES (27, 'admin_useredit', 'hlbook', '1', 4, '0', 0, 'adminedituser.xml', '0', 'Edit User', NULL);
INSERT INTO base_Registry (id, page, plugin, login, acclevel, disabled, pageviews, layout, redirect, title, description) VALUES (28, 'admin_usereditbook', 'hlbook', '1', 4, '0', 0, 'adminedituser.xml', '0', 'Edit Booking Settings', NULL);
INSERT INTO base_Registry (id, page, plugin, login, acclevel, disabled, pageviews, layout, redirect, title, description) VALUES (29, 'admin_useradd', 'hlbook', '1', 4, '0', 0, 'adminusers.xml', '0', 'Add User', NULL);
INSERT INTO base_Registry (id, page, plugin, login, acclevel, disabled, pageviews, layout, redirect, title, description) VALUES (30, 'admin_servers', 'hlbook', '1', 4, '0', 0, 'adminservers.xml', '0', 'Servers', NULL);
INSERT INTO base_Registry (id, page, plugin, login, acclevel, disabled, pageviews, layout, redirect, title, description) VALUES (31, 'admin_serveradd', 'hlbook', '1', 4, '0', 0, 'adminservers.xml', '0', 'Add Server', NULL);
INSERT INTO base_Registry (id, page, plugin, login, acclevel, disabled, pageviews, layout, redirect, title, description) VALUES (32, 'admin_serveredit', 'hlbook', '1', 4, '0', 0, 'admineditserver.xml', '0', 'Edit Server', NULL);
INSERT INTO base_Registry (id, page, plugin, login, acclevel, disabled, pageviews, layout, redirect, title, description) VALUES (33, 'admin_serverdelconf', 'hlbook', '1', 4, '0', 0, 'adminservers.xml', '0', 'Delete Server', NULL);
INSERT INTO base_Registry (id, page, plugin, login, acclevel, disabled, pageviews, layout, redirect, title, description) VALUES (34, 'admin_userdelconf', 'hlbook', '1', 4, '0', 0, 'adminusers.xml', '0', 'Delete User', NULL);
INSERT INTO base_Registry (id, page, plugin, login, acclevel, disabled, pageviews, layout, redirect, title, description) VALUES (35, 'admin_servermodes', 'hlbook', '1', 4, '0', 0, 'admineditserver.xml', '0', 'Edit Server Modes', NULL);
INSERT INTO base_Registry (id, page, plugin, login, acclevel, disabled, pageviews, layout, redirect, title, description) VALUES (36, 'admin_servereditopts', 'hlbook', '1', 4, '0', 0, 'admineditserver.xml', '0', 'Edit Server Options', NULL);
INSERT INTO base_Registry (id, page, plugin, login, acclevel, disabled, pageviews, layout, redirect, title, description) VALUES (37, 'admin_options', 'hlbook', '1', 4, '0', 0, 'adminoptions.xml', '0', 'Options', NULL);
INSERT INTO base_Registry (id, page, plugin, login, acclevel, disabled, pageviews, layout, redirect, title, description) VALUES (38, 'admin_bookings', 'hlbook', '1', 4, '0', 0, 'admindefault.xml', '0', 'Admin Bookings', NULL);
INSERT INTO base_Registry (id, page, plugin, login, acclevel, disabled, pageviews, layout, redirect, title, description) VALUES (39, 'admin_maps', 'hlbook', '1', 4, '0', 0, 'adminmaps.xml', '0', 'Admin Maps', NULL);
INSERT INTO base_Registry (id, page, plugin, login, acclevel, disabled, pageviews, layout, redirect, title, description) VALUES (40, 'admin_configs', 'hlbook', '1', 4, '0', 0, 'adminconfigs.xml', '0', 'Admin Maps', NULL);
INSERT INTO base_Registry (id, page, plugin, login, acclevel, disabled, pageviews, layout, redirect, title, description) VALUES (41, 'admin_optionsbase', 'hlbook', '1', 4, '0', 0, 'adminoptions.xml', '0', 'Base Options', NULL);
INSERT INTO base_Registry (id, page, plugin, login, acclevel, disabled, pageviews, layout, redirect, title, description) VALUES (42, 'admin_optionsauth', 'hlbook', '1', 4, '0', 0, 'adminoptions.xml', '0', 'Auth-Session Options', NULL);
INSERT INTO base_Registry (id, page, plugin, login, acclevel, disabled, pageviews, layout, redirect, title, description) VALUES (43, 'admin_bookdel', 'hlbook', '1', 4, '0', 0, 'admindefault.xml', '0', 'Delete Booking', NULL);
INSERT INTO base_Registry (id, page, plugin, login, acclevel, disabled, pageviews, layout, redirect, title, description) VALUES (44, 'admin_bookedit', 'hlbook', '1', 4, '0', 0, 'admindefault.xml', '0', 'Edit Booking', NULL);
INSERT INTO base_Registry (id, page, plugin, login, acclevel, disabled, pageviews, layout, redirect, title, description) VALUES (45, 'admin_mapsadd', 'hlbook', '1', 4, '0', 0, 'adminmaps.xml', '0', 'Add Map', NULL);
INSERT INTO base_Registry (id, page, plugin, login, acclevel, disabled, pageviews, layout, redirect, title, description) VALUES (47, 'owner_addressbook', 'hlbook', '1', 2, '0', 0, 'admindefault.xml', '0', 'Address book', NULL);
INSERT INTO base_Registry (id, page, plugin, login, acclevel, disabled, pageviews, layout, redirect, title, description) VALUES (48, 'owner_profile', 'hlbook', '1', 2, '0', 0, 'admindefault.xml', '0', 'User Profile', NULL);
INSERT INTO base_Registry (id, page, plugin, login, acclevel, disabled, pageviews, layout, redirect, title, description) VALUES (49, 'owner_viewshots', 'hlbook', '1', 2, '0', 0, 'clbookowner.xml', '0', 'View Screenshots', NULL);
INSERT INTO base_Registry (id, page, plugin, login, acclevel, disabled, pageviews, layout, redirect, title, description) VALUES (50, 'owner_mybookings', 'hlbook', '1', 2, '0', 0, 'clbookedit.xml', '0', 'My Bookings', NULL);
INSERT INTO base_Registry (id, page, plugin, login, acclevel, disabled, pageviews, layout, redirect, title, description) VALUES (52, 'owner_viewinprog', 'hlbook', '1', 2, '0', 0, 'ownersmon.xml', '0', 'Match Settings', NULL);
INSERT INTO base_Registry (id, page, plugin, login, acclevel, disabled, pageviews, layout, redirect, title, description) VALUES (53, 'user_viewprog', 'hlbook', '0', 0, '0', 0, 'default.xml', '0', 'In Progress Booking', NULL);
INSERT INTO base_Registry (id, page, plugin, login, acclevel, disabled, pageviews, layout, redirect, title, description) VALUES (54, 'admin_configadd', 'hlbook', '1', 4, '0', 0, 'adminconfigs.xml', '0', 'Add a new config', NULL);
INSERT INTO base_Registry (id, page, plugin, login, acclevel, disabled, pageviews, layout, redirect, title, description) VALUES (55, 'admin_configdel', 'hlbook', '1', 4, '0', 0, 'adminconfigs.xml', '0', 'Delete Config', NULL);
INSERT INTO base_Registry (id, page, plugin, login, acclevel, disabled, pageviews, layout, redirect, title, description) VALUES (56, 'admin_configedit', 'hlbook', '1', 4, '0', 0, 'adminconfigs.xml', '0', 'Edit Config', NULL);
INSERT INTO base_Registry (id, page, plugin, login, acclevel, disabled, pageviews, layout, redirect, title, description) VALUES (57, 'admin_mapsdel', 'hlbook', '1', 4, '0', 0, 'adminmaps.xml', '0', 'Map Delete', NULL);
INSERT INTO base_Registry (id, page, plugin, login, acclevel, disabled, pageviews, layout, redirect, title, description) VALUES (58, 'admin_optionsshots', 'hlbook', '1', 4, '0', 0, 'adminoptions.xml', '0', 'Screenshot Options', NULL);
INSERT INTO base_Registry (id, page, plugin, login, acclevel, disabled, pageviews, layout, redirect, title, description) VALUES (59, 'admin_useremail', 'hlbook', '1', 4, '0', 0, 'adminusers.xml', '0', 'Email All Users', NULL);
INSERT INTO base_Registry (id, page, plugin, login, acclevel, disabled, pageviews, layout, redirect, title, description) VALUES (60, 'owner_bookdel', 'hlbook', '1', 2, '0', 0, 'clbookedit.xml', '0', 'Delete Booking', NULL);
# --------------------------------------------------------

#
# Table structure for table `hlbook_AddrBook`
#

DROP TABLE IF EXISTS hlbook_AddrBook;
CREATE TABLE hlbook_AddrBook (
  addrid mediumint(8) unsigned NOT NULL auto_increment,
  userid mediumint(8) unsigned NOT NULL default '0',
  label varchar(32) NOT NULL default '',
  email varchar(128) NOT NULL default '',
  PRIMARY KEY  (addrid),
  KEY userid (userid)
) TYPE=MyISAM;

#
# Dumping data for table `hlbook_AddrBook`
#

# --------------------------------------------------------

#
# Table structure for table `hlbook_Bookings`
#

DROP TABLE IF EXISTS hlbook_Bookings;
CREATE TABLE hlbook_Bookings (
  matchid mediumint(8) unsigned NOT NULL auto_increment,
  serverid smallint(5) unsigned NOT NULL default '0',
  modid varchar(16) NOT NULL default '',
  posted int(10) unsigned NOT NULL default '0',
  initdate int(10) unsigned NOT NULL default '0',
  timeblock smallint(5) unsigned NOT NULL default '7200',
  userid int(10) unsigned NOT NULL default '0',
  servername varchar(64) default NULL,
  svpasswd varchar(16) default NULL,
  rcon varchar(16) default NULL,
  status enum('S','P','R','E','C','K') NOT NULL default 'S',
  comments text,
  map varchar(32) NOT NULL default '',
  config varchar(32) NOT NULL default '',
  logopen char(1) NOT NULL default '0',
  shots tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (matchid),
  KEY status (status),
  KEY serverid (serverid)
) TYPE=MyISAM;

#
# Dumping data for table `hlbook_Bookings`
#

# --------------------------------------------------------

#
# Table structure for table `hlbook_Configs`
#

DROP TABLE IF EXISTS hlbook_Configs;
CREATE TABLE hlbook_Configs (
  configid int(10) unsigned NOT NULL auto_increment,
  title varchar(32) NOT NULL default '',
  lastupdate int(10) unsigned NOT NULL default '0',
  modid varchar(12) NOT NULL default '',
  filename varchar(32) NOT NULL default '',
  config text NOT NULL,
  website varchar(128) NOT NULL default '',
  KEY configid (configid,modid)
) TYPE=MyISAM;

#
# Dumping data for table `hlbook_Configs`
#

INSERT INTO hlbook_Configs (configid, title, lastupdate, modid, filename, config, website) VALUES (4, 'Playway Advanced', 1044242445, 'cstrike', 'pwadv.cfg', 'mp_friendlyfire 1', 'http://www.playway.net');
# --------------------------------------------------------

#
# Table structure for table `hlbook_Maps`
#

DROP TABLE IF EXISTS hlbook_Maps;
CREATE TABLE hlbook_Maps (
  name varchar(32) NOT NULL default '',
  modid varchar(16) NOT NULL default '',
  PRIMARY KEY  (name)
) TYPE=MyISAM;

#
# Dumping data for table `hlbook_Maps`
#

INSERT INTO hlbook_Maps (name, modid) VALUES ('de_dust', 'cstrike');
INSERT INTO hlbook_Maps (name, modid) VALUES ('de_nuke', 'cstrike');
INSERT INTO hlbook_Maps (name, modid) VALUES ('de_aztec', 'cstrike');
INSERT INTO hlbook_Maps (name, modid) VALUES ('cs_747', 'cstrike');
INSERT INTO hlbook_Maps (name, modid) VALUES ('cs_assault', 'cstrike');
INSERT INTO hlbook_Maps (name, modid) VALUES ('cs_backalley', 'cstrike');
INSERT INTO hlbook_Maps (name, modid) VALUES ('cs_estate', 'cstrike');
INSERT INTO hlbook_Maps (name, modid) VALUES ('cs_havana', 'cstrike');
INSERT INTO hlbook_Maps (name, modid) VALUES ('cs_italy', 'cstrike');
INSERT INTO hlbook_Maps (name, modid) VALUES ('cs_militia', 'cstrike');
INSERT INTO hlbook_Maps (name, modid) VALUES ('cs_office', 'cstrike');
INSERT INTO hlbook_Maps (name, modid) VALUES ('cs_siege', 'cstrike');
INSERT INTO hlbook_Maps (name, modid) VALUES ('de_cbble', 'cstrike');
INSERT INTO hlbook_Maps (name, modid) VALUES ('de_chateau', 'cstrike');
INSERT INTO hlbook_Maps (name, modid) VALUES ('de_dust2', 'cstrike');
INSERT INTO hlbook_Maps (name, modid) VALUES ('de_inferno', 'cstrike');
INSERT INTO hlbook_Maps (name, modid) VALUES ('de_piranesi', 'cstrike');
INSERT INTO hlbook_Maps (name, modid) VALUES ('de_prodigy', 'cstrike');
INSERT INTO hlbook_Maps (name, modid) VALUES ('de_storm', 'cstrike');
INSERT INTO hlbook_Maps (name, modid) VALUES ('de_survivor', 'cstrike');
INSERT INTO hlbook_Maps (name, modid) VALUES ('de_torn', 'cstrike');
INSERT INTO hlbook_Maps (name, modid) VALUES ('de_train', 'cstrike');
INSERT INTO hlbook_Maps (name, modid) VALUES ('de_vegas', 'cstrike');
INSERT INTO hlbook_Maps (name, modid) VALUES ('de_vertigo', 'cstrike');
INSERT INTO hlbook_Maps (name, modid) VALUES ('2fort', 'tfc');
INSERT INTO hlbook_Maps (name, modid) VALUES ('avanti', 'tfc');
INSERT INTO hlbook_Maps (name, modid) VALUES ('badlands', 'tfc');
INSERT INTO hlbook_Maps (name, modid) VALUES ('casbah', 'tfc');
INSERT INTO hlbook_Maps (name, modid) VALUES ('crossover2', 'tfc');
INSERT INTO hlbook_Maps (name, modid) VALUES ('cz2', 'tfc');
INSERT INTO hlbook_Maps (name, modid) VALUES ('dustbowl', 'tfc');
INSERT INTO hlbook_Maps (name, modid) VALUES ('epicenter', 'tfc');
INSERT INTO hlbook_Maps (name, modid) VALUES ('flagrun', 'tfc');
# --------------------------------------------------------

#
# Table structure for table `hlbook_Mods`
#

DROP TABLE IF EXISTS hlbook_Mods;
CREATE TABLE hlbook_Mods (
  modid varchar(16) NOT NULL default '',
  title varchar(32) NOT NULL default '',
  url varchar(128) NOT NULL default '',
  version varchar(12) NOT NULL default '',
  PRIMARY KEY  (modid)
) TYPE=MyISAM;

#
# Dumping data for table `hlbook_Mods`
#

INSERT INTO hlbook_Mods (modid, title, url, version) VALUES ('cstrike', 'Counter-Strike', 'http://www.counter-strike.net', '1.5');
INSERT INTO hlbook_Mods (modid, title, url, version) VALUES ('tfc', 'Team Fortress Classic', 'http://www.valvesoftware.com', '');
# --------------------------------------------------------

#
# Table structure for table `hlbook_Players`
#

DROP TABLE IF EXISTS hlbook_Players;
CREATE TABLE hlbook_Players (
  wonid mediumint(8) unsigned NOT NULL default '0',
  matchid mediumint(8) unsigned NOT NULL default '0',
  serverid tinyint(3) unsigned NOT NULL default '0',
  userid mediumint(8) unsigned NOT NULL default '0',
  conntime int(10) unsigned NOT NULL default '0',
  ip varchar(15) NOT NULL default '0',
  name varchar(32) NOT NULL default '',
  frags tinyint(3) unsigned NOT NULL default '0',
  deaths tinyint(3) unsigned NOT NULL default '0',
  KEY wonid (wonid,serverid,conntime),
  KEY matchid (matchid),
  KEY userid (userid)
) TYPE=MyISAM;

#
# Dumping data for table `hlbook_Players`
#

# --------------------------------------------------------

#
# Table structure for table `hlbook_ServerModes`
#

DROP TABLE IF EXISTS hlbook_ServerModes;
CREATE TABLE hlbook_ServerModes (
  modeid smallint(5) unsigned NOT NULL auto_increment,
  serverid smallint(5) unsigned NOT NULL default '0',
  action enum('public','private','offline','bookable') NOT NULL default 'bookable',
  start time NOT NULL default '00:00:00',
  svpasswd varchar(16) binary NOT NULL default 'none',
  rcon varchar(16) binary NOT NULL default 'none',
  map varchar(32) NOT NULL default '',
  config varchar(32) NOT NULL default '',
  PRIMARY KEY  (modeid),
  KEY serverid (serverid)
) TYPE=MyISAM;

#
# Dumping data for table `hlbook_ServerModes`
#

INSERT INTO hlbook_ServerModes (modeid, serverid, action, start, svpasswd, rcon, map, config) VALUES (1, 1, 'bookable', '00:00:00', 'd97adca063', 'b0z0cz', '', '');
# --------------------------------------------------------

#
# Table structure for table `hlbook_ServerProps`
#

DROP TABLE IF EXISTS hlbook_ServerProps;
CREATE TABLE hlbook_ServerProps (
  serverid smallint(5) unsigned NOT NULL default '0',
  os enum('Windows','Linux','FreeBSD') NOT NULL default 'Linux',
  cpu varchar(32) NOT NULL default '',
  connection varchar(32) NOT NULL default '',
  location varchar(32) NOT NULL default '',
  PRIMARY KEY  (serverid)
) TYPE=MyISAM;

#
# Dumping data for table `hlbook_ServerProps`
#

INSERT INTO hlbook_ServerProps (serverid, os, cpu, connection, location) VALUES (1, 'Linux', 'AMD Athlon XP 2800+', 'DS3', 'East Coast');
# --------------------------------------------------------

#
# Table structure for table `hlbook_Servers`
#

DROP TABLE IF EXISTS hlbook_Servers;
CREATE TABLE hlbook_Servers (
  serverid smallint(5) unsigned NOT NULL auto_increment,
  disabled char(1) NOT NULL default '0',
  mode enum('public','private','bookable','offline') NOT NULL default 'bookable',
  timeblock mediumint(8) unsigned NOT NULL default '0',
  hostname varchar(48) NOT NULL default '',
  ip varchar(15) NOT NULL default '',
  port smallint(5) unsigned NOT NULL default '27015',
  rcon varchar(16) NOT NULL default '',
  defrcon varchar(16) NOT NULL default '',
  modid varchar(12) NOT NULL default '',
  protocol varchar(8) NOT NULL default 'hl',
  error varchar(128) NOT NULL default '',
  PRIMARY KEY  (serverid)
) TYPE=MyISAM;

#
# Dumping data for table `hlbook_Servers`
#

INSERT INTO hlbook_Servers (serverid, disabled, mode, timeblock, hostname, ip, port, rcon, defrcon, modid, protocol, error) VALUES (1, '0', 'bookable', 7200, 'My Server', '127.0.0.1', 27015, 'b0z0cz', 'b0z0cz', 'cstrike', 'hl', '');
# --------------------------------------------------------

#
# Table structure for table `hlbook_Users`
#

DROP TABLE IF EXISTS hlbook_Users;
CREATE TABLE hlbook_Users (
  userid mediumint(8) unsigned NOT NULL default '0',
  expdate int(10) unsigned NOT NULL default '0',
  maxscheduled tinyint(3) unsigned NOT NULL default '0',
  maxbooks tinyint(3) unsigned NOT NULL default '0',
  prebook tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (userid)
) TYPE=MyISAM;

#
# Dumping data for table `hlbook_Users`
#

INSERT INTO hlbook_Users (userid, expdate, maxscheduled, maxbooks, prebook) VALUES (1, 0, 0, 0, 0);

