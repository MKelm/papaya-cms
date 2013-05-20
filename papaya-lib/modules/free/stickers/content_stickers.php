<?php
/**
* Page module for stickers list
*
* @package Papaya-Modules
* @subpackage Free-Stickers
* @version $Id: content_stickers.php 36224 2011-09-20 08:00:57Z weinert $
*/

/**
* Basic class page module
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_content.php');

/**
* Page module for stickers list
*
* @package Papaya-Modules
* @subpackage Free-Stickers
*/
class content_stickers extends base_content {

  var $editFields = array(
    'Texts',
    'nl2br' => array('Automatic linebreak', 'isNum', FALSE, 'translatedcombo',
      array(0 => 'Yes', 1 => 'No'),
      'Apply linebreaks from input to the HTML output.'),
    'title' => array('Title', 'isSomeText', TRUE, 'input', 400),
    'subtitle' => array('Subtitle', 'isSomeText', FALSE, 'input', 400),
    'teaser' => array('Teaser', 'isSomeText', FALSE, 'simplerichtext', 10),
    'Stickers',
    'collection_id' => array('Collection', 'isNum', TRUE, 'function', 'callbackCollections'),
    'limit' => array('Stickers per page', 'isNum', TRUE, 'input', 10, '', 20),
    'max_pages' => array('Number of visible pages', 'isNum', TRUE, 'input', 10, '', 10)
  );

  /**
  * This method initializes parameters offset and limit.
  */
  function initializePaging() {
    if (!isset($this->params['offset'])) {
      $this->params['offset'] = 0;
    }

    if (!isset($this->params['limit'])) {
      if (isset($this->data['limit'])) {
        $this->params['limit'] = $this->data['limit'];
      } else {
        $this->params['limit'] = 10;
      }
    }
  }

  /**
  * Get parsed data
  *
  * @see getOutput()
  * @access public
  * @return string
  */
  function getParsedData() {
    $this->initializePaging();
    $result = '';
    $result .= sprintf(
      '<title>%s</title>'.LF,
      $this->getXHTMLString(@$this->data['title']));
    $result .= sprintf(
      '<subtitle>%s</subtitle>'.LF,
      $this->getXHTMLString(@$this->data['subtitle']));
    $result .= sprintf(
      '<text>%s</text>'.LF,
      $this->getXHTMLString(@$this->data['teaser'], !((bool)@$this->data['nl2br'])));
    $result .= sprintf(
      '<image align="%s" break="%s">%s</image>'.LF,
      papaya_strings::escapeHTMLChars(@$this->data['imgalign']),
      papaya_strings::escapeHTMLChars(@$this->data['breakstyle']),
      $this->getPapayaImageTag(@$this->data['image']));

    $result .= $this->getStickersXML();

    return $result;
  }

  /**
  * Generates the collection stickers XML output
  *
  * @return string $result stickers collection XML
  */
  function getStickersXML() {
    $result = '';
    include_once(dirname(__FILE__).'/base_stickers.php');
    $this->stickerObj = new base_stickers;

    $stickers = $this->stickerObj->getStickersByCollection(
      $this->data['collection_id'],
      $this->data['limit'], $this->params['offset']);
    if ($this->params['offset'] > $this->stickerObj->absCount) {
      $this->params['offset'] = 0;
      $stickers = $this->stickerObj->getStickersByCollection(
        $this->data['collection_id'],
        $this->data['limit'], $this->params['offset']);
    }
    if (is_array($stickers) && count($stickers) > 0) {
      if ($this->stickerObj->absCount > $this->params['offset'] + $this->data['limit']) {
        $nextLink = $this->getLink(
          array('offset' => $this->params['offset'] + $this->data['limit']));
      } else {
        $nextLink = '';
      }
      if ($this->params['offset'] > 0) {
        $backOffset = $this->params['offset'] - $this->data['limit'];
        if ($backOffset < 0) {
          $backOffset = 0;
        }
        $backLink = $this->getLink(array('offset' => $backOffset));
      } else {
        $backLink = '';
      }

      $collections = $this->stickerObj->getCollections();
      $result .= sprintf(
        '<collection id="%d" title="%s">'.LF,
        $this->data['collection_id'],
        $collections[$this->data['collection_id']]['collection_title']);
      $result .= sprintf('<paging nextlink="%s" backlink="%s">'.LF, $nextLink, $backLink);
        // add pages with links here

      $result .= $this->getPagingPages();
      $result .= '</paging>'.LF;

      $result .= sprintf('<stickers count="%d">'.LF, $this->stickerObj->absCount);
      foreach ($stickers as $sticker) {
        $result .= $this->getSingleStickerXML($sticker);
      }
      $result .= '</stickers>'.LF;
      $result .= '</collection>'.LF;
    }
    return $result;
  }

