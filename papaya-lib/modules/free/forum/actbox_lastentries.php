<?php
/**
* Box to show last forum entries.
**************************************************************************************
* +--------------------+        +----------------+  * list recent entries
* | actbox_lastentries |------->| base_actionbox |
* +--------------------+        +----------------+
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
* The only purpose of this box module is to render short information about the
* most recent changes within forums into a papaya-box. The forum page is provided
* to point to the papaya-pageId where the main forum page is stored. This way a user
* may click on one of the entries in this box to get to the corresponding thread
* on the forum page directly.
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
* @version $Id: actbox_lastentries.php 37761 2012-11-30 16:50:26Z smekal $
*/

/**
* Base class for boxes.
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_actionbox.php');

/**
* Output / Base class for forum.
*/
require_once(dirname(__FILE__).'/output_forum.php');

/**
* Box to show last forum entries.
*
* @package Papaya-Modules
* @subpackage Free-Forum
*/
class actionbox_lastentries extends base_actionbox {

  /**
  * Preview ?
  * @var boolean $preview
  */
  public $preview = TRUE;

  /**
  * Module parameter name
  * @var string
  */
  public $paramName = 'ff';

  /**
  * Content edit fields
  * @var array $editFields
  */
  public $editFields = array(
    'forum' => array('Forum', 'isNoHTML', TRUE, 'function', 'getForumCombo', NULL),
    'lastcount' => array('Show last entries', 'isNum', TRUE, 'input', 2, NULL, 3),
    'forumpage' => array('Forum page', 'isNum', TRUE, 'pageid', 5, NULL, NULL),
    'linkname' => array(
      'Link name',
      'isNoHTML',
      TRUE,
      'input',
      50,
      'Pagename in links, e.g. default value "forum" makes a link like "forum.[id].html".',
      'forum'
    )
  );

  /**
  * Get parsed data.
  *
  * @return string $str XML
  */
  public function getParsedData() {
    $this->setDefaultData();

    $moduleConfiguration = $this->data;
    $moduleConfiguration['purpose'] = 2;
    $moduleConfiguration['post_answers'] = FALSE;
    $moduleConfiguration['post_questions'] = FALSE;
    $moduleConfiguration['subscribe_threads'] = FALSE;

    include_once(dirname(__FILE__).'/output_forum.php');
    $forumOutput = new output_forum();
    if (isset($this->data['forumpage'])) {
      $forumOutput->setPageData($this->data['linkname'], $this->data['forumpage']);
    }
    $forumOutput->initialize($this, $moduleConfiguration);
    if (isset($this->data['forum'])) {
      $baseConfiguration = $forumOutput->decodeForumData($this->data['forum']);
    } else {
      $baseConfiguration = array('mode' => '', 'id' => '');
    }
    return $forumOutput->getOutputLastEntries(
      $baseConfiguration['mode'] == 'categ' ? $baseConfiguration['id'] : NULL,
      $baseConfiguration['mode'] == 'forum' ? $baseConfiguration['id'] : NULL,
      NULL,
      $this->data['lastcount']
    );
  }

  /**
  * Get forum combo from output object.
  *
  * @param string $name
  * @param array $field
  * @param string $data
  * @return string xml
  */
  public function getForumCombo($name, $field, $data) {
    include_once(dirname(__FILE__).'/output_forum.php');
    $forumOutput = new output_forum();
    return $forumOutput->getForumCombo($name, $field, $data);
  }

}