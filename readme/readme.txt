-----------------------------------------------------------------------
|  papaya CMS                                                         |
|  Version: 5.2                                                       |
|  Copyright: 2002-2010 papaya Software GmbH                          |
|  Website: http://www.papaya-cms.com                                 |
-----------------------------------------------------------------------

The actual full documentation about the installation is available at:

http://www.papaya-cms.com/documentation.989.en.html (english)

http://www.papaya-cms.com/dokumentation.989.de.html (german, more comprehensive)

-----------------------------------------------------------------------

  General

  Licence

  System Requirements

  Installation
    1. Copying files
    2. Customization for the server
    3. Setting permissions

  Initialization and Configuration
    1. Initialize Database
    2. Configuration
    3. Users and Passwords

  Troubleshooting

  Appendix
    A Installing papaya CMS in a subdirectory
    B Rewrite Rules in httpd.conf
    C Apache mod_vhost_alias
    D MySQL >= 4.1 and character sets


------------
| General  |
------------

Thank you for downloading papaya CMS and testing/using it. Usage is 
free, under the conditions described in the GNU General Public 
Licence, version 2 (GPL V2).

papaya CMS is an Open Source CMS aiming primarily at large-scale
websites and complex web applications. It does not use any proprietary
templating- or scripting-languages but is entirely based upon open standards
(e.g. PHP, MySQL/PostgreSQL, XSL/XSLT etc.).

Please point your browser to http://www.papaya-cms.com/index.1017.en.html
to get an overview of papaya's capabilities.

Up-to-date information can be obtained from
[http://www.papaya-cms.com].


------------
| Licence  |
------------

papaya CMS is subject to the GNU General Public Licence, version 2
(GPL V2). See gpl.txt for the complete text of the GPL.

See credits.txt for a list of other open source software included in
this release of papaya CMS.


------------------------
| System Requirements  |
------------------------

Server:
  Apache httpd 2.x
    mod_rewrite
  PHP >= 5.2
    XML (ext/xml)
    XSLT (ext/xsl)
    MySQL or PostgreSQL(ext/mysql, ext/mysqli or ext/pgsql)
    Sessions (ext/session)
    PCRE (ext/pcre)
    GD (ext/gd)
  MySQL >= 4.1.x or PostgreSQL >= 8.0

Client (for Administration):
  Webbrowser (Firefox recommended)
    JavaScript
    Flash

Client (for output with default templates):
  Webbrowser
    JavaScript (optional - for popups and flash)


------------------
|  Installation  |
------------------

1. Copying Files

There are four directories and three files in the install directory.

  papaya-data/    - Data directory for papaya CMS
  papaya-lib/     - papaya class library

  papaya/         - papaya administration interface
  papaya-themes/  - Themes - CSS files and layout images, miscellaneous
                    script files (flash identification, popups, ...)

  .htaccess       - RewriteRules for Apache
  conf.inc.php    - Base configuration (database access, library path)
  index.php       - index page

Copy the first two directories ("papaya-data" and "papaya-lib") to a
directory outside of the document root of your webserver. Copy the
remaining directories, as well as the files ".htaccess" and "index.php"
to the document root of your webserver. (note: if you can't see the 
.htaccess files, remember that Unix-type operating systems don't list
files with a leading dot by default.)


2. Server Customization.

You have to modify two values in conf.inc.php.

1) The database address (PAPAYA_DB_URI) following this scheme:

   "protocol://user:password@hostname/database"

   e.g.
   "mysql://web1:secret@localhost/usr_web1_1"

   You should have received this information from your ISP or System         
   Administrator.


2) The value for PAPAYA_INCLUDE_PATH is the path to the papaya class
   framework.

   You can use an absolute path or a path below the php include path.
   Possible values are:

   "/home/www/web1/files/lib/papaya-lib/" or "lib/papaya-lib/"

   Copy the modified file to the document root of your webserver.


3) Setting permissions

3a) Setting Permissions for Windows (XP, 2003 server, and higher)

    Write permission has to be granted to the webserver for the folder  
    "papaya-data". File permissions can be set by using Windows 
    Explorer and right-clicking on the folder.  However, Windows 
    installations do not usually require permissions to be set.

    papaya CMS is now installed. Continue on to the 
    *Initialization and Configuration* section of this document.

3b) Setting Permissions for Unix (linux, unix, BSD etc)

   Write permission has to be granted to the webserver for the 
   directory "papaya-data". File permissions can be set by your FTP 
   client. Set the permissions for this directory to "0777".

                user  group others
   Read           X     X     X
   Write          X     X     X
   Execute        X     X     X

   More restrictive permissions may be possible. Please ask your server
   administrator.

   papaya CMS is now installed. Continue on to the *Initialization and 
   Configuration* section of this document.


--------------------------------------
|  Initialization and Configuration  |
--------------------------------------

1. Start install script

Open http://www.domain.tld/papaya/install.php with your webbrowser
(replace www.domain.tld with your own domain). The start page of the
installation script is displayed. The start page contains a couple of
links to the FAQ, the installation forum, the support page, and the
papaya website.

Click on "Next" to get to the next step.


2. Agree to license

The next step of the insall script will display a copy of the GPL. You
need to accept the license agreement in order to proceed. Do do so, click
on "Accept license".


3. Check system

In the following step of the installation, the script will check if your
system is compatible with papaya CMS and whether all needed extensions
are available. If this is the case, you can go on with the next step of the
installation. Do so by clicking on "Next".


4. Define PAPAYA_PATH_DATA and set up admin account

4a. Set path for PAPAYA_PATH_DATA