  /**
  * This method generates the paging pages for a sticker collection.
  *
  * @return string $result list of <page>-nodes for paging
  */
  function getPagingPages() {
    $result = '';

    $offset = $this->params['offset'];
    $limit = $this->params['limit'];

    $maxPages = (!empty($this->data['max_pages'])) ? $this->data['max_pages'] : 10;
    $nrOfPages = ceil($this->stickerObj->absCount / $limit);

    $currentPage = $offset / $limit;

    $pagesEachDirection = ceil(($maxPages - 1) / 2);

    if ($maxPages >= $nrOfPages) {
      $startPage = 0;
      $endPage = $nrOfPages;
    } elseif ($currentPage + $pagesEachDirection >= $nrOfPages) {
      $startPage = $nrOfPages - $maxPages;
      $endPage = $nrOfPages;
    } elseif ($currentPage - $pagesEachDirection < 0) {
      $startPage = 0;
      $endPage = $maxPages;
    } else {
      $startPage = $currentPage - $pagesEachDirection;
      $endPage = $currentPage + $pagesEachDirection + 1;
    }

    for ($i = $startPage; $i < $endPage; $i++) {
      $selected = ($i * $limit == $offset) ? ' selected="selected"' : '';
      $result .= sprintf(
        '<page id="%d" href="%s" %s/>'.LF,
        $i + 1,
        $this->getLink(array('offset' => $i * $limit)),
        $selected);
    }
    return $result;
  }

  /**
  * Generates the XML for a single sticker
  *
  * @param array $sticker a sticker record
  * @return string $result sticker XML
  */
  function getSingleStickerXML(&$sticker) {
    $result = '';
    $result .= sprintf(
      '<sticker id="%d" collection="%d" author="%s" >'.LF,
      $sticker['sticker_id'],
      $sticker['collection_id'],
      papaya_strings::escapeHTMLChars($sticker['sticker_author']));
    $result .= sprintf(
      '<text>%s</text>'.LF,
      $this->getXHTMLString($sticker['sticker_text']));
    $result .= sprintf(
      '<image>%s</image>'.LF,
      $this->getPapayaImageTag($sticker['sticker_image']));
    $result .= '</sticker>'.LF;
    return $result;
  }

  /**
  * Get parsed teaser
  *
  * @access public
  * @return string
  */
  function getParsedTeaser() {
    $result = sprintf(
      '<title>%s</title>'.LF,
      $this->getXHTMLString(@$this->data['title']));
    $result .= sprintf(
      '<subtitle>%s</subtitle>'.LF,
      $this->getXHTMLString(@$this->data['subtitle']));
    $result .= sprintf(
      '<text>%s</text>'.LF,
      $this->getXHTMLString(@$this->data['teaser'], !((bool)@$this->data['nl2br'])));
    $result .= sprintf(
      '<image align="%s" break="%s">%s</image>'.LF,
      papaya_strings::escapeHTMLChars(@$this->data['imgalign']),
      papaya_strings::escapeHTMLChars(@$this->data['breakstyle']),
      $this->getPapayaImageTag(@$this->data['image']));
    return $result;
  }

  /**
  * Generates a combobox for selecting a collection in the content configuration.
  */
  function callbackCollections($name, $element, $data) {
    $result = '';
    include_once(dirname(__FILE__).'/base_stickers.php');
    $this->stickerObj = new base_stickers;
    $collections = $this->stickerObj->getCollections();
    $result .= sprintf(
      '<select name="%s[%s]" class="dialogSelect dialogScale">'.LF,
      $this->paramName,
      $name);
    foreach ($collections as $collection) {
      $selected = ($data == $collection['collection_id']) ? ' selected="selected"' : '';
      $result .= sprintf(
        '<option value="%s" %s>%s</option>'.LF,
        $collection['collection_id'], $selected, $collection['collection_title']);
    }
    $result .= '</select>'.LF;
    return $result;
  }
}
?>
