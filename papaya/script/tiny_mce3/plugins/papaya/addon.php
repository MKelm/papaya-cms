<?php
/**
* Addons tinymce popup page.
*
* @package Papaya
* @subpackage Administration-TinyMCE
*/

/**
* PHP functions for the tinymce popups.
*/
require_once(dirname(__FILE__).'/inc.functions.php');

if (initializeAdministrationPage()) {
// @codingStandardsIgnoreStart
  ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
  <html>
    <head>
      <title>{#papaya.addon_title}</title>
      <script language="javascript" type="text/javascript" src="../../tiny_mce_popup.js"></script>
      <script language="javascript" type="text/javascript" src="../../utils/mctabs.js"></script>
      <script language="javascript" type="text/javascript" src="../../utils/form_utils.js"></script>
      <script language="javascript" type="text/javascript" src="../../utils/validate.js"></script>

      <link rel="stylesheet" type="text/css" href="css/papaya.css">
      <script language="javascript" type="text/javascript" src="../../../xmlrpc.js"></script>
      <script language="javascript" type="text/javascript" src="js/jsonclass.js"></script>
      <script language="javascript" type="text/javascript" src="js/papayatag.js"></script>
      <script language="javascript" type="text/javascript" src="js/papayaparser.js"></script>
      <script language="javascript" type="text/javascript" src="js/papayautils.js"></script>
      <script language="javascript" type="text/javascript" src="js/papayaform.js"></script>
      <script language="javascript" type="text/javascript" src="js/addon_dlg.js"></script>
    </head>
    <body id="papayaAddOn" style="display: none">
      <form action="javascript:function(){}();"
            onsubmit="PapayaAddOnDialog.apply();" id="addonForm">
        <fieldset>
          <legend>{#papaya.addon_select}</legend>
  <?php
  include_once(PAPAYA_INCLUDE_PATH.'system/papaya_modulemanager.php');
  $manager = new papaya_modulemanager();
  $modules = $manager->loadModulesList('parser');
  if (isset($modules) && is_array($modules) && count($modules) > 0) {
    echo '<select id="addon_guid" onchange="PapayaAddOnDialog.getAddOnDialog()">';
    foreach ($modules as $module) {
      printf(
        '<option value="%s">%s</option>',
        htmlspecialchars($module['module_guid']),
        htmlspecialchars($module['module_title'])
      );
    }
    echo '</select>';
  }
  ?>
        </fieldset>
        <fieldset>
          <div id="dynamicDialog"></div>
        </fieldset>
        <div class="mceActionPanel">
          <div style="float: left; clear: both;">
            <input type="submit" id="insert" name="insert" value="{#insert}" style="float: left;"/>
            <input type="button" id="browseButton" name="browseButton"
              class="browseButton" value="Browse"
              style="float: left; margin-left: 20px; display: none;"/>
          </div>
          <div style="float: right">
            <input type="button" id="cancel" name="cancel" value="{#cancel}"
                   onclick="tinyMCEPopup.close();" />
          </div>
        </div>
        <div id="papayaErrorBox" style="display: none;">
          <div class="errorPanel">
            <img src="img/dialog-error.png" alt="" class="dialogIcon" />
            <p id="papayaErrorMsg"></p>
            <p id="papayaErrorInfo"></p>
            <button type="button" id="papayaErrorButton">{#close}</button>
            <div class="fixFloat"></div>
          </div>
        </div>
      </form>
    </body>
  </html>
  <?php
// @codingStandardsIgnoreEnd
}
?>
