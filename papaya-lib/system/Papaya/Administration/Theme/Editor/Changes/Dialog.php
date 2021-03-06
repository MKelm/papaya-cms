<?php
/**
* Dialog command that allows to edit the dynamic values on on page, the groups are field groups
*
* @copyright 2012 by papaya Software GmbH - All rights reserved.
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
* @version $Id: Dialog.php 38261 2013-03-11 16:10:20Z weinert $
*/

/**
* Dialog command that allows to edit the dynamic values on on page, the groups are field groups
*
* @package Papaya-Library
* @subpackage Administration
*/
class PapayaAdministrationThemeEditorChangesDialog
  extends PapayaUiControlCommandDialogDatabaseRecord {

  /**
   * @var PapayaThemePage
   */
  private $_themePage = NULL;
  /**
   * @var PapayaThemeHandler
   */
  private $_themeHandler = NULL;
  /**
   * @var PapayaUiDialogFieldFactory
   */
  private $_fieldFactory = NULL;
  /**
   * @var PapayaCacheService
   */
  private $_cacheService = NULL;

  /**
   * Create dialog and add fields for the dynamic values defined by the current theme values page
   *
   * @see PapayaUiControlCommandDialog::createDialog()
   * @return PapayaUiDialog
   */
  public function createDialog() {
    $setId = $this->parameters()->get('set_id', 0);
    if ($setId > 0) {
      $this->record()->load($setId);
    }
    $dialog = new PapayaUiDialogDatabaseSave($this->record());
    if ($page = $this->themePage()) {
      $dialog->caption = new PapayaUiStringTranslated('Dynamic Values: %s', array($page->title));
      $dialog->options->topButtons = TRUE;
      $dialog->parameterGroup($this->parameterGroup());
      $dialog->parameters($this->parameters());
      $dialog->hiddenFields()->merge(
        array(
          'cmd' => 'values_edit',
          'theme' => $this->parameters()->get('theme', ''),
          'set_id' => $setId,
          'page_identifier' => $this->parameters()->get('page_identifier', '')
        )
      );
      foreach ($page->groups() as $group) {
        $fieldset = new PapayaUiDialogFieldGroup($group->title);
        foreach ($group->values() as $value) {
          try {
            $options = new PapayaUiDialogFieldFactoryOptions(
              array(
                'name' => 'values/'.$value->getIdentifier(),
                'caption' => $value->title,
                'default' => $value->default,
                'parameters' => $value->fieldParameters
              )
            );
            $fieldset->fields[] = $field = $this->fieldFactory()->getField(
              $value->fieldType, $options
            );
            $field->setHint($value->hint);
          } catch (PapayaUiDialogFieldFactoryException $e) {
            $fieldset->fields[] = new PapayaUiDialogFieldMessage(
              PapayaMessage::TYPE_ERROR, $e->getMessage()
            );
          }
        }
        $dialog->fields[] = $fieldset;
      }
      if (count($dialog->fields) == 0) {
        $dialog->fields[] = new PapayaUiDialogFieldMessage(
          PapayaMessage::TYPE_ERROR,
          new PapayaUiStringTranslated('Invalid value definition!')
        );
      } else {
        $dialog->buttons[] = new PapayaUiDialogButtonSubmit(new PapayaUiStringTranslated('Save'));
        $this->callbacks()->onExecuteSuccessful = array($this, 'callbackSaveValues');
        $this->callbacks()->onExecuteFailed = array($this, 'callbackShowError');
      }
    } else {
      $dialog->caption = new PapayaUiStringTranslated('Error');
      if (count($dialog->fields) == 0) {
        $dialog->fields[] = new PapayaUiDialogFieldMessage(
          PapayaMessage::TYPE_ERROR,
          new PapayaUiStringTranslated('Theme page not found!')
        );
      }
    }
    return $dialog;
  }

  /**
   * Save data from dialog
   *
   * @param object $context
   * @param PapayaUiDialog $dialog
   */
  public function callbackSaveValues($context, $dialog) {
    $this->papaya()->messages->dispatch(
      new PapayaMessageDisplayTranslated(
        PapayaMessage::TYPE_INFO,
        'Values saved.'
      )
    );
    if ($cache = $this->cache()) {
      $cache->delete('theme', $this->parameters()->get('theme', ''));
    }
  }

  /**
   * Save data from dialog
   *
   * @param object $context
   * @param PapayaUiDialog $dialog
   */
  public function callbackShowError($context, $dialog) {
    $this->papaya()->messages->dispatch(
      new PapayaMessageDisplayTranslated(
        PapayaMessage::TYPE_ERROR,
        'Invalid input. Please check the field(s) "%s".',
        array(implode(', ', $dialog->errors()->getSourceCaptions()))
      )
    );
  }

  /**
   * Theme definition page to access the group and value definition of the selected page
   *
   * @param PapayaContentStructurePage $themePage
   * @return PapayaThemePage
   */
  public function themePage(PapayaContentStructurePage $themePage = NULL) {
    if (isset($themePage)) {
      $this->_themePage = $themePage;
    } elseif (NULL === $this->_themePage) {
      $this->_themePage = $this
        ->themeHandler()
        ->getDefinition($this->parameters()->get('theme', ''))
        ->getPage($this->parameters()->get('page_identifier', ''));
    }
    return $this->_themePage;
  }

  /**
   * The theme handler is an helper object to get general information about the
   * themes of the current installation
   *
   * @param PapayaThemeHandler $handler
   * @return PapayaThemeHandler
   */
  public function themeHandler(PapayaThemeHandler $themeHandler = NULL) {
    if (isset($themeHandler)) {
      $this->_themeHandler = $themeHandler;
    } elseif (NULL === $this->_themeHandler) {
      $this->_themeHandler = new PapayaThemeHandler();
      $this->_themeHandler->papaya($this->papaya());
    }
    return $this->_themeHandler;
  }

  /**
   * The dialog field factory creates field for the given field types using profile classes/objects
   * defined by the field type name.
   *
   * @param PapayaUiDialogFieldFactory $factory
   * @return PapayaUiDialogFieldFactory
   */
  public function fieldFactory(PapayaUiDialogFieldFactory $factory = NULL) {
    if (isset($factory)) {
      $this->_fieldFactory = $factory;
    } elseif (NULL === $this->_fieldFactory) {
      $this->_fieldFactory = new PapayaUiDialogFieldFactory();
    }
    return $this->_fieldFactory;
  }

  /**
   * Access to the theme cache service - to reset the cache after changes.
   *
   * @param PapayaCacheService $service
   * @return PapayaCacheService
   */
  public function cache(PapayaCacheService $service = NULL) {
    if (isset($service)) {
      $this->_cacheService = $service;
    } elseif (NULL == $this->_cacheService) {
      $this->_cacheService = PapayaCache::getService($this->papaya()->options);
    }
    return $this->_cacheService;
  }
}
