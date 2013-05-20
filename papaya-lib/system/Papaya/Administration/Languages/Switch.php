<?php
/**
* Language switch administration control
*
* @copyright 2011 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Library
* @subpackage Administration
* @version $Id: Switch.php 38101 2013-02-12 10:32:02Z weinert $
*/

/**
* Language switch administration control, allows to access the current content language and
* append links for available content languages.
*
* The object will be available in the application registry, because the content language
* informations are needed in different administration controls.
*
* @package Papaya-Library
* @subpackage Administration
*/
class PapayaAdministrationLanguagesSwitch extends PapayaUiControlInteractive {

  /**
  * Internal property for language list
  *
  * @var PapayaContentLanguages
  */
  private $_languages = NULL;

  /**
  * Internal property for current language
  *
  * @var PapayaContentLanguage
  */
  private $_current = NULL;

  /**
  * Getter/Setter for a content languages record list.
  *
  * @param PapayaContentLanguages $languages
  * @return PapayaContentLanguages
  */
  public function languages(PapayaContentLanguages $languages = NULL) {
    if (isset($languages)) {
      $this->_languages = $languages;
    }
    if (is_null($this->_languages)) {
      $this->_languages = new PapayaContentLanguages();
    }
    return $this->_languages;
  }

  /**
  * Get the currently selected content language. If no language is found, a default language
  * object is initialized.
  *
  * @return PapayaContentLanguage
  */
  public function getCurrent() {
    $this->prepare();
    return $this->_current;
  }

  /**
  * Appends a <links> element with references for the different content languages.
  *
  * @param PapayaXmlElement $parent
  */
  public function appendTo(PapayaXmlElement $parent) {
    $current = $this->getCurrent();
    $links = $parent->appendElement(
      'links',
      array('title' => new PapayaUiStringTranslated('Content Language'))
    );
    foreach ($this->languages() as $id => $language) {
      $reference = new PapayaUiReference();
      $reference->papaya($this->papaya());
      $reference->setParameters(array('language_select' => $id), 'lngsel');
      $link = $links->appendElement(
        'link',
        array(
          'href' => $reference->getRelative(),
          'title' => $language['title'],
          'image' => $language['image']
        )
      );
      if ($current->id == $id) {
        $link->setAttribute('selected', 'selected');
      }
    }
    return $links;
  }

  /**
  * Load content languages and determine current language. The method looks for
  * a request parameter, a session value, the user interface language, the default content language
  * and the default interface language.
  *
  * If none of these are found a default language object containing data for English ist created.
  *
  * @return PapayaContentLanguage
  */
  private function prepare() {
    $application = $this->papaya();
    if (is_null($this->_current)) {
      $languages = $this->languages();
      $languages->load(PapayaContentLanguages::FILTER_IS_CONTENT);
      if ($id = $this->parameters()->get('lngsel[language_select]')) {
        $this->_current = $languages->getLanguage($id);
      } elseif ($id = $application->session->values()->get(array($this, 'CONTENT_LANGUAGE'))) {
        $this->_current = $languages->getLanguage($id);
      } elseif (isset($application->administrationUser->options['PAPAYA_CONTENT_LANGUAGE'])) {
        $this->_current = $languages->getLanguage(
          $application->administrationUser->options['PAPAYA_CONTENT_LANGUAGE']
        );
      } elseif ($id = $application->options->getOption('PAPAYA_CONTENT_LANGUAGE')) {
        $this->_current = $languages->getLanguage($id);
      } elseif ($code = $application->options->getOption('PAPAYA_UI_LANGUAGE')) {
        $this->_current = $languages->getLanguageByCode($code);
      }
    }
    if (is_null($this->_current)) {
      if ($language = reset(iterator_to_array($languages))) {
        $this->_current = $languages->getLanguage($language['id']);
      } else {
        $this->_current = $this->getDefault();
      }
    } else {
      $application->session->values()->set(array($this, 'CONTENT_LANGUAGE'), $this->_current->id);
    }
  }

  /**
  * Create and return a language object with default (English) data.
  *
  * @return PapayaContentLanguage
  */
  private function getDefault() {
    $result = new PapayaContentLanguage();
    $result->assign(
      array(
        'id' => 1,
        'identifier' => 'en',
        'code' => 'en-US',
        'title' => 'English',
        'image' => 'us.gif',
        'is_content' => 1,
        'is_interface' => 1
      )
    );
    return $result;
  }
}