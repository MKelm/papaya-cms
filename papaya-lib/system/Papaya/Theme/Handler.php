<?php
/**
* Papaya theme handler class
*
* @copyright 2010 by papaya Software GmbH - All rights reserved.
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
* @subpackage Theme
* @version $Id: Handler.php 37655 2012-11-08 16:58:19Z weinert $
*/

/**
* Papaya theme handler class
*
* @package Papaya-Library
* @subpackage Theme
*/
class PapayaThemeHandler extends PapayaObject {

  /**
   * Name of sub theme.
   */
  protected $_subThemeName = NULL;

  /**
  * Get url for theme files, is $themeName is empty the current theme is used.
  *
  * @param string $themeName
  * @return string
  */
  public function getUrl($themeName = NULL) {
    $options = $this->papaya()->options;
    $baseUrl = '';
    if (PapayaUtilServerProtocol::isSecure()) {
      $baseUrl = $options->getOption('PAPAYA_CDN_THEMES_SECURE', '');
    }
    if (empty($baseUrl)) {
      $baseUrl = $options->getOption('PAPAYA_CDN_THEMES', '');
    }
    if (empty($baseUrl)) {
      $baseUrl = $this
        ->papaya()
        ->request
        ->getUrl()
        ->getHostUrl();
      $baseUrl .= PapayaUtilFilePath::cleanup(
        $options->getOption('PAPAYA_PATH_WEB').$options->getOption('PAPAYA_PATH_THEMES')
      );
    }
    if (empty($themeName)) {
      $themeName = $this->getTheme();
    } else {
      $themeNameParts = explode('-', $themeName);
      if (count($themeNameParts) == 2) {
        $themeName = $themeNameParts[0];
        $this->_subThemeName = $themeNameParts[1];
      }
    }
    return $baseUrl.$themeName.'/';
  }

  /**
  * Get local path on server to theme directories
  *
  * @return string
  */
  public function getLocalPath() {
    $root = PapayaUtilFilePath::getDocumentRoot(
      $this->papaya()->options
    );
    $path = $this
      ->papaya()
      ->options
      ->getOption('PAPAYA_PATH_THEMES');
    return PapayaUtilFilePath::cleanup($root.'/'.$path);
  }

  /**
  * Get local path on server to theme files
  *
  * @param string $themeName
  * @return string
  */
  public function getLocalThemePath($themeName = NULL) {
    if (empty($themeName)) {
      $themeName = $this->getTheme();
    }
    return PapayaUtilFilePath::cleanup(
      $this->getLocalPath().$themeName
    );
  }

  /**
   * Load the dynamic value defintion from the theme.xml and return it
   *
   * @return PapayaThemeDefinition
   */
  public function getDefinition($theme) {
    $definition = new PapayaThemeDefinition();
    $definition->load(
      $this->getLocalThemePath($theme).'/theme.xml'
    );
    return $definition;
  }

  /**
  * Get the currently active theme name
  *
  * @return string
  */
  public function getTheme() {
    $theme = '';
    $isPreview = $this
      ->papaya()
      ->request
      ->getParameter('preview', FALSE, NULL, PapayaRequest::SOURCE_PATH);
    if ($isPreview) {
      $theme = $this
        ->papaya()
        ->session
        ->values
        ->get('PapayaPreviewTheme');
    }
    if (empty($theme)) {
      $theme = $this
        ->papaya()
        ->options
        ->getOption('PAPAYA_LAYOUT_THEME');
    }
    $themeNameParts = explode('-', $theme);
    if (count($themeNameParts) == 2) {
      $theme = $themeNameParts[0];
      $this->_subThemeName = $themeNameParts[1];
    }
    return $theme;
  }

  /**
  * Get the currently active sub theme name
  *
  * @return string
  */
  public function getSubTheme() {
    return $this->_subThemeName;
  }

  /**
  * Get the currently active theme set id
  *
  * @return string
  */
  public function getThemeSet() {
    $themeSet = 0;
    $isPreview = $this
      ->papaya()
      ->request
      ->getParameter('preview', FALSE, NULL, PapayaRequest::SOURCE_PATH);
    if ($isPreview) {
      $themeSet = $this
        ->papaya()
        ->session
        ->values
        ->get('PapayaPreviewThemeSet', 0);
    }
    if ($themeSet <= 0) {
      $themeSet = $this
        ->papaya()
        ->options
        ->getOption('PAPAYA_LAYOUT_THEME_SET', 0);
    }
    return (int)$themeSet;
  }

  /**
  * Set preview theme (saved in session)
  *
  * @param string $themeName
  * @return void
  */
  public function setThemePreview($themeName) {
    $this
      ->papaya()
      ->session
      ->values
      ->set('PapayaPreviewTheme', $themeName);
  }

  /**
  * Remove preview theme (saved in session)
  *
  * @return void
  */
  public function removeThemePreview() {
    $this
      ->papaya()
      ->session
      ->values
      ->set('PapayaPreviewTheme', NULL);
  }
}