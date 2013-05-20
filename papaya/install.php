<?php
/**
* Installer
*
* @copyright 2002-2009 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya
* @subpackage Administration
* @version $Id: install.php 37690 2012-11-19 14:57:24Z weinert $
*/

/**
* Configuration file
*/
require_once("./inc.conf.php");
require_once("./inc.func.php");

define('PAPAYA_ADMIN_PAGE', TRUE);

/**
* check include path - try to include installer
*/
if (defined('PAPAYA_DBG_DEVMODE') && PAPAYA_DBG_DEVMODE) {
  $found = include_once(PAPAYA_INCLUDE_PATH.'system/papaya_installer.php');
} else {
  $found = @include_once(PAPAYA_INCLUDE_PATH.'system/papaya_installer.php');
}

if (!$found) {
  //no installer found - output error message and exit
  ?>
  <html>
    <head>
      <title>Papaya-CMS Installer</title>
      <style type="text/css">
        body {
          font-family: Verdana, Arial, Helvetic, sans serif;
          font-size: 14px;
          background-color: threedface;
        }
        h1 {
          width: 400px;
          margin: auto;
          float: center;
          margin-top: 40px;
          margin-bottom: 40px;
          padding: 4px;
          text-align: center;
        }
        div.error {
          width: 400px;
          float: center;
          margin: auto;
          background-color: window;
          color: windowtext;
          border: 1px inset threedface;
          margin-top: 20px;
          margin-bottom: 20px;
          padding: 4px;
        }
        .optname {
          font-family: monospace;
          font-weight: bold;
        }
        .filename {
          font-family: monospace;
        }
        div.options {
          width: 600px;
          float: center;
          margin: auto;
          background-color: window;
          color: windowtext;
          border: 1px inset threedface;
          margin-top: 20px;
          margin-bottom: 20px;
          padding: 0px;
        }
        div.options table {
          width: 100%;
          background-color: window;
          color: windowtext;
          border-collapse: collapse;
        }
        div.options table th {
          border: 1px solid threedshadow;
          font-size: 13px;
        }
        div.options table td {
          border: 1px solid threedshadow;
          font-weight: bold;
          font-family: monospace;
          font-size: 12px;
        }
        div.options table td + td {
          font-weight: normal;
          font-size: 12px;
        }
      </style>
    </head>
    <body>
      <h1>Error</h1>
      <div class="error">
        The system can not find the class framework. Please check the
        <span class="optname">PAPAYA_INCLUDE_PATH</span> in the
        <span class="filename">conf.inc.php</span> in your install path. The
        <span class="optname">PAPAYA_INCLUDE_PATH</span> can be an absolute path or a
        subdirectory of your <span class="optname">include_path</span> in the php
        configuration.
      </div>
      <div class="options">
        <table summary="Current option values">
          <thead>
            <tr>
              <th>Name</th>
              <th>Value</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>DOCUMENT_ROOT</td>
              <td><?php echo $_SERVER['DOCUMENT_ROOT'] ?></td>
            </tr>
            <tr>
              <td>include_path</td>
              <td><?php echo get_include_path(); ?></td>
            </tr>
            <tr>
              <td>PAPAYA_INCLUDE_PATH</td>
              <td>
                <?php echo defined('PAPAYA_INCLUDE_PATH') ? PAPAYA_INCLUDE_PATH : ''; ?>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </body>
  </html>

  <?php
} else {
  //installer found - continue installation

  /**
  * button glyphs
  */
  include_once('./inc.glyphs.php');

  /**
  * Error handling
  */
  include_once(PAPAYA_INCLUDE_PATH.'system/papaya_errors.php');
  $PAPAYA_MSG = new papaya_errors();

  /**
  * Application object
  */
  include_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Autoloader.php');
  spl_autoload_register('PapayaAutoloader::load');
  $application = PapayaApplication::getInstance();
  $application->registerProfiles(
    new PapayaApplicationProfilesCms()
  );
  $application->response = new PapayaResponse();
  $application->images = new PapayaUiImages($PAPAYA_IMAGES);

  // check if the options table is present
  $installer = new papaya_installer();
  $status = $installer->getCurrentStatus();

  $application->options->defineConstants();

  if ($application->options->get('PAPAYA_UI_SECURE', FALSE) &&
      !PapayaUtilServerProtocol::isSecure()) {
    $url = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    redirectToURL($url);
  }

  $application->messages->setUp($application->options);

  header('Content-type: text/html; charset=utf-8');

  /**
  * layout object
  */
  include_once(PAPAYA_INCLUDE_PATH.'system/papaya_xsl.php');
  $PAPAYA_LAYOUT = new papaya_xsl(
    dirname(__FILE__)."/skins/".$application->options->get('PAPAYA_UI_SKIN')."/style.xsl"
  );

  $PAPAYA_LAYOUT->setParam('PAGE_TITLE', _gt('Administration').' - '._gt('Installation / Update'));
  $PAPAYA_LAYOUT->setParam('PAGE_ICON', $application->images['categories-installer']);

  $PAPAYA_LAYOUT->setParam('PAGE_PROJECT', '');
  $PAPAYA_LAYOUT->setParam('PAGE_USER', 'NO USER');

  //create session
  define('PAPAYA_ADMIN_SESSION', TRUE);
  $application->session->setName(
    'sid'.$application->options->get('PAPAYA_SESSION_NAME', '').'admin'
  );

  $application->session->options->cache = PapayaSessionOptions::CACHE_NONE;
  if ($redirect = $application->session->activate(TRUE)) {
    $redirect->send();
    exit();
  }

  if (!PapayaUtilServerProtocol::isSecure()) {
    $dialog = new PapayaUiDialog();
    $dialog->caption = new PapayaUiStringTranslated('Warning');
    $url = new PapayaUrlCurrent();
    $url->setScheme('https');
    $dialog->action($url->getUrl());
    $dialog->fields[] = new PapayaUiDialogFieldMessage(
      PapayaMessage::TYPE_WARNING,
      new PapayaUiStringTranslated(
        'If possible, please use https to access the administration interface.'
      )
    );
    $dialog->buttons[] = new PapayaUiDialogButtonSubmit(
      new PapayaUiStringTranslated('Use https')
    );
    $PAPAYA_LAYOUT->add($dialog->getXml());
  }

  //setup and run installer object
  $installer->msgs = &$PAPAYA_MSG;
  $installer->options = $application->options;
  $installer->images = $application->images;
  $installer->layout = &$PAPAYA_LAYOUT;


  $installer->initialize();
  $installer->execute();

  if (!$installer->rpcResponseSent) {
    $PAPAYA_LAYOUT->add($PAPAYA_MSG->get(), 'messages');
    print $PAPAYA_LAYOUT->xhtml();
  }
}