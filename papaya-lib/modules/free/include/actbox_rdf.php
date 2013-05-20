<?php
/**
* Action box for RDF
*
* @copyright 2002-2007 by papaya Software GmbH - All rights reserved.
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
* @subpackage Free-Include
* @version $Id: actbox_rdf.php 32997 2009-11-11 17:24:15Z weinert $
*/

/**
* Basic class aktion box
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_actionbox.php');

/**
* Action box for RDF
*
* read and cache XML-Content, transformed using  XSLT
*
* @package Papaya-Modules
* @subpackage Free-Include
*/
class actionbox_rdf extends base_actionbox {

  /**
  * Preview possible ?
  * @var boolean $preview
  */
  var $preview = TRUE;

  /**
  * Edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'url' => array(
      'URL', 'isNoHTML', TRUE, 'input', 800, 'Please enter a relative or an absolute URL.'
    ),
    'cachetime' => array('Cache time (minutes)', 'isNum', TRUE, 'input', 6, '', 1800),
    'max' => array('Count', 'isNum', TRUE, 'input', 5, '', 10),
    'show_description' => array(
      'Show feed description', 'isNum', TRUE, 'combo', array('1' => 'yes', '0' => 'no')
    ),
    'link_headline' => array(
      'Show headline as link', 'isNum', TRUE, 'combo', array('1' => 'yes', '0' => 'no')
    )
  );

  /**
  * Get parsed data
  *
  * @access public
  * @return string
  */
  function getParsedData() {
    $result = '';
    $resultRSS = FALSE;
    $bitValue = array('1' => 'yes', '0' => 'no');
    $max = (isset($this->data['max'])) ? (int)$this->data['max'] : $this->editFields['max'][6];
    $showDescription = (isset($this->data['show_description']))
      ? (int)$this->data['show_description'] : 0;
    $showHeadlineLink = (isset($this->data['link_headline']))
      ? (int)$this->data['link_headline'] : 0;
    if (isset($this->data['url'])) {
      $cacheFileName = PAPAYA_PATH_CACHE.'.rdf_rss_'.md5($this->data['url']).'.xml';
      if (file_exists($cacheFileName) && is_file($cacheFileName)) {
        $now = time();
        $cacheTime = filectime($cacheFileName) + ((int)$this->data['cachetime'] * 60);
        if ($now < $cacheTime) {
          if (!($resultRSS = file_get_contents($cacheFileName))) {
            $resultRSS = FALSE;
          }
        }
      }
      if (!$resultRSS) {
        $resultRSS = $this->generateCachefile($this->data['url'], $cacheFileName);
      }

      $result .= sprintf(
        '<feed maximum="%d" description="%s" headlinelink="%s">'.LF,
        (int)$max,
        $bitValue[$showDescription],
        $bitValue[$showHeadlineLink]
      );
      $result .= '<rdf>'.LF;
      $result .= preg_replace('~<\?([^?]+|\?[^>])+\?>~', '', $resultRSS).LF;
      $result .= '</rdf>'.LF;
      $result .= '</feed>'.LF;
    }
    return $result;
  }

  /**
  * Generate cache file
  *
  * @param string $url
  * @param string $fileName
  * @access public
  * @return string
  */
  function generateCachefile($url, $fileName) {
    $result = '';
    if (checkit::isHTTPX($url, TRUE)) {
      $feed = file_get_contents($url);
      if (($feed != '') && ($fh = @fopen($fileName, 'w'))) {
        fwrite($fh, $feed);
        fclose($fh);
      }
      return $feed;
    }
    return $result;
  }

}
?>