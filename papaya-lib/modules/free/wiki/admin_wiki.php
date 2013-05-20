<?php
/**
* Wiki management
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
* @package Papaya-Modules
* @subpackage Beta-Wiki
* @version $Id: admin_wiki.php 36224 2011-09-20 08:00:57Z weinert $
*/

/**
* Basic class wiki base class
*/
require_once(dirname(__FILE__).'/base_wiki.php');

/**
* Basic class check conditions
*/
require_once(PAPAYA_INCLUDE_PATH.'system/sys_checkit.php');

/**
* Basic class language selection
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_language_select.php');

/**
* Wiki management
*
* @package Papaya-Modules
* @subpackage Beta-Wiki
*/
class wiki_admin extends base_wiki {
  /**
  * Current content language id
  * @var int $language
  */
  var $language = 0;

  /**
  * Number of articles to display per page
  * @var int $articlesPerPage
  */
  var $articlesPerPage = 25;

  /**
  * Constructor
  */
  function __construct(&$msgs, $paramName = "wiki") {
    parent::__construct($paramName);
    $this->paramName = $paramName;
    $this->sessionParamName = __CLASS__.$this->paramName;
    $this->sessionParams = $this->getSessionValue($this->sessionParamName);
    $this->msgs = $msgs;
  }

  /**
  * Get current content language
  *
  * @access private
  * @return int
  */
  function _initContentLanguage() {
    if ($this->language == 0) {
      $lngSelect = base_language_select::getInstance();
      $this->language = $lngSelect->currentLanguage['lng_id'];
    }
  }

  /**
  * Basic function for handling parameters
  *
  * Decides which actions to perform depending on the GET/POST paramaters
  * from the paramName array, stored in the params attribute
  *
  * @access public
  */
  function execute() {
    $this->initializeParams();
    if (isset($this->params['cmd'])) {
      switch ($this->params['cmd']) {
      case 'open_panel' :
        if (isset($this->params['panel'])) {
          $this->sessionParams['panel_state'][$this->params['panel']] = 'open';
          $this->setSessionValue($this->sessionParamName, $this->sessionParams);
        }
        break;
      case 'close_panel' :
        if (isset($this->params['panel'])) {
          $this->sessionParams['panel_state'][$this->params['panel']] = 'closed';
          $this->setSessionValue($this->sessionParamName, $this->sessionParams);
        }
        break;
      case 'del_article':
        $this->deleteArticle();
        break;
      case 'set_status':
        $this->setStatus();
        break;
      }
    }
  }

  /**
  * Get page layout
  *
  * Creates the page layout according to parameters
  *
  * @param object xsl_layout &$layout
  * @access public
  */
  function getXML(&$layout) {
    $layout->setParam('COLUMNWIDTH_LEFT', '300px');
    if (isset($this->params['cmd'])) {
      switch ($this->params['cmd']) {
      case 'del_article':
        $layout->add($this->getDeleteArticleForm());
        break;
      }
    }
    $layout->addLeft($this->generateFilterDialogXml());
    $layout->addLeft($this->getArticleList());
    if (isset($this->params['article'])) {
      $layout->add($this->getStatusDialog());
      $layout->add($this->getArticleSheet());
    }
  }

  /**
  * Generate the output XML for article paging links.
  *
  * @param int $articleCount absolute article count
  * @return string XML output
  */
  public function generateArticlePagingLinksXml($articleCount) {
    include_once(PAPAYA_INCLUDE_PATH.'system/papaya_paging_buttons.php');
    $params = array();
    if (isset($this->params['article'])) {
      $params['article'] = $this->params['article'];
    }
    return papaya_paging_buttons::getPagingButtons(
      $this,
      $params,
      isset($this->params['article_offset']) ? $this->params['article_offset'] : 0,
      $this->articlesPerPage,
      $articleCount,
      9,
      'article_offset'
    );
  }

  /**
  * Generate a filter form
  *
  * @return string XML formatted string representing the form
  */
  public function generateFilterDialogXml() {
    $result = '';
    if (isset($this->sessionParams['panel_state']['filter']) &&
        $this->sessionParams['panel_state']['filter'] == 'open') {
      $resize = sprintf(
        ' minimize="%s"',
        papaya_strings::escapeHTMLChars(
          $this->getLink(
            array(
              'cmd' => 'close_panel',
              'panel' => 'filter'
            )
          )
        )
      );
      $result .= sprintf(
        '<dialog action="%s" method="post" title="%s" %s>'.LF,
        papaya_strings::escapeHTMLChars($this->getBaseLink()),
        papaya_strings::escapeHTMLChars($this->_gt('Filter')),
        $resize
      );
      $filterSearchString = (isset($this->params['filter'])) ?
        $this->params['filter'] : '';
      $result .= sprintf(
        '<lines class="dialogXSmall">'.LF.
        '<line caption="%s">'.LF.
        '<input type="text" name="%s[filter]" value="%s" '.LF.
        'class="dialogInput dialogScale"/>'.LF.
        '</line>'.LF.
        '</lines>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Search')),
        papaya_strings::escapeHTMLChars($this->paramName),
        papaya_strings::escapeHTMLChars($filterSearchString)
      );

