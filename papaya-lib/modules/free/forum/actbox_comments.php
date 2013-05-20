<?php
/**
* Box to display page comments.
**************************************************************************************
* +-----------------+        +----------------+  * page comments
* | actbox_comments |------->| box_forum      |
* +-----------------+        +----------------+
*       <>
*        |                 +--------------+
*        +-----------------| output_forum |
*                          +--------------+
*                                 |
*                                 |
*                                \|/
*                          +--------------+
*                          | base_forum   |
*                          +--------------+
**************************************************************************************
* This box module is used to make papaya boxes to comment pages. This module
* is set to either a node in the category tree or a forum directly, which it
* affects. The type of representation depends on the BBS,Threaded, Threaded BBS
* settings. User rights may be set for specific actions within the forum this page
* shows allowing editors to restrict access to specific forums. (@see content_forum)
*
* Contributed comments will be stored within the forum database structure. If there is no forum
* within the provided category, one is created for each page having a comments box
* attached to, where for each user comment another thread is created. Users can
* answer to topics and subthreads as well. The page the comments box is attached to
* is determined automatically by reading the parent page's id and using this. This
* way it is enough to just place one of these boxes on a specific page to gain
* user comments for this page.
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
* Box to display page comments.
*
* @package Papaya-Modules
* @subpackage Free-Forum
*/
class actionbox_comments extends box_forum {

  /**
  * Contains the final purpose of this box module.
  * @var integer
  */
  protected $_purpose = base_forum::SHOW_COMMENTS;

  /**
  * Remove threats per page option.
  *
  * @var object $aOWner
  * @var string $paramName
  */
  public function __construct(&$aOwner, $paramName = NULL) {
    parent::__construct($aOwner, $paramName);
    // remove thread paging limit, a comment box contains entries only
    unset($this->editGroups[0][2]['perpage']);
    $this->data['perpage'] = NULL;
  }

}