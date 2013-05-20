<?php
/**
* papaya Wiki, base class
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
* @version $Id: base_wiki.php 37506 2012-09-05 14:03:16Z kersken $
*/

/**
* Base class base_db
*/
require_once(PAPAYA_INCLUDE_PATH.'system/sys_base_db.php');

/**
* Article status constants
*/
if (!defined('PAPAYA_WIKI_ARTICLE_OPEN')) {
  define('PAPAYA_WIKI_ARTICLE_OPEN', 0);
}
if (!defined('PAPAYA_WIKI_ARTICLE_SURFERONLY')) {
  define('PAPAYA_WIKI_ARTICLE_SURFERONLY', 1);
}
if (!defined('PAPAYA_WIKI_ARTICLE_READONLY')) {
  define('PAPAYA_WIKI_ARTICLE_READONLY', 2);
}
if (!defined('PAPAYA_WIKI_ARTICLE_LOCKED')) {
  define('PAPAYA_WIKI_ARTICLE_LOCKED', 3);
}

/**
*
* @package Papaya-Modules
* @subpackage Beta-Wiki
*/
class base_wiki extends base_db {
  /**
  * papaya database table wiki article
  * @var string $tableArticle
  */
  var $tableArticle = '';

  /**
  * papaya database table wiki article version
  * @var string $tableArticleVersion
  */
  var $tableArticleVersion = '';

  /**
  * papaya database table wiki article XML
  * @var string $tableArticle
  */
  var $tableArticleXML = '';

  /**
  * papaya database table wiki categories
  * @var string $tableCategories
  */
  var $tableCategories = '';

  /**
  * papaya database table categories to articles links
  * @var string $tableCategoriesArticles
  */
  var $tableCategoriesArticles = '';

  /**
  * papaya database table wiki links
  * @var string $tableWikiLinks
  */
  var $tableWikiLinks = '';

  /**
  * papaya database table translations
  * @var string $tableTranslations
  */
  var $tableTranslations = '';

  /**
  * papaya database table media
  * @var string
  */
  var $tableMedia = '';

  /**
  * papaya database table media to articles links
  * @var string
  */
  var $tableMediaArticles = '';

  /**
  * List of wiki categories
  * @var array $categories
  */
  var $categories = array();

  /**
  * List of wiki links
  * @var array $wikiLinks
  */
  var $wikiLinks = array();

  /**
  * List of article translations
  * @var array $translations
  */
  var $translations = array();

  /**
  * List of media files
  * @var array $files
  */
  var $files = array();

  /**
  * Article status variants
  * @var array
  */
  var $status = array(
    PAPAYA_WIKI_ARTICLE_OPEN => 'Editable by everyone',
    PAPAYA_WIKI_ARTICLE_SURFERONLY => 'Editable by logged-in surfers only',
    PAPAYA_WIKI_ARTICLE_READONLY => 'Not editable',
    PAPAYA_WIKI_ARTICLE_LOCKED => 'Locked (invisible)'
  );

  /**
  * Current article id
  * @var integer
  */
  var $articleId = 0;

  /**
  * Constructor
  *
  * @param &string $msgs
  * @param string $paramName optional, default 'wiki'
  */
  function __construct(&$msgs, $paramName = 'wiki') {
    $this->paramName = $paramName;
    // Wiki database tables
    $this->tableArticle = PAPAYA_DB_TABLEPREFIX.'_wiki_article';
    $this->tableArticleVersion = PAPAYA_DB_TABLEPREFIX.'_wiki_article_version';
    $this->tableArticleXML = PAPAYA_DB_TABLEPREFIX.'_wiki_article_xml';
    $this->tableCategories = PAPAYA_DB_TABLEPREFIX.'_wiki_categories';
    $this->tableCategoriesArticles = PAPAYA_DB_TABLEPREFIX.'_wiki_categories_articles';
    $this->tableWikiLinks = PAPAYA_DB_TABLEPREFIX.'_wiki_links';
    $this->tableTranslations = PAPAYA_DB_TABLEPREFIX.'_wiki_translations';
    $this->tableMedia = PAPAYA_DB_TABLEPREFIX.'_wiki_media';
    $this->tableMediaArticles = PAPAYA_DB_TABLEPREFIX.'_wiki_media_articles';
  }

  /**
  * PHP4 constructor
  *
  * @param &string $msgs
  * @param string $paramName optional, default 'wiki'
  */
  function base_wiki(&$msgs, $paramName = 'wiki') {
    $this->__construct($msgs, $paramName);
  }

