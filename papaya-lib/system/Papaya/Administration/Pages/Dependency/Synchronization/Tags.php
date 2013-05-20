<?php
/**
* Synchronize assigned tags of the page
*
* @copyright 2011 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Library
* @subpackage Administration
* @version $Id: Tags.php 36219 2011-09-16 13:38:06Z weinert $
*/

/**
* Synchronize assigned tags of the page
*
* @package Papaya-Library
* @subpackage Administration
*/
class PapayaAdministrationPagesDependencySynchronizationTags
  implements PapayaAdministrationPagesDependencySynchronization {

  /**
  * buffer variable for the page tags content object
  *
  * @var PapayaContentPageTags
  */
  private $_tags = NULL;

  /**
  * Synchronize the tags of the page dependencies
  *
  * @param array $targetIds
  * @param integer $originId
  * @param array|NULL $languages
   */
  public function synchronize(array $targetIds, $originId, array $languages = NULL) {
    if ($this->tags()->load($originId)) {
      $tagIds = array();
      foreach ($this->tags() as $tag) {
        $tagIds[] = $tag['id'];
      }
      foreach ($targetIds as $targetId) {
        if (!$this->synchronizeTags($targetId, $tagIds)) {
          return FALSE;
        }
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Getter/Setter for the tags subobject
  *
  * @param PapayaContentPageTags $tags
  */
  public function tags(PapayaContentPageTags $tags = NULL) {
    if (isset($tags)) {
      $this->_tags = $tags;
    } elseif (is_null($this->_tags)) {
      $this->_tags = new PapayaContentPageTags();
    }
    return $this->_tags;
  }

  /**
  * Syncronize/set the tags of one taget page
  *
  * @param PapayaContentPageTags $tags
  */
  public function synchronizeTags($targetId, array $tagIds) {
    if ($this->tags()->clear($targetId)) {
      if (!empty($tagIds)) {
        return $this->tags()->insert($targetId, $tagIds);
      } else {
        return TRUE;
      }
    } else {
      return FALSE;
    }
  }
}