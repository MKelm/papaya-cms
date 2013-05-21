<?php
/**
* Media image gallery page
*
* @copyright 2002-2013 by papaya Software GmbH - All rights reserved.
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
* @subpackage Free-Media
* @version $Id: $
*/

/**
* Basic class page module
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_content.php');


/**
* Media image gallery page
*
* @package Papaya-Modules
* @subpackage Free-Media
*/
class MediaImageGalleryPage extends base_content {

  /**
   * Parameter group
   * @var string
   */
  public $paramName = 'mig';

  /**
  * Content edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'nl2br' => array('Automatic linebreak', 'isNum', FALSE, 'translatedcombo',
      array(0 => 'Yes', 1 => 'No'), 'Apply linebreaks from input to the HTML output.', 0
    ),
    'title' => array('Title', 'isSomeText', FALSE, 'input', 400, '', ''),
    'subtitle' => array('Subtitle', 'isSomeText', FALSE, 'input', 400, '', ''),
    'teaser' => array('Teaser', 'isSomeText', FALSE, 'simplerichtext', 5, '', ''),
    'text' => array('Text', 'isSomeText', FALSE, 'richtext', 10, '', ''),

    'Thumbnails',
    'directory' => array('Folder', 'isNum', TRUE, 'mediafolder'),
    'maxperpage' => array('Thumbnail count', 'isNum', TRUE, 'input', 5, '', 9),
    'maxperline' => array('Column count', 'isNum', TRUE, 'input', 5, '', 3),
    'resize' => array('Resize mode', 'isAlpha', TRUE, 'translatedcombo',
      array('abs' => 'Absolute', 'max' => 'Maximum', 'min' => 'Minimum',
        'mincrop' => 'Minimum cropped'), '', 'max'),
    'thumbwidth' => array('Thumbnail width', 'isNum', TRUE, 'input', 5, '', 100),
    'thumbheight' => array('Thumbnail height', 'isNum', TRUE, 'input', 5, '', 100),
    'order' => array('Sort type', 'isNoHTML', TRUE, 'translatedcombo',
      array('name' => 'File name', 'date' => 'File date'), '', 'name'),
    'sort' => array('Sort order', 'isNoHTML', TRUE, 'translatedcombo',
      array('asc' => 'Ascending', 'desc' => 'Descending'), '', 'asc'),

    'Images',
    'display_title' => array('Display title?', 'isNoHTML', TRUE, 'translatedcombo',
       array('false' => 'no', 'true' => 'yes'), NULL, 'false'),
    'display_comment' => array('Display description?', 'isNoHTML', TRUE, 'translatedcombo',
       array('false' => 'no', 'true' => 'yes'), NULL, 'false'),
    'show_lightbox' => array('Display in lightbox?', 'isNum', TRUE, 'yesno', NULL, NULL, '1'),
    'show_mode' => array('Display mode', 'isNum', TRUE, 'translatedcombo',
      array(
        0 => 'Resized image',
        4 => 'Resized image with original image link',
        5 => 'Resized image with download link',
        1 => 'Original image',
        2 => 'Original image download',
        3 => 'Resized image download'
      ), '', 0
    ),
    'width' => array('Width', 'isNum', TRUE, 'input', 5, '', 640),
    'height' => array('Height', 'isNum', TRUE, 'input', 5, '', 480),
    'show_resize' => array('Resize mode', 'isAlpha', TRUE, 'translatedcombo',
      array('abs' => 'Absolute', 'max' => 'Maximum', 'min' => 'Minimum',
        'mincrop' => 'Minimum cropped'), '', 'max')
  );

  /**
   * Gallery object
   * @var MediaImageGallery
   */
  protected $_gallery = NULL;

  /**
  * Get (and, if necessary, initialize) the MediaImageGallery object
  *
  * @return MediaImageGallery $gallery
  */
  public function gallery(MediaImageGallery $gallery = NULL) {
    if (isset($gallery)) {
      $this->_gallery = $gallery;
    } elseif (is_null($this->_gallery)) {
      include_once(dirname(__FILE__).'/../Gallery.php');
      $this->_gallery = new MediaImageGallery();
      $this->_gallery->parameterGroup($this->paramName);
      $this->_gallery->languageId = $this->papaya()->request->languageId;
    }
    return $this->_gallery;
  }

  /**
  * Get parsed teaser
  *
  * @access public
  * @return string $result
  */
  function getParsedTeaser() {
    $this->setDefaultData();
    $this->gallery()->initialize($this, $this->data, 'teaser');
    $this->gallery()->load();
    return $this->gallery()->getXml();
  }

  /**
  * Get parsed data
  *
  * build an XML string containing either...
  * an image with links to previous image and next image or...
  * a page containing thumbnails with links to the previous and next pages of thumbnails.
  *
  * @access public
  * @return string $result
  */
  function getParsedData() {
    $this->setDefaultData();
    $this->initializeParams();
    $this->gallery()->initialize($this, $this->data);
    $this->gallery()->load();
    return $this->gallery()->getXml();
  }
}