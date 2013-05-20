<?php
/**
* XML Output for Ajax/Flash-Requests
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
* @version $Id: xmltree.php 37256 2012-07-19 14:30:38Z weinert $
*/

/**
* Authentication
*/
require_once("./inc.auth.php");

if ($PAPAYA_SHOW_ADMIN_PAGE) {
  /**
  * RPC Object
  */
  include_once(PAPAYA_INCLUDE_PATH.'system/papaya_rpc.php');

  $rpcCall = new papaya_rpc();
  $rpcCall->images = &$PAPAYA_IMAGES;
  $rpcCall->msgs = &$PAPAYA_MSG;
  $rpcCall->authUser = &$PAPAYA_USER;

  $rpcCall->initialize();
  $rpcCall->execute();

  $str = $rpcCall->getXML();

  header('Content-Type: text/xml; charset=utf-8');
  //echo '<?xml version="1.0" encoding="UTF-8" ?','>';
  echo $str;
}

?>