<?php
/**
* Userverwaltung
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
* @package Papaya
* @subpackage Administration
* @version $Id: glyphview.php 37753 2012-11-30 11:37:43Z weinert $
*/

/**
* Authentication
*/
require_once("./inc.auth.php");

if ($PAPAYA_SHOW_ADMIN_PAGE &&
    $PAPAYA_USER->hasPerm(PapayaAdministrationPermissions::SYSTEM_SETTINGS)) {
  initNavigation('options.php');
  $PAPAYA_LAYOUT->setParam(
    'PAGE_TITLE', _gt('Administration').' - '._gt('Settings').' - '._gt('Icons')
  );
  $PAPAYA_LAYOUT->setParam('PAGE_ICON', $PAPAYA_IMAGES['items-option']);

  $images = array();
  foreach ($PAPAYA_IMAGES as $imgIdx => $fileName) {
    if (!preg_match('~^\d+$~', $imgIdx)) {
      $images[$imgIdx] = $fileName;
    }
  }
  ksort($images);

  $groupPrefix = NULL;
  $oldImages = 0;
  $imageCount = 0;
  $fileCount = 0;
  $result = '';
  foreach ($images as $imgIdx => $fileName) {
    if (preg_match('~^(\w+)-([\w-]+)~', $imgIdx, $matches)) {
      $currentPrefix = $matches[1];
      if ($groupPrefix != $currentPrefix) {
        if (isset($groupPrefix)) {
          $result .= '</items>'.LF;
          $result .= sprintf(
            '<status>%s: %d / %s: %d</status>'.LF,
            htmlspecialchars(_gt('Icons')),
            (int)$imageCount,
            htmlspecialchars(_gt('Files')),
            (int)$fileCount
          );
          $result .= '</listview>'.LF;
        }
        $groupPrefix = $currentPrefix;
        $result .= sprintf(
          '<listview title="%s" mode="tile"><items>',
          htmlspecialchars($groupPrefix)
        );
        $imageCount = 0;
        $fileCount = 0;
      }
      $sizes = array('16x16', '22x22', '48x48');
      $glyph = 'pics/tpoint.gif';
      $sizesAvailable = array();
      foreach ($sizes as $size) {
        $imageFile = 'pics/icons/'.$size.'/'.$fileName;
        if (file_exists(dirname(__FILE__).'/'.$imageFile)) {
          $glyph = $imageFile;
          $sizesAvailable[] = $size;
          $fileCount++;
        }
      }
      $imageCount++;
      $result .= sprintf(
        '<listitem title="%s" hint="%s: %s" image="./%s?" subtitle="%s"/>'."\n",
        htmlspecialchars($matches[2]),
        htmlspecialchars($imgIdx),
        htmlspecialchars($fileName),
        htmlspecialchars($glyph),
        htmlspecialchars(implode(', ', $sizesAvailable))
      );
    }
  }
  if (isset($groupPrefix)) {
    $result .= '</items>'.LF;
    $result .= sprintf(
      '<status>%s: %d / %s: %d</status>'.LF,
      htmlspecialchars(_gt('Icons')),
      (int)$imageCount,
      htmlspecialchars(_gt('Files')),
      (int)$fileCount
    );
    $result .= '</listview>'.LF;
  }

  $PAPAYA_LAYOUT->add($result);
}
require('inc.footer.php');
?>