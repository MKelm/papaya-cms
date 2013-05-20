<?php
/**
* Box to display forum content.
**************************************************************************************
* +--------------+        +----------------+  * show/browse categories
* | actbox_forum |------->| box_forum      |  * list forums in categories
* +--------------+        +----------------+  * list topics in forum
*       <>                                    * list threads in topics
*        |                 +--------------+   * show entries in threads
*        +-----------------| output_forum |   * new topics/threads
*                          +--------------+   * new entries
*                                 |           * page comments
*                                 |
*                                \|/
*                          +--------------+
*                          | base_forum   |
*                          +--------------+
**************************************************************************************
* This box module is used to display a forum in a papaya box. This module
* is set to either a node in the category tree or a forum directly.
* The type of representation depends on the selected mode: BBS, Threaded, Threaded BBS.
* When in category mode, the user can browse through the forum categories
* and select any forum he finds. In forum mode, when provided with a specific forum,
* the user is restricted to only one forum. User rights may be set for specific actions
* within the forum this page shows allowing editors to restrict access to specific
* forums. (@see content_forum)
*************************************************************************************
*
* @copyright 2002-2011 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Modules
* @subpackage Free-Forum
*/

/**
* Base class for forum or comments box.
*/
require_once(dirname(__FILE__).'/box_forum.php');

/**
* Box to display forum content.
*
* @package Papaya-Modules
* @subpackage Free-Forum
*/
class actionbox_forum extends box_forum {

  /**
  * Contains the final purpose of this box module.
  * @var integer
  */
  protected $_purpose = base_forum::SHOW_FORUM;

}