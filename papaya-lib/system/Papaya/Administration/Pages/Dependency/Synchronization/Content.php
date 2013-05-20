<?php
/**
* Synchronize view and content of the page working copy
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
* @version $Id: Content.php 36450 2011-11-24 15:01:03Z weinert $
*/

/**
* Synchronize view and content of the page working copy
*
* @package Papaya-Library
* @subpackage Administration
*/
class PapayaAdministrationPagesDependencySynchronizationContent
  implements PapayaAdministrationPagesDependencySynchronization {

  /**
  * Translation records object
  *
  * @var PapayaContentPageTranslations
  */
  private $_translations = NULL;

  /**
  * Synchronize a dependency
  *
  * @param array $targetIds
  * @param integer $originId
  * @param array|NULL $languages
   */
  public function synchronize(array $targetIds, $originId, array $languages = NULL) {
    $this->translations()->load($originId);
    if (empty($languages)) {
      $languages = array_keys(PapayaUtilArray::ensure($this->translations()));
    }
    $existing = $this->getExistingTargetTranslations($targetIds, $languages);
    $missing = $this->getMissingTargetTranslations($targetIds, $languages, $existing);
    return $this->synchronizeTranslations($originId, $languages, $existing, $missing);
  }

  /**
  * Getter/Setter for the translation records list.
  *
  * @param PapayaContentPageTranslations $translations
  * @return PapayaContentPageTranslations
  */
  public function translations(PapayaContentPageTranslations $translations = NULL) {
    if (isset($translations)) {
      $this->_translations = $translations;
    } elseif (is_null($this->_translations)) {
      $this->_translations = new PapayaContentPageTranslations();
    }
    return $this->_translations;
  }

  /**
  * Determine the existing target translations (to decide between updates and inserts)
  *
  * @param array $targetIds
  * @param array $languageIds
  * @return array
  */
  protected function getExistingTargetTranslations(array $targetIds, array $languageIds) {
    $databaseAccess = $this->translations()->getDatabaseAccess();
    $filter = $databaseAccess->getSqlCondition(
      array(
        'topic_id' => $targetIds,
        'lng_id' => $languageIds
      )
    );
    $sql = "SELECT topic_id, lng_id
              FROM %s
             WHERE $filter";
    $parameters = array(
      $databaseAccess->getTableName(PapayaContentTables::PAGE_TRANSLATIONS)
    );
    $result = array();
    if ($databaseResult = $databaseAccess->queryFmt($sql, $parameters)) {
      while ($row = $databaseResult->fetchRow(PapayaDatabaseResult::FETCH_ASSOC)) {
        $result[$row['lng_id']][] = $row['topic_id'];
      }
    }
    return $result;
  }

  /**
  * Get the missing target translations using the already found existing ones.
  *
  * @param array $targetIds
  * @param array $languageIds
  * @param array $existing
  * @return array
  */
  protected function getMissingTargetTranslations(
                    array $targetIds, array $languageIds, array $existing
                  ) {
    $result = array();
    foreach ($languageIds as $languageId) {
      foreach ($targetIds as $targetId) {
        if (!(
              isset($existing[$languageId]) &&
              is_array($existing[$languageId]) &&
              in_array($targetId, $existing[$languageId])
             )) {
          $result[$languageId][] = $targetId;
        }
      }
    }
    return $result;
  }

  /**
  * Load each translation of the current page and sync them with the target pages.
  *
  * @param integer $originId
  * @param array $languages
  * @param array $existing
  * @param array $missing
  * @return boolean
  */
  public function synchronizeTranslations(
                    $originId, array $languages, array $existing, array $missing
                  ) {
    $databaseAccess = $this->translations()->getDatabaseAccess();
    foreach ($languages as $languageId) {
      $translation = $this->translations()->getTranslation($originId, $languageId);
      if ($translation->pageId > 0) {
        if (isset($existing[$languageId])) {
          if (!$this->updateTranslations($translation, $existing[$languageId])) {
            return FALSE;
          }
        }
        if (isset($missing[$languageId])) {
          if (!$this->insertTranslations($translation, $missing[$languageId])) {
            return FALSE;
          }
        }
      } else {
        if (isset($existing[$languageId])) {
          $this->deleteTranslations($languageId, $existing[$languageId]);
        }
      }
    }
    return TRUE;
  }

  /**
  * Update content data of existing translations
  *
  * @param array $languages
  * @param array $existing
  * @param array $missing
  * @return boolean
  */
  protected function updateTranslations(PapayaContentPageTranslation $origin, $targetIds) {
    $databaseAccess = $origin->getDatabaseAccess();
    return FALSE !== $databaseAccess->updateRecord(
      $databaseAccess->getTableName(PapayaContentTables::PAGE_TRANSLATIONS),
      array(
        'topic_content' => PapayaUtilStringXml::serializeArray($origin->content),
        'topic_trans_modified' => $origin->modified
      ),
      array(
        'lng_id' => $origin->languageId,
        'topic_id' => $targetIds
      )
    );
  }

  /**
  * Insert missing translations
  *
  * @param array $languages
  * @param array $existing
  * @param array $missing
  * @return boolean
  */
  protected function insertTranslations($origin, $targetIds) {
    foreach ($targetIds as $targetId) {
      $target = clone $origin;
      $target->pageId = $targetId;
      if (!$target->save()) {
        return FALSE;
      }
    }
    return TRUE;
  }

  /**
  * Delete deprecated translations
  *
  * @param array $languages
  * @param array $existing
  * @param array $missing
  * @return boolean
  */
  protected function deleteTranslations($languageId, $targetId) {
    $databaseAccess = $this->translations()->getDatabaseAccess();
    return FALSE !== $databaseAccess->deleteRecord(
      $databaseAccess->getTableName(PapayaContentTables::PAGE_TRANSLATIONS),
      array(
        'lng_id' => $languageId,
        'topic_id' => $targetId
      )
    );
  }
}