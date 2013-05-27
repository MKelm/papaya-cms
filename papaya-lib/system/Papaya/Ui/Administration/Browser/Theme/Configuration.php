<?php
/**
* Configuration manager for themes
*
* @copyright 2010 by papaya Software GmbH - All rights reserved.
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
* @subpackage Ui
* @version $Id: Configuration.php 35573 2011-03-29 10:48:18Z weinert $
*/

/**
* Configuration class for themes
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiAdministrationBrowserThemeConfiguration {

  /**
  * DOM Document for parsing XML.
  * @var DOMDocument
  */
  private $_domDocument;

  /**
  * XML configuration file of theme.
  * @var string
  */
  private $_themeConfigurationFile = '~theme(-.+)?\.xml~i';

  /***************************************************************************/
  /** Methods                                                                */
  /***************************************************************************/

  /**
  * Retrieves themes configurations.
  * Returned array structure:
  * array(
  *   'name' => {THEME NAME}
  *   'templates' => {TEMPLATE DIRECTORY}
  *   'version' => {VERSION NUMBER}
  *   'date' => {ISO DATE}
  *   'author' => {AUTHOR NAME}
  *   'description' => {DESCRIPTION TEXT}
  *   'thumbMedium' => {THUMB PATH}
  *   'thumbLarge' => {THUMB PATH}
  * )
  *
  * @param array $themes including theme names
  * @return array
  * @throws InvalidArgumentException
  */
  public function getThemeConfiguration($themePath) {
    $result = array();
    $document = $this->getDOMDocumentObject();

    if (is_dir($themePath) && $directory = opendir($themePath)) {
      while ($fileName = readdir($directory)) {
        if (is_file($themePath.'/'.$fileName) &&
            preg_match($this->_themeConfigurationFile, $fileName)) {
          $xmlFile = $themePath . '/' . $fileName;
          if ($this->loadXml($xmlFile)) {
            $xpath = new DOMXPath($document);
            $config['name'] = $xpath->evaluate('string(//papaya-theme/name)', $document);
            $config['templates'] = $xpath->evaluate(
              'string(//papaya-theme/templates/@directory)',
              $document
            );
            $config['version'] = $xpath->evaluate(
              'string(//papaya-theme/version/@number)',
              $document
            );
            $config['date'] = $xpath->evaluate(
              'string(//papaya-theme/version/@date)',
              $document
            );
            $config['author'] = $xpath->evaluate('string(//papaya-theme/author)', $document);
            $config['description'] = $xpath->evaluate('string(//papaya-theme/description)', $document);
            $config['thumbMedium'] = $xpath->evaluate(
              "string(//papaya-theme/thumbs/thumb[@size = 'medium']/@src)",
              $document
            );
            $config['thumbLarge'] = $xpath->evaluate(
              "string(//papaya-theme/thumbs/thumb[@size = 'large']/@src)",
              $document
            );
            $subTheme = $xpath->evaluate(
              'string(//papaya-theme/sub-theme)',
              $document
            );
            if (empty($subTheme)) {
              $subTheme = '';
            }
            $result[$subTheme] = $config;
          }
        }
      }
      closedir($directory);
    }
    if (count($result) > 0) {
      ksort($result);
    }
    return $result;
  }

  /***************************************************************************/
  /** Helper / instances                                                     */
  /***************************************************************************/

  /**
  * Retrieves a DOMDocument object.
  * @return DOMDocument
  */
  public function getDOMDocumentObject() {
    if (!(isset($this->_domDocument) && $this->_domDocument instanceof DOMDocument)) {
      $this->_domDocument = new DOMDocument;
      $this->_domDocument->preserveWhiteSpace = FALSE;
    }
    return $this->_domDocument;
  }

  /**
  * Loads the xml configuration if exists.
  *
  * @param string $xmlFilePath path & file of xml configuration file
  * @return boolean result
  */
  public function loadXml($xmlFilePath) {
    $result = FALSE;
    if (file_exists($xmlFilePath)) {
      $document = $this->getDOMDocumentObject();
      $result = (FALSE !== $document->load($xmlFilePath));
    }
    return $result;
  }
}