Enter the path to the directory papaya-data for the option PAPAYA_PATH_DATA.
Please provide an absolute path.

4b Set up account for the administrator

Enter the givenname, surname, the email address, the login name as well as
the password. Click on "Save".


5. Set up the configuration table

In the next step of the install script, you are prompted to create the 
configuration table. Click on "Create" to create the configuration table
and proceed with the installation.

NOTE - The prompt isn't displayed when the database connection hasn't
       been configured properly.


6. Initialize database

Once the configuration table is created, you will see a list of database
tables as well as the following menu.

 1) Analyze database

    Checks existing tables in the database.
    (disabled when none of the necessary tables exists)

 2) Update database

    Create missing tables and update existing tables.
    (disabled if no modifications are necessary)

 3) Insert default data

    Insert default values in selected tables. 
    WARNING :: EXISTING DATA WILL BE DELETED FROM THE TABLE!
    (you can perform this operation multiple times)

 4) Check options and modules

    Check options, set default values and look for installed modules
    (you can perform this operation multiple times)

 5) Go to admin interface

    Opens the user admin interface.

Klick on each option, one after one. The installation tool will
modify existing tables without deleting data. The tool can be reused
when you want to update your system, without losing your content.

Tables for additional modules (e.g. forum) can be installed later, via 
the administration module.

The database for papaya CMS is now ininitialized, and papaya CMS is ready 
for configuration. When you click on the link in step "5) Go to admin 
interface", you will automatically be logged in in the papaya backend where
you can start configuring papaya CMS.


7. Configuration

7a) Login after database initialization

In case you have interrupted the installation procedure for papaya CMS and 
want to configure papaya CMS at a later time, you need to log into the backend
of papaya CMS:

  a) Open http://www.domain.tld/papaya/ with your webbrowser. Please replace
     www.domain.tld with your actual domain where you have installed papaya
     CMS.
  b) Log in using your username and password. You have entered a username and
     a password when you have configured the account for the default 
     administrator and should have the account information already.

In case you have continued with the installation directly after the database
initialization, you will be logged in automatically.

7b) Going on with the configuration

Klick on the button "Settings" in the menu group "Administration". The  system
settings section of papaya CMS is opened.

Important options:

  Files and Directories
    PAPAYA_PATH_DATA   - Path to data directory (papaya-data/)
    PAPAYA_PATH_WEB    - Path below webroot (/)

  Layout
    PAPAYA_LAYOUT_TEMPLATES - XSLT directory
    PAPAYA_LAYOUT_THEME     - directory containing CSS and layout 
                              images

IMPORTANT - Sometimes, the option PAPAYA_PATH_DATA cannot be set during
            installation. You can recognize a failed setting if a value 
            is displayed for this option, but the option is set between
            brackets. You will have to edit and save the option. After
            saving the option, the brackets will disappear.

Click on "Check paths" after setting the option PAPAYA_PATH_DATA. 
The system checks the data path permissions and creates 
necessary subdirectories for the media database if they don't exist.


8. Users and passwords

Click in the menu group "Administration" on "Users". In the user admninistration,
you can create an account for each new user.

NOTE - Create a user account for each author. Each page's author will 
       then be displayed as part of the page information.

---------------------
|  Troubleshooting  |
---------------------

If you have any problems installing or using papaya CMS, please consult
the following resources. This helps us to help you by spending less time
repeatedly answering questions answered elsewhere and concentrate on 
developing the system. It also helps us conserve what little hair we have
remaining.

1) Read the FAQ:           http://www.papaya-cms.com/faq/

2) Read the docs:          http://www.papaya-cms.com/docs/

3) Search the forum:       http://www.papaya-cms.com/forum/

4) Steps 1 - 3 didn't help?
   -> Write a message in our forum (http://www.papaya-cms.com/forum/)
      Please try to give as much information about your problem as 
      possible (ie, operating system, version numbers etc).  This will
      not only help us to track down the problem, but also help those
      users with similar problems who come after you.


--------------
|  Appendix  |
--------------

A - Install papaya CMS in a subdirectory

It is possible to install papaya CMS in a subdirectory of your 
webserver.  You will have to modify the .htaccess file to point to the 
subdirectory.  The .htaccess file must remain in your document root. 
You can find an example .htaccess in this directory (htaccess.tpl). 
Substitute the directory name for the placeholder {%webpath_pages%}.

Examples:

  pages/
  cms/page/

Note that you may not enter a leading slash (before path), but have to
add a trailing slash (after path).


B - Rewrite Rules in httpd.conf

You can put the content of your .htaccess directly in your webservers
configuration file. If possible, use a per-directory configuration.

The .htaccess is then no longer needed and can be disabled, or completely 
removed.


C - Apache mod_vhost_alias

If you use mod_vhost_alias, the PHP superglobal $_SERVER['DOCUMENT_ROOT']
will give a false value. The installer will fail to calculate the 
correct paths. In this case, you have to manually correct the paths in 
your conf.inc.php, and add the following line:

$_SERVER['DOCUMENT_ROOT'] = '/path/vhosts/hostname/';

Please replace '/path/vhosts/hostname/' with the actual path to the virtual
document root of your papaya installation on your webserver. 


The paths of the Rewrite Rules in the .htaccess file have to be 
corrected as well. If you installed papaya CMS directly into your 
document root, you can use the .htaccess from the files directory of 
your install package.


D - MySQL >= 4.1 and character sets

Starting with Version 4.1, MySQL supports unicode character sets. If
you use MySQL 4.1 or higher, make sure the tables use UTF-8 as default 
character set. You can verify this by looking at the table's collation. 
It has to start with "utf8" (e.g. utf8_general_ci).
