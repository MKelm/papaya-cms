<?php
/**
* Media image gallery
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
* Media image gallery
*
* @package Papaya-Modules
* @subpackage Free-Media
*/
class MediaImageGallery extends PapayaUiControlInteractive {

  /**
   * Current language id
   * @var integer
   */
  public $languageId = NULL;

  /**
   * Module obect to use some methods from
   * @var base_content|base_actionbox
   */
  protected $_module = NULL;

  /**
   * Title, subtitle and text of gallery
   * @var array
   */
  protected $_content = array();

  /**
   * Options to display gallery
   * @var array
   */
  protected $_options = array();

  /**
   * Folder contents
   * @var array
   */
  protected $_folder = array();

  /**
   * Properties to load media db folder
   * @var array
   */
  protected $_folderProperties = array();

  /**
   * Links to get navigation
   * @var array
   */
  protected $_navigationLinks = array();

  /**
   * Media DB object to get image files
   * @var base_mediadb
   */
  protected $_mediaDB = NULL;

  /**
  * Reference object to create urls
  * @var PapayaUiReference
  */
  protected $_reference = NULL;

  /**
   * Initialize properties by module configuration data and data mode (all or teaser)
   */
  public function initialize($module, $data, $dataMode = 'all') {
    $this->_module = $module;
    $this->_content = array(
      'title' => $data['title'],
      'subtitle' => $data['subtitle'],
      'text' => $this->_module->getXHTMLString(
        $dataMode == 'teaser' ? $data['teaser'] : $data['text'], !((bool)$data['nl2br'])
      )
    );
    $offset = (int)$this->parameters()->get('offset', 0); // current page or image offset
    $enlarge = (int)$this->parameters()->get('enlarge', 0); // parameter for enlarge view
    if ($dataMode == 'all') {
      // options
      switch ($data['show_mode']) {
      case 5: // show resized images with download image links
        $showMode = 'resized-with-download-link';
        break;
      case 4: // show resized images with original links
        $showMode = 'resized-with-original-link';
        break;
      case 3: // download resized images
        $showMode = 'download-resized';
        break;
      case 2: // download images
        $showMode = 'download';
        break;
      case 1: // show original images
        $showMode = 'original';
        break;
      case 0:
      default: // show resized images
        $showMode = 'resized';
        break;
      }
      $this->_options = array(
        'max_per_line' => (int)$data['maxperline'],
        'thumb_width' => (int)$data['thumbwidth'],
        'thumb_height' => (int)$data['thumbheight'],
        'thumb_resize' => $data['resize'],
        'width' => (int)$data['width'],
        'height' => (int)$data['height'],
        'resize' => $data['show_resize'],
        'mode' => $showMode,
        'data_mode' => $dataMode,
        'lightbox' => (int)$data['show_lightbox'],
        'enlarge' => (int)$enlarge,
        'display_title' => $data['display_title'] == 'true' ? 1 : 0,
        'display_description' => $data['display_comment'] == 'true' ? 1 : 0
      );
      // properties for image thumbnails
      if ($this->_options['enlarge'] == 0) {
        $limit = (!isset($data['maxperpage']) || $data['maxperpage'] <= 1) ? 9 : $data['maxperpage'];
        $this->_folderProperties = array(
          'limit' => $limit,
          'offset' => (!empty($offset) && $offset >= $limit) ? floor($offset / $limit) * $limit : 0
        );
      } else {
        $indexOffset = (int)$this->parameters()->get('index', 0);
        $this->_folderProperties = array(
          'limit' => 1, 'offset' => $offset, 'index_offset' => $indexOffset
        );
      }
    } else {
      // properties for teaser image thumbnails
      $this->_folderProperties = array('limit' => 1, 'offset' => 0);
    }
    $this->_folderProperties['sort_order'] = isset($data['sort']) ? $data['sort'] : 'asc';
    $this->_folderProperties['sort_type'] = isset($data['order']) ? $data['order'] : 'name';
    $this->_folderProperties['id'] = !empty($data['directory']) ? $data['directory'] : NULL;
    if ($this->_options['data_mode'] == 'all') {
      $reference = clone $this->reference();
      if (isset($this->_folderProperties['index_offset'])) {
        $reference->setParameters(
          array('index' => $this->_folderProperties['index_offset']), $this->parameterGroup()
        );
      }
      // navigation link previous
      if ($this->_folderProperties['offset'] > 0) {
        $previousReference = clone $reference;
        if ($this->_options['enlarge'] == 1) {
          $newOffset = $this->_folderProperties['offset'] - 1;
          $previousReference->setParameters(array('enlarge' => 1), $this->parameterGroup());
        } else {
          $newOffset = $this->_folderProperties['offset'] - $this->_folderProperties['limit'];
        }
        $previousReference->setParameters(array('offset' => $newOffset), $this->parameterGroup());
        $this->_navigationLinks['previous'] = $previousReference->getRelative();
      }
      // navigation link index
      if ($this->_options['enlarge'] == 1) {
        $indexReference = clone $this->reference();
        $indexReference->setParameters(
          array('offset' => $this->_folderProperties['index_offset']), $this->parameterGroup()
        );
        $this->_navigationLinks['index'] = $indexReference->getRelative();
      }
      // navigation link next
      $nextReference = clone $reference;
      if ($this->_options['enlarge'] == 1) {
        $newOffset = $this->_folderProperties['offset'] + 1;
        $nextReference->setParameters(array('enlarge' => 1), $this->parameterGroup());
      } else {
        $newOffset = $this->_folderProperties['offset'] + $this->_folderProperties['limit'];
      }
      $nextReference->setParameters(array('offset' => $newOffset), $this->parameterGroup());
      $this->_navigationLinks['next'] = $nextReference->getRelative();
    }
  }

