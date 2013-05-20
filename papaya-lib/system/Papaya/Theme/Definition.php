<?php
/**
* Load and provide access to the theme definition.
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
* @subpackage Theme
* @version $Id: Definition.php 38261 2013-03-11 16:10:20Z weinert $
*/

/**
* Load and provide access to the theme definition stored in theme.xml inside the theme directory.
*
* @package Papaya-Library
* @subpackage Theme
*/
class PapayaThemeDefinition extends PapayaContentStructure {

  /**
   * Theme data
   * @var array
   */
  private $_properties = array(
    'title' => '',
    'version' => '',
    'version_date' => '',
    'author' => '',
    'description' => '',
    'template_path' => ''
  );

  /**
   * Theme thunbnails
   * @var array
   */
  private $_thumbnails = array(
    'medium' => '',
    'large' => ''
  );

  /**
   * Load theme data from an xml file
   *
   * @param string $location
   */
  public function load($location) {
    $dom = new PapayaXmlDocument();
    $dom->load($location);
    $xpath = $dom->xpath();
    $this->_properties['title'] = $xpath->evaluate('string(/papaya-theme/name)');
    $this->_properties['version'] = $xpath->evaluate('string(/papaya-theme/version/@number)');
    $this->_properties['version_date'] = $xpath->evaluate('string(/papaya-theme/version/@date)');
    $this->_properties['author'] = $xpath->evaluate('string(/papaya-theme/author)');
    $this->_properties['description'] = $xpath->evaluate('string(/papaya-theme/description)');
    $this->_properties['template_path'] = $xpath->evaluate(
      'string(/papaya-theme/templates/@directory)'
    );
    foreach ($xpath->evaluate('/papaya-theme/thumbs/thumb') as $thumbNode) {
      $size = $thumbNode->getAttribute('size');
      if (isset($this->_thumbnails[$size])) {
        $this->_thumbnails[$size] = $thumbNode->getAttribute('src');
      }
    }
    if ($xpath->evaluate('count(/papaya-theme/dynamic-values)') > 0) {
      parent::load($xpath->evaluate('/papaya-theme/dynamic-values')->item(0));
    }
  }

  /**
   * Get a theme property
   * @param string $name
   */
  public function __get($name) {
    $identifier = PapayaUtilStringIdentifier::toUnderscoreLower($name);
    if (isset($this->_properties[$identifier])) {
      return $this->_properties[$identifier];
    } elseif ($identifier == 'thumbnails') {
      return $this->_thumbnails;
    }
    throw new UnexpectedValueException(
      sprintf(
        'Can not read unknown property "%s::$%s".',
        get_class($this),
        $name
      )
    );
  }

}