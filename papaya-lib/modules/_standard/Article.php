<?php
/**
* A simple, standard CMS article page.
*
* @copyright 2013 by papaya Software GmbH - All rights reserved.
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
* @subpackage Modules-Standard
* @version $Id: Article.php 38507 2013-05-27 12:39:00Z weinert $
*/

/*
* A simple, standard CMS article page.
*
* @package Papaya-Library
* @subpackage Modules-Standard
*/
class PapayaModuleStandardArticle
  extends
    PapayaObject
  implements
    PapayaPluginConfigurable,
    PapayaPluginAppendable,
    PapayaPluginQuoteable,
    PapayaPluginEditable,
    PapayaPluginCacheable {

  private $_content = NULL;
  private $_configuration = NULL;

  private $_editor = NULL;
  private $_cacheDefiniton = NULL;

  private $_owner = NULL;
  private $_contentFilters = NULL;

  public function __construct($owner) {
    $this->_owner = $owner;
  }

  /**
   * Append the page output xml to the DOM.
   *
   * @see PapayaXmlAppendable::appendTo()
   */
  public function appendTo(PapayaXmlElement $parent) {
    $filters = $this->filters();
    $filters->prepare(
      $this->content()->get('text', ''), $this->configuration()
    );
    $parent->appendElement('title', array(), $this->content()->get('title', ''));
    $parent->appendElement('teaser')->appendXml($this->content()->get('teaser', ''));
    $parent->appendElement('text')->appendXml(
      $filters->applyTo($this->content()->get('text', ''))
    );
    $parent->append($filters);
  }

  /**
   * Append the teaser output xml to the DOM.
   *
   * @see PapayaXmlAppendable::appendTo()
   */
  public function appendQuoteTo(PapayaXmlElement $parent) {
    $parent->appendElement('title', array(), $this->content()->get('title', ''));
    $parent->appendElement('text')->appendXml($this->content()->get('teaser', ''));
  }

  /**
   * The content is an {@see ArrayObject} containing the stored data.
   *
   * @see PapayaPluginEditable::content()
   * @param PapayaPluginEditableContent $content
   */
  public function content(PapayaPluginEditableContent $content = NULL) {
    if (isset($content)) {
      $this->_content = $content;
    } elseif (NULL == $this->_content) {
      $this->_content = new PapayaPluginEditableContent();
      $this->_content->callbacks()->onCreateEditor = array($this, 'createEditor');
    }
    return $this->_content;
  }

  /**
   * The configuration is an {@see ArrayObject} containing options that can affect the
   * execution of other methods (like appendTo()).
   *
   * @see PapayaPluginConfigurable::configuration()
   * @param PapayaObjectParameters $configuration
   */
  public function configuration(PapayaObjectParameters $configuration = NULL) {
    if (isset($configuration)) {
      $this->_configuration = $configuration;
    } elseif (NULL == $this->_configuration) {
      $this->_configuration = new PapayaObjectParameters();
    }
    return $this->_configuration;
  }

  /**
   * The editor is used to change the stored data in the administration interface.
   *
   * In this case it the editor creates an dialog from a field definition.
   *
   * @see PapayaPluginEditableContent::editor()
   *
   * @return PapayaPluginEditor
   */
  public function createEditor($callbackContext, PapayaPluginEditableContent $content) {
    $editor = new PapayaAdministrationPluginEditorFields(
      $content,
      array(
        'title' => array(
          'caption' => new PapayaUiStringTranslated('Title'),
          'mandatory' => TRUE,
          'type' => 'input',
          'parameters' => 400
        ),
        'teaser' => array(
          'caption' => new PapayaUiStringTranslated('Teaser'),
          'type' => 'richtext_simple',
          'parameters' => 6
        ),
        'text' => array(
          'caption' => new PapayaUiStringTranslated('Text'),
          'type' => 'richtext',
          'parameters' => 20
        )
      )
    );
    $editor->papaya($this->papaya());
    return $editor;
  }

  /**
   * Define the code definition parameters for the output.
   *
   * @see PapayaPluginCacheable::cacheable()
   * @param PapayaCacheIdentifierDefinition $definition
   * @return PapayaCacheIdentifierDefinition
   */
  public function cacheable(PapayaCacheIdentifierDefinition $definition = NULL) {
    if (isset($definition)) {
      $this->_cacheDefiniton = $definition;
    } elseif (NULL == $this->_cacheDefiniton) {
      $this->_cacheDefiniton = new PapayaCacheIdentifierDefinitionPage();
    }
    return $this->_cacheDefiniton;
  }

  public function filters(PapayaPluginFilterContent $filters = NULL) {
    if (isset($filters)) {
      $this->_contentFilters = $filters;
    } elseif (NULL == $this->_contentFilters) {
      $this->_contentFilters = new PapayaPluginFilterContentRecords($this->_owner);
    }
    return $this->_contentFilters;
  }
}
