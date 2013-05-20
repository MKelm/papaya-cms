<?php
/**
* Stickers administration
*
* @package Papaya-Modules
* @subpackage Free-Stickers
* @version $Id: admin_stickers.php 36224 2011-09-20 08:00:57Z weinert $
*/

/**
* basic stickers class for database access
*/
require_once(dirname(__FILE__).'/base_stickers.php');

/**
* Stickers administration
*
* @package Papaya-Modules
* @subpackage Free-Stickers
*/
class admin_stickers extends base_stickers {

  /**
  * @var $paramName param name used in forms/links
  */
  var $paramName = 'st';

  /**
  * Initialize parameters, admin images and blog module
  */
  function initialize() {
    $this->sessionParamName = 'PAPAYA_SESS_'.$this->paramName;
    $this->initializeParams();
    $this->sessionParams = $this->getSessionValue($this->sessionParamName);
    $this->initializeSessionParam('col_id');

    $this->initializeSessionParam('offset_sticker');
    if (empty($this->params['offset_sticker'])) {
      $this->params['offset_sticker'] = 0;
    }
    $this->initializeSessionParam('limit');
    if (empty($this->params['limit'])) {
      $this->params['limit'] = 15;
    }

    if (empty($this->params['cmd'])) {
      $this->params['cmd'] = '';
    }

    $this->setSessionValue($this->sessionParamName, $this->sessionParams);

    $this->moduleImages['sticker'] =
      $this->module->getIconURI('pics/stickers.png');

    $this->layout->setParam('COLUMNWIDTH_LEFT', '50%');
    $this->layout->setParam('COLUMNWIDTH_CENTER', '0%');
    $this->layout->setParam('COLUMNWIDTH_RIGHT', '50%');

    include_once(PAPAYA_INCLUDE_PATH.'system/base_btnbuilder.php');
    $this->menubar = new base_btnbuilder;
    $this->menubar->images = &$this->images;

    if (!isset($this->collections)) {
      $this->collections = $this->getCollections();
    }

  }

  /**
  * executes commands sent by user
  */
  function execute() {

    switch ($this->params['cmd']) {
    case 'add_collection':
      $this->initializeCollectionDialog($this->params['cmd']);
      if (isset($this->params['submit']) && $this->params['submit']) {
        if ($this->dialog->checkDialogInput()) {
          if ($collectionId = $this->addCollection(
                $this->params['collection_title'],
                $this->params['collection_description'])) {
            $this->addMsg(
              MSG_INFO,
              sprintf(
                $this->_gt('Collection "%s" (#%d) added.'),
                $this->params['collection_title'], $collectionId));
            $this->initializeCollectionDialog($this->params['cmd'], FALSE);
          }
        }
      }
      break;
    case 'add_sticker':
      $this->initializeStickerDialog($this->params['cmd']);
      if (isset($this->params['submit']) && $this->params['submit']) {
        if ($this->dialog->checkDialogInput()) {
          if ($this->addSticker($this->params['col_id'], $this->dialog->data)) {
            $this->addMsg(MSG_INFO, $this->_gt('Sticker added.'));
            $this->initializeStickerDialog($this->params['cmd'], FALSE);
          }
        }
      }
      break;
    case 'edit_collection':
      $this->initializeCollectionDialog($this->params['cmd']);
      if (isset($this->params['submit']) && $this->params['submit']) {
        if ($this->dialog->checkDialogInput()) {
          if ($this->updateCollection(
                $this->params['col_id'],
                $this->params['collection_title'],
                $this->params['collection_description'])) {
            $this->addMsg(MSG_INFO, $this->_gt('Collection updated.'));
            unset($this->dialog);
          }
        }
      }
      break;
    case 'edit_sticker':
      $this->initializeStickerDialog($this->params['cmd']);
      if (isset($this->params['submit']) && $this->params['submit']) {
        if ($this->dialog->checkDialogInput()) {
          if ($this->updateSticker(
                $this->params['col_id'],
                $this->params['sticker_id'],
                $this->dialog->data)) {
            $this->addMsg(MSG_INFO, $this->_gt('Sticker updated.'));
            unset($this->dialog);
          }
        }
      }
      break;
    case 'del_collection':
      if (isset($this->params['confirm']) && $this->params['confirm']) {
        if ($this->deleteCollection($this->params['col_id'])) {
          $this->addMsg(
            MSG_INFO,
            sprintf($this->_gt('Collection %d deleted.'), $this->params['col_id']));
        }
      } else {
        $this->layout->addRight($this->getDeleteCollectionConfirmDialog());
      }
      break;
    case 'del_sticker':
      if (isset($this->params['confirm']) && $this->params['confirm']) {
        if ($this->deleteSticker($this->params['sticker_id'])) {
          $this->addMsg(MSG_INFO, $this->_gt('Sticker deleted.'));
        }
      } else {
        $this->layout->addRight($this->getDeleteStickerConfirmDialog());
      }
      break;
    }
  }

