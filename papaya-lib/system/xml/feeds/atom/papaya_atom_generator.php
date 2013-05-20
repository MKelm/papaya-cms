<?php
/**
* Identifies the software used to generate the feed
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
* @package Papaya-Library
* @subpackage XML-Feed
* @version $Id: papaya_atom_generator.php 37118 2012-06-11 11:19:33Z weinert $
*/


/**
* Identifies the software used to generate the feed
* @package Papaya-Library
* @subpackage XML-Feed
*/
class papaya_atom_generator extends papaya_atom_element {

  /**
  * conveys a human-readable name for the generator.
  * @var string
  */
  var $name = '';

  /**
  * contains a home page for the generator.
  * @var string
  */
  var $uri = NULL;

  /**
  * contains the version information of the generator.
  * @var string
  */
  var $version = NULL;

  /**
  * constructor - initialize properties
  *
  * @param papaya_atom_feed $feed reference of feed object
  * @param string $name conveys a human-readable name for the generator.
  * @param string $uri optional, contains a home page for the generator.
  * @param string $email optional, contains a vaersion information for the generator.
  * @access public
  */
  function __construct(papaya_atom_feed $feed, $name, $uri = NULL, $version = NULL) {
    $this->_feed = $feed;
    $this->name = $name;
    $this->uri = $uri;
    $this->version = $version;
  }

  /**
  * return generator data as xml
  *
  * @param string $tagName name of the tag to save the element
  * @access public
  * @return string
  */
  function saveXML($tagName) {
    if (!empty($this->name)) {
      $result = '<'.$tagName;
      if (isset($this->uri)) {
        $result .= ' uri="'.htmlspecialchars($this->uri).'"';
      }
      if (isset($this->version)) {
        $result .= ' version="'.htmlspecialchars($this->version).'"';
      }
      $result .= '>'.htmlspecialchars($this->name).'</'.$tagName.'>'."\n";
      return $result;
    }
    return '';
  }
}

?>