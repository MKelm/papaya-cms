<?php
/**
* Page rating box
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
* @subpackage Free-PageRating
* @version $Id: actbox_pagerating.php 34957 2010-10-05 15:57:41Z weinert $
*/

/**
* Basic class aktion box
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_actionbox.php');

/**
* Page rating box
*
* @package Papaya-Modules
* @subpackage Free-PageRating
*/
class actionbox_pagerating extends base_actionbox {

  /**
  * Preview
  * @var boolean $preview
  */
  var $preview = TRUE;

  /**
  * Parameter name
  * @var string $paramName
  */
  var $paramName = 'pr';

  /**
  * Content edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'link_text' => array('Link text', 'isSomeText', TRUE, 'input', 200, '', ''),
    'voted_text' => array('Voted text', 'isSomeText', TRUE, 'input', 200, '', ''),
    'vote_deny' => array('Voted deny', 'isSomeText', TRUE, 'input', 200, '', ''),
    'good_bad_rating' => array('Simple rating', 'isNum', TRUE, 'combo',
      array(1 => 'Yes', 0 => 'No'),
      'Rating selection contains only good or bad', 0),
    'Rating Limits',
    'rating_min' => array('Min', 'isNum', FALSE, 'input', 200, '', -1),
    'rating_max' => array('Max', 'isNum', FALSE, 'input', 200, '', 1),
    'Result Display',
    'dynamic_image' => array('Dynamic image', 'isAlpha', TRUE, 'input', 200, '', ''),
  );

  /**
  * Get parsed data
  *
  * @access public
  * @return string
  */
  function getParsedData() {
    $this->setDefaultData();
    include_once(dirname(__FILE__).'/base_pagerating.php');
    $this->pageratingObj = new base_pagerating();
    $this->pageratingObj->module = &$this;
    $this->pageratingObj->initialize();
    $str = $this->getBoxOutput();
    return $str;
  }

  /**
  * Box page output
  *
  * @access public
  * @return string xml
  */
  function getBoxOutput() {
    $result = '';
    $result .= '<pageranking>'.LF;
    if (!isset($this->params['vote']) &&
        $this->pageratingObj->userCanVote($this->parentObj->topicId)) {
      $result .= '<links>'.LF;
      if (isset($this->data['good_bad_rating']) &&
          $this->data['good_bad_rating']) {
        $result .= sprintf(
          '<link href="%s" value="-1" />'.LF,
          papaya_strings::escapeHTMLChars(
            $this->getWebLink(
              NULL,
              NULL,
              NULL,
              PapayaUtilArray::merge(
                $_GET,
                array('vote' => -1)
              ),
              $this->paramName
            )
          )
        );
        $result .= sprintf(
          '<link href="%s" value="1" />'.LF,
          papaya_strings::escapeHTMLChars(
            $this->getWebLink(
              NULL,
              NULL,
              NULL,
              PapayaUtilArray::merge(
                $_GET,
                array('vote' => 1)
              ),
              $this->paramName
            )
          )
        );
      } else {
        for ($i = $this->data['rating_min']; $i <= $this->data['rating_max']; $i++) {
          $result .= sprintf(
            '<link href="%s" value="%d"/>'.LF,
            papaya_strings::escapeHTMLChars(
              $this->getWebLink(
                NULL,
                NULL,
                NULL,
                PapayaUtilArray::merge(
                  $_GET,
                  array('vote' => $i)
                ),
                $this->paramName
              )
            ),
            (int)$i
          );
        }
      }
      $result .= '</links>'.LF;
    } else {
      if ($this->data['good_bad_rating']) {
        $this->data['rating_min'] = -1;
        $this->data['rating_max'] = 1;
      }
      if (isset($this->params['vote']) &&
          $this->params['vote'] >= $this->data['rating_min'] &&
          $this->params['vote'] <= $this->data['rating_max']) {
        $added = $this->pageratingObj->addResult(
          $this->parentObj->topicId,
          $this->parentObj->getContentLanguageId(),
          empty($this->params['vote']) ? 0 : (int)$this->params['vote']
        );
        if ($added) {
          $result .= sprintf('<message>%s</message>', $this->data['voted_text']);
        } else {
          $result .= sprintf('<message>%s</message>', $this->data['vote_deny']);
        }
      }
      $success = $this->pageratingObj->loadPageList(
        $this->parentObj->getContentLanguageId(),
        $this->parentObj->topicId
      );
      if ($success) {
        $this->pageratingObj->loadTopicTitle($this->parentObj->getContentLanguageId());
        $this->pageratingObj->calculateRating();
        if (isset($this->pageratingObj->pageList[$this->parentObj->topicId]['rating'])) {
          $rating = $this->pageratingObj->pageList[$this->parentObj->topicId]['rating'];
          if (isset($this->data['dynamic_image']) && $this->data['dynamic_image'] != '') {
            $img = $this->escapeForFilename($this->data['dynamic_image'], 'index');
            $img .= '.image.jpg';
            $src = sprintf(' src="%s"', $img);
          } else {
            $src = '';
          }
          $result .= sprintf(
            '<rating value="%d" votes="%d"%s/>',
            (int)$rating,
            (int)$this->pageratingObj->pageList[$this->parentObj->topicId]['votes'],
            $src
          );
        }
      }
    }
    $result .= '</pageranking>'.LF;
    return $result;
  }

  /**
  * Get cache id
  *
  * @access public
  * @return FALSE
  */
  function getCacheId() {
    return FALSE;
  }
}
?>