  /**
  * Parse wiki code to XML
  *
  * Gets the singleton instance of the wiki parser class
  * and calls its parse() method to parse a string of wiki code
  *
  * @param string $wikiCode
  * @param array $keywords
  * @param boolean $xml optional, default TRUE
  * @return string XML
  */
  function parseWikiCodeToXML($wikiCode, $keywords, $xmlMode = TRUE) {
    include_once(dirname(__FILE__).'/base_wiki_parser.php');
    $parser = &base_wiki_parser::getInstance();
    $xml = $parser->parse($wikiCode, $keywords, $xmlMode);
    if ($xmlMode) {
      if (!empty($parser->categories)) {
        $this->categories = $parser->categories;
      }
      if (!empty($parser->wikiLinks)) {
        $this->wikiLinks = $parser->wikiLinks;
      }
      if (!empty($parser->translations)) {
        $this->translations = $parser->translations;
      }
      if (!empty($parser->files)) {
        $this->files = $parser->files;
      }
    }
    return $xml;
  }

  /**
  * Parse wiki code to plain text
  *
  * Calls parseWikiCodeToXML with $xml set to FALSE and empty keyword list
  *
  * @param string $wikiCode
  * @return string plain text
  */
  function parseWikiCodeToPlainText($wikiCode) {
    return $this->parseWikiCodeToXML($wikiCode, array(), FALSE);
  }

