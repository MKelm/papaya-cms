<?php
/**
* Dynamic images tinymce popup page.
*
* @package Papaya
* @subpackage Administration-TinyMCE
*/

/**
* PHP functions for the tinymce popups.
*/
require_once(dirname(__FILE__).'/inc.functions.php');

if (initializeAdministrationPage()) {
  header('Content-type: text/html; charset=utf-8');
  // @codingStandardsIgnoreStart
  ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
  <html>
    <head>
      <title>{#papaya.image_title}</title>
      <meta http-equiv="content-type" content="text/html; charset=utf-8">
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
      <script language="javascript" type="text/javascript" src="js/dynimage_dlg.js"></script>
    </head>
    <body id="papayaImage" style="display: none">
      <form action="javascript:function(){}();"
            onsubmit="PapayaImageDialog.apply();" id="imageForm">
        <div class="panel_wrapper">
          <table border="0" summary="">
            <tr>
              <td>
                <div id="divPreview">
                  <iframe src="about:blank" id="iframePreview" class="iframePreview"
                     frameborder="0" scrolling="no"></iframe></div>
              </td>
              <td>
                <fieldset>
                  <legend>{#papaya.image_select}</legend>
  <?php
  include_once(PAPAYA_INCLUDE_PATH.'system/papaya_imagegenerator.php');
  $imgGenerator = new papaya_imagegenerator();
  $imgGenerator->loadImageConfs();
  if (isset($imgGenerator->imageConfs) &&
      is_array($imgGenerator->imageConfs) &&
      count($imgGenerator->imageConfs) > 0) {
    echo '<select id="image_ident" onchange="PapayaImageDialog.getImageDialog()">';
    foreach ($imgGenerator->imageConfs as $conf) {
      printf(
        '<option value="%s">%s</option>',
        htmlspecialchars($conf['image_ident']),
        htmlspecialchars($conf['image_title'])
      );
    }
    echo '</select>';
  }
  ?>
                </fieldset>
                <fieldset>
                  <legend>{#papaya.image_attributes}</legend>
                  <div id="dynamicDialog">
                  </div>
                </fieldset>
              </td>
            </tr>
          </table>
        </div>
        <div class="mceActionPanel">
          <div style="float: left; clear: both;">
            <input type="submit" id="insert" name="insert" value="{#insert}" style="float: left;"/>
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
