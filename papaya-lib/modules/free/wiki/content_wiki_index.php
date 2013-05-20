<?php
/**
* papaya Wiki, index page
*
* @copyright 2002-2008 by papaya Software GmbH - All rights reserved.
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
* @version $Id: content_wiki_index.php 37504 2012-09-04 17:23:59Z kersken $
*/

/**
* Base class base_content
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_content.php');

/**
* Wiki base class
*/
require_once(dirname(__FILE__).'/base_wiki.php');

/**
* Surfer base class
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_surfer.php');

/**
* Index page of the papaya Wiki
*
* @package Papaya-Modules
* @subpackage Beta-Wiki
*/
class content_wiki_index extends base_content {
  /**
  * base_wiki object
  * @var object $baseWiki
  */
  var $baseWiki = NULL;

  /**
  * Edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'title' => array('Title', 'isNoHTML', TRUE, 'input', 100),
    'Settings',
    'mode' => array('Mode', 'isNum', TRUE, 'combo', array(0 => 'all', 1 => 'by letter'), '', 0),
    'num_articles' => array('Articles per page', 'isNum', TRUE, 'input', 10, '', 25),
    'num_categories' => array('Categories per page', 'isNum', TRUE, 'input', 10, '', 10),
    'num_recent' => array('Number of recent articles', 'isNum', TRUE, 'input', 10, '', 25),
    'wiki_page' => array('Wiki page', 'isNum', TRUE, 'pageid', 100, '', 0),
    'Keywords',
    'category' => array('Category', 'isNoHTML', TRUE, 'input', 100, '', 'Category'),
    'Captions',
    'caption_article' => array('Article', 'isNoHTML', TRUE, 'input', 100, '', 'Article'),
    'caption_go' => array('Search article', 'isNoHTML', TRUE, 'input', 100, '', 'Go'),
    'caption_categories' => array('Categories', 'isNoHTML', TRUE, 'input', 100, '', 'Categories'),
    'caption_noarticles' => array(
      'No articles',
      'isNoHTML',
      TRUE,
      'input',
      100,
      '',
      'No articles yet'
    ),
    'caption_nocategories' => array(
      'No categories',
      'isNoHTML',
      TRUE,
      'input',
      100,
      '',
      'No categories yet'
    ),
    'caption_recent' => array(
      'Recently modified',
      'isNoHTML',
      TRUE,
      'input',
      100,
      '',
      'Recently modified'
    ),
    'caption_articles' => array(
      'Articles',
      'isNoHTML',
      TRUE,
      'input',
      100,
      '',
      'Articles'
    ),
    'caption_page' => array(
      'Page',
      'isNoHTML',
      TRUE,
      'input',
      100,
      '',
      'Page'
    )
  );

  /**
  * Initialize wiki object
  */
  function initWiki() {
    if (!is_object($this->baseWiki)) {
      $this->baseWiki = new base_wiki($this->msgs);
      $this->paramName = $this->baseWiki->paramName;
    }
  }

  /**
  * Get XML for the article selection form
  *
  * @return string XML
  */
  public function getArticleSelector() {
    $result = '';
    if ($this->data['wiki_page'] > 0) {
      $result .= sprintf(
        '<article-select href="%s">',
        $this->getWebLink($this->data['wiki_page'])
      );
      $result .= sprintf('<hidden param="%s[mode]"/>', $this->paramName);
      $result .= sprintf(
        '<field param="%s[article_field]" caption="%s" />',
        $this->paramName,
        papaya_strings::escapeHTMLChars($this->data['caption_article'])
      );
      $result .= sprintf(
        '<button caption="%s" />',
        papaya_strings::escapeHTMLChars($this->data['caption_go'])
      );
      $result .= '</article-select>';
    }
    return $result;
  }