  /**
   * Get/set mediaDB object
   *
   * @param base_mediadb $mediaDB
   * @return base_mediadb
   */
  public function mediaDB(base_mediadb $mediaDB = NULL) {
    if (isset($mediaDB)) {
      $this->_mediaDB = $mediaDB;
    } elseif (is_null($this->_mediaDB)) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_mediadb.php');
      $this->_mediaDB = &base_mediadb::getInstance();
    }
    return $this->_mediaDB;
  }

  /**
   * Load gallery images / thumbnails by media db and folder properties
   */
  public function load() {
    $this->_folder['files'] = $this->mediaDB()->getFiles(
      $this->_folderProperties['id'],
      $this->_folderProperties['limit'],
      $this->_folderProperties['offset'],
      $this->_folderProperties['sort_order'],
      $this->_folderProperties['sort_type']
    );
    if (!empty($this->_folder['files']) && $this->_options['data_mode'] == 'all') {
      // load file translation data
      $this->_folder['translations'] = $this->mediaDB()->getFileTrans(
        array_keys($this->_folder['files']), $this->languageId
      );
    }
    $this->_folder['absolute_count'] = $this->mediaDB()->absCount;
  }

  /**
   * Create dom node structure of the given object and append it to the given xml
   * element node.
   *
   * @param PapayaXmlElement $parent
   */
  public function appendTo(PapayaXmlElement $parent) {
    foreach ($this->_content as $contentName => $contentValue) {
      $content = $parent->appendElement($contentName);
      $content->appendXml($contentValue);
    }
    if (!empty($this->_options)) {
      $options = $parent->appendElement('options');
      foreach ($this->_options as $optionName => $optionValue) {
        $options->appendElement($optionName, array(), PapayaUtilStringXml::escape($optionValue));
      }
    }
    if (!empty($this->_folder['files'])) {
      if ($this->_options['enlarge'] == 1 || $this->_options['data_mode'] == 'teaser') {
        $this->_appendImageTo($parent, reset(array_keys($this->_folder['files'])));
      } else {
        $this->_appendImagesTo($parent);
      }
      $this->_appendNavigationTo($parent);
    }
  }

  /**
   * Append images by files in folder to parent element
   *
   * @param PapayaXmlElement $parent
   */
  protected function _appendImagesTo(PapayaXmlElement $parent) {
    $images = $parent->appendElement('images');
    $fileOffset = 0;
    foreach ($this->_folder['files'] as $fileId => $file) {
      $this->_appendImageTo($images, $fileId, $fileOffset, TRUE);
      $fileOffset++;
    }
  }

  /**
   * Append image or image thumbnail by current file id to parent element
   *
   * @param PapayaXmlElement $parent
   * @param integer $currentFileId
   * @param integer $fileOffset of current file in folder
   * @param boolean $thumbnail
   */
  protected function _appendImageTo(
              PapayaXmlElement $parent, $fileId, $fileOffset = 0, $thumbnail = FALSE
            ) {
    $fileTitle = !empty($this->_folder['translations'][$fileId]['file_title']) ?
      $this->_folder['translations'][$fileId]['file_title'] : NULL;
    $fileDescription = !empty($this->_folder['translations'][$fileId]['file_description']) ?
      $this->_folder['translations'][$fileId]['file_description'] : NULL;

    if ($thumbnail == TRUE) {
      // build a direct link to the image's thumb, will generate it if needed
      switch ($this->_options['mode']) {
      case 'download-resized': // download images resized version
        $destinationImageTag = PapayaUtilStringPapaya::getImageTag(
          $fileId,
          $this->_options['width'],
          $this->_options['height'],
          '',
          $this->_options['resize'],
          ''
        );
        include_once(PAPAYA_INCLUDE_PATH.'/system/base_thumbnail.php');
        $thumbnailObj = new base_thumbnail();
        $destinationImageLink = $this->_module->getWebMediaLink(
          $thumbnailObj->getThumbnail(
            $fileId,
            NULL,
            $this->_options['width'],
            $this->_options['height'],
            $this->_options['resize']
          ),
          'thumb',
          $fileTitle
        );
        break;
      case 'download': // download image no resized version
        $destinationImageTag = PapayaUtilStringPapaya::getImageTag($fileId);
        $destinationImageLink = $this->_module->getWebMediaLink(
          $fileId, 'download', $fileTitle, $this->_folder['files'][$fileId]['mimetype_ext']
        );
        break;
      case 'original': // show original image
        $destinationImageTag = PapayaUtilStringPapaya::getImageTag($fileId);
        $destinationImageLink = $this->_module->getWebMediaLink(
          $fileId, 'media', $fileTitle, $this->_folder['files'][$fileId]['mimetype_ext']
        );
        break;
      case 'resized-with-download-link': // show resized image page with download link
      case 'resized-with-original-link': // show resized image page with original link
      case 'resized': // show resized image page
      default:
        $destinationImageTag = PapayaUtilStringPapaya::getImageTag(
          $fileId,
          $this->_options['width'],
          $this->_options['height'],
          '',
          $this->_options['resize']
        );
        $reference = clone $this->reference();
        $reference->setParameters(
          array(
            'index' => $this->_folderProperties['offset'],
            'enlarge' => 1,
            'offset' => $this->_folderProperties['offset'] + $fileOffset
          ),
          $this->parameterGroup()
        );
        $destinationImageLink = $reference->getRelative();
        break;
      }
    }
    $image = $parent->appendElement('image');
    $image->appendXml(
      PapayaUtilStringPapaya::getImageTag(
        $fileId,
        $thumbnail == FALSE ? $this->_options['width'] : $this->_options['thumb_width'],
        $thumbnail == FALSE ? $this->_options['height'] : $this->_options['thumb_height'],
        '',
        $thumbnail == FALSE ? $this->_options['resize'] : $this->_options['thumb_resize'],
        ''
      )
    );
    // destination image with link for thumbnails
    if (isset($destinationImageLink) && isset($destinationImageTag)) {
      $destination = $image->appendElement(
        'destination',
        array('href' => PapayaUtilStringXml::escapeAttribute($destinationImageLink))
      );
      if ($this->_options['lightbox'] == 1) {
        $destination->appendXml($destinationImageTag);
      }
    }
    // image title
    if ($this->_options['display_title'] == 1 && !empty($fileTitle)) {
      $image->appendElement(
        'title',
        array(),
        PapayaUtilStringXml::escape($this->_folder['translations'][$fileId]['file_title'])
      );
    }
    if ($this->_options['enlarge'] == 1) {
      // image comment / description
      if ($this->_options['display_description'] == 1 && !empty($fileDescription)) {
        $description = $image->appendElement('description');
        $description->appendXml(
          $this->_module->getXHTMLString($this->_folder['translations'][$fileId]['file_description'])
        );
      }
      // original image or image download
      if ($this->_options['mode'] == 'resized-with-original-link' ||
          $this->_options['mode'] == 'resized-with-download-link') {
        $imageHref = $this->_module->getWebMediaLink(
          $fileId,
          $this->_options['mode'] == 'resized-with-original-link' ? 'media' : 'download',
          $fileTitle,
          $this->_folder['files'][$fileId]['mimetype_ext']
        );
        $image->appendElement(
          $this->_options['mode'] == 'resized-with-original-link' ? 'original-link' : 'download-link',
          array(),
          PapayaUtilStringXml::escape($imageHref)
        );
      }
    }
  }

  /**
   * Append navigation to parent element
   *
   * @param PapayaXmlElement $parent
   */
  protected function _appendNavigationTo(PapayaXmlElement $parent) {
    $navigation = $parent->appendElement('navigation');
    if (isset($this->_navigationLinks['previous'])) {
      $navigation->appendElement(
        'navlink',
        array(
          'direction' => 'previous',
          'href' => PapayaUtilStringXml::escapeAttribute($this->_navigationLinks['previous'])
        )
      );
    }
    if (isset($this->_navigationLinks['index'])) {
      $navigation->appendElement(
        'navlink',
        array(
          'direction' => 'index',
          'href' => PapayaUtilStringXml::escapeAttribute($this->_navigationLinks['index'])
        )
      );
    }
    if (isset($this->_navigationLinks['next']) &&
        $this->_folder['absolute_count'] > $this->_folderProperties['limit'] &&
        (($this->_folderProperties['offset'] + $this->_folderProperties['limit']) <
          $this->_folder['absolute_count'])) {
      $navigation->appendElement(
        'navlink',
        array(
          'direction' => 'next',
          'href' => PapayaUtilStringXml::escapeAttribute($this->_navigationLinks['next'])
        )
      );
    }
  }

  /**
  * The basic reference object used to create urls.
  *
  * @param PapayaUiReference $reference
  * @return PapayaUiReference
  */
  public function reference(PapayaUiReference $reference = NULL) {
    if (isset($reference)) {
      $this->_reference = $reference;
    } elseif (is_null($this->_reference)) {
      $this->_reference = new PapayaUiReference();
      $this->_reference->papaya($this->papaya());
    }
    return $this->_reference;
  }
}