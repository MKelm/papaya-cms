<?php
/**
* Box to output single image or quote of module stickers
*
* @package Papaya-Modules
* @subpackage Free-Stickers
* @version $Id: actbox_stickers.php 36224 2011-09-20 08:00:57Z weinert $
*/

/**
* Basic class action box
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_actionbox.php');

/**
* dr stickers class
*/
require_once(dirname(__FILE__).'/base_stickers.php');

/**
* Box to output single image or quote of module stickers
*
* @package Papaya-Modules
* @subpackage Free-Stickers
*/
class actionbox_stickers extends base_actionbox {

  /**
  * edit fields
  * @var array
  */
  var $editFields = array(
    'collection_id' => array('Collection', 'isNum', TRUE, 'function', 'callbackCollections'),
  );

  var $preview = TRUE;

  /**
  * Get parsed data
  *
  * @access public
  * @return string xml
  */
  function getParsedData() {
    $result = '';
    $this->stickerObj = new base_stickers;

    $sticker = $this->stickerObj->getRandomSticker($this->data['collection_id']);
    if ($sticker) {
      $result .= sprintf(
        '<sticker id="%d" collection="%d"  author="%s" >'.LF,
        $sticker['sticker_id'],
        $sticker['collection_id'],
        papaya_strings::escapeHTMLChars($sticker['sticker_author'])
      );
      $result .= sprintf('<image src="image.media.%s" />'.LF, $sticker['sticker_image']);
      $result .= sprintf('<text>%s</text>'.LF, $this->getXHTMLString($sticker['sticker_text']));
      $result .= '</sticker>'.LF;
    }
    return $result;
  }

  /**
  * Generates a combobox for selecting a collection in the content configuration.
  */
  function callbackCollections($name, $element, $data) {
    $result = '';
    $this->stickerObj = new base_stickers;
    $collections = $this->stickerObj->getCollections();
    $result .= sprintf(
      '<select name="%s[%s]" class="dialogSelect dialogScale">'.LF,
      $this->paramName,
      $name
    );
    foreach ($collections as $collection) {
      $selected = ($data == $collection['collection_id']) ? ' selected="selected"' : '';
      $result .= sprintf(
        '<option value="%s" %s>%s</option>'.LF,
        $collection['collection_id'],
        $selected,
        $collection['collection_title']
      );
    }
    $result .= '</select>'.LF;
    return $result;
  }
}
?>
