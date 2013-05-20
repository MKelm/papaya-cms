<?php
/**
* Page module - thumbnail gallery.
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
* @subpackage Free-Media
* @version $Id: content_thumbs.php 36224 2011-09-20 08:00:57Z weinert $
*/

/**
* Basic class page module
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_content.php');

/**
* Basic mediadb class for file and folder access
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_mediadb.php');


/**
* Page module - Thumbnail Gallery.
*
* @package Papaya-Modules
* @subpackage Free-Media
*/
class content_thumbs extends base_content {

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
    'display_comment' => array('Display comment?', 'isNoHTML', TRUE, 'translatedcombo',
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
  * Get parsed teaser
  *
  * @access public
  * @return string $result
  */
  function getParsedTeaser() {
    $this->setDefaultData();
    $result = sprintf(
      '<title>%s</title>'.LF,
      papaya_strings::escapeHTMLChars($this->data['title'])
    );
    $result .= sprintf(
      '<subtitle>%s</subtitle>'.LF,
      $this->getXHTMLString($this->data['subtitle'])
    );
    $result .= sprintf(
      '<text>%s</text>'.LF,
      $this->getXHTMLString(
        $this->data['teaser'],
        !((bool)$this->data['nl2br'])
      )
    );

    $this->mediaDB = &base_mediadb::getInstance();
    $sort = $this->data['sort'];
    $order = $this->data['order'];
    if (!empty($this->data['directory'])) {
      $files = array_keys(
        $this->mediaDB->getFiles($this->data['directory'], 1, 0, $order, $sort)
      );
      if (!empty($files[0])) {
        $result .= sprintf(
          '<image>%s</image>'.LF,
          $this->getPapayaImageTag($files[0])
        );
      }
    }
    return $result;
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
    $this->mediaDB = &base_mediadb::getInstance();
    $this->initializeParams();

    $result = '';

    $result .= sprintf(
      '<title>%s</title>'.LF,
      papaya_strings::escapeHTMLChars($this->data['title'])
    );
    $result .= sprintf(
      '<subtitle>%s</subtitle>'.LF,
      papaya_strings::escapeHTMLChars($this->data['subtitle'])
    );
    $result .= sprintf(
      '<text>%s</text>'.LF,
      $this->getXHTMLString($this->data['text'], !((bool)$this->data['nl2br']))
    );
    // options subsection
    $result .= '<options>'.LF;
    // maximum no. thumbnails per line
    $result .= sprintf(
      '<maxperline>%d</maxperline>'.LF,
      (int)$this->data['maxperline']
    );
    // width of thumbnails in pixels
    $result .= sprintf(
      '<thumbwidth>%d</thumbwidth>'.LF,
      (int)$this->data['thumbwidth']
    );
    // height of thumbnails in pixels
    $result .= sprintf(
      '<thumbheight>%d</thumbheight>'.LF,
      (int)$this->data['thumbheight']
    );
    $result .= sprintf(
      '<width>%d</width>'.LF,
      (int)$this->data['width']
    );
    $result .= sprintf(
      '<height>%d</height>'.LF,
      (int)$this->data['height']
    );
    switch ($this->data['show_mode']) {
    case 5 : // show resized images with download image links
      $mode = 'resized-with-download-link';
      break;
    case 4 : // show resized images with original links
      $mode = 'resized-with-original-link';
      break;
    case 3 : // download resized images
      $mode = 'download-resized';
      break;
    case 2 : // download images
      $mode = 'download';
      break;
    case 1 : // show original images
      $mode = 'original';
      break;
    case 0 :
    default : // show resized images
      $mode = 'resized';
      break;
    }
    $result .= sprintf(
      '<mode lightbox="%d">%s</mode>'.LF,
      papaya_strings::escapeHTMLChars($this->data['show_lightbox']),
      papaya_strings::escapeHTMLChars($mode)
    );
    $result .= sprintf(
      '<displaytitle>%s</displaytitle>'.LF,
      papaya_strings::escapeHTMLChars($this->data['display_title'])
    );
    $result .= '</options>'.LF;
    // end of options subsection.

    // make sure the number of thumbnails per page makes sense
    if (!isset($this->data['maxperpage']) || $this->data['maxperpage'] <= 1) {
      $this->data['maxperpage'] = 9;
    }

    // calculate offset for file retrieval by idx (page)
    if (isset($this->params['idx']) && (int)$this->params['idx'] >= $this->data['maxperpage']) {
      $min = (floor($this->params['idx'] / $this->data['maxperpage']) * $this->data['maxperpage']);
    } else {
      $min = 0;
    }

    $sort = (isset($this->data['sort'])) ? $this->data['sort'] : 'asc';
    $order = (isset($this->data['order'])) ? $this->data['order'] : 'name';
    if (empty($this->data['directory'])) {
      $files = array();
      $filesTrans = array();
      $count = 0;
    } else {
      $files = $this->mediaDB->getFiles(
        $this->data['directory'],
        $this->data['maxperpage'],
        $min,
        $order,
        $sort
      );
      // load file translation data
      $filesTrans = $this->mediaDB->getFileTrans(
        array_keys($files),
        $this->parentObj->currentLanguage['lng_id']
      );
      // resets array indices to numbers, instead of file ids
      $files = array_values($files);
      $count = $this->mediaDB->absCount;
    }

    // make sure offset doesn't outrun actual number of images
    if ($min < 0) {
      $min = 0;
    } elseif ($min >= $count) {
      $min = $count - (int)$this->data['maxperpage'] - 1;
    }
    $max = $min + (int)$this->data['maxperpage'];
    if ($max > $count) {
      $max = $count;
    }

    if (isset($this->params['mode']) &&
        isset($this->params['img']) &&
        $this->params['mode'] == 'max' &&
        $this->params['img'] >= 0) {
      // build xml document displaying current image,
      // with links to next and previous images.

      $result .= '<image>';   // start image block
      $file = $files[$this->params['img']];    // retrieve filename

      // get thumbnail object for generating resized image
      include_once(PAPAYA_INCLUDE_PATH.'/system/base_thumbnail.php');
      $thumbnailObj = new base_thumbnail();
      //get size for image tag
      // build papaya xml media tag
      $imageSrc = $this->getWebMediaLink(
        $thumbnailObj->getThumbnail(
          $file['file_id'],
          NULL,
          $this->data['width'],
          $this->data['height'],
          $this->data['show_resize']
        ),
        'thumb'
      );
      list($width, $height) = $thumbnailObj->lastThumbSize;
      $result .= sprintf(
        '<img src="%s" style="width: %dpx; height: %dpx" />'.LF,
        $imageSrc,
        (int)$width,
        (int)$height
      );

      $result .= '</image>';  // end image block
      if ($this->data['display_title'] == 'true' &&
          !empty($filesTrans[$file['file_id']]['file_title'])) {
        $result .= sprintf(
           '<imagetitle>%s</imagetitle>',
           papaya_strings::escapeHTMLChars($filesTrans[$file['file_id']]['file_title'])
        );
      }
      // image comment
      if ($this->data['display_comment'] == 'true' &&
          !empty($filesTrans[$file['file_id']]['file_description'])) {
        $result .= sprintf(
          '<imagecomment>%s</imagecomment>',
          $this->getXHTMLString($filesTrans[$file['file_id']]['file_description'], TRUE)
        );
      }
      // original image or image download
      if ($this->data['show_mode'] == 4 || $this->data['show_mode'] == 5) {
        if (!empty($filesTrans[$file['file_id']]) && 
            !empty($filesTrans[$file['file_id']]['file_title'])) {
          $fileTitle = $filesTrans[$file['file_id']]['file_title'];
        } else {
          $fileTitle = '';
        }
        $imageHref = $this->getWebMediaLink(
          $file['file_id'], 
          $this->data['show_mode'] == 4 ? 'media' : 'download', 
          $fileTitle, 
          $file['mimetype_ext']
        );
        if ($this->data['show_mode'] == 4) {
          $result .= sprintf(
            '<originalimage href="%s" />',
            papaya_strings::escapeHTMLChars($imageHref)
          );
        } else {
          $result .= sprintf(
            '<imagedownload href="%s" />',
            papaya_strings::escapeHTMLChars($imageHref)
          );
        }
      }

      // navigation control block
      $result .= '<navigation>'.LF;

      // if not at the first image
      if ((int)$this->params['idx'] > 0 || (int)$this->params['img'] > 0) {
        // work out parameters for previous
        $imgIdx = $this->params['img'] - 1;
        if ($imgIdx < 0) {
          $imgIdx = $this->data['maxperpage'] - 1;
          $pageIdx = $this->params['idx'] - $this->data['maxperpage'];
        } else {
          $pageIdx = $this->params['idx'];
        }

        // construct html link to previous
        $href = $this->getWebLink(
          NULL,
          NULL,
          NULL,
          array(
            'mode' => 'max',
            'idx' => $pageIdx,
            'img' => $imgIdx,
            'page_id' => $this->parentObj->topicId
          ),
          $this->paramName
        );

        //add link to xml list
        $result .= sprintf(
          '<navlink dir="prior" href="%s" />'.LF,
          papaya_strings::escapeHTMLChars($href)
        );
      }

      // build link to current and add to html list
      $result .= sprintf(
        '<navlink dir="index" href="%s" />' . LF,
        papaya_strings::escapeHTMLChars(
          $this->getWebLink(
            NULL, NULL, NULL, array('idx' => (int)$this->params['idx']), $this->paramName
          )
        )
      );

      // if we are not at last image
      if ((int)$this->params['img'] + $min < ($count - 1)) {
        // calculate parameters for next
        $imgIdx = $this->params['img'] + 1;
        if ($imgIdx >= (int)$this->data['maxperpage']) {
          $imgIdx = 0;
          $pageIdx = $this->params['idx'] + $this->data['maxperpage'];
        } else {
          $pageIdx = $this->params['idx'];
        }

        // build link for next
        $href = $this->getWebLink(
          NULL,
          NULL,
          NULL,
          array(
            'mode' => 'max',
            'idx' => $pageIdx,
            'img' => $imgIdx,
            'page_id' => $this->parentObj->topicId
          ),
          $this->paramName
        );

        // add link to list for next
        $result .= sprintf(
          '<navlink dir="next" href="%s" />'.LF,
          papaya_strings::escapeHTMLChars($href)
        );
      }

      $result .= '</navigation>' . LF;
      // end of navigation control block
    } else {
      // build list of thumbnails
      $result .= '<thumbnails>'.LF;

      // get thumbnail object for generating linked thumbnails
      include_once(PAPAYA_INCLUDE_PATH.'/system/base_thumbnail.php');
      $thumbnailObj = new base_thumbnail();

      // process the list of thumbnail files
      foreach ($files as $i => $file) {
        $fileId = $file['file_id'];

        // get translated or own title
        $fileTitle = '';
        if (isset($filesTrans) && is_array($filesTrans) &&
            isset($filesTrans[$fileId]) && !empty($filesTrans[$fileId]['file_title'])) {
          $fileTitle = $filesTrans[$fileId]['file_title'];
        } elseif (!empty($file['file_title'])) {
          $fileTitle = $file['file_title'];
        } else {
          $fileTitle = '';
        }
        // get translated or own description
        $fileDescription = '';
        if (isset($filesTrans) && is_array($filesTrans) &&
            isset($filesTrans[$fileId]) && !empty($filesTrans[$fileId]['file_description'])) {
          $fileDescription = $filesTrans[$fileId]['file_description'];
        } elseif (!empty($file['file_description'])) {
          $fileDescription = $file['file_description'];
        } else {
          $fileDescription = '';
        }
        // build a direct link to the image's thumb, will generate it if needed
        switch ($this->data['show_mode']) {
        case 3 : // download images resized version
          $forHref = $this->getWebMediaLink(
            $thumbnailObj->getThumbnail(
              $file['file_id'],
              NULL,
              $this->data['width'],
              $this->data['height'],
              $this->data['show_resize']
            ),
            'thumb',
            $fileTitle
          );
          list($imageWidth, $imageHeight) = $thumbnailObj->lastThumbSize;
          $href = $this->getWebMediaLink(
            $file['file_id'], 'download', $fileTitle, $file['mimetype_ext']
          );
          break;
        case 2 : // download image no resized version
          $forHref = $this->getWebMediaLink(
            $file['file_id'], 'media', $fileTitle, $file['mimetype_ext']
          );
          $imageWidth = $file['width'];
          $imageHeight = $file['height'];
          $href = $this->getWebMediaLink(
            $file['file_id'], 'download', $fileTitle, $file['mimetype_ext']
          );
          break;
        case 1 : // show original image
          $forHref = $this->getWebMediaLink(
            $file['file_id'], 'media', $fileTitle, $file['mimetype_ext']
          );
          $imageWidth = $file['width'];
          $imageHeight = $file['height'];
          $href = $forHref;
          break;
        case 5 : // show resized image page with download link
        case 4 : // show resized image page with original link
        case 0 : // show resized image page
        default :
          $forHref = $this->getWebMediaLink(
            $thumbnailObj->getThumbnail(
              $file['file_id'],
              NULL,
              $this->data['width'],
              $this->data['height'],
              $this->data['show_resize']
            ),
            'thumb',
            $fileTitle
          );
          list($imageWidth, $imageHeight) = $thumbnailObj->lastThumbSize;
          $href = $this->getWebLink(
            NULL,
            NULL,
            NULL,
            array(
              'mode' => 'max',
              'idx' => (empty($this->params['idx'])) ? 0 : (int)$this->params['idx'],
              'img' => $i,
              'page_id' => $this->parentObj->topicId
            ),
            $this->paramName
          );
          break;
        }

        $thumbSrc = $this->getWebMediaLink(
          $thumbnailObj->getThumbnail(
            $file['file_id'],
            NULL,
            $this->data['thumbwidth'],
            $this->data['thumbheight'],
            $this->data['resize']
          ),
          'thumb',
          $fileTitle
        );
        list($thumbWidth, $thumbHeight) = $thumbnailObj->lastThumbSize;

        // build thumbnail media link
        $result .= sprintf(
          '<thumb for="%s" title="%s" width="%d" height="%d" mimetype="%s" updated="%s">'.
          '<a href="%s"><img src="%s" style="width: %dpx; height: %dpx" /></a>'.
          '<description>%s</description>'.
          '</thumb>'.LF,
          papaya_strings::escapeHTMLChars($forHref),
          papaya_strings::escapeHTMLChars($fileTitle),
          (int)$imageWidth,
          (int)$imageHeight,
          papaya_strings::escapeHTMLChars($file['mimetype']),
          PapayaUtilDate::timestampToString($file['file_date']),
          papaya_strings::escapeHTMLChars($href),
          papaya_strings::escapeHTMLChars($thumbSrc),
          (int)$thumbWidth,
          (int)$thumbHeight,
          $this->getXHTMLString($fileDescription)
        );
      }
      //end thumbnails
      $result .= '</thumbnails>' . LF;

      //start navigation
      $result .= '<navigation>' . LF;

      $min = (int)$min;
      $max = (int)$max;
      if ($max < $count) {
        // if not at last page, build a 'next' link
        $result .= sprintf(
          '<navlink dir="next" href="%s" />'.LF,
          papaya_strings::escapeHTMLChars(
            $this->getWebLink(NULL, NULL, NULL, array('idx' => $max), $this->paramName)
          )
        );
      }

      if ($min > 0) {
        // if not at first page, build a 'previous' link
        $min -= $this->data['maxperpage'];
        if ($min < 0) {
          $min = 0;
        }
        $result .= sprintf(
          '<navlink dir="prior" href="%s" />'.LF,
          papaya_strings::escapeHTMLChars(
            $this->getWebLink(NULL, NULL, NULL, array('idx' => $min), $this->paramName)
          )
        );
      }

      // end of navigation block
      $result .= '</navigation>' . LF;
    }
    return $result;
  }
}
