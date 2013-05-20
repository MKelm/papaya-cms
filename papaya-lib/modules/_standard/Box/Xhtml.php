<?php
/**
* A simple CMS box for some Xhtml content
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
* @version $Id: Xhtml.php 38384 2013-04-10 14:50:30Z weinert $
*/

/*
* A simple CMS box for some Xhtml content
*
* @package Papaya-Library
* @subpackage Modules-Standard
*/
class PapayaModuleStandardBoxXhtml
  extends
    PapayaObject
  implements
    PapayaPluginAppendable,
    PapayaPluginEditable,
    PapayaPluginCacheable {

  private $_content = NULL;
  private $_editor = NULL;
  private $_cacheDefiniton = NULL;

  /**
   * Append the page output xml to the DOM.
   *
   * @see PapayaXmlAppendable::appendTo()
   */
  public function appendTo(PapayaXmlElement $parent) {
    $parent->appendXml($this->content()->get('text', ''));
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
        'text' => array(
          'caption' => new PapayaUiStringTranslated('Text'),
          'type' => 'richtext',
          'parameters' => 20
        )
      )
    );
    $editor->papaya($this->papaya());
    $editor->dialog()->options['CAPTION_STYLE'] = PapayaUiDialogOptions::CAPTION_NONE;
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
}