  /**
  * Create a new article node
  *
  * Required arguments are title and language id
  * On success, the id of the newly created node
  * is returned, otherwise FALSE
  *
  * @param string $title
  * @return mixed int|boolean article id or FALSE
  */
  function createArticle($title, $lngId) {
    // If this article already exists, return FALSE
    $sql = "SELECT COUNT(*)
              FROM %s
             WHERE article_title = '%s'
               AND article_lng = '%s'";
    $sqlParams = array($this->tableArticle, $title, $lngId);
    $numArticles = 0;
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      if ($num = $res->fetchField()) {
        $numArticles = $num;
      }
    }
    if ($numArticles > 0) {
      return FALSE;
    }
    // Otherwise create a new article node and return its id
    $data = array(
      'article_title' => $title,
      'article_lng' => $lngId
    );
    $articleId = $this->databaseInsertRecord(
      $this->tableArticle, 'article_node_id', $data
    );
    return $articleId;
  }

  /**
  * Get the status of an article by id
  *
  * @param integer $articleId
  * @return integer
  */
  public function getArticleStatus($articleId) {
    $result = PAPAYA_WIKI_ARTICLE_OPEN;
    $sql = "SELECT article_node_id, article_status
              FROM %s
             WHERE article_node_id = %d";
    $sqlParams = array($this->tableArticle, $articleId);
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result = $row['article_status'];
      }
    }
    return $result;
  }

  /**
  * Set the status of an article
  *
  * @param string $article
  * @param integer $lngId
  * @param integer $status
  * @return mixed numer of affected rows on success, boolean FALSE otherwise
  */
  public function setArticleStatus($article, $lngId, $status) {
    $result = FALSE;
    if (in_array($status, array_keys($this->status))) {
      $articleId = $this->getArticleId($article, $lngId);
      if ($articleId > 0) {
        $result = $this->databaseUpdateRecord(
          $this->tableArticle,
          array('article_status' => $status),
          'article_node_id',
          $articleId
        );
      }
    }
    return $result;
  }

  /**
  * Get the id of an existing article node
  *
  * Required arguments are title and language id
  * If the desired article does not exist, behavior
  * depends on the optional third argument:
  * If set to TRUE, the id of the newly created article
  * will be returned; by default, though, 0 will be returned.
  *
  * @param string $title
  * @param integer $lngId
  * @param boolean $create optional, default FALSE
  * @return integer article id or 0
  */
  function getArticleId($title, $lngId, $create = FALSE) {
    if ($this->articleId == 0 || $create === TRUE) {
      $sql = "SELECT article_node_id, article_title, article_lng
                FROM %s
               WHERE article_title = '%s'
                 AND article_lng = '%s'";
      $sqlParams = array($this->tableArticle, $title, $lngId);
      $articleId = 0;
      if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
        if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $articleId = $row['article_node_id'];
        }
      }
      // Optionally create the article node
      if ($articleId == 0 && $create === TRUE) {
        $articleId = $this->createArticle($title, $lngId);
      }
      $this->articleId = $articleId;
    }
    // By default, simply return the article id or 0
    return $this->articleId;
  }

  /**
  * Get the id of an existing article without caching it
  * (important for redirections)
  *
  * @param string $title
  * @param integer $lngId
  * @return integer article id or 0
  */
  function getArticleIdNoCache($title, $lngId) {
    $sql = "SELECT article_node_id, article_title, article_lng
              FROM %s
             WHERE article_title = '%s'
               AND article_lng = '%s'";
    $sqlParams = array($this->tableArticle, $title, $lngId);
    $articleId = 0;
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $articleId = $row['article_node_id'];
      }
    }
    return $articleId;
  }

  /**
  * Get an article's title by its id
  *
  * @param integer $articleId
  * @return string
  */
  function getArticleTitleById($articleId) {
    $sql = "SELECT article_title, article_node_id
              FROM %s
             WHERE article_node_id = %d";
    $sqlParams = array($this->tableArticle, $articleId);
    $title = '';
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $title = $row['article_title'];
      }
      return $title;
    }
  }

  /**
  * Search article
  *
  * Search an article using an SQL pattern
  * An array of all matching article titles will be returned
  *
  * @param string $pattern
  * @param integer $lngId
  * @param boolean $omitLocked optional, default TRUE
  * @param integer $limit optional, default NULL
  * @param integer $offset optional, default NULL
  * @return array
  */
  function searchArticle($pattern, $lngId, $omitLocked = TRUE, $limit = NULL, $offset = NULL) {
    $sql = "SELECT article_title, article_lng, article_status
              FROM %s
             WHERE article_title ".$pattern."
               AND article_lng = %d";
    if ($omitLocked) {
      $sql .= " AND article_status != ".PAPAYA_WIKI_ARTICLE_LOCKED;
    }
    $sql .= " ORDER BY article_title ASC";
    $sqlParams = array($this->tableArticle, $lngId);
    $result = array();
    $this->databaseEnableAbsoluteCount();
    $this->lastAbsCount = 0;
    if ($res = $this->databaseQueryFmt($sql, $sqlParams, $limit, $offset)) {
      $this->lastAbsCount = $res->absCount();
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result[] = array('title' => $row['article_title'], 'status' => $row['article_status']);
      }
    }
    return $result;
  }

  /**
  * Search contents
  *
  * Return a list of article titles that match a search string in title or content
  *
  * @param string $query
  * @param integer $lngId
  * @return array
  */
  public function searchContents($query, $lngId) {
    $sql = "SELECT a.article_title, a.article_lng, a.article_node_id, ax.article_text
              FROM %1\$s a
             INNER JOIN %2\$s ax
                ON a.article_node_id = ax.article_node_id
             WHERE (a.article_title LIKE '%%%3\$s%%'
                OR ax.article_text LIKE '%%%3\$s%%')
               AND a.article_lng = %4\$d
             ORDER BY a.article_title";
    $sqlParams = array($this->tableArticle, $this->tableArticleXML, $query, $lngId);
    $result = array();
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result[$row['article_node_id']] = $row['article_title'];
      }
    }
    return $result;
  }

  /**
  * Get the currently cached XML of the latest article version
  *
  * If no XML is available, you will receive an empty string
  *
  * @param integer $articleId
  * @return string XML
  */
  function getArticleXML($articleId) {
    $sql = "SELECT article_node_id, article_xml
              FROM %s
             WHERE article_node_id = %d";
    $sqlParams = array($this->tableArticleXML, $articleId);
    $wikiXML = '';
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $wikiXML = $row['article_xml'];
      }
    }
    return $wikiXML;
  }

  /**
  * Get the currently cached plain text of the latest article version
  *
  * Optionally, you can provide a maximum number of characters;
  * otherwise, the complete plain text will be returned.
  * If no plain text is cached, the method will try to create it
  * from the articles wiki code.
  * If no plain text is available at all, you will receive an empty string.
  *
  * @param mixed $articleId (single integer or array)
  * @param integer $length optional, default 0 (== complete text)
  * @return string XML
  */
  function getArticlePlainText($articleId, $length = 0) {
    $multiple = TRUE;
    if (!is_array($articleId)) {
      $multiple = FALSE;
      $singleId = $articleId;
      $articleId = array($articleId);
    }
    $condition = $this->databaseGetSqlCondition('article_node_id', $articleId);
    $sql = "SELECT article_node_id, article_text
              FROM %s
             WHERE ".str_replace('%', '%%', $condition);
    $sqlParams = array($this->tableArticleXML);
    $teasers = array();
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $teasers[$row['article_node_id']] = $row['article_text'];
      }
    }
    foreach ($teasers as $id => $wikiText) {
      if ($wikiText == '') {
        $wikiText = $this->parseWikiCodeToPlainText(
          $this->getArticleSource($articleId)
        );
      }
      if ($length > 0 && strlen($wikiText) > $length) {
        $wikiText = substr($wikiText, 0, $length);
        while (strlen($wikiText) > 0 && !preg_match('(\s$)', $wikiText)) {
          $wikiText = substr($wikiText, 0, strlen($wikiText) - 1);
        }
        $wikiText .= ' ...';
      }
      $teasers[$id] = $wikiText;
    }
    if ($multiple === FALSE) {
      return $teasers[$singleId];
    }
    return $teasers;
  }

  /**
  * Retrieve the wiki code source of an article
  *
  * The article node id is a required argument;
  * you might get it by calling getArticleId() with
  * a title and a language id
  * If you do not provide a timestamp, you will
  * get the latest available version; otherwise you
  * can receive a particular version by providing a timestamp.
  * To learn about the available versions and their timestamps,
  * you might call getArticleVersions() first.
  * If the desired version is available, you will receive
  * the complete record as an associative array.
  * If you provide an invalid timestamp or if there is no
  * article version at all, the method will return NULL.
  *
  * @param integer $articleId
  * @param integer $timestamp optional
  * @return mixed array|NULL
  */
  function getArticleSource($articleId, $timestamp = NULL) {
    $sql = "SELECT article_node_id, version_timestamp,
                   version_source
              FROM %s
             WHERE article_node_id = %d";
    if ($timestamp !== NULL && is_numeric($timestamp)) {
      $sql .= " AND version_timestamp = %d";
    } else {
      $sql .= " ORDER BY version_timestamp DESC";
    }
    $sqlParams = array($this->tableArticleVersion, $articleId);
    if ($timestamp !== NULL && is_numeric($timestamp)) {
      $sqlParams[] = $timestamp;
    }
    $source = NULL;
    if ($res = $this->databaseQueryFmt($sql, $sqlParams, 1)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $source = $row['version_source'];
      }
    }
    return $source;
  }

  /**
  * Get all version timestamps of an article
  *
  * By default, you will get a plain array of timestamps,
  * newest first. If you set the optional $withAuthors argument
  * to TRUE, the return value will be an associative array
  * in which the keys are timestamps and the values are again
  * associative arrays with 'type' and 'author' keys.
  *
  * @param integer $articleId
  * @param boolean $withAutors optional, default FALSE
  */
  function getArticleVersions($articleId, $withAuthors = FALSE) {
    $sql = "SELECT article_node_id, version_timestamp";
    if ($withAuthors === TRUE) {
      $sql .= ", version_author_type, version_author, version_comment";
    }
    $sql .= " FROM %s
             WHERE article_node_id = %d
          ORDER BY version_timestamp DESC";
    $sqlParams = array($this->tableArticleVersion, $articleId);
    $versions = array();
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if ($withAuthors === TRUE) {
          $versions[$row['version_timestamp']] = array(
            'type' => $row['version_author_type'],
            'author' => $row['version_author'],
            'comment' => $row['version_comment']
          );
        } else {
          $versions[] = $row['version_timestamp'];
        }
      }
    }
    return $versions;
  }

  /**
  * Get last modified articles for a language
  *
  * @param integer $lngId
  * @param integer $limit optional, default NULL
  * @param integer $offset optional, default NULL
  * @return array
  */
  function getLastModifiedArticles($lngId, $limit = NULL, $offset = NULL) {
    $sql = "SELECT article_node_id, article_title, article_lng,
                   article_current_timestamp
              FROM %s
             WHERE article_lng = %d
             ORDER BY article_current_timestamp DESC";
    $sqlParams = array($this->tableArticle, $lngId);
    $this->lastAbsCount = 0;
    $this->databaseEnableAbsoluteCount();
    $result = array();
    if ($res = $this->databaseQueryFmt($sql, $sqlParams, $limit, $offset)) {
      $this->lastAbsCount = $res->absCount();
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result[] = $row['article_title'];
      }
    }
    return $result;
  }

  /**
  * Store new version
  *
  * Takes in an article id, a string of wiki code,
  * an array of language-specific keywords,
  * and an optional author type.
  * First, author information is resolved:
  * Retrieve and store the id of the current surfer;
  * if anonymous editing is not allowed and no surfer is logged in,
  * the method will fail with a FALSE return value.
  * If anonymous editing is allowed, retrieve and store the
  * client IP address for anonymous articles.
  * Next, version data including the current timestamp is stored
  * in the version table.
  * Finally, the wiki code is parsed and stored in the article XML table.
  *
  * The return value is either FALSE or the id of the inserted/updated
  * record in the XML table
  *
  * @param integer $articleId
  * @param string $wikiCode
  * @param array $keywords
  * @param boolean $anonymous optional, default FALSE
  * @param string $comment optional
  * @return mixed int|boolean id of the new version or FALSE
  */
  function storeNewVersion($articleId, $wikiCode, $keywords, $anonymous = FALSE, $comment = '') {
    // Author information
    // Get surfer id
    $surfer = $this->papaya()->surfer;
    if ($surfer->isValid) {
      $authorType = 'surfer';
      $authorInfo = $surfer->surferId;
    } elseif ($anonymous === FALSE) {
      // No valid surfer => return FALSE
      return FALSE;
    } else {
      $authorType = 'anon';
      // Get client IP address
      $authorInfo = $_SERVER['REMOTE_ADDR'];
    }
    // Store wiki code in version table
    $versionTime = time();
    $data = array(
      'article_node_id' => $articleId,
      'version_timestamp' => $versionTime,
      'version_author_type' => $authorType,
      'version_author' => $authorInfo,
      'version_source' => $wikiCode,
      'version_comment' => $comment
    );
    $success = $this->databaseInsertRecord(
      $this->tableArticleVersion, NULL, $data
    );
    if ($success === FALSE) {
      return FALSE;
    }
    // Parse wiki code
    $wikiXML = $this->parseWikiCodeToXML($wikiCode, $keywords);
    $wikiText = $this->parseWikiCodeToPlainText($wikiCode);
    // Add or replace XML in the XML table
    $sql = "SELECT COUNT(*)
              FROM %s
             WHERE article_node_id = %d";
    $sqlParams = array($this->tableArticleXML, $articleId);
    $articleExists = 0;
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      if ($num = $res->fetchField()) {
        $articleExists = $num;
      }
    }
    $data = array(
      'article_xml' => $wikiXML,
      'article_text' => $wikiText
    );
    if ($articleExists) {
      $success = $this->databaseUpdateRecord(
        $this->tableArticleXML, $data, 'article_node_id', $articleId
      );
    } else {
      $data['article_node_id'] = $articleId;
      $success = $this->databaseInsertRecord(
        $this->tableArticleXML, NULL, $data
      );
    }
    $this->databaseUpdateRecord(
      $this->tableArticle,
      array('article_current_timestamp' => $versionTime),
      'article_node_id',
      $articleId
    );
    return $success;
  }

  /**
  * Get random article id
  *
  * Implements the popular function to display a random article
  * The only parameter is a language id
  * Returns 0 if there are no articles in the desired language
  *
  * @param integer $lngId
  * @return integer
  */
  function getRandomArticle($lngId) {
    $randFunc = $this->databaseGetSQLSource('RANDOM');
    $sql = "SELECT article_node_id, article_title, article_lng
              FROM %s
             WHERE article_lng = %d
          ORDER BY $randFunc";
    $sqlParams = array($this->tableArticle, $lngId);
    $randomData = NULL;
    if ($res = $this->databaseQueryFmt($sql, $sqlParams, 1)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $randomData = array($row['article_node_id'], $row['article_title']);
      }
    }
    return $randomData;
  }

  /**
  * Get all categories of a specific language
  *
  * @param integer $lngId
  * @param mixed $limit optional, default NULL
  * @param mixed $offset optional, default NULL
  * @return array
  */
  function getCategories($lngId, $limit = NULL, $offset = NULL) {
    $sql = "SELECT category_name, category_lng
              FROM %s
             WHERE category_lng = %d
          ORDER BY category_name ASC";
    $sqlParams = array($this->tableCategories, $lngId);
    $categories = array();
    $this->databaseEnableAbsoluteCount();
    $this->lastAbsCount = 0;
    if ($res = $this->databaseQueryFmt($sql, $sqlParams, $limit, $offset)) {
      $this->lastAbsCount = $res->absCount();
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $categories[] = $row['category_name'];
      }
    }
    return $categories;
  }

  /**
  * Get all categories for an article id
  *
  * @param integer $articleId
  * @return array
  */
  function getArticleCategories($articleId) {
    $sql = "SELECT ca.article_id, ca.category_id, c.category_name
              FROM %s ca
        INNER JOIN %s c
                ON ca.category_id = c.category_id
             WHERE ca.article_id = %d
             ORDER BY c.category_name ASC";
    $sqlParams = array($this->tableCategoriesArticles, $this->tableCategories, $articleId);
    $categories = array();
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $categories[] = $row['category_name'];
      }
    }
    return $categories;
  }

  /**
  * Get all articles for a category name and language
  *
  * @param string $category
  * @param integer $lngId
  * @return array
  */
  function getCategoryArticles($category, $lngId) {
    $sql = "SELECT c.category_name, c.category_lng, c.category_id, ca.article_id, a.article_title
              FROM %s c
        INNER JOIN %s ca
                ON c.category_id = ca.category_id
        INNER JOIN %s a
                ON ca.article_id = a.article_node_id
             WHERE c.category_name = '%s'
               AND c.category_lng = %d
          ORDER BY a.article_title ASC";
    $sqlParams = array(
      $this->tableCategories,
      $this->tableCategoriesArticles,
      $this->tableArticle,
      $category,
      $lngId
    );
    $articles = array();
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $articles[] = $row['article_title'];
      }
    }
    return $articles;
  }

  /**
  * Set categories for an article
  *
  * @param integer $articleId
  * @param array $categories
  * @param integer $lngId
  */
  function setCategories($articleId, $categories, $lngId) {
    // First check whether all categories exist
    $categoryCond = $this->databaseGetSQLCondition('category_name', $categories);
    $sql = "SELECT category_id, category_name, category_lng
              FROM %s
             WHERE ".str_replace('%', '%%', $categoryCond)
            ." AND category_lng = %d";
    $sqlParams = array($this->tableCategories, $lngId);
    $categoryIds = array();
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $categoryIds[$row['category_name']] = $row['category_id'];
      }
    }
    // Create missing categories
    foreach ($categories as $category) {
      if (!isset($categoryIds[$category])) {
        $categoryIds[$category] = $this->databaseInsertRecord(
          $this->tableCategories,
          'category_id',
          array('category_name' => $category, 'category_lng' => $lngId)
        );
      }
    }
    // Delete all existing category entries for current article (easier than checking)
    $this->deleteCategoryLinks($articleId);
    // Now add records for each category
    foreach ($categoryIds as $name => $categoryId) {
      $this->databaseInsertRecord(
        $this->tableCategoriesArticles,
        NULL,
        array('category_id' => $categoryId, 'article_id' => $articleId)
      );
    }
  }

  /**
  * Delete all category links for an article
  *
  * @param integer $articleId
  */
  function deleteCategoryLinks($articleId) {
    $this->databaseDeleteRecord($this->tableCategoriesArticles, 'article_id', $articleId);
  }

  /**
  * Add an uploaded file to the files table
  *
  * @param string $mediaId
  * @param string $fileName
  * @param array $params
  * @return mixed FALSE or new file name
  */
  function addFile($mediaId, $fileName, $params) {
    $data = array(
      'media_id' => $mediaId,
      'media_name' => $fileName
    );
    if (isset($params['filename']) && trim($params['filename']) != '') {
      $data['media_name'] = trim($params['filename']);
    }
    if (isset($params['title']) && trim($params['title']) != '') {
      $data['media_title'] = trim($params['title']);
    }
    if (isset($params['description']) && trim($params['description']) != '') {
      $data['media_description'] = trim($params['description']);
    }
    $success = $this->databaseInsertRecord($this->tableMedia, NULL, $data);
    if (FALSE !== $success) {
      return $data['media_name'];
    }
    return FALSE;
  }

  /**
  * Get a file and its metadata by its name
  *
  * @param string $fileName
  * @return array
  */
  function getFileByName($fileName) {
    $sql = "SELECT media_id, media_name, media_title, media_description
              FROM %s
             WHERE media_name = '%s'";
    $sqlParams = array($this->tableMedia, $fileName);
    $result = array();
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result = $row;
      }
    }
    return $result;
  }

  /**
  * Get all files used in a article
  *
  * @param integer $articleId
  * @return array
  */
  function getArticleFiles($articleId) {
    $sql = "SELECT ma.media_id, ma.article_id, ma.media_index, ma.media_thumb,
                   ma.media_width, ma.media_height, ma.media_border, ma.media_align,
                   m.media_id, m.media_name, m.media_title, m.media_description
              FROM %s ma
             INNER JOIN %s m
                ON ma.media_id = m.media_id
             WHERE ma.article_id = %d";
    $sqlParams = array($this->tableMediaArticles, $this->tableMedia, $articleId);
    $result = array();
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result[$row['media_id']] = $row;
      }
    }
    return $result;
  }

  /**
  * Set the file links to be used in an article
  *
  * @param integer $articleId
  * @param array $files
  */
  function setFiles($articleId, $files) {
    // Delete all file associations for the article (faster/easier than checking)
    $this->deleteFileLinks($articleId);
    // First check whether the requested files are in the files table
    $fileNames = array();
    foreach ($files as $data) {
      $fileNames[] = $data['name'];
    }
    $existingFiles = array();
    $sql = "SELECT media_name, media_id
              FROM %s
             WHERE ".$this->databaseGetSqlCondition('media_name', array_unique($fileNames));
    $sqlParams = array($this->tableMedia);
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $existingFiles[$row['media_name']] = $row['media_id'];
      }
    }
    foreach ($files as $data) {
      if (in_array($data['name'], array_keys($existingFiles))) {
        $linkData = array(
          'media_id' => $existingFiles[$data['name']],
          'article_id' => $articleId,
          'media_index' => $data['index']
        );
        if (is_array($data['options'])) {
          $options = $data['options'];
          if (isset($options['thumb'])) {
            $linkData['media_thumb'] = 1;
          }
          if (isset($options['width']) && $options['width'] > 0) {
            $linkData['media_width'] = $options['width'];
          }
          if (isset($options['height']) && $options['height'] > 0) {
            $linkData['media_height'] = $options['height'];
          }
          if (isset($options['border'])) {
            $linkData['media_border'] = 1;
          }
          if (isset($options['align'])) {
            $linkData['media_align'] = $options['align'];
          }
        }
        $this->databaseInsertRecord(
          $this->tableMediaArticles,
          NULL,
          $linkData
        );
      }
    }
  }

  /**
  * Delete all media links for an article
  *
  * @param integer $articleId
  */
  function deleteFileLinks($articleId) {
    $this->databaseDeleteRecord($this->tableMediaArticles, 'article_id', $articleId);
  }

  /**
  * Get wiki link list for an article
  *
  * The return value is an array of link node => link id|NULL values
  *
  * @param integer $articleId
  * @return $array
  */
  function checkWikiLinks($articleId) {
    $sql = "SELECT l.link_node, l.link_lng, a.article_title, a.article_lng, a.article_node_id
              FROM %s l
   LEFT OUTER JOIN %s a
                ON l.link_node = a.article_title
               AND l.link_lng = a.article_lng
             WHERE l.article_id = %d";
    $sqlParams = array($this->tableWikiLinks, $this->tableArticle, $articleId);
    $linkList = array();
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $linkList[$row['link_node']] = $row['article_node_id'];
      }
    }
    return $linkList;
  }

  /**
  * Set wiki links for an article
  *
  * @param integer $articleId
  * @param array $wikiLinks
  * @param integer $lngId
  */
  function setWikiLinks($articleId, $wikiLinks, $lngId) {
    // Delete all existing links for current article (easier than checking)
    $this->databaseDeleteRecord($this->tableWikiLinks, 'article_id', $articleId);
    // Now add records for each link
    foreach ($wikiLinks as $node) {
      $this->databaseInsertRecord(
        $this->tableWikiLinks,
        NULL,
        array('article_id' => $articleId, 'link_node' => $node, 'link_lng' => $lngId)
      );
    }
  }

  /**
  * Get translations for an article
  *
  * @param integer $articleId
  * @return array
  */
  function getTranslations($articleId) {
    $sql = "SELECT t.translation_lng, t.translation_node, t.article_id, l.lng_ident,
                   l.lng_short, l.lng_id
              FROM %s t
        INNER JOIN %s l
                ON t.translation_lng = l.lng_ident
             WHERE t.article_id = %d";
    $sqlParams = array($this->tableTranslations, PAPAYA_DB_TBL_LNG, $articleId);
    $translations = array();
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $translations[$row['lng_short']] = array(
          'lng_id' => $row['lng_id'],
          'lng' => $row['lng_ident'],
          'node' => $row['translation_node']
        );
      }
    }
    return $translations;
  }

  /**
  * Set translations for an article
  *
  * @param integer $articleId
  * @param array $translations
  */
  function setTranslations($articleId, $translations) {
    // Delete all existing translations for current article (easier than checking)
    $this->databaseDeleteRecord($this->tableTranslations, 'article_id', $articleId);
    // Now add records for each translation
    foreach ($translations as $lng => $node) {
      $data = array(
        'article_id' => $articleId,
        'translation_lng' => $lng,
        'translation_node' => $node
      );
      $this->databaseInsertRecord($this->tableTranslations, NULL, $data);
    }
  }

  /**
  * Compare two versions of an article
  *
  * @param integer $articleId
  * @param integer $oldVersionTimestamp
  * @param integer $newVersionTimestamp
  * @return string XML
  */
  function compareVersions($articleId, $oldVersionTimestamp, $newVersionTimestamp) {
    $sql = "SELECT article_node_id, version_timestamp, version_source
              FROM %s
             WHERE article_node_id = %d
               AND (version_timestamp = %d OR version_timestamp = %d)";
    $sqlParams = array(
      $this->tableArticleVersion,
      $articleId,
      $oldVersionTimestamp,
      $newVersionTimestamp
    );
    $oldSource = '';
    $newSource = '';
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if ($row['version_timestamp'] == $oldVersionTimestamp) {
          $oldSource = $row['version_source'];
        } else {
          $newSource = $row['version_source'];
        }
      }
    }
    $oldText = '';
    if ($oldSource != '') {
      $oldText = $this->parseWikiCodeToPlainText($oldSource);
    }
    $newText = '';
    if ($newSource != '') {
      $newText = $this->parseWikiCodeToPlainText($newSource);
    }
    return $this->simpleLineDiff($oldText, $newText);
  }

  /**
  * Simple line-based diff
  *
  * @param string $oldText
  * @param string $newText
  * @return string XML
  */
  function simpleLineDiff($oldText, $newText) {
    // Split both strings to arrays of single lines
    $oldLinesRaw = preg_split('((\n|\r\n))', $oldText);
    $newLinesRaw = preg_split('((\n|\r\n))', $newText);
    $oldLines = array();
    $newLines = array();
    // Ignore empty lines from both arrays
    foreach ($oldLinesRaw as $oldLine) {
      if (trim($oldLine) != '') {
        $oldLines[] = trim($oldLine);
      }
    }
    foreach ($newLinesRaw as $newLine) {
      if (trim($newLine) != '') {
        $newLines[] = trim($newLine);
      }
    }
    $result = '';
    // Compare in both directions
    $oldToNew = array_diff($oldLines, $newLines);
    $newToOld = array_diff($newLines, $oldLines);
    foreach ($oldLines as $number => $oldLine) {
      if (isset($oldToNew[$number]) && isset($newToOld[$number])) {
        $result .= '<subst>';
        $result .= sprintf('<from>%s</from>', $oldToNew[$number]);
        $result .= sprintf('<by>%s</by>', $newToOld[$number]);
        $result .= '</subst>';
      } elseif (isset($oldToNew[$number])) {
        $result .= sprintf('<del>%s</del>', $oldToNew[$number]);
      } elseif (isset($newToOld[$number])) {
        $result .= sprintf('<ins>%s</ins>', $newToOld[$number]);
      } else {
        $result .= sprintf('<unchanged>%s</unchanged>', $oldLine);
      }
    }
    if (!empty($newToOld)) {
      if (max(array_keys($newToOld)) >= count($oldLines)) {
        foreach ($newToOld as $number => $line) {
          if ($number >= count($oldLines)) {
            $result .= sprintf('<ins>%s</ins>'.LF, $line);
          }
        }
      }
    }
    return $result;
  }

  /**
  * Simple word-based diff
  *
  * @param string $oldLine
  * @param string $newLine
  * @return string XML
  */
  function simpleWordDiff($oldLine, $newLine) {
    // Split both strings to arrays of single words
    $oldWords = preg_split('(\s+)', $oldLine);
    $newWords = preg_split('(\s+)', $newLine);
    $result = '';
    // Compare in both directions
    $oldToNew = array_diff($oldWords, $newWords);
    $newToOld = array_diff($newWords, $oldWords);
    $this->debug($oldWords, $newWords, $oldToNew, $newToOld);
    $ntoOffset = 0;
    foreach ($oldWords as $number => $oldWord) {
      if (isset($oldToNew[$number]) && isset($newToOld[$number])) {
        $result .= sprintf('<del>%s</del>', $oldToNew[$number]);
        $result .= sprintf('<ins>%s</ins>', $newToOld[$number]);
      } elseif (isset($oldToNew[$number])) {
        $result .= sprintf('<del>%s</del>', $oldToNew[$number]);
      } elseif (isset($newToOld[$number])) {
        $result .= sprintf('<ins>%s</ins>', $newToOld[$number]);
        $ntoOffset++;
      } else {
        $result .= sprintf('<unchanged>%s</unchanged>', $oldWord);
      }
    }
    /*
    if (!empty($newToOld)) {
      if (max(array_keys($newToOld)) >= count($oldWords)) {
        foreach ($newToOld as $number => $line) {
          if ($number >= count($oldWords)) {
            $result .= sprintf('<ins>%s</ins>'.LF, $line);
          }
        }
      }
    }
    */
    return $result;
  }
}
?>
