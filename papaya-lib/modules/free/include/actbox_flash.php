<?php
/**
* Action box for flash
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
* @version $Id: actbox_flash.php 33004 2009-11-12 09:38:59Z weinert $
*/

/**
* Basic class aktion box
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_actionbox.php');
/**
* Action box for HTML
*
* @package Papaya-Modules
* @subpackage Free-Include
*/
class actionbox_flash extends base_actionbox {

  /**
  * Preview ?
  * @var boolean $preview
  */
  var $preview = TRUE;

  /**
  * Content edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'flashfile' => array('Flash filename', '#^[a-z\d]{32}$#', TRUE, 'mediafile', 100, '', ''),
    'width' => array('Width', 'isNumUnit', FALSE, 'input', 5, '', ''),
    'height' => array('Height', 'isNumUnit', FALSE, 'input', 5, '', ''),
    'version' => array(
      'Flash version', '#^[0-9]+(\.[0-9]+)?(\.[0-9]+)?$#', FALSE, 'input', 10, '', 10
    ),
    'Movie options',
    'scale' => array('Scaling mode', 'isAlpha', TRUE, 'combo',
      array('showall' => 'Standard', 'exactfit' => 'Exact fit',
      'noborder' => 'No border', 'noscale' => 'No scale'), '', 'showall'),
    'shalign' => array('Horizontal Align', 'isAlpha', FALSE, 'combo',
      array('m' => 'Middle', 'l' => 'Left', 'r' => 'Right'), '', 'm'),
    'svalign' => array('Vertical Align', 'isAlpha', FALSE, 'combo',
      array('m' => 'Middle', 't' => 'Top', 'b' => 'Bottom'), '', 'm'),
    'quality' => array('Quality', 'isAlpha', FALSE, 'combo',
      array('high' => 'High', 'medium' => 'Medium', 'low' =>'Low'), '', 'high'),
    'background_color' => array(
      'Background color', '/^#[0-9A-F]+$/i', FALSE, 'input', 7, '', '#FFFFFF'
    ),
    'wmode' => array('Wmode', 'isAlpha', FALSE, 'combo',
      array('transparent' => 'Transparent', 'none' => 'Empty'), '', ''),
    'object_id' => array('Object ID', 'isAlpha', FALSE, 'input', 100, '', ''),
    'script_access' => array(
      'Script access', 'isAlpha', FALSE, 'combo',
      array('always' => 'Always', 'sameDomain' => 'Same domain', 'never' => 'Never'),
      '', 'sameDomain'
    ),
    'flash_vars' => array('flashVars', 'isNoHTML', FALSE, 'input', 300, '', ''),
    'Alternative texts',
    'noflash' => array('No Flash', 'isSomeText', FALSE, 'textarea', 10, '')
  );

  /**
  * Get parsed data
  *
  * @access public
  * @return string
  */
  function getParsedData() {
    $this->setDefaultData();
    include_once(PAPAYA_INCLUDE_PATH.'system/base_mediadb.php');
    $mediaDB = base_mediadb::getInstance();
    if (preg_match('#^[a-z\d]{32}$#', $this->data['flashfile']) &&
        ($mediaFile = $mediaDB->getFile($this->data['flashfile'])) &&
        in_array($mediaFile['FILETYPE'], array(4, 13)) &&
        file_exists($mediaFile['FILENAME'])) {
      $fileName = $this->getWebMediaLink(
        $mediaFile['file_id'], 'media', $mediaFile['file_name'], $mediaFile['mimetype_ext']
      );
      if (empty($this->data['width'])) {
        $this->data['width'] = $mediaFile['WIDTH'];
      }
      if (empty($this->data['height'])) {
        $this->data['height'] = $mediaFile['HEIGHT'];
      }
    } elseif (file_exists(getenv('DOCUMENT_ROOT').'/'.$this->data['flashfile'])) {
      $fileName = $this->data['flashfile'];
    } elseif (checkit::isHTTPX($this->data['flashfile'], TRUE) &&
              (strtolower(substr($this->data['flashfile'], -4)) == '.swf')) {
      $fileName = $this->data['flashfile'];
    }
    if ($fileName != '') {
      include_once(PAPAYA_INCLUDE_PATH.'system/papaya_swfobject.php');
      $swfObject = new papaya_swfobject();
      $swfObject->setFlashVersion($this->data['version']);
      $swfObject->setObjectId($this->data['object_id']);
      $swfObject->setNoFlashMessage(
        $this->getXHTMLString($this->data['noflash'])
      );
      $swfObject->setSWFParam('allowscriptaccess', $this->data['script_access']);
      $swfObject->setSWFParam('bgcolor', $this->data['background_color']);
      $swfObject->setSWFParam('flashvars', $this->data['flash_vars']);
      $swfObject->setSWFParam(
        'salign',
        str_replace('m', '', $this->data['svalign'].$this->data['shalign'])
      );
      $swfObject->setSWFParam('scale', $this->data['scale']);
      $swfObject->setSWFParam('quality', $this->data['quality']);
      $swfObject->setSWFParam('wmode', $this->data['wmode']);
      return '<div>'.$swfObject->getXHTML(
        $fileName, $this->data['width'], $this->data['height']
      ).'</div>';
    } else {
      return '';
    }
  }
}
?>