  /**
  * generates XML for admin page
  */
  function getXML() {
    $this->layout->addMenu($this->getMenubarXML());
    $this->layout->add($this->getCollectionsList());
    $this->layout->add($this->getStickersList());
    if (isset($this->dialog) && is_object($this->dialog) &&
        'base_dialog' == get_class($this->dialog)) {
      $this->layout->addRight($this->dialog->getDialogXML());
    }
  }

  /**
  * This method generates the menubar
  *
  * @return string $result menubar XML
  */
  function getMenubarXML() {
    $result = '';
    $this->menubar->addButton(
      'Add collection',
      $this->getLink(array('cmd' => 'add_collection')),
      'actions-folder-add',
      'Add a new collection',
      (!empty($this->params['cmd']) && $this->params['cmd'] == 'add_collection'));
    if (isset($this->params['col_id']) && $this->params['col_id'] > 0) {
      $this->menubar->addButton(
        'Delete collection',
        $this->getLink(
          array(
            'cmd' => 'del_collection',
            'sticker_id' => $this->params['col_id'])),
        'actions-folder-delete',
        'Delete the current collection',
        (!empty($this->params['cmd']) && $this->params['cmd'] == 'del_collection'));
      $this->menubar->addSeperator();
      $this->menubar->addButton(
        'Add sticker',
        $this->getLink(
          array(
            'cmd' => 'add_sticker',
            'col_id' => $this->params['col_id'])),
        'actions-page-add',
        'Add a sticker to the current collection',
        (!empty($this->params['cmd']) && $this->params['cmd'] == 'add_sticker'));
    }
    if ($menu = $this->menubar->getXML()) {
      $result = sprintf('<menu>%s</menu>'.LF, $menu);
    }
    return $result;
  }

