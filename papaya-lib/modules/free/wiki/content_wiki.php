<?php
/**
* papaya Wiki, standard page
*
* @copyright 2002-2011 by papaya Software GmbH - All rights reserved.
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
* @version $Id: content_wiki.php 38502 2013-05-23 15:30:26Z kersken $
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
* papaya Wiki, standard page
*
* @package Papaya-Modules
* @subpackage Beta-Wiki
*/
class content_wiki extends base_content {
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
    'anonymous' => array('Allow anonymous edits?', 'isNum', TRUE, 'yesno', 10, '', 0),
    'index_page' => array('Index page', 'isNum', TRUE, 'pageid', 10, '', 0),
    'upload_page' => array('Upload page', 'isNum', FALSE, 'pageid', 10, '', 0),
    'show_teaser' => array(
      'Show teaser',
      'isNum',
      TRUE,
      'yesno',
      NULL,
      'Show a teaser if no article is selected',
      0
    ),
    'teaser_article' => array(
      'Teaser article',
      'isNoHTML',
      FALSE,
      'input',
      100,
      'Leave blank for random teaser'
    ),
    'teaser_length' => array('Teaser length (chars)', 'isNum', TRUE, 'input', 10, '', 400),
    'result_teaser_length' => array(
      'Teaser length in search results',
      'isNum',
      TRUE,
      'input',
      10,
      '',
      200
    ),
    'Media settings',
    'thumb_width' => array('Thumbnail width', 'isNum', FALSE, 'input', 10, '', 320),
    'thumb_height' => array('Thumbnail height', 'isNum', FALSE, 'input', 10, '', 0),
    'Keywords',
    'redirect' => array('Redirect', 'isNoHTML', TRUE, 'input', 100, '', 'Redirect'),
    'category' => array('Category', 'isNoHTML', TRUE, 'input', 100, '', 'Category'),
    'file' => array('File', 'isNoHTML', TRUE, 'input', 100, '', 'File'),
    'thumb' => array('Thumbnail', 'isNoHTML', TRUE, 'input', 100, '', 'thumb'),
    'references' => array('References', 'isNoHTML', TRUE, 'input', 100, '', 'References'),
    'Messages',
    'no_article' => array(
      'No article selected',
      'isNoHTML',
      TRUE,
      'input',
      100,
      '',
      'Please enter an article name'
    ),
    'no_anon' => array(
      'No anonymous editing',
      'isNoHTML',
      TRUE,
      'input',
      100,
      '',
      'Please log in to edit'
    ),
    'saved' => array('Article saved', 'isNoHTML', TRUE, 'input', 100, '', 'Article saved'),
    'error_create' => array(
      'Error creating article',
      'isNoHTML',
      TRUE,
      'input',
      100,
      '',
      'Could not create article'
    ),
    'error_save' => array(
      'Error saving article',
      'isNoHTML',
      TRUE,
      'input',
      100,
      '',
      'Could not save article'
    ),
    'error_file_not_found' => array(
      'Error: Media file not found',
      'isNoHTML',
      TRUE,
      'input',
      100,
      '',
      'Media file "%s" not found.'
    ),
    'error_article_locked' => array(
      'Error: Article locked',
      'isNoHTML',
      TRUE,
      'input',
      100,
      '',
      'This article has been locked by an administrator'
    ),
    'Captions',
    'caption_index' => array('Index', 'isNoHTML', TRUE, 'input', 100, '', 'Index'),
    'caption_article' => array('Article', 'isNoHTML', TRUE, 'input', 100, '', 'Article'),
    'caption_read_article' => array(
      'Read article',
      'isNoHTML',
      TRUE,
      'input',
      100,
      '',
      'Read article'
    ),
    'caption_toc' => array(
      'Table of contents',
      'isNoHTML',
      TRUE,
      'input',
      100,
      '',
      'Table of Contents'
    ),
    'caption_searchresults' => array(
      'Search results',
      'isNoHTML',
      TRUE,
      'input',
      100,
      'Use %s as the search term',
      'Search results: Articles containing "%s"'
    ),
    'caption_create' => array(
      'Create',
      'isNoHTML',
      TRUE,
      'input',
      100,
      'Use %s as the article title',
      'Create article "%s"'
    ),
    'caption_edit' => array('Edit', 'isNoHTML', TRUE, 'input', 100, '', 'Edit'),
    'caption_versions' => array('Versions', 'isNoHTML', TRUE, 'input', 100, '', 'Versions'),
    'caption_compare' => array(
      'Comparing versions',
      'isSomeText',
      TRUE,
      'input',
      100,
      '',
      'Comparing versions of "%s"'
    ),
    'caption_button_compare' => array(
      'Compare button',
      'isNoHTML',
      TRUE,
      'input',
      100,
      '',
      'Compare selected versions'
    ),
    'caption_random' => array(
      'Random article',
      'isNoHTML',
      TRUE,
      'input',
      100,
      '',
      'Random article'
    ),
    'caption_upload' => array('Upload', 'isNoHTML', TRUE, 'input', 100, '', 'Upload'),
    'caption_go' => array('Search article', 'isNoHTML', TRUE, 'input', 100, '', 'Go'),
    'caption_preview' => array('Preview', 'isNoHTML', TRUE, 'input', 100, '', 'Preview'),
    'caption_save' => array('Save', 'isNoHTML', TRUE, 'input', 100, '', 'Save article'),
    'caption_edit_article' => array(
      'Edit article',
      'isSomeText',
      TRUE,
      'input',
      100,
      '',
      'Edit "%s"'
    ),
    'caption_file_title' => array(
      'File title',
      'isNoHTML',
      TRUE,
      'input',
      100,
      '',
      'Title'
    ),
    'caption_file_description' => array(
      'File description',
      'isNoHTML',
      TRUE,
      'input',
      100,
      '',
      'Description'
    ),
    'caption_file_imagesize' => array(
      'Image size',
      'isNoHTML',
      TRUE,
      'input',
      100,
      '',
      'Image size'
    ),
    'caption_file_size' => array(
      'File size',
      'isNoHTML',
      TRUE,
      'input',
      100,
      '',
      'File size'
    ),
    'caption_file_type' => array(
      'File MIME type',
      'isNoHTML',
      TRUE,
      'input',
      100,
      '',
      'MIME type'
    ),
    'caption_article_versions' => array(
      'Article versions',
      'isSomeText',
      TRUE,
      'input',
      100,
      '',
      'Versions of "%s"'
    ),
    'caption_version_from' => array(
      'Version from',
      'isSomeText',
      TRUE,
      'input',
      100,
      '',
      'Version from %s'
    ),
    'caption_version' => array('Version', 'isNoHTML', TRUE, 'input', 100, '', 'Version'),
    'caption_author' => array('Author', 'isNoHTML', TRUE, 'input', 100, '', 'Author'),
    'caption_comment' => array('Comment', 'isNoHTML', TRUE, 'input', 100, '', 'Comment'),
    'caption_redirected_from' => array(
      'Redirected from',
      'isNoHTML',
      TRUE,
      'input',
      100,
      '',
      'Redirected from'
    ),
    'caption_redirection_to' => array(
      'Redirection to',
      'isNoHTML',
      TRUE,
      'input',
      100,
      '',
      'Redirection to'
    ),
    'caption_categories' => array('Categories', 'isNoHTML', TRUE, 'input', 100, '', 'Categories'),
    'caption_subcategories' => array(
      'Subcategories',
      'isNoHTML',
      TRUE,
      'input',
      100,
      '',
      'Subcategories'
    )
  );

  /**
  * Special wiki keywords
  * @var array $keywords
  */
  var $keywords = array();

  /**
  * Supported image file types
  * @var array
  */
  var $supportedImages = array('image/gif', 'image/png', 'image/jpeg');

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
  * Get parsed teaser
  *
  * Create XML content for page teaser
  *
  * @param boolean $textOnly optional, default FALSE
  * @return string XML
  */
  function getParsedTeaser($textOnly = FALSE) {
    $this->setDefaultData();
    $this->initWiki();
    $language = $this->parentObj->topic['TRANSLATION']['lng_id'];
    $articleId = 0;
    if (isset($this->data['teaser_article']) && trim($this->data['teaser_article']) != '') {
      $article = trim($this->data['teaser_article']);
      $articleId = $this->baseWiki->getArticleId($article, $language);
    }
    if ($articleId == 0) {
      do {
        list($articleId, $article) = $this->baseWiki->getRandomArticle($language);
      } while ($article == '#REDIRECT');
    }
    $result = '';
    if ($articleId > 0) {
      if (!$textOnly) {
        $result .= sprintf('<title>%s</title>', $this->data['title']);
        $result .= sprintf('<subtitle>%s</subtitle>', $article);
        $nodeName = 'text';
      } else {
        $nodeName = 'teaser';
      }
      $result .= sprintf(
        '<%1$s>%2$s</%1$s>',
        $nodeName,
        $this->baseWiki->getArticlePlainText($articleId, $this->data['teaser_length'])
      );
    }
    if (!$textOnly) {
      return $result;
    }
    return array($article, $result);
  }

  /**
  * Get the internal Wiki navigation
  *
  * @param integer $articleId
  * @param string $article
  * @param integer $articleStatus
  * @param boolean $anonymous
  * @return string XML
  */
  function getWikiNavigation($articleId, $article, $articleStatus, $anonymous) {
    $surfer = $this->papaya()->surfer;
    $result = '<links>';
    $result .= sprintf(
      '<link mode="%s" href="%s" caption="%s"/>',
      'external',
      $this->getWebLink($this->data['index_page']),
      papaya_strings::escapeHTMLChars($this->data['caption_index'])
    );
    if ($articleId > 0 && $articleStatus != PAPAYA_WIKI_ARTICLE_LOCKED) {
      $result .= sprintf(
        '<link mode="%s" href="%s" caption="%s"/>',
        'read',
        $this->getWebLink(
          NULL,
          NULL,
          NULL,
          array('article' => $article, 'mode' => 'read'),
          $this->paramName
        ),
        papaya_strings::escapeHTMLChars($this->data['caption_article'])
      );
      if (($anonymous && $articleStatus == PAPAYA_WIKI_ARTICLE_OPEN) ||
          ($surfer->isValid && $articleStatus < PAPAYA_WIKI_ARTICLE_READONLY)) {
        $result .= sprintf(
          '<link mode="%s" href="%s" caption="%s"/>',
          'edit',
          $this->getWebLink(
            NULL,
            NULL,
            NULL,
            array('article' => $article, 'mode' => 'edit'),
            $this->paramName
          ),
          papaya_strings::escapeHTMLChars($this->data['caption_edit'])
        );
      }
      $result .= sprintf(
        '<link mode="%s" href="%s" caption="%s"/>',
        'versions',
        $this->getWebLink(
          NULL,
          NULL,
          NULL,
          array('article' => $article, 'mode' => 'versions'),
          $this->paramName
        ),
        papaya_strings::escapeHTMLChars($this->data['caption_versions'])
      );
    }
    $result .= sprintf(
      '<link mode="%s" href="%s" caption="%s"/>',
      'random',
      $this->getWebLink(
        NULL,
        NULL,
        NULL,
        array('mode' => 'random', 'nocache' => time()),
        $this->paramName
      ),
      papaya_strings::escapeHTMLChars($this->data['caption_random'])
    );
    if (isset($this->data['upload_page']) && $this->data['upload_page'] > 0) {
      if ($surfer->isValid) {
        $result .= sprintf(
          '<link mode="upload" href="%s" caption="%s"/>',
          $this->getWebLink($this->data['upload_page']),
          $this->data['caption_upload']
        );
      }
    }
    $result .= '</links>';
    return $result;
  }

  /**
  * Get the form to select an article
  *
  * @return string XML
  */
  function getArticleSelectForm() {
    $result = sprintf('<article-select href="%s">', $this->getWebLink());
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
    return $result;
  }

  /**
  * Get XML for category contents
  *
  * @param string $categoryName
  * @param integer $lngId
  * @return string XML
  */
  function getCategoryDataXml($categoryName, $lngId) {
    $result = '';
    $articles = $this->baseWiki->getCategoryArticles($categoryName, $lngId);
    if (!empty($articles)) {
      $result .= '<category-data>';
      // Loop two times, once for normal articles and once for subcategories
      $subcategories = FALSE;
      foreach ($articles as $articleNode) {
        if (
          preg_match(
            '{^(category|'.$this->keywords['category'].'):(.+)$}i', $articleNode, $matches
          )
        ) {
          if (!$subcategories) {
            $subcategories = TRUE;
            $result .= sprintf(
              '<subcategories>%s</subcategories>',
              papaya_strings::escapeHTMLChars($this->data['caption_subcategories'])
            );
          }
          $result .= sprintf(
            '<wiki-link href="%s" node="%s" exists="true" subcategory="true">%s</wiki-link>',
            $this->getWebLink(
              NULL,
              NULL,
              NULL,
              array('article' => $articleNode),
              $this->paramName
            ),
            papaya_strings::escapeHTMLChars($articleNode),
            papaya_strings::escapeHTMLChars($matches[2])
          );
        }
      }
      foreach ($articles as $articleNode) {
        if (!preg_match('{^(category|'.$this->keywords['category'].'):(.+)$}i', $articleNode)) {
          $result .= sprintf(
            '<wiki-link href="%1$s" node="%2$s" exists="true" subcategory="false">'.
            '%2$s</wiki-link>',
            $this->getWebLink(
              NULL,
              NULL,
              NULL,
              array('article' => $articleNode),
              $this->paramName
            ),
            papaya_strings::escapeHTMLChars($articleNode)
          );
        }
      }
      $result .= '</category-data>';
    }
    return $result;
  }

  /**
  * Get file data XML
  *
  * @param string $fileName
  * @return string XML
  */
  function getFileDataXml($fileName) {
    $result = '';
    $fileData = $this->baseWiki->getFileByName($fileName);
    if (!empty($fileData)) {
      $mediaDb = new base_mediadb();
      $fileInfo = $mediaDb->getFile($fileData['media_id']);
      $result .= '<file-details>'.LF;
      $type = in_array($fileInfo['mimetype'], $this->supportedImages) ? 'image' : 'link';
      $result .= sprintf(
        '<file href="%s" type="%s" />'.LF,
        $this->getWebMediaLink($fileData['media_id']),
        $type
      );
      $result .= sprintf(
        '<title caption="%s">%s</title>'.LF,
        papaya_strings::escapeHTMLChars($this->data['caption_file_title']),
        papaya_strings::escapeHTMLChars($fileData['media_title'])
      );
      $result .= sprintf(
        '<description caption="%s">%s</description>'.LF,
        papaya_strings::escapeHTMLChars($this->data['caption_file_description']),
        papaya_strings::escapeHTMLChars($fileData['media_description'])
      );
      $result .= sprintf(
        '<size caption="%s">%s</size>'.LF,
        papaya_strings::escapeHTMLChars($this->data['caption_file_size']),
        PapayaUtilBytes::toString((int)$fileInfo['file_size'])
      );
      $result .= sprintf(
        '<type caption="%s">%s</type>'.LF,
        papaya_strings::escapeHTMLChars($this->data['caption_file_type']),
        papaya_strings::escapeHTMLChars($fileInfo['mimetype'])
      );
      if ($type == 'image') {
        $result .= sprintf(
          '<imagesize caption="%s" width="%d" height="%d" />'.LF,
          papaya_strings::escapeHTMLChars($this->data['caption_file_imagesize']),
          $fileInfo['WIDTH'],
          $fileInfo['HEIGHT']
        );
      }
      $result .= '</file-details>'.LF;
    } else {
      if (strpos($this->data['error_file_not_found'], '%s') !== FALSE) {
        $message = sprintf($this->data['error_file_not_found'], $fileName);
      } else {
        $message = $this->data['error_file_not_found'];
      }
      $result = sprintf(
        '<message type="error">%s</message>',
        papaya_strings::escapeHTMLChars($message)
      );
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
    $this->setDefaultData();
    $this->initWiki();
    $this->initializeParams();
    $this->keywords = array(
      'redirect' => $this->data['redirect'],
      'category' => $this->data['category'],
      'file' => $this->data['file'],
      'thumb' => $this->data['thumb'],
      'references' => $this->data['references']
    );
    $result = '<wikipage>';
    // Form to select an article
    $result .= $this->getArticleSelectForm();
    if (isset($this->data['title']) && trim($this->data['title']) != '') {
      // Add page title if available
      $result .= sprintf(
        '<title>%s</title>',
        papaya_strings::escapeHTMLChars($this->data['title'])
      );
    }
    // TOC caption
    $result .= sprintf(
      '<caption for="toc">%s</caption>',
      papaya_strings::escapeHTMLChars($this->data['caption_toc'])
    );
    // Are anonymous edits allowed?
    $anonymous = FALSE;
    if (isset($this->data['anonymous']) && $this->data['anonymous'] != FALSE) {
      $anonymous = TRUE;
    }
    // Figure out mode, article, and content language
    $language = $this->parentObj->topic['TRANSLATION']['lng_id'];
    $mode = 'read';
    if (isset($this->params['mode']) &&
        in_array(
          $this->params['mode'],
          array('edit', 'preview', 'versions', 'compare', 'random')
        )
       ) {
      $mode = $this->params['mode'];
    }
    $comparison = FALSE;
    if ($mode == 'compare') {
      if (isset($this->params['node']) &&
          isset($this->params['old']) &&
          isset($this->params['new']) &&
          $this->params['old'] != $this->params['new']) {
        $comparison = TRUE;
        $result .= sprintf(
          '<comparison old="%s" new="%s">',
          date('Y-m-d H:i:s', $this->params['old']),
          date('Y-m-d H:i:s', $this->params['new'])
        );
        $result .= $this->baseWiki->compareVersions(
          $this->params['node'],
          $this->params['old'],
          $this->params['new']
        );
        $result .= '</comparison>';
      }
      $mode = 'read';
    }
    if ($mode == 'random') {
      $randomData = $this->baseWiki->getRandomArticle($language);
      if (!empty($randomData)) {
        list($articleId, $article) = $randomData;
        $mode = 'read';
      } else {
        $mode = NULL;
      }
    } else {
      $article = '';
      $rawArticle = '';
      if (isset($this->params['article']) && trim($this->params['article'] != '')) {
        $rawArticle = $this->params['article'];
      } elseif (isset($this->params['article_field']) &&
                trim($this->params['article_field'] != '')) {
        $rawArticle = $this->params['article_field'];
      }
      if ($rawArticle != '') {
        $article = papaya_strings::escapeHTMLChars(trim($rawArticle));
      }
    }
    // Is there no article?
    if ($article == '') {
      if ($this->data['show_teaser'] == TRUE) {
        list($teaserArticle, $teaserText) = $this->getParsedTeaser(TRUE);
        $result .= $teaserText;
        $result .= sprintf(
          '<teaser-link caption="%s">%s</teaser-link>',
          $this->data['caption_read_article'],
          $this->getWebLink(NULL, NULL, NULL, array('article' => $teaserArticle), $this->paramName)
        );
      } else {
        $result .= sprintf(
          '<message type="error">%s</message>',
          papaya_strings::escapeHTMLChars($this->data['no_article'])
        );
      }
      $result .= '<links>';
      $result .= sprintf(
        '<link mode="%s" href="%s" caption="%s"/>',
        'external',
        $this->getWebLink($this->data['index_page']),
        papaya_strings::escapeHTMLChars($this->data['caption_index'])
      );
      $result .= sprintf(
        '<link mode="%s" href="%s" caption="%s"/>',
        'random',
        $this->getWebLink(
          NULL,
          NULL,
          NULL,
          array('mode' => 'random', 'nocache' => time()),
          $this->paramName
        ),
        papaya_strings::escapeHTMLChars($this->data['caption_random'])
      );
      $result .= '</links>';
    } else {
      // write article name to XML
      $result .= sprintf(
        '<article url="%s" param="%s[article]" node="%s"/>',
        $this->getWebLink(),
        $this->paramName,
        papaya_strings::escapeHTMLChars($article)
      );
      $categoryMode = FALSE;
      if (preg_match(
          '{^(category|'.$this->keywords['category'].'):(.+)$}i',
          $article,
          $matches)) {
        // If it's a category we're looking for, get its contents
        $result .= $this->getCategoryDataXml($matches[2], $language);
        $categoryMode = TRUE;
      } elseif (preg_match(
          '{^(file|'.$this->keywords['file'].'):(.+)$}i',
          $article,
          $matches
        )) {
        $result .= $this->getFileDataXml($matches[2]);
        $mode = 'file';
      }
      // Redirection link and information
      $result .= sprintf(
        '<redirection url="%s" param-node="%2$s[article]" param-noredir="%2$s[redirect]=no">',
        $this->getWebLink(),
        $this->paramName
      );
      $result .= sprintf(
        '<caption for="redirected-from">%s</caption>',
        papaya_strings::escapeHTMLChars($this->data['caption_redirected_from'])
      );
      $result .= sprintf(
        '<caption for="redirection-to">%s</caption>',
        papaya_strings::escapeHTMLChars($this->data['caption_redirection_to'])
      );
      $result .= '</redirection>';
      // Try to get the article id
      $articleId = $this->baseWiki->getArticleId($article, $language);
      // If the article id is 0 and we're not in edit/preview/file mode,
      // add search results
      if ($articleId == 0 && $mode != 'edit' && $mode != 'preview' && $mode != 'file') {
        if ($categoryMode) {
          $searchResult = array();
        } else {
          $searchResult = $this->baseWiki->searchContents($article, $language);
        }
        if (!empty($searchResult)) {
          $teasers = $this->baseWiki->getArticlePlainText(
            array_keys($searchResult),
            $this->data['result_teaser_length']
          );
          $result .= '<search-results>'.LF;
          $result .= sprintf(
            '<caption>%s</caption>'.LF,
            papaya_strings::escapeHTMLChars($title)
          );
          foreach ($searchResult as $id => $title) {
            $currentTeaser = '';
            if (isset($teasers[$id])) {
              $currentTeaser = $teasers[$id];
            }
            $result .= sprintf(
              '<link href="%s" title="%s">%s</link>'.LF,
              $this->getWebLink(
                NULL,
                NULL,
                NULL,
                array('article' => $title),
                $this->paramName
              ),
              papaya_strings::escapeHTMLChars($title),
              papaya_strings::escapeHTMLChars($currentTeaser)
            );
          }
          $result .= '</search-results>'.LF;
          if (strstr($this->data['caption_create'], '%s')) {
            $createTitle = sprintf($this->data['caption_create'], $article);
          } else {
            $createTitle = $this->data['caption_create'];
          }
          $result .= sprintf(
            '<create-link href="%s">%s</create-link>'.LF,
            $this->getWebLink(
              NULL,
              NULL,
              NULL,
              array('article' => $article, 'mode' => 'edit'),
              $this->paramName
            ),
            papaya_strings::escapeHTMLChars($createTitle)
          );
          $mode = 'search';
        } else {
          $mode = 'edit';
        }
      }
      // If editing is not allowed without login,
      // check surfer or display a message in edit or preview mode
      if ($anonymous === FALSE && ($mode == 'edit' || $mode == 'preview')) {
        $surfer = $this->papaya()->surfer;
        if (!$surfer->isValid) {
          $mode = NULL;
          $result .= sprintf(
            '<message type="error">%s</message>',
            papaya_strings::escapeHTMLChars($this->data['no_anon'])
          );
        }
      }
      // Is this an attempt to save the article?
      if (isset($this->params['save'])) {
        if (trim($this->params['article_source']) != '') {
          if ($articleId == 0) {
            $articleId = $this->baseWiki->createArticle($article, $language);
            if ($articleId === FALSE) {
              $result .= sprintf(
                '<message type="error">%s</message>',
                papaya_strings::escapeHTMLChars($this->data['error_create'])
              );
            }
          }
          if ($articleId) {
            $comment = '';
            if (isset($this->params['article_comment'])) {
              $comment = $this->params['article_comment'];
            }
            $success = $this->baseWiki->storeNewVersion(
              $articleId,
              $this->params['article_source'],
              $this->keywords,
              $anonymous,
              $comment
            );
            // Do we have to handle categories?
            if (!empty($this->baseWiki->categories)) {
              $this->baseWiki->setCategories($articleId, $this->baseWiki->categories, $language);
            } else {
              $this->baseWiki->deleteCategoryLinks($articleId);
            }
            // What about wiki links?
            if (!empty($this->baseWiki->wikiLinks)) {
              $this->baseWiki->setWikiLinks($articleId, $this->baseWiki->wikiLinks, $language);
            }
            // Files/media?
            if (!empty($this->baseWiki->files)) {
              $this->baseWiki->setFiles($articleId, $this->baseWiki->files);
            } else {
              $this->baseWiki->deleteFileLinks($articleId);
            }
            if (!empty($this->baseWiki->translations)) {
              $this->baseWiki->setTranslations($articleId, $this->baseWiki->translations);
            }
            if ($success !== FALSE) {
              $result .= sprintf(
                '<message type="info">%s</message>',
                papaya_strings::escapeHTMLChars($this->data['saved'])
              );
            } else {
              $result .= sprintf(
                '<message type="error">%s</message>',
                papaya_strings::escapeHTMLChars($this->data['error_save'])
              );
            }
          }
        }
      }
      $articleStatus = PAPAYA_WIKI_ARTICLE_OPEN;
      if ($articleId > 0) {
        $articleStatus = $this->baseWiki->getArticleStatus($articleId);
      }
      $result .= $this->getWikiNavigation($articleId, $article, $articleStatus, $anonymous);
      if ($mode !== NULL) {
        $result .= sprintf('<mode>%s</mode>', $mode);
      }
      // If we've got an article id, get the article's current wiki code
      if ($articleId > 0) {
        if ($mode == 'versions') {
          $versionsList = $this->baseWiki->getArticleVersions($articleId, TRUE);
          if (!empty($versionsList)) {
            include_once(PAPAYA_INCLUDE_PATH.'system/base_pluginloader.php');
            $surfersObj = base_pluginloader::getPluginInstance(
              '06648c9c955e1a0e06a7bd381748c4e4',
              $this
            );
            $result .= sprintf(
              '<versions caption="%s" href="%s" param-name="%s">',
              papaya_strings::escapeHTMLChars($this->data['caption_button_compare']),
              $this->getWebLink(),
              $this->paramName
            );
            $result .= sprintf(
              '<hidden name="%s[node]" value="%d"/>',
              $this->paramName,
              $articleId
            );
            $result .= sprintf(
              '<hidden name="%s[article]" value="%s"/>',
              $this->paramName,
              $article
            );
            $result .= sprintf('<hidden name="%s[mode]" value="compare"/>', $this->paramName);
            $result .= sprintf(
              '<caption for="version" value="%s"/>',
              papaya_strings::escapeHTMLChars($this->data['caption_version'])
            );
            $result .= sprintf(
              '<caption for="author" value="%s"/>',
              papaya_strings::escapeHTMLChars($this->data['caption_author'])
            );
            $result .= sprintf(
              '<caption for="comment" value="%s"/>',
              papaya_strings::escapeHTMLChars($this->data['caption_comment'])
            );
            foreach ($versionsList as $timestamp => $info) {
              if ($info['type'] == 'surfer') {
                $author = $surfersObj->getHandleById($info['author']);
              } else {
                $author = $info['author'];
              }
              $result .= sprintf(
                '<version link="%s" plaintimestamp="%s" timestamp="%s" author="%s" comment="%s" />',
                $this->getWebLink(
                  NULL,
                  NULL,
                  NULL,
                  array('article' => $article, 'mode' => 'preview', 'version' => $timestamp),
                  $this->paramName
                ),
                $timestamp,
                date('Y-m-d H:i:s', $timestamp),
                $author,
                papaya_strings::escapeHTMLChars($info['comment'])
              );
            }
            $result .= '</versions>';
          }
        } elseif ($mode == 'read') {
          if ($articleStatus != PAPAYA_WIKI_ARTICLE_LOCKED) {
            $articleXML = $this->baseWiki->getArticleXML($articleId);
            // Resolve redirections iteratively if redirect="no" is not set
            if (!isset($this->params['redirect']) || $this->params['redirect'] != 'no') {
              while (preg_match('{^<redirect node="([^"]+)"/>$}', $articleXML, $matches)) {
                $redirectArticle = $matches[1];
                $redirectArticleId = $this->baseWiki->getArticleIdNoCache(
                  $redirectArticle,
                  $language
                );
                if ($redirectArticleId == 0) {
                  $articleXML = '';
                } else {
                  $result .= sprintf('<redirected-from node="%s"/>', $article);
                  $articleId = $redirectArticleId;
                  $article = $redirectArticle;
                  $articleXML = $this->baseWiki->getArticleXML($redirectArticleId);
                }
              }
            }
            $result .= $articleXML;
          } else {
            $result .= sprintf(
              '<message type="error">%s</message>',
              papaya_strings::escapeHTMLChars($this->data['error_article_locked'])
            );
          }
          // Does this article belong to any categories?
          $categories = $this->baseWiki->getArticleCategories($articleId);
          if (!empty($categories)) {
            $result .= sprintf(
              '<categories caption="%s">',
              papaya_strings::escapeHTMLChars($this->data['caption_categories'])
            );
            foreach ($categories as $category) {
              $result .= sprintf(
                '<wiki-link node="%1$s:%2$s" href="%3$s" exists="true">%2$s</wiki-link>',
                papaya_strings::escapeHTMLChars($this->data['category']),
                papaya_strings::escapeHTMLChars($category),
                $this->getWebLink(
                  NULL,
                  NULL,
                  NULL,
                  array('article' => $this->data['category'].':'.$category),
                  $this->paramName
                )
              );
            }
            $result .= '</categories>';
          }
          // Does this article contain media files?
          $files = $this->baseWiki->getArticleFiles($articleId);
          if (!empty($files)) {
            $mediaIds = array_keys($files);
            $mediaDb = new base_mediadb();
            $thumb = new base_thumbnail();
            $fileInfo = $mediaDb->getFilesById($mediaIds);
            $result .= '<files>'.LF;
            foreach ($files as $id => $data) {
              if (!isset($fileInfo[$id])) {
                continue;
              }
              $link = '';
              if (in_array($fileInfo[$id]['mimetype'], $this->supportedImages)) {
                $type = 'image';
                if ($data['media_thumb'] == 1) {
                  if ($data['media_width'] > 0) {
                    $width = $data['media_width'];
                  } elseif ($this->data['thumb_width'] > 0) {
                    $width = $this->data['thumb_width'];
                  } else {
                    $width = NULL;
                  }
                  if ($data['media_height'] > 0) {
                    $height = $data['media_height'];
                  } elseif ($this->data['thumb_height'] > 0) {
                    $height = $this->data['thumb_height'];
                  } else {
                    $height = NULL;
                  }
                  $mode = ($height !== NULL && $width !== NULL) ? 'mincrop' : 'max';
                  $thumbnail = $thumb->getThumbnail(
                    $data['media_id'],
                    NULL,
                    $width,
                    $height,
                    $mode
                  );
                  if ($thumbnail) {
                    $link = $this->getWebMediaLink($thumbnail, 'thumb');
                  }
                }
              } else {
                $type = 'link';
              }
              if ($link == '') {
                $link = $this->getWebMediaLink($data['media_id']);
              }
              $src = '';
              if ($type == 'image') {
                $src = sprintf(' src="%s"', papaya_strings::escapeHTMLChars($link));
              }
              $href = $this->getWebLink(
                NULL,
                NULL,
                NULL,
                array('article' => $this->data['file'].':'.$data['media_name']),
                $this->paramName
              );
              $result .= sprintf(
                '<file%s index="%d" name="%s" href="%s" type="%s" title="%s">%s</file>',
                $src,
                $data['media_index'],
                papaya_strings::escapeHTMLChars($data['media_name']),
                papaya_strings::escapeHTMLChars($href),
                $type,
                papaya_strings::escapeHTMLChars($data['media_title']),
                papaya_strings::escapeHTMLChars($data['media_description'])
              );
            }
            $result .= '</files>'.LF;
          }
          // Set info about the article's wiki links
          $linkList = $this->baseWiki->checkWikiLinks($articleId);
          if (!empty($linkList)) {
            $result .= '<wiki-links>';
            foreach ($linkList as $node => $exists) {
              $existAttribute = $exists !== NULL ? ' exists="true"' : '';
              $result .= sprintf(
                '<link href="%s" node="%s"%s/>',
                $this->getWebLink(NULL, NULL, NULL, array('article' => $node), $this->paramName),
                papaya_strings::escapeHTMLChars($node),
                $existAttribute
              );
            }
            $result .= '</wiki-links>';
          }
        } elseif ($mode == 'edit') {
          $articleSource = $this->baseWiki->getArticleSource($articleId);
          $result .= $this->getEditForm($articleSource);
        }
      }
      if ($mode == 'preview') {
        $articleSource = NULL;
        if (isset($this->params['version'])) {
          $articleSource = $this->baseWiki->getArticleSource($articleId, $this->params['version']);
          if ($articleSource !== NULL) {
            $versionString = sprintf(
              papaya_strings::escapeHTMLChars($this->data['caption_version_from']),
              date('Y-m-d H:i:s', $this->params['version'])
            );
            $result .= sprintf('<message type="info">%s</message>', $versionString);
          }
        } elseif (isset($this->params['article_source'])) {
          $articleSource = $this->params['article_source'];
        }
        if ($articleSource !== NULL && $articleSource != '') {
          $articleXML = $this->baseWiki->parseWikiCodeToXML(
            $articleSource,
            $this->keywords
          );
          $result .= $articleXML;
        }
        $result .= $this->getEditForm($articleSource);
      } elseif ($mode == 'edit' && $articleId == 0) {
        $result .= $this->getEditForm();
      }
      if ($comparison) {
        $title = sprintf($this->data['caption_compare'], $article);
      } elseif ($mode == 'edit' || $mode == 'preview') {
        $title = sprintf($this->data['caption_edit_article'], $article);
      } elseif ($mode == 'versions') {
        $title = sprintf($this->data['caption_article_versions'], $article);
      } elseif ($mode == 'search') {
        if (strstr($this->data['caption_searchresults'], '%s')) {
          $title = sprintf($this->data['caption_searchresults'], $article);
        } else {
          $title = $this->data['caption_searchresults'];
        }
      } else {
        $title = $article;
      }
      $result .= sprintf('<subtitle>%s</subtitle>', papaya_strings::escapeHTMLChars($title));
    }
    // Translations
    $pageTranslations = $this->parentObj->loadTranslationsData();
    if (isset($articleId) && $articleId > 0) {
      $translations = $this->baseWiki->getTranslations($articleId);
    } else {
      $translations = array();
    }
    $result .= '<article-translations>';
    $result .= sprintf('<index href="%s" />', $this->getWebLink($this->data['index_page']));
    foreach ($pageTranslations as $pageTranslation) {
      if ($pageTranslation['lng_id'] == $language) {
        // Current translation
        $result .= sprintf(
          '<translation lng="%s" href="%s"/>',
          $pageTranslation['lng_short'],
          $this->getWebLink(NULL, NULL, NULL, array('article' => $article), $this->paramName)
        );
      } elseif (isset($translations[$pageTranslation['lng_short']])) {
        // Wiki page translation available
        $result .= sprintf(
          '<translation lng="%s" href="%s"/>',
          $pageTranslation['lng_short'],
          $this->getWebLink(
            NULL,
            $pageTranslation['lng_ident'],
            NULL,
            array('article' => $translations[$pageTranslation['lng_short']]['node']),
            $this->paramName
          )
        );
      } else {
        // Wiki page translation not available -- use index page
        $result .= sprintf(
          '<translation lng="%s" href="%s"/>',
          $pageTranslation['lng_short'],
          $this->getWebLink($this->data['index_page'], $pageTranslation['lng_ident'])
        );
      }
    }
    $result .= '</article-translations>';
    $result .= '</wikipage>';
    return $result;
  }

  /**
  * Get the article editor form
  *
  * @param string $articleSource optional, default ''
  * @return string XML
  */
  function getEditForm($articleSource = '') {
    $result = sprintf(
      '<edit href="%s">',
      $this->getWebLink()
    );
    $result .= sprintf('<mode param="%s[mode]"/>', $this->paramName);
    $result .= sprintf(
      '<source name="%s[article_source]">%s</source>',
      $this->paramName,
      papaya_strings::escapeHTMLChars($articleSource)
    );
    if (isset($this->params['article_comment'])) {
      $commentValue = sprintf(
        ' value="%s"',
        papaya_strings::escapeHTMLChars($this->params['article_comment'])
      );
    } else {
      $commentValue = '';
    }
    $result .= sprintf(
      '<comment name="%s[article_comment]" caption="%s" %s />',
      $this->paramName,
      papaya_strings::escapeHTMLChars($this->data['caption_comment']),
      $commentValue
    );
    $result .= sprintf(
      '<preview param="%s[preview]" caption="%s"/>',
      $this->paramName,
      papaya_strings::escapeHTMLChars($this->data['caption_preview'])
    );
    $result .= sprintf(
      '<save param="%s[save]" caption="%s"/>',
      $this->paramName,
      papaya_strings::escapeHTMLChars($this->data['caption_save'])
    );
    $result .= '</edit>';
    return $result;
  }
}
?>
