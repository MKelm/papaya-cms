<?php
/**
*
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
* @package Papaya
* @subpackage Scripts-Controls
* @version $Id: googlemaps.php 37558 2012-10-16 14:35:57Z weinert $
*/

/**
* incusion of base or additional libraries
*/
require_once(dirname(__FILE__).'/inc.controls.php');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
  <title><?php echo _gt('Select Geo Position');?></title>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <link rel="stylesheet"
          type="text/css" href="../../skins/<?php echo PAPAYA_UI_SKIN; ?>/css.popups.php">
    <script
      src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php echo PAPAYA_GMAPS_API_KEY ?>"
      type="text/javascript"></script>
    <script type="text/javascript">
      //<![CDATA[
      function load() {
        if (GBrowserIsCompatible()) {
          var map = new GMap2(document.getElementById("map"));
          map.setCenter(new GLatLng(<?php echo PAPAYA_GMAPS_DEFAULT_POSITION ?>), 13);
          map.addControl(new GLargeMapControl());
          map.addControl(new GMapTypeControl());
          GEvent.addListener(map, "click", function(overlay, point){
            map.clearOverlays();
            if (point) {
              map.addOverlay(new GMarker(point));
              aCurrentData.lat = point.lat();
              aCurrentData.lng = point.lng();
              msg = "Lat: "+point.y.toString()+" Long: "+point.x.toString();
              document.getElementById("editPage").value = msg;
            }
          });
        }
      }

      function CPTD(p,t) {
        var r='';
        var degrees = Math.floor(Math.abs(p));
        var minutes = Math.floor((Math.abs(p)-degrees)*60);
        var seconds = (((Math.abs(p)-degrees)*60-minutes)*60).toFixed(2);
        var orientation;
        if (t=='lat'){
          if (p > 0) {
            orientation='N';
          } else {
            orientation='S';
          }
        } else {
          if (p > 0) {
            orientation='O';
          } else {
            orientation='W';
          }
        }
        return degrees + '� '+minutes+'\' '+seconds+'\'\' '+orientation;
      }

      var aCurrentData = new Array();
      var papayaContext = null;

      function Cancel() {
        if (papayaContext) {
          papayaContext.defer.reject();
        }
        if (!window.frameElement) {
          window.close();
        }
      }

      function Ok() {
        if (papayaContext) {
          papayaContext.defer.resolve(aCurrentData.lat, aCurrentData.lng);
        } else if (typeof linkedObject != 'undefined' && linkedObject != null) {
          if (linkedObject.setGeoPos) {
            linkedObject.setGeoPos(aCurrentData.lat, aCurrentData.lng);
          } else if (linkedObject.value != undefined) {
            linkedObject.value = aCurrentData.lat+","+aCurrentData.lng;
          }
        }
        if (!window.frameElement) {
          window.close();
        }
      }
      //]]>
    </script>
  </head>
  <body id="popup" onload="load()">
    <div class="title">
      <div class="titleArtworkOverlay">
	  <h1 id="popupTitle"><?php echo _gt('Select Geo Position');?></h1>
      </div>
    </div>
    <div id="divLinkModePage">
      <input type="text" class="text" id="editPage" disabled style="width: 300px"/>
    </div>
    <div id="map" style="width: 300px; height: 300px"></div>
    <div class="popupButtonsArea" style="text-align: right; white-space: nowrap;">
      <input type="button" value="<?php echo _gt('Ok');?>"
             class="dialogButton" onclick="Ok();" papayaLang="DlgBtnOk" />
      <input type="button" value="<?php echo _gt('Cancel')?>"
             class="dialogButton" onclick="Cancel();" papayaLang="DlgBtnCancel" />
    </div>
    <div class="footer">
      <div class="footerArtworkOverlay"> </div>
    </div>
  </body>
</html>