  /**
  * This method generates the list of collections
  *
  * @return string $result collections listview XML
  */
  function getCollectionsList() {
    $result = '';
    $this->collections = $this->getCollections();
    $result .= sprintf(
      '<listview title="%s">'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Collections')));
    if (is_array($this->collections)&& count($this->collections) > 0) {
      $result .= '<items>'.LF;
      foreach ($this->collections as $collectionId => $collection) {
        if (isset($this->params['col_id']) && $collectionId == $this->params['col_id']) {
          $selected = ' selected="selected"';
        } else {
          $selected = '';
        }
        $result .= sprintf(
          '<listitem image="%s" title="%s" href="%s" %s>'.LF,
          $this->images['items-folder'],
          papaya_strings::escapeHTMLChars($collection['collection_title']),
          $this->getLink(array('cmd' => 'edit_collection', 'col_id' => $collectionId)),
          $selected);
        $result .= '</listitem>';
      }
      $result .= '</items>'.LF;
    } else {
      $result .= sprintf('<status>%s</status>', $this->_gt('No collections found.'));
    }
    $result .= '</listview>';
    return $result;
  }

  /**
  * This method generates the list of stickers
  *
  * @return string $result stickers listview XML
  */
  function getStickersList() {
    $result = '';
    if (isset($this->params['col_id']) && $this->params['col_id'] > 0) {
      $stickers = $this->getStickersByCollection(
        $this->params['col_id'],
        $this->params['limit'],
        $this->params['offset_sticker']);
      $result .= sprintf(
        '<listview title="%s">'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Stickers')));
      if (is_array($stickers) && count($stickers) > 0) {
        $result .= $this->getPagingBarXML();
        $result .= '<items>'.LF;
        foreach ($stickers as $stickerId => $sticker) {
          if (isset($this->params['sticker_id']) && $this->params['sticker_id'] == $stickerId) {
            $selected = ' selected="selected"';
          } else {
            $selected = '';
          }
          $result .= sprintf(
            '<listitem image="%s" title="#%d" href="%s" %s>'.LF,
            $this->moduleImages['sticker'],
            $stickerId,
            $this->getLink(array('cmd' => 'edit_sticker', 'sticker_id' => $stickerId)),
            $selected);
          $result .= sprintf(
            '<subitem><a href="%s">%s</a></subitem>'.LF,
            $this->getLink(array('cmd' => 'edit_sticker', 'sticker_id' => $stickerId)),
            papaya_strings::truncate(strip_tags($sticker['sticker_text'])));
          $result .= sprintf(
            '<subitem><a href="%s"><glyph src="%s" hint="%s"/></a></subitem>'.LF,
            $this->getLink(array('cmd' => 'del_sticker', 'sticker_id' => $stickerId)),
            $this->images['actions-page-delete'],
            $this->_gt('Delete sticker'));
          $result .= '</listitem>';
        }
        $result .= '</items>'.LF;
      } else {
        $result .= sprintf('<status>%s</status>'.LF, $this->_gt('No stickers in this collection.'));
      }
      $result .= '</listview>';
    }
    return $result;
  }

  /**
  * This method generates the stickers create and edit dialog.
  *
  * @param string $cmd the command, i.e. what is in $this->params['cmd']
  * @param boolean $loadParams whether or not to load previously submitted params as values
  */
  function initializeStickerDialog($cmd, $loadParams = TRUE) {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
    $hidden = array(
      'submit' => 1,
      'cmd' => $cmd,
      'col_id' => $this->params['col_id'],
    );
    if ($cmd == 'edit_sticker' && isset($this->params['sticker_id'])) {
      $title = $this->_gt('Edit sticker');
      $hidden['sticker_id'] = $this->params['sticker_id'];
      $data = $this->getSticker($this->params['sticker_id']);
      $buttonTitle = 'Save';
    } else {
      $title = $this->_gt('Add a sticker');
      $buttonTitle = 'Add';
      $data = array();
    }
    $fields = array(
      'sticker_collection' => array('Collection', 'isNum', FALSE, 'input_disabled', 200),
      'sticker_text' => array('Text', 'isSomeText', FALSE, 'textarea', 5),
      'sticker_image' => array('Image', '~[a-fA-F0-9]{32}~', FALSE, 'imagefixed'),
      'sticker_author' => array('Author', 'isNoHTML', FALSE, 'input', 200),
    );
    $this->dialog = new base_dialog($this, $this->paramName, $fields, $data, $hidden);
    if ($loadParams) {
      $this->dialog->loadParams();
    }
    $this->dialog->dialogTitle = $title;
    $this->dialog->buttonTitle = $buttonTitle;
    $this->dialog->inputFieldSize = 'large';
  }

  /**
  * This method generates the delete dialog for stickers
  *
  * @return string delete sticker dialog XML
  */
  function getDeleteStickerConfirmDialog() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_msgdialog.php');
    $hidden = array(
      'cmd' => $this->params['cmd'],
      'sticker_id' => $this->params['sticker_id'],
      'confirm' => 1,
    );

    $msg = $this->_gtf(
      'Do you really want to delete sticker #%d?',
      array($this->params['sticker_id'])
    );
    $this->dialog = new base_msgdialog($this, $this->paramName, $hidden, $msg, 'question');
    $this->dialog->buttonTitle = 'Delete';
    return $this->dialog->getMsgDialog();
  }

  /**

  * This method generates the stickers create and edit dialog.
  *
  * @param string $cmd the command, i.e. what is in $this->params['cmd']
  * @param boolean $loadParams whether or not to load previously submitted params as values
  */
  function initializeCollectionDialog($cmd, $loadParams = TRUE) {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
    $hidden = array(
      'cmd' => $cmd,
      'submit' => 1,
    );
    if ($cmd == 'edit_collection' && $this->params['col_id'] > 0) {
      $title = $this->_gt('Edit collection');
      $hidden['col_id'] = $this->params['col_id'];
      $data = $this->collections[$this->params['col_id']];
      $buttonTitle = 'Save';
    } else {
      $title = $this->_gt('Add a collection');
      $buttonTitle = 'Add';
      $data = array();
    }
    $fields = array(
      'collection_title' => array('Title', 'isNoHTML', TRUE, 'input', 200),
      'collection_description' => array('Description', 'isNoHTML', FALSE, 'textarea', 5),
    );
    $this->dialog = new base_dialog($this, $this->paramName, $fields, $data, $hidden);
    if ($loadParams) {
      $this->dialog->loadParams();
    }
    $this->dialog->dialogTitle = $title;
    $this->dialog->buttonTitle = $buttonTitle;
    $this->dialog->inputFieldSize = 'large';
  }

  /**
  * This method generates the delete dialog for collections
  *
  * @return string delete collection dialog XML
  */
  function getDeleteCollectionConfirmDialog() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_msgdialog.php');
    $hidden = array(
      'cmd' => $this->params['cmd'],
      'col_id' => $this->params['col_id'],
      'confirm' => 1,
    );

    $msg = sprintf(
      $this->_gt('Do you really want to delete collection "%s" #%d?'),
      $this->collections[$this->params['col_id']]['collection_title'],
      $this->params['col_id']);
    $this->dialog = new base_msgdialog($this, $this->paramName, $hidden, $msg, 'question');
    $this->dialog->buttonTitle = 'Delete';
    return $this->dialog->getMsgDialog();
  }

  /**
  * This method generates the paging bar for stickers
  *
  * @return string $result listview paging bar XML
  */
  function getPagingBarXML() {
    $result = '';
    if (isset($this->params['col_id'])) {
      include_once(PAPAYA_INCLUDE_PATH.'system/papaya_paging_buttons.php');
      $result = papaya_paging_buttons::getPagingButtons(
        $this,
        NULL,
        $this->params['offset_sticker'],
        $this->params['limit'],
        $this->getNumberOfStickersInCollection($this->params['col_id']),
        9,
        'offset_sticker'
      );
    }
    return $result;
  }
}
?>