  /**
  * Get parsed data
  *
  * Create XML content of the page
  *
  * @return string XML
  */
  function getParsedData() {
    $this->initWiki();
    $this->setDefaultData();
    $this->initializeParams();
    $language = $this->parentObj->topic['TRANSLATION']['lng_id'];
    $result = '<wiki-index>';
    $result .= sprintf('<title>%s</title>', $this->data['title']);
    $result .= $this->getArticleSelector();
    $result .= sprintf(
      '<link-recent href="%s">%s</link-recent>',
      $this->getWebLink(NULL, NULL, NULL, array('recent' => 1), $this->paramName),
      papaya_strings::escapeHTMLChars($this->data['caption_recent'])
    );
    $result .= sprintf(
      '<link-categ href="%s">%s</link-categ>',
      $this->getWebLink(NULL, NULL, NULL, array('categ' => 1), $this->paramName),
      papaya_strings::escapeHTMLChars($this->data['caption_categories'])
    );
    $filter = "LIKE '%%'";
    $recent = FALSE;
    $categoryMode = FALSE;
    if (isset($this->params['recent']) && $this->params['recent'] == 1) {
      $recent = TRUE;
    } elseif (isset($this->params['categ']) && $this->params['categ'] == 1) {
      $categoryMode = TRUE;
    }
    $letter = '';
    if ($this->data['mode'] == 1) {
      $letter = 'a';
      if ($recent || $categoryMode) {
        $letter = '';
      } elseif (
          isset($this->params['letter']) && preg_match('(^[0a-z]$)', $this->params['letter'])
        ) {
        $letter = $this->params['letter'];
      }
      if ($letter == '0') {
        $filter = " < 'a'";
      } else {
        $filter = " LIKE '".$letter."%%'";
      }
      $result .= $this->getLetterLinks($letter);
    } else {
      $result .= sprintf(
        '<link-articles href="%s">%s</link-articles>',
        $this->getWebLink(),
        papaya_strings::escapeHTMLChars($this->data['caption_articles'])
      );
    }
    $offset = 0;
    if (isset($this->params['offset'])) {
      $offset = $this->params['offset'];
    }
    if ($recent) {
      $result .= '<recent/>';
      $articles = $this->baseWiki->getLastModifiedArticles($language, $this->data['num_recent']);
    } elseif ($categoryMode) {
      $offset = isset($this->params['categoffset']) ? $this->params['categoffset'] : 0;
      $categories = $this->baseWiki->getCategories(
        $language,
        $this->data['num_categories'],
        $offset
      );
      if (!empty($categories)) {
        $numCategories = $this->baseWiki->lastAbsCount;
        $result .= sprintf(
          '<categories caption="%s">',
          papaya_strings::escapeHTMLChars($this->data['caption_categories'])
        );
        foreach ($categories as $category) {
          $link = $this->getWebLink(
            $this->data['wiki_page'],
            NULL,
            NULL,
            array(
              'mode' => 'read',
              'article' => sprintf('%s:%s', $this->data['category'], $category)
            ),
            $this->paramName
          );
          $result .= sprintf(
            '<category href="%s" name="%s"/>',
            papaya_strings::escapeHTMLChars($link),
            papaya_strings::escapeHTMLChars($category)
          );
        }
        $result .= '</categories>';
        $result .= $this->getCategoryPaging($numCategories, $offset);
      } else {
        $result .= sprintf(
          '<message type="error">%s</message>'.LF,
          papaya_strings::escapeHTMLChars($this->data['caption_nocategories'])
        );
      }
    } else {
      $articles = $this->baseWiki->searchArticle(
        $filter,
        $language,
        TRUE,
        $this->data['num_articles'],
        $offset
      );
      $result .= $this->getArticlePaging(
        $this->baseWiki->lastAbsCount,
        $offset,
        $this->data['mode'],
        $letter
      );
    }
    if (!$categoryMode) {
      if (empty($articles)) {
        $result .= sprintf(
          '<message type="error">%s</message>'.LF,
          papaya_strings::escapeHTMLChars($this->data['caption_noarticles'])
        );
      } else {
        $result .= '<articles>';
        foreach ($articles as $data) {
          if ($recent) {
            $article = $data;
          } else {
            $article = $data['title'];
          }
          $link = $this->getWebLink(
            $this->data['wiki_page'],
            NULL,
            NULL,
            array('mode' => 'read', 'article' => $article),
            $this->paramName
          );
          $result .= sprintf('<article href="%s" name="%s"/>', $link, $article);
        }
        $result .= '</articles>';
      }
    }
    $result .= '</wiki-index>';
    return $result;
  }

  /**
  * Get a link for each article starting letter
  *
  * @param string $letter
  * @return string XML
  */
  public function getLetterLinks($letter) {
    $result = '<letter-links>'.LF;
    $selected = ($letter == '0') ? ' selected="selected"' : '';
    $result .= sprintf(
      '<link href="%s"%s>%s</link>'.LF,
      $this->getWebLink(NULL, NULL, NULL, array('letter' => 0), $this->paramName),
      $selected,
      '#'
    );
    for ($i = 97; $i <= 122; $i++) {
      $currentLetter = chr($i);
      $selected = ($letter == $currentLetter) ? ' selected="selected"' : '';
      $result .= sprintf(
        '<link href="%s"%s>%s</link>'.LF,
        $this->getWebLink(
          NULL,
          NULL,
          NULL,
          array('letter' => $currentLetter),
          $this->paramName
        ),
        $selected,
        strtoupper($currentLetter)
      );
    }
    $result .= '</letter-links>'.LF;
    return $result;
  }

  /**
  * Get article paging links
  *
  * @param integer $numArticles
  * @param integer $offset
  * @param integer $mode
  * @param string $letter
  */
  public function getArticlePaging($numArticles, $offset, $mode, $letter) {
    $result = '';
    $limit = $this->data['num_articles'];
    if ($limit >= $numArticles) {
      return $result;
    }
    $result = sprintf(
      '<article-paging caption="%s">'.LF,
      papaya_strings::escapeHTMLChars($this->data['caption_page'])
    );
    for ($i = 0; $i <= $numArticles; $i += $limit) {
      $page = ($i / $limit) + 1;
      $selected = ($offset == $i) ? ' selected="selected"' : '';
      $linkParams = array('offset' => $i);
      if ($mode == 1) {
        $linkParams['letter'] = $letter;
      }
      $result .= sprintf(
        '<link href="%s"%s>%s</link>'.LF,
        $this->getWebLink(NULL, NULL, NULL, $linkParams, $this->paramName),
        $selected,
        $page
      );
    }
    $result .= '</article-paging>'.LF;
    return $result;
  }

  /**
  * Get category paging links
  *
  * @param integer $numCategories
  * @param integer $offset
  */
  public function getCategoryPaging($numCategories, $offset) {
    $result = '';
    $limit = $this->data['num_categories'];
    if ($limit >= $numCategories) {
      return $result;
    }
    $result = sprintf(
      '<article-paging caption="%s">'.LF,
      papaya_strings::escapeHTMLChars($this->data['caption_page'])
    );
    for ($i = 0; $i <= $numCategories; $i += $limit) {
      $page = ($i / $limit) + 1;
      $selected = ($offset == $i) ? ' selected="selected"' : '';
      $linkParams = array('categ' => 1, 'categoffset' => $i);
      $result .= sprintf(
        '<link href="%s"%s>%s</link>'.LF,
        $this->getWebLink(NULL, NULL, NULL, $linkParams, $this->paramName),
        $selected,
        $page
      );
    }
    $result .= '</article-paging>'.LF;
    return $result;
  }
}
?>