      $result .= sprintf(
        '<dlgbutton value="%s"/>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Filter'))
      );
      $result .= '</dialog>'.LF;
    } else {
      $resize = sprintf(
        ' maximize="%s"',
        papaya_strings::escapeHTMLChars(
          $this->getLink(
            array(
              'cmd' => 'open_panel',
              'panel' => 'filter'
            )
          )
        )
      );
      $result .= sprintf(
        '<listview title="%s" %s />'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Filter')),
        $resize
      );
    }
    return $result;
  }

  /**
  * Get article list
  *
  * Get the list of articles in the current content language
  *
  * @access public
  * @return string XML
  */
  function getArticleList() {
    $this->_initContentLanguage();
    $offset = (isset($this->params['article_offset'])) ? $this->params['article_offset'] : 0;
    $filter = "LIKE '%%'";
    if (isset($this->params['filter'])) {
      if (preg_match('(^[\w\s]+$)', $this->params['filter'])) {
        $filter = "LIKE '%%".$this->params['filter']."%%'";
      } else {
        $this->addMsg(MSG_WARNING, $this->_gt('Invalid filter.'));
      }
    }
    $articles = $this->searchArticle(
      $filter,
      $this->language,
      FALSE,
      $this->articlesPerPage,
      $offset
    );
    $result = sprintf('<listview title="%s">', $this->_gt('Articles'));
    $result .= $this->generateArticlePagingLinksXml($this->lastAbsCount);
    $result .= '<cols>';
    $result .= sprintf('<col>%s</col>', $this->_gt('Article'));
    $result .= '</cols>';
    $result .= '<items>';
    foreach ($articles as $data) {
      $article = $data['title'];
      $status = $data['status'];
      $linkParams = array('article' => $article);
      if ($offset > 0) {
        $linkParams['article_offset'] = $offset;
      }
      $link = $this->getLink($linkParams);
      $selected = '';
      if (isset($this->params['article']) && $this->params['article'] == $article) {
        $selected = ' selected="selected"';
      }
      switch ($status) {
      case PAPAYA_WIKI_ARTICLE_OPEN:
        $glyph = 'status-page-modified';
        break;
      case PAPAYA_WIKI_ARTICLE_SURFERONLY:
        $glyph = 'status-page-modified-hidden';
        break;
      case PAPAYA_WIKI_ARTICLE_READONLY:
        $glyph = 'status-page-created';
        break;
      case PAPAYA_WIKI_ARTICLE_LOCKED:
        $glyph = 'status-page-locked';
        break;
      }
      $result .= sprintf(
        '<listitem image="%s" href="%s" title="%s"%s/>',
        papaya_strings::escapeHTMLChars($this->images[$glyph]),
        papaya_strings::escapeHTMLChars($link),
        papaya_strings::escapeHTMLChars($article),
        $selected
      );
    }
    $result .= '</items>';
    $result .= '</listview>';
    return $result;
  }

  /**
  * Get article sheet
  *
  * Get text content of the current article and display it
  *
  * @return string XML
  */
  function getArticleSheet() {
    $article = $this->params['article'];
    $this->_initContentLanguage();
    $articleId = $this->getArticleId($article, $this->language);
    if ($articleId == 0) {
      return '';
    }
    $articleSource = $this->getArticleSource($articleId);
    $articleText = $this->parseWikiCodeToPlainText($articleSource);
    $articleText = preg_replace(
      '((\n|\r\n))',
      '<br />',
      papaya_strings::escapeHTMLChars($articleText)
    );
    if (trim($articleText) == '') {
      return $articleText;
    }
    $result = '<sheet>';
    $result .= '<header>';
    $result .= '<lines>';
    $result .= sprintf('<line>%s</line>', papaya_strings::escapeHTMLChars($article));
    $result .= '</lines>';
    $result .= '</header>';
    $result .= '<text>';
    $result .= $articleText;
    $result .= '</text>';
    $result .= '</sheet>';
    return $result;
  }

  /**
  * Deletes the selected article
  *
  * @return NULL
  */
  function deleteArticle() {
    if (!(isset($this->params['confirm_delete']) && $this->params['confirm_delete'] == 1)) {
      return;
    }
    if (!(isset($this->params['article_id']) && isset($this->params['article']))) {
      $this->addMsg(MSG_ERROR, $this->_gt('Please select an article to delete'));
      return;
    }
    $articleId = $this->params['article_id'];
    $this->databaseDeleteRecord($this->tableWikiLinks, 'article_id', $articleId);
    $this->databaseDeleteRecord($this->tableCategoriesArticles, 'article_id', $articleId);
    $this->databaseDeleteRecord($this->tableTranslations, 'article_id', $articleId);
    $this->databaseDeleteRecord($this->tableArticleXML, 'article_node_id', $articleId);
    $this->databaseDeleteRecord($this->tableArticleVersion, 'article_node_id', $articleId);
    $this->databaseDeleteRecord($this->tableArticle, 'article_node_id', $articleId);
    $this->databaseDeleteRecord($this->tableMediaArticles, 'article_id', $articleId);
    $this->addMsg(
      MSG_INFO,
      sprintf(
        $this->_gt('Article "%s" deleted.'),
        papaya_strings::escapeHtmlChars($this->params['article'])
      )
    );
  }

  /**
  * Change the status of an article
  */
  function setStatus() {
    $this->_initContentLanguage();
    $success = $this->setArticleStatus(
      $this->params['article'],
      $this->language,
      $this->params['status']
    );
    if (FALSE !== $success) {
      $this->addMsg(
        MSG_INFO,
        sprintf(
          $this->_gt('Set status of article "%s" to %s'),
          $this->params['article'],
          $this->_gt($this->status[$this->params['status']])
        )
      );
    } else {
      $this->addMsg(MSG_ERROR, $this->_gt('Status change failed'));
    }
  }

  /**
  * Generate a HTML form to set an article's status
  *
  * @return string
  */
  function getStatusDialog() {
    $result = '';
    $fields = array('status' => array('Status', 'isNum', TRUE, 'function', 'getStatusSelector'));
    $data = array();
    $hidden = array(
      'article' => $this->params['article'],
      'cmd' => 'set_status'
    );
    $statusDialog = new base_dialog($this, $this->paramName, $fields, $data, $hidden);
    if (is_object($statusDialog)) {
      $statusDialog->dialogTitle = sprintf(
        $this->_gt('Set status for "%s"'),
        $this->params['article']
      );
      $statusDialog->buttonTitle = $this->_gt('Save');
      $result = $statusDialog->getDialogXML();
    }
    return $result;
  }

  /**
  * Get a drop-down menu for article status
  *
  * @param string $name
  * @param array $field
  * @param integer $value
  * @return string XHTML
  */
  function getStatusSelector($name, $field, $value) {
    $this->_initContentLanguage();
    $articleId = $this->getArticleId($this->params['article'], $this->language);
    $currentStatus = $this->getArticleStatus($articleId);
    $result = sprintf(
      '<select name="%s[%s]" class="dialogSelect dialogScale">'.LF,
      $this->paramName,
      $name
    );
    foreach ($this->status as $option => $title) {
      $selected = ($option == $currentStatus) ? ' selected="selected"' : '';
      $result .= sprintf(
        '<option value="%s"%s>%s</option>'.LF,
        $option,
        $selected,
        $title
      );
    }
    $result .= '</select>'.LF;
    return $result;
  }

  /**
  * Generate a HTML form for article deletion
  *
  * @return string
  */
  function getDeleteArticleForm() {
    if (isset($this->params['confirm_delete']) && $this->params['confirm_delete'] == 1) {
      return '';
    }
    if (!(isset($this->params['article_id']) && isset($this->params['article']))) {
      return '';
    }
    include_once(PAPAYA_INCLUDE_PATH.'system/base_msgdialog.php');
    $hidden = array(
      'cmd' => 'del_article',
      'article_id' => $this->params['article_id'],
      'article' => $this->params['article'],
      'confirm_delete' => 1
    );
    $msg = sprintf(
      $this->_gt('Delete article "%s"?'),
      papaya_strings::escapeHtmlChars($this->params['article'])
    );
    $dialog = new base_msgdialog(
      $this,
      $this->paramName,
      $hidden,
      $msg,
      'question'
    );
    $dialog->baseLink = $this->baseLink;
    $dialog->msgs = &$this->msgs;
    $dialog->buttonTitle = 'Delete';
    $this->layout->add($dialog->getMsgDialog());
  }

  /**
  * Get buttons
  *
  * This method builds the main button bar for country management
  *
  * @access public
  */
  function getButtons() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_btnbuilder.php');
    include_once(PAPAYA_INCLUDE_PATH.'system/base_language_select.php');

    $toolbar = new base_btnbuilder;
    $toolbar->images = &$this->images;
    $pushed = (isset($this->params['cmd']) && $this->params['cmd'] == 'del_article') ? TRUE : FALSE;
    if (isset($this->articleId) && isset($this->params['article'])) {
      $toolbar->addButton(
        'Delete article',
        $this->getLink(
          array(
            'article_id' => $this->articleId,
            'article' => $this->params['article'],
            'cmd' => 'del_article'
          )
        ),
        'actions-page-delete',
        'Delete this article',
        $pushed
      );
    }
    if ($str = $toolbar->getXML()) {
      $this->layout->addMenu(sprintf('<menu ident="%s">%s</menu>'.LF, 'edit', $str));
    }
  }
}
