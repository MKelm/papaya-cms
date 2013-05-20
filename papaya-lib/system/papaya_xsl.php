<?php
/**
* XSL Layout-object with XML data buffer
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
* @subpackage Core
* @version $Id: papaya_xsl.php 37983 2013-01-17 17:41:18Z weinert $
*/

/**
* XSL-transformation
*/
require_once(PAPAYA_INCLUDE_PATH.'system/sys_xsl.php');

/**
* XSL Layout-object with XML data buffer
*
* @package Papaya
* @subpackage Core
*/
class papaya_xsl extends sys_xsl {

  /**
  * base target for navigation links
  * @var string $baseTarget
  */
  var $baseTarget = "_top";

  /**
  * Values object
  * @var array $data
  */
  var $_values = NULL;

  /**
  * Constuctor
  *
  * @param string $xslFileName XSL-Stylesheet optional, default value ""
  * @access public
  */
  public function __construct($xslFileName = "") {
    parent::__construct();
    $this->setXSL($xslFileName);
  }

  /**
  * Set XSL-file
  *
  * @param string $xslFileName XSL-Stylesheet optional, default value ""
  * @access public
  */
  function setXSL($xslFileName = "") {
    $this->xslFileName = $xslFileName;
  }

  /**
  * Add XML data
  *
  * @param string $col column
  * @param string|DOMElement $xmlData data
  * @param boolean $encode encode special characters? optional, default value TRUE
  * @access public
  */
  function addData($col, $xmlData, $encode = TRUE) {
    try {
      if ($xmlData instanceOf DOMNode) {
        $this->values()->getValueByPath('/page/'.$col)->append($xmlData);
      } else {
        $this->values()->getValueByPath('/page/'.$col)->appendXml(
          $encode ? papaya_strings::entityToXML($xmlData) : $xmlData
        );
      }
    } catch (PapayaXmlException $e) {
      $message = new PapayaMessageLog(
        PapayaMessageLogable::GROUP_SYSTEM,
        PapayaMessage::TYPE_ERROR,
        $e->getMessage()
      );
      $message
        ->context()
        ->append(
          new PapayaMessageContextText($xmlData)
        )
        ->append(
          new PapayaMessageContextBacktrace(1)
        );
      $this->papaya()->messages->dispatch($message);
    }
  }

  /**
  * Add Content for middle column (XML / XHTML)
  *
  * @param string|DOMElement $xmlData data
  * @param string  $tagName optional, default value NULL
  * @param boolean $encode encode spezial characters ? optional, default value TRUE
  * @access public
  */
  function add($xmlData, $tagName = NULL, $encode = TRUE) {
    if (isset($tagName)) {
      $this->addData($tagName, $xmlData, $encode);
    } else {
      $this->addData("centercol", $xmlData, $encode);
    }
  }

  /**
  * Add content for left column (XML)
  * @param string|DOMElement $xmlData data
  * @param boolean $encode encode special character ? optional, default value TRUE
  * @access public
  */
  function addLeft($xmlData, $encode = TRUE) {
    $this->addData("leftcol", $xmlData, $encode);
  }

  /**
  * Add content for middle column (XML)
  *
  * @param string|DOMElement $xmlData data
  * @param boolean $encode encode special character ? optional, default value TRUE
  * @access public
  */
  function addCenter($xmlData, $encode = TRUE) {
    $this->addData("centercol", $xmlData, $encode);
  }

  /**
  * Add content for right column (XML)
  *
  * @param string|DOMElement $xmlData data
  * @param boolean $encode encode special character ? optional, default value TRUE
  * @access public
  */
  function addRight($xmlData, $encode = TRUE) {
    $this->addData("rightcol", $xmlData, $encode);
  }

  /**
  * Add content for menu (XML)
  *
  * @param string|DOMElement $xmlData data
  * @param boolean $encode encode special character ? optional, default value TRUE
  * @access public
  */
  function addMenu($xmlData, $encode = TRUE) {
    $this->addData("menus", $xmlData, $encode);
  }

  /**
  * Add content for scripts (XML)
  *
  * @param string|DOMElement $xmlData data
  * @param boolean $encode encode special character ? optional, default value TRUE
  * @access public
  */
  function addScript($xmlData, $encode = TRUE) {
    $this->addData("scripts", $xmlData, $encode);
  }

  /**
  * Output XML
  *
  * @access public
  * @return string
  */
  function xml() {
    return $this->values()->document()->saveXml();
  }

  /**
  * Parse
  *
  * @param boolean $stripXMLDeclaration optional, default value FALSE
  * @param boolean $stripNamespaces optional, default value FALSE
  * @access public
  * @return mixed boolean or string
  */
  function parse($stripXMLDeclaration = FALSE, $stripNamespaces = FALSE) {
    if (!(FALSE === ($str = parent::parse()))) {
      $replace = array(
        '#\s*xmlns(:[a-zA-Z]+)?="\s*"#',
        '#<([\w:-]+)\s\s*>#s'
      );
      $with = array('', '<\\1>');
      if ($stripXMLDeclaration) {
        $replace[] = '#<\?xml[^>]+\?>#';
        $with [] = '';
      }
      if ($stripNamespaces) {
        $replace[] = '#\s*xmlns="[^"]*"#';
        $with [] = '';
      }
      $str = preg_replace($replace, $with, $str);
      $this->result = $str;
      return $this->result;
    }
    return FALSE;
  }

  /**
  * Transform XML with XSL to HTML
  *
  * @access public
  * @return string
  */
  function xhtml() {
    if ((isset($_GET['XML']) && $_GET['XML']) ||
        (isset($_GET['PAGEXML']) && $_GET['PAGEXML'])) {
      if (defined('PAPAYA_ADMIN_PAGE') && PAPAYA_ADMIN_PAGE) {
        header('Content-type: text/xml; charset=utf-8');
      }
      echo $this->xml();
    } else {
      if ($res = $this->parse()) {
        return $this->result;
      }
    }
    return FALSE;
  }
}
?>
