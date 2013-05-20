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
* @subpackage Skins-Default
* @version $Id: css.style.php 38398 2013-04-17 10:10:14Z weinert $
*/

/**
* inclusion of base or additional libraries
*/
require_once(dirname(__FILE__).'/../../inc.func.php');
includeThemeDefinition();
controlScriptFileCaching(__FILE__, FALSE);
header('Content-type: text/css');
?>
body, div, ul, li, input, select, textarea {
  -moz-box-sizing: border-box;
  -khtml-box-sizing: border-box;
  box-sizing: border-box;
}
body {
  font-family: sans-serif;
  margin: 0;
  padding: 0;
  background-color: <?php echo PAPAYA_BGCOLOR_WINDOW; ?>;
  font-size: 10px;
  display: inline-block;
  min-width: 100%;
}
body div.pageBorder {
  padding: 10px;
  box-sizing: border-box;
  display: inline-block;
  width: 100%;
}
body.framePage {
  padding: 0;
  background-color: <?php echo PAPAYA_BGCOLOR_WORKAREA; ?>;
  min-width: 20px;
}
body.framePage #workarea {
  border: none;
}

img {
  border: none;
}
li {
  list-style-position: inside;
}
table {
  empty-cells: show;
}

div.fixFloat {
  height: 0;
  line-height: 0;
  clear: both;
  overflow: hidden;
  border: none;
}
div.fixLine {
  font-size: 1px;
}
span.allowWrap {
  font-size: 1px;
  width: 1px;
}

.nonGraphicBrowser {
  display: none;
  visibility: hidden;
}

/* icons */
img.dialogIconLarge {
  width: 48px;
  height: 48px;
  margin: 20px;
  float: left;
}

span.glyphGroup img {
  margin-right: 0.5em;
  margin-left: 0.5em;
}
img.glyph16 {
  width: 16px;
  height: 16px;
  vertical-align: middle;
}
img.glyph22 {
  width: 22px;
  height: 22px;
  vertical-align: middle;
}
img.glyph48 {
  width: 48px;
  height: 48px;
  vertical-align: middle;
}

/* title bar */
#title {
  background-color: <?php echo PAPAYA_BGCOLOR_HEADER; ?>;
  background-image: url(pics/<?php echo PAPAYA_THEME_PICS; ?>/background-title.png);
  background-position: left top;
  color: <?php echo PAPAYA_FGCOLOR_HEADER; ?>;
  font-weight: bold;
  font-size: 18px;
  border-bottom: 1px solid <?php echo PAPAYA_BGCOLOR_WINDOW; ?>;
}
#titleArtworkLeft {
  background-image: url(pics/<?php echo PAPAYA_THEME_PICS; ?>/corner-top.png);
  background-position: left top;
  background-repeat: no-repeat;
}
#titleArtworkRight {
  background-image: url(pics/<?php echo PAPAYA_THEME_PICS; ?>/corner-top.png);
  background-position: right -45px;
  background-repeat: no-repeat;
}
#titleArtworkOverlay {
  background-image: url(pics/background-grid.png);
  background-position: right top;
  background-repeat: repeat;
  padding: 0;
  padding-left: 5px;
  padding-right: 10px;
  height: 37px;
  line-height: 37px;
}
#title .papayaLogo {
  width: 100px;
  height: 30px;
  border: none;
  float: left;
  margin: 4px 100px 0px 0px;
}
#title .pageLogo {
 width: 22px;
 height: 22px;
 float: left;
 margin: 8px 10px 0px 20px;
}
#titleButtons {
  float: right;
  padding: 8px 0 0 10px;
  line-height: 22px;
}
#titleButtons img {
  border: none;
  margin: 0;
  margin-left: 10px;
}
#titleText {
  font-size: 18px;
  -moz-opacity: 0.8;
  opacity: 0.8;
  margin: 0;
  padding: 0;
  margin-right: 100px;
}

/* local page navigation */
#pageNavigation {
  display: none;
  visibility: hidden;
}
a.pageNavigation {
  display: none;
  visibility: hidden;
}

/* footer bar */
#footer {
  background-color: <?php echo PAPAYA_BGCOLOR_FOOTER; ?>;
  background-image: url(pics/<?php echo PAPAYA_THEME_PICS; ?>/background-title.png);
  background-position: right -20px;
  color: <?php echo PAPAYA_FGCOLOR_FOOTER; ?>;
  font-weight: bold;
  font-size: 10px;
  text-align: right;
  border-top: 1px solid <?php echo PAPAYA_BGCOLOR_WINDOW; ?>;
}

#footerArtworkLeft {
  background-image: url(pics/corner-leftbottom.png);
  background-position: left bottom;
  background-repeat: no-repeat;
}
#footerArtworkRight {
  background-image: url(pics/corner-rightbottom.png);
  background-position: right bottom;
  background-repeat: no-repeat;
}
#footerArtworkOverlay {
  background-image: url(pics/background-grid.png);
  background-position: right top;
  background-repeat: repeat;
  padding: 5px 10px;
}
#footer .versionString {

}
#footer .projectString {
  float: left;
}
#footer a {
  color: <?php echo PAPAYA_FGCOLOR_FOOTER; ?>;
  text-decoration: none;
}

/* title menu bar */
#titleMenu {
  background-color: <?php echo PAPAYA_BGCOLOR_SUBTITLE; ?>;
  background-image: url(pics/<?php echo PAPAYA_THEME_PICS; ?>/background-title.png);
  background-position: left -80px;
  background-repeat: repeat-x;
  color: <?php echo PAPAYA_FGCOLOR_SUBTITLE; ?>;
  text-align: left;
  font-size: 10px;
  padding: 2px 5px;
  line-height: 20px;
  font-weight: bold;
}
#titleMenu ul.links {
  display: inline;
  list-style-type: none;
  margin: 0;
  padding: 0;
  line-height: 20px;
}
#titleMenu ul.links li {
  display: inline;
  list-style-type: none;
  margin: 0;
  padding: 0;
  line-height: 20px;
  border-left: 1px solid <?php echo PAPAYA_FGCOLOR_SUBTITLE; ?>;
  padding-left: 5px;
}
#titleMenu ul.links li.caption,
#titleMenu ul.links li.selected {
  border: none;
  padding: 0;
}
#titleMenu ul.links li img {
  vertical-align: middle;
  display: inline;
  margin-bottom: 1px;
  -moz-opacity: 0.4;
  opacity: 0.4;
}
#titleMenu ul.links li.caption img,
#titleMenu ul.links li.selected img,
#titleMenu ul.links li:hover img {
  -moz-opacity: 1;
  opacity: 1;
}
#titleMenu ul.rightLinks {
  float: right;
  list-style-type: none;
  margin: 0;
  padding: 0;
  padding-right: 5px;
  line-height: 20px;
}
#titleMenu ul.rightLinks li {
  display: inline;
  margin-left: 5px;
  line-height: 20px;
}
#titleMenu ul.rightLinks li a {
  border: 1px solid #000000;
  padding: 2px 8px;
}
#titleMenu ul.rightLinks li.selected a {
  background-color: <?php echo PAPAYA_BGCOLOR_HIGHLIGHT; ?>;
  color: <?php echo PAPAYA_FGCOLOR_HIGHLIGHT; ?>;
}
#titleMenu ul.rightLinks li.caption {
  border: none;
}
#titleMenu .user {
  font-weight: bold;
}
#titleMenu a {
  color: <?php echo PAPAYA_FGCOLOR_SUBTITLE; ?>;
  text-decoration: none;
}

/* menu bar */
#pageMenuBar {
  background-color: <?php echo PAPAYA_BGCOLOR_WORKAREA; ?>;
  clear: both;
  min-width: 970px;
}
div.toolBar {
  background-color: <?php echo PAPAYA_BGCOLOR_WORKAREA; ?>;
  border: 1px solid <?php echo PAPAYA_BORDERCOLOR_DOWN; ?>;
  border-bottom: 1px solid <?php echo PAPAYA_BORDERCOLOR_UP; ?>;
  border-right: 1px solid <?php echo PAPAYA_BORDERCOLOR_UP; ?>;
  margin-bottom: 10px;
}
div.menuBar {
  border-top: 1px solid <?php echo PAPAYA_BORDERCOLOR_UP; ?>;
  border-bottom: 1px solid <?php echo PAPAYA_BORDERCOLOR_DOWN; ?>;
  clear: both;
}
div.toolBar div.menuBar {
  border: 1px solid <?php echo PAPAYA_BORDERCOLOR_UP; ?>;
  border-bottom: 1px solid <?php echo PAPAYA_BORDERCOLOR_DOWN; ?>;
  border-right: 1px solid <?php echo PAPAYA_BORDERCOLOR_DOWN; ?>;
}
div.menuBar ul {
  list-style-type: none;
  padding: 0;
  margin: 0;
  display: block;
}
div.menuBar ul li {
  padding: 0;
  border: 1px solid <?php echo PAPAYA_BGCOLOR_WORKAREA; ?>;
}
div.menuBar ul li.button {
  float: left;
  padding: 2px 0 0 0;
}
div.menuBar ul li.combobox {
  float: left;
  padding: 0 2px;
}
div.menuBar ul li.combobox form {
  padding: 0;
  margin: 0;
  width: auto;
}
div.menuBar ul li.separator {
  float: left;
  border-left: 1px solid <?php echo PAPAYA_BORDERCOLOR_DOWN; ?>;
  border-right: 1px solid <?php echo PAPAYA_BORDERCOLOR_UP; ?>;
  color: <?php echo PAPAYA_BGCOLOR_WORKAREA; ?>;
  margin-right: 2px;
  width: 0px;
  overflow: hidden;
  line-height: 20px;
}
div.menuBar ul li.button a.caption {
  white-space: nowrap;
  text-decoration: none;
  color: <?php echo PAPAYA_FGCOLOR_WORKAREA; ?>;
  font-size: 10px;
  line-height: 16px;
  display: block;
  padding: 0;
  margin-left: 20px;
  margin-right: 6px;
}

div.menuBar ul li.combobox span.caption {
  white-space: nowrap;
  text-decoration: none;
  color: <?php echo PAPAYA_FGCOLOR_WORKAREA; ?>;
  font-size: 10px;
  line-height: 16px;
  padding: 0;
  margin-right: 5px;
}

div.menuBar ul li.selected {
  border: 1px solid <?php echo PAPAYA_BORDERCOLOR_DOWN; ?>;
  border-right-color: <?php echo PAPAYA_BORDERCOLOR_UP; ?>;
  border-bottom-color: <?php echo PAPAYA_BORDERCOLOR_UP; ?>;
  background-color: <?php echo PAPAYA_BORDERCOLOR_UP; ?>;
}
div.menuBar ul li.button a.icon img {
  border: none;
  width: 16px;
  height: 16px;
  float: left;
  margin: 0;
  margin-left: 2px;
  vertical-align: bottom;
}

div.menuBar .group {
  border: 1px solid <?php echo PAPAYA_BGCOLOR_HEADER; ?>;
  border-top: none;
  float: left;
  margin: 2px;
}
div.menuBar .group .name {
  clear: both;
  font-size: 9px;
  color: <?php echo PAPAYA_FGCOLOR_HEADER; ?>;
  background-color: <?php echo PAPAYA_BGCOLOR_HEADER; ?>;
  background-image: url(pics/<?php echo PAPAYA_THEME_PICS; ?>/background-title.png);
  text-align: center;
}
div.menuBar .group .buttons {
  display: table;
  float: none;
}
div.menuBar .group ul {
  list-style-type: none;
  padding: 0;
  margin: 0;
  display: table-row;
  float: none;
  line-height: 12px;
}
div.menuBar .group ul li {
  display: table-cell;
  float: none;
  min-width: 45px;
  border: 1px solid <?php echo PAPAYA_BGCOLOR_WORKAREA; ?>;
  padding: 0;
  padding: 0px 2px;
}
div.menuBar .group ul li.selected {
  border: 1px solid <?php echo PAPAYA_BORDERCOLOR_DOWN; ?>;
  border-right-color: <?php echo PAPAYA_BORDERCOLOR_UP; ?>;
  border-bottom-color: <?php echo PAPAYA_BORDERCOLOR_UP; ?>;
  background-color: <?php echo PAPAYA_BORDERCOLOR_UP; ?>;
}
div.menuBar .group ul li a.icon {
  display: block;
  text-decoration: none;
  padding: 2px;
  text-align: center;
}
div.menuBar .group ul li a.icon img {
  border: none;
  width: 22px;
  height: 22px;
  float: none;
  display: block;
  margin: 0 auto 0 auto;
}
div.menuBar .group ul li a.caption {
  display: block;
  font-size: 10px;
  padding: 0px 2px 0px 2px;
  margin: 0;
  text-decoration: none;
  color: <?php echo PAPAYA_FGCOLOR_WORKAREA; ?>;
  text-align: center;
  white-space: nowrap;
}
/* menubar combos */
div.menuBar form {
  float: left;
  padding: 0px 2px 0px 0px;
  margin: 0;
}
div.menuBar form select {
  border: 1px solid <?php echo PAPAYA_BORDERCOLOR_DOWN; ?>;
  max-width: 300px;
}

div.hierarchyMenu {
  background-color: <?php echo PAPAYA_BGCOLOR_SUBTITLE; ?>;
  background-image: url(pics/<?php echo PAPAYA_THEME_PICS; ?>/background-title.png);
  background-position: left -85px;
  border: 1px solid <?php echo PAPAYA_BORDERCOLOR_UP; ?>;
  border-bottom: 1px solid <?php echo PAPAYA_BORDERCOLOR_DOWN; ?>;
  border-right: 1px solid <?php echo PAPAYA_BORDERCOLOR_DOWN; ?>;
  color: <?php echo PAPAYA_FGCOLOR_SUBTITLE; ?>;
}
div.hierarchyMenu ul {
  background-color: <?php echo PAPAYA_BGCOLOR_TITLE; ?>;
  background-image: url(pics/<?php echo PAPAYA_THEME_PICS; ?>/background-title.png);
  background-position: left -55px;
  margin: 0;
  padding: 0;
  list-style-type: none;
  display: table-row;
}
div.hierarchyMenu ul li.item {
  background-image: url(pics/<?php echo PAPAYA_THEME_PICS; ?>/hierarchy-separators.png);
  background-position: right -5px;
  background-repeat: no-repeat;
  color: <?php echo PAPAYA_FGCOLOR_TITLE; ?>;
  display: table-cell;
  white-space: nowrap;
  padding-left: 4px;
  padding-right: 20px;
  line-height: 18px;
}
div.hierarchyMenu ul li.item.last {
  background-position: right -35px;
  display: block;
}
div.hierarchyMenu ul li.item .caption {
  text-decoration: none;
  font-size: 11px;
  padding: 0;
  color: <?php echo PAPAYA_FGCOLOR_TITLE; ?>;
  line-height: 17px;
}
div.hierarchyMenu ul li.item a.caption {
  font-weight: bold;
}
div.hierarchyMenu ul li.item .icon img {
  border: none;
  width: 16px;
  height: 16px;
  margin: 1px;
  vertical-align: middle;
}

/* working area */
#workarea {
  display: inline-block;
  min-width: 100%;
  border-top: 1px solid white;
  background-color: <?php echo PAPAYA_BGCOLOR_WORKAREA; ?>;
  clear: both;
  padding: 10px 0;
}

table.columnGrid {
  background-color: <?php echo PAPAYA_BGCOLOR_WORKAREA; ?>;
  width: 100%;
  border-spacing: 0;
  border-collapse: collapse;
}
table.columnGrid td {
  vertical-align: top;
}
table.columnGrid td.columnToolbar {
  min-width: 200px;
  padding: 0 10px 0 10px;
}
table.columnGrid td.columnLeft {
  min-width: 200px;
  padding: 0 0 10px 10px;
}
table.columnGrid td.columnCenter {
  min-width: 200px;
  padding: 0 10px;
}
table.columnGrid td.columnRight {
  min-width: 200px;
  padding: 0 10px 10px 0;
}



/******************************************************************************
   GUI Controls
******************************************************************************/

/* panel */
#workarea div.panel {
  background-color: <?php echo PAPAYA_BGCOLOR_WORKAREA; ?>;
  border: 1px solid <?php echo PAPAYA_BORDERCOLOR_DOWN; ?>;
  border-bottom-color: <?php echo PAPAYA_BORDERCOLOR_UP; ?>;
  border-right-color: <?php echo PAPAYA_BORDERCOLOR_UP; ?>;
  margin-bottom: 10px;
  width: 100%;
}

#workarea div.panel h2.panelHeader {
  border: 1px solid <?php echo PAPAYA_BORDERCOLOR_UP; ?>;
  border-bottom-color: <?php echo PAPAYA_BORDERCOLOR_DOWN; ?>;
  border-right-color: <?php echo PAPAYA_BORDERCOLOR_DOWN; ?>;
  background-color: <?php echo PAPAYA_BGCOLOR_TITLE; ?>;
  background-image: url(pics/<?php echo PAPAYA_THEME_PICS; ?>/background-title.png);
  background-position: left -50px;
  margin: 0;
  padding: 0 4px;
  font-size: 12px;
  color: <?php echo PAPAYA_FGCOLOR_TITLE; ?>;
  line-height: 18px;
  height: 20px;
}

#workarea form.dialogChanged div.panel h2.panelHeader {
  background-image: url(pics/<?php echo PAPAYA_THEME_PICS; ?>/background-title.png);
  background-position: left -120px;
  margin: 0;
  padding: 0 4px;
  font-size: 12px;
  color: <?php echo PAPAYA_FGCOLOR_TITLE; ?>;
  line-height: 18px;
  height: 20px;
}
#workarea .panelIcon {
  float: left;
  vertical-align: bottom;
  padding-top: 1px;
  padding-right: 4px;
  height: 20px;
}

#workarea .panelInfoButton {
  float: right;
  vertical-align: bottom;
  padding-top: 1px;
  height: 20px;
}

#workarea div.panel div.panelBody {
  border: 1px solid <?php echo PAPAYA_BORDERCOLOR_UP; ?>;
  border-bottom-color: <?php echo PAPAYA_BORDERCOLOR_DOWN; ?>;
  border-right-color: <?php echo PAPAYA_BORDERCOLOR_DOWN; ?>;
  border-top: none;
  width: 100%;
}
#workarea div.panel div.panelBody div.menuBar ul li.button a.caption {
  margin: 2px;
}

#workarea div.panel div.statusBar {
  border-top: 1px solid <?php echo PAPAYA_BORDERCOLOR_DOWN; ?>;
  font-size: 11px;
}

#workarea div.panel div.statusBarArtWork {
  border-bottom: 1px solid <?php echo PAPAYA_BORDERCOLOR_UP; ?>;
  padding: 2px;
  padding-left: 5px;
}

/* listview */
#workarea table.listview {
  width: 100%;
  border-spacing: 0;
  empty-cells: show;
  border-collapse: separate;
}
#workarea table.listview tr.columns th {
  border: 1px solid <?php echo PAPAYA_BORDERCOLOR_UP; ?>;
  border-bottom-color: <?php echo PAPAYA_BORDERCOLOR_DOWN; ?>;
  border-right-color: <?php echo PAPAYA_BORDERCOLOR_DOWN; ?>;
  background-color: <?php echo PAPAYA_BGCOLOR_WORKAREA; ?>;
  font-size: 10px;
  line-height: 16px;
  padding: 0 2px;
  font-weight: normal;
  text-align: left;
}
#workarea table.listview tr.columns th.first {
  border-left: none;
}
#workarea table.listview tr.columns th.last {
  border-right: none;
}
#workarea table.listview tr.columns th img.columnBullet {
  float: right;
  margin-right: 4px;
  margin-top: 4px;
}

#workarea table.listview tr,
#workarea table.listview tr.odd {
  background-color: <?php echo PAPAYA_BGCOLOR_LISTVIEW_ODD; ?>;
}
#workarea table.listview tr.even {
  background-color: <?php echo PAPAYA_BGCOLOR_LISTVIEW_EVEN; ?>;
}
#workarea table.listview tr:hover {
  background-color: <?php echo PAPAYA_BGCOLOR_HIGHLIGHT; ?>;
}
#workarea table.listview tr td {
  color: <?php echo PAPAYA_FGCOLOR_LISTVIEW; ?>;
  font-size: 11px;
  text-align: left;
}
#workarea table.listview tr a {
  color: <?php echo PAPAYA_FGCOLOR_LISTVIEW; ?>;
  text-decoration: none;
}
#workarea table.listview tr.selected, table.listview tr.selected td {
  background-color: <?php echo PAPAYA_BGCOLOR_HIGHLIGHT; ?>;
  color: <?php echo PAPAYA_FGCOLOR_HIGHLIGHT; ?>;
}
#workarea table.listview tr.selected a {
  color: <?php echo PAPAYA_FGCOLOR_HIGHLIGHT; ?>;
}
#workarea table.listview tr td {
  line-height: 16px;
  padding: 2px;
  vertical-align: top;
}
#workarea table.listview .nodeIcon img {
  width: 16px;
  height: 16px;
  vertical-align: bottom;
  margin-right: 2px;
}
#workarea table.listview .itemIcon img {
  width: 16px;
  height: 16px;
  vertical-align: bottom;
  margin-right: 2px;
}
#workarea table.listview .itemSubTitle {
  display: block;
  padding-left: 18px;
  font-style: italic;
  clear: both;
}

/* listview  iwth large icons (tiles) */
#workarea div.listviewBackground {
  background-color: <?php echo PAPAYA_BGCOLOR_LISTVIEW_ODD; ?>;
  padding: 10px;
}
#workarea div.listitemTile {
  float: left;
  width: 210px;
  margin: 5px;
}
#workarea div.listitemTile div.tile {
  height: 70px;
  padding: 5px;
  border: 1px solid <?php echo PAPAYA_BGCOLOR_LISTVIEW_EVEN; ?>;
  background-color: <?php echo PAPAYA_BGCOLOR_LISTVIEW_ODD; ?>;
}
#workarea div.listitemTile div.icon {
  float: left;
  width: 48px;
  height: 48px;
  margin-right: 10px;
  text-align: center;
  line-height: 48px;
}
#workarea div.listitemTile div.icon img.glyph {
  display: inline;
}
#workarea div.listitemTile div.subitems {
  float: right;
  text-align: right;
}
#workarea div.listitemTile .data {
  padding-top: 10px;
  color: <?php echo PAPAYA_FGCOLOR_LISTVIEW; ?>;
  font-size: 12px;
  font-weight: bold;
  overflow: hidden;
}
#workarea div.listitemTile .data a {
  color: <?php echo PAPAYA_FGCOLOR_LISTVIEW; ?>;
  font-size: 12px;
  text-decoration: none;
  font-weight: bold;
}
#workarea div.listitemTile .data .description {
  display: block;
  font-size: 0.8em;
  font-weight: normal;
}

#workarea div.listitemTile div.tile:hover,
#workarea div.listitemTile div.selected {
  background-color: <?php echo PAPAYA_BGCOLOR_HIGHLIGHT; ?>;
}
#workarea div.listitemTile:hover .data,
#workarea div.listitemTile:hover .data a
#workarea div.listitemTile div.selected .data
#workarea div.listitemTile div.selected .data a {
  color: <?php echo PAPAYA_FGCOLOR_HIGHLIGHT; ?>;
}

#workarea div.topToolbar {
  clear: both;
}

#workarea div.bottomToolbar {
  border-top: 1px solid <?php echo PAPAYA_BORDERCOLOR_UP; ?>;
  border-bottom: none;
  clear: both;
}

#workarea .listviewMenu {
  border-top: 1px solid <?php echo PAPAYA_BORDERCOLOR_DOWN; ?>;
  clear: both;
}
#workarea .listviewMenu .menuBar {
  border-bottom: none;
}

/* Listview with thumbnails */
#workarea div.listitemThumbnail {
  width: 128px;
  font-size: 10px;
  text-align: center;
  color: <?php echo PAPAYA_BGCOLOR_LISTVIEW_ODD; ?>;
  text-decoration: none;
  line-height: 124px;
  height: 124px;
  vertical-align: middle;
  float: left;
  margin: 4px;
}
#workarea div.listitemThumbnail div.thumbnail {
  line-height: 100px;
  height: 124px;
  padding: 4px;
  vertical-align: middle;
  text-decoration: none;
  border: 1px solid <?php echo PAPAYA_BGCOLOR_LISTVIEW_EVEN; ?>;
  background-color: <?php echo PAPAYA_BGCOLOR_LISTVIEW_ODD; ?>;
}
#workarea div.listitemThumbnail div.thumbnail div.image {
  vertical-align: middle;
  text-decoration: none;
}
#workarea div.listitemThumbnail div.thumbnail img {
  display: inline;
  vertical-align: middle;
  margin: auto;
}
#workarea div.listitemThumbnail div.data a {
  display: block;
  color: <?php echo PAPAYA_FGCOLOR_LISTVIEW; ?>;
  line-height: 14px;
  text-decoration: none;
  overflow: hidden;
}
#workarea div.listitemThumbnail div.thumbnail:hover,
#workarea div.listitemThumbnail div.selected {
  color: <?php echo PAPAYA_FGCOLOR_HIGHLIGHT; ?>;
  background-color: <?php echo PAPAYA_BGCOLOR_HIGHLIGHT; ?>;
}
#workarea div.listitemThumbnail div.thumbnail:hover div.data a,
#workarea div.listitemThumbnail div.selected div.data a {
  color: <?php echo PAPAYA_FGCOLOR_HIGHLIGHT; ?>;
  background-color: <?php echo PAPAYA_BGCOLOR_HIGHLIGHT; ?>;
}

/* sheet */
#workarea .sheetBackGround {
  border: 1px solid <?php echo PAPAYA_BORDERCOLOR_SHEET_SHADOW; ?>;
  border-bottom-color: <?php echo PAPAYA_BORDERCOLOR_UP; ?>;
  border-right-color: <?php echo PAPAYA_BORDERCOLOR_UP; ?>;
  background-color: <?php echo PAPAYA_BORDERCOLOR_SHEET_SPACE; ?>;
  padding: 10px 6px 6px 10px;
  margin-bottom: 10px;
}
#workarea .sheetShadow {
  border-right: 4px solid  <?php echo PAPAYA_BORDERCOLOR_SHEET_SHADOW; ?>;
  border-bottom: 4px solid  <?php echo PAPAYA_BORDERCOLOR_SHEET_SHADOW; ?>;
  margin-left: auto;
  margin-right: auto;
}
#workarea .sheet {
  background: <?php echo PAPAYA_BGCOLOR_SHEET; ?> url(pics/back_blossom.jpg) no-repeat right top;
  border: 1px solid <?php echo PAPAYA_BORDERCOLOR_SHEET; ?>;
  font-size: 12px;
}
#workarea .sheet .teletype {
  font-family: "DejaVu Sans Mono", "Bitstream Vera Sans Mono", "Lucida Console", monospace;
  padding: 10px;
  font-size: 14px;
}
#workarea .sheet a {
  color: <?php echo PAPAYA_FGCOLOR_LINK; ?>;
}
#workarea .sheet .header {
  padding: 10px;
  border-bottom: 1px dotted  <?php echo PAPAYA_BORDERCOLOR_SHEET_SHADOW; ?>;
}
#workarea .sheet .header .headertitle {
  font-weight: bold;
  font-size: 14px;
  padding: 1px 0px;
}
#workarea .sheet .header .headersubtitle {
  padding: 1px 0px;
}
#workarea .sheet .header .infos {
  padding-top: 5px;
  color:  <?php echo PAPAYA_BORDERCOLOR_SHEET_SHADOW; ?>;
}
#workarea .sheet li {
  list-style-position: outside;
}

/* dialogs */
#workarea form {
  margin: 0;
  padding: 0;
  width: 100%;
}
#workarea table.dialog {
  margin: 0;
  padding: 0;
  width: 100%;
  border-spacing: 0;
}
#workarea table.dialog th.subtitle {
  background-color: <?php echo PAPAYA_BGCOLOR_SUBTITLE; ?>;
  background-image: url(pics/<?php echo PAPAYA_THEME_PICS; ?>/background-title.png);
  background-position: left -80px;
  background-repeat: repeat-x;
  color: <?php echo PAPAYA_FGCOLOR_SUBTITLE; ?>;
  font-size: 12px;
  padding: 2px 5px;
}
#workarea table.dialog tr.odd {
  background-color: <?php echo PAPAYA_BGCOLOR_DIALOG_ODD; ?>;
}
#workarea table.dialog tr.even {
  background-color: <?php echo PAPAYA_BGCOLOR_DIALOG_EVEN; ?>;
}
#workarea table.dialog td.caption {
  font-size: 12px;
  width: 150px;
  line-height: 20px;
  padding: 1px;
  text-align: left;
}
#workarea table.dialogXSmall td.caption {
  width: 40px;
  min-width: 40px;
}
#workarea table.dialogSmall td.caption {
  width: 50px;
  min-width: 50px;
}
#workarea table.dialogXLarge td.caption {
  width: 200px;
  min-width: 200px;
}
#workarea table.dialog td.infos {
  font-size: 12px;
  width: 40px;
  min-width: 40px;
  text-align: right;
  padding-right: 4px;
  padding-top: 2px;
}
#workarea table.dialog td.element  {
  min-width: 50px;
  font-size: 12px;
  line-height: 20px;
}
#workarea table.dialog td.element a {
  color: <?php echo PAPAYA_FGCOLOR_WORKAREA; ?>;
  text-decoration: none;
}
#workarea table.dialogGrid {
  width: 100%;
  padding: 5px;
  font-size: 10px;
}

/*listview in dialog */
#workarea table.dialog div.panel {
  margin: 0;
}
#workarea table.dialog table.listview tr.odd {
  background-color: <?php echo PAPAYA_BGCOLOR_LISTVIEW_ODD; ?>;
}
#workarea table.dialog table.listview tr.even {
  background-color: <?php echo PAPAYA_BGCOLOR_LISTVIEW_EVEN; ?>;
}

/* grid view */
#workarea table.grid td {
  font-size: 11px;
}
#workarea table.grid th {
  font-size: 25px;
}
#workarea table.grid a {
  color:  <?php echo PAPAYA_BGCOLOR_HIGHLIGHT; ?>;
  text-decoration: none;
}
#workarea table.grid tr.odd {
  background-color: <?php echo PAPAYA_BGCOLOR_LISTVIEW_ODD; ?>;
}
#workarea table.grid tr.top td {
  border-top: 1px solid <?php echo PAPAYA_BGCOLOR_WORKAREA; ?>;
}
#workarea table.grid tr.even {
  background-color: <?php echo PAPAYA_BGCOLOR_LISTVIEW_EVEN; ?>;
}
#workarea table.grid tr th {
  border-top: 1px solid <?php echo PAPAYA_BGCOLOR_WORKAREA; ?>;
  border-right: 1px solid <?php echo PAPAYA_BGCOLOR_WORKAREA; ?>;
}
#workarea table.grid tr th.odd {
  background-color: <?php echo PAPAYA_BGCOLOR_LISTVIEW_ODD; ?>;
}
#workarea table.grid tr th.even {
  background-color: <?php echo PAPAYA_BGCOLOR_LISTVIEW_EVEN; ?>;
}

/* iframes */
iframe {
  border: none;
  background-color: <?php echo PAPAYA_BGCOLOR_WINDOW; ?>;
}

/* icon panel */
#workarea .iconPanel {
  border: 1px solid <?php echo PAPAYA_BORDERCOLOR_ICONPANEL_DOWN; ?>;
  border-bottom-color: <?php echo PAPAYA_BORDERCOLOR_UP; ?>;
  border-right-color: <?php echo PAPAYA_BORDERCOLOR_UP; ?>;
  background-color: <?php echo PAPAYA_BGCOLOR_ICONPANEL; ?>;
  padding: 0;
  margin-bottom: 10px;
}
#workarea .iconPanel ul {
  list-style-type: none;
  margin: 0;
  padding: 0;
}
#workarea .iconPanel ul li {
  text-align: center;
  margin: 10px 4px;
}
#workarea .iconPanel ul li a {
  font-size: 12px;
  text-decoration: none;
  color: <?php echo PAPAYA_FGCOLOR_ICONPANEL; ?>;
  display: block;
}
#workarea .iconPanel .iconPanelTitle {
  border: 1px solid <?php echo PAPAYA_BORDERCOLOR_UP; ?>;
  border-bottom-color: <?php echo PAPAYA_BORDERCOLOR_ICONPANEL_DOWN; ?>;
  border-right-color: <?php echo PAPAYA_BORDERCOLOR_ICONPANEL_DOWN; ?>;
  background-color: <?php echo PAPAYA_BGCOLOR_WORKAREA; ?>;
}
#workarea .iconPanel .iconPanelTitle a {
  color: <?php echo PAPAYA_FGCOLOR_WORKAREA; ?>;
  font-size: 12px;
  text-decoration: none;
  display: block;
  padding: 1px 2px 2px 4px;
  font-size: 12px;
  font-weight: bold;
}

#workarea .buttonBar {
  border: 1px solid <?php echo PAPAYA_BORDERCOLOR_DOWN; ?>;
  border-bottom-color: <?php echo PAPAYA_BORDERCOLOR_UP; ?>;
  border-right-color: <?php echo PAPAYA_BORDERCOLOR_UP; ?>;
  margin-bottom: 10px;
  vertical-align: middle;
}
#workarea .buttonBarArtWork {
  border: 1px solid <?php echo PAPAYA_BORDERCOLOR_UP; ?>;
  border-bottom-color: <?php echo PAPAYA_BORDERCOLOR_DOWN; ?>;
  border-right-color: <?php echo PAPAYA_BORDERCOLOR_DOWN; ?>;
  text-align: center;
}
#workarea .buttonBar ul {
  list-style-type: none;
  margin: 0;
  padding: 0;
}
#workarea .buttonBar ul li {
  list-style-type: none;
  display: inline;
  line-height: 16px;
  font-size: 12px;
  vertical-align: middle;
}
#workarea .buttonBar ul li a {
  text-decoration: none;
  color: <?php echo PAPAYA_FGCOLOR_LISTVIEW; ?>;
}
#workarea .buttonBar ul.leftButtons {
  float: left;
}
#workarea .buttonBar ul.rightButtons {
  float: right;
}
#workarea .buttonBar ul.centerButtons {
  display: inline;
}

/******************************************************************************
   Form Controls
******************************************************************************/
.dialogButtonsBottom {
  border-top: 1px solid <?php echo PAPAYA_BORDERCOLOR_DOWN; ?>;
  text-align: right;
  margin-top: 2px;
  clear: both;
}
.dialogButtonsBottom .dialogButtonsArtWork {
  border-top: 1px solid <?php echo PAPAYA_BORDERCOLOR_UP; ?>;
  padding: 2px;
}
.dialogButtonsTop {
  border-bottom: 1px solid <?php echo PAPAYA_BORDERCOLOR_UP; ?>;
  text-align: right;
  margin-bottom: 2px;
}
.dialogButtonsTop .dialogButtonsArtWork {
  border-bottom: 1px solid <?php echo PAPAYA_BORDERCOLOR_DOWN; ?>;
  padding: 2px;
}
button {
  background: transparent;
  border: 1px solid <?php echo PAPAYA_BORDERCOLOR_UP; ?>;
  border-bottom-color: <?php echo PAPAYA_BORDERCOLOR_DOWN; ?>;
  border-right-color: <?php echo PAPAYA_BORDERCOLOR_DOWN; ?>;
  cursor: pointer;
  margin-left: 2px;
}
button.dialogPopupButton {
  border: none;
  background: transparent;
}
button.dialogImageButton {
  border: none;
}
button.dialogButton img {
  margin: 0;
  margin-right: 4px;
  float: left;
}

img.dialogImage, div.dialogImage {
  float: left;
  padding: 10px;
}
.dialogText {
  padding: 10px;
  padding-top: 20px;
  font-size: 13px;
  font-weight: bold;
  margin-left: 60px;
}


.dialogScale {
  width: 100%;
}

input.dialogInput, input.dialogPassword,
input.dialogImage, input.dialogFixedImage, input.dialogMediaFile,
select.dialogSelect, input.dialogPageId, input.dialogGeoPos,
input.dialogInputDate, input.dialogInputDateTime, input.dialogInputColor,
textarea.dialogTextarea, textarea.dialogSimpleRichtext, textarea.dialogRichtext {
  border: 1px solid <?php echo PAPAYA_BORDERCOLOR_DOWN; ?>;
  background-color: <?php echo PAPAYA_BGCOLOR_INPUT; ?>;
  color: <?php echo PAPAYA_FGCOLOR_INPUT; ?>;
}
input.dialogInput:focus, input.dialogPassword:focus,
input.dialogImage:focus, input.dialogFixedImage:focus, input.dialogMediaFile:focus,
select.dialogSelect:focus, input.dialogPageId:focus, input.dialogGeoPos:focus,
input.dialogInputDate:focus, input.dialogInputDateTime:focus, input.dialogInputColor:focus,
textarea.dialogTextarea:focus,
textarea.dialogSimpleRichtext:focus, textarea.dialogRichtext:focus {
  border: 1px solid <?php echo PAPAYA_BORDERCOLOR_FOCUS; ?>;
  background-color: <?php echo PAPAYA_BGCOLOR_FOCUS; ?>;
  color: <?php echo PAPAYA_FGCOLOR_FOCUS; ?>;
}
input.dialogDisabled, input.dialogDisabled:focus,
select.dialogDisabled, select.dialogDisabled:focus {
  border: 1px solid <?php echo PAPAYA_BORDERCOLOR_DOWN; ?>;
  background-color: <?php echo PAPAYA_BGCOLOR_DIALOG_EVEN; ?>;
  color: <?php echo PAPAYA_FGCOLOR_DIALOG; ?>;
}
span.dialogCheckbox input {
  border: 1px solid <?php echo PAPAYA_BORDERCOLOR_DOWN; ?>;
  background-color: <?php echo PAPAYA_BGCOLOR_INPUT; ?>;
  color: <?php echo PAPAYA_FGCOLOR_INPUT; ?>;
}
span.dialogCheckbox input:focus {
  border: 1px solid <?php echo PAPAYA_BORDERCOLOR_FOCUS; ?>;
  background-color: <?php echo PAPAYA_BGCOLOR_FOCUS; ?>;
  color: <?php echo PAPAYA_FGCOLOR_FOCUS; ?>;
}

select.dialogSelect optgroup {
  background-color: <?php echo PAPAYA_BGCOLOR_SUBTITLE; ?>;
  background-image: url(pics/<?php echo PAPAYA_THEME_PICS; ?>/background-title.png);
  background-position: left -80px;
  color: <?php echo PAPAYA_FGCOLOR_SUBTITLE; ?>;
  font-style: normal;
  line-height: 16px;
  text-align: center;
}
select.dialogSelect option {
  background-color: <?php echo PAPAYA_BGCOLOR_LISTVIEW_ODD; ?>;
  color: <?php echo PAPAYA_FGCOLOR_LISTVIEW; ?>;
  line-height: 16px;
  text-align: left;
}
select.dialogSelect option.even {
  background-color: <?php echo PAPAYA_BGCOLOR_LISTVIEW_EVEN; ?>;
  color: <?php echo PAPAYA_FGCOLOR_LISTVIEW; ?>;
}

div.dialogMessage {
  font-size: 13px;
  font-weight: bold;
}
div.dialogMessage .dialogMessageImage {
  width: 48px;
  height: 48px;
  float: left;
  margin: 10px;
}
div.dialogMessage .message {
  padding: 20px;
}

input.dialogSearch {
  background-image: url(pics/search.png);
  background-position: left;
  background-repeat: no-repeat;
}

span.dialogRadio {
  white-space: nowrap;
}
span.dialogCheckBox {
  white-space: nowrap;
}
span.dialogRadio label {
  cursor: pointer;
}
table.dialog td.error input,
table.dialog td.error textarea,
table.dialog td.error select {
  background-color: #FDE3E3;
  border: 1px solid <?php echo PAPAYA_BORDERCOLOR_DOWN; ?>;
}

/* login dialog */
#workarea #loginDialog {
  margin: 50px auto 50px auto;
  width: 400px;
  font-size: 12px;
}
#workarea #loginDialog div.fields {
  width: 300px;
  float: left;
  margin-bottom: 1em;
  margin-top: 1em;
}
#workarea #loginDialog label {
  display: block;
  float: left;
  width: 110px;
  padding: 2px 5px 2px 0;
}
#workarea #loginDialog .buttons {
  text-align: right;
  padding: 4px;
}
#workarea #loginDialog input {
  float: left;
  width: 180px;
  margin: 2px;
  background-color: <?php echo PAPAYA_BGCOLOR_INPUT; ?>;
  color: <?php echo PAPAYA_FGCOLOR_INPUT; ?>;
}
#workarea #loginDialog input:focus {
  border: 1px solid <?php echo PAPAYA_BORDERCOLOR_FOCUS; ?>;
  background-color: <?php echo PAPAYA_BGCOLOR_FOCUS; ?>;
  color: <?php echo PAPAYA_FGCOLOR_FOCUS; ?>;
}
#workarea #loginDialog .message {
  padding: 1em 1em 1em 0;
  font-weight: bold;
}
#workarea #loginDialog a {
  color: <?php echo PAPAYA_FGCOLOR_LINK; ?>;
}

/* javascript progress dialog */
#lightBox, .lightBox {
  position: absolute;
  top: 0px;
  left: 0px;
  width: 100%;
  height: 100%;
  background-color: #000;
  z-index: 500;
  -moz-opacity: 0.5;
  opacity: 0.5;
}
#lightBox {
  display: none;
}
#lightBoxDialog, .lightBoxFrame {
  position: absolute;
  z-index: 501;
}
#lightBoxDialog {
  top: 100px;
  width: 400px;
  background-color: <?php echo PAPAYA_BGCOLOR_WORKAREA; ?>;
  -moz-border-radius: 10px;
  border-radius: 10px;
  display: none;
}
#lightBoxDialog .lightBoxDialogTitle {
  font-size: 18px;
  margin: 0;
  padding: 0;
}
#lightBoxDialog .title {
  background-color: <?php echo PAPAYA_BGCOLOR_HEADER; ?>;
  background-image: url(pics/<?php echo PAPAYA_THEME_PICS; ?>/background-title.png);
  background-position: left top;
  color: <?php echo PAPAYA_FGCOLOR_HEADER; ?>;
  font-weight: bold;
  font-size: 18px;
  border-bottom: 1px solid <?php echo PAPAYA_BGCOLOR_WINDOW; ?>;
}
#lightBoxDialog .titleArtworkOverlay {
  background-image: url(pics/background-grid.png);
  background-position: right top;
  background-repeat: repeat;
  padding: 0;
  padding-left: 5px;
  padding-right: 10px;
  height: 37px;
  line-height: 37px;
}
#lightBoxDialog .footer {
  background-color: <?php echo PAPAYA_BGCOLOR_HEADER; ?>;
  background-image: url(pics/<?php echo PAPAYA_THEME_PICS; ?>/background-title.png);
  background-position: left top;
  border-top: 1px solid <?php echo PAPAYA_BGCOLOR_WINDOW; ?>;
}
#lightBoxDialog .footerArtworkOverlay {
  background-image: url(pics/background-grid.png);
  background-position: right top;
  background-repeat: repeat;
  padding: 0;
  height: 16px;
  line-height: 16px;
}
#lightBoxDialog .lightBoxButtons {
  text-align: right;
  padding: 5px 15px;
  height: 32px;
}

#lightBoxDialog .progressDialog {
  height: 149px;
}
#lightBoxDialog .progressDialogMessage {
  font-size: 12px;
  font-weight: bold;
  padding: 10px 15px;
  height: 40px;
}
#lightBoxDialog .progressBarBorder {
  border: 1px solid <?php echo PAPAYA_BORDERCOLOR_DOWN; ?>;
  border-bottom-color: <?php echo PAPAYA_BORDERCOLOR_UP; ?>;
  border-right-color: <?php echo PAPAYA_BORDERCOLOR_UP; ?>;
  padding: 4px;
  margin: 0 15px;
}
#lightBoxDialog .progressBar {
  background-color: <?php echo PAPAYA_BGCOLOR_HIGHLIGHT; ?>;
  background-image: url(pics/<?php echo PAPAYA_THEME_PICS; ?>/progress.gif);
  background-repeat: repeat-x;
  height: 20px;
  width: 20%;
}

/* installer - step by step texts */
div.installer {
  padding: 10px;
  font-size: 12px;
}
div.installer h1 {
  font-size: 18px;
  margin: 0;
  padding: 0;
  padding-bottom: 0.8em;
}
div.installer a.nextLink {
  display: block;
  text-align: center;
  font-weight: bold;
  margin: 1em 0 0 0;
  font-size: 18px;
  text-decoration: none;
}
div.installer a.rightLink {
  display: block;
  text-align: right;
  margin: 1em 0;
  text-decoration: none;
}
div.installer a.nextLink:hover {
  text-decoration: underline;
}
div.installer hr {
  border: none;
  border-top: 1px dotted silver;
  margin: 1em 0;
}

div.installer table th {
  font-size: 14px;
  font-weight: bold;
  text-align: left;
  vertical-align: middle;
  padding: 2px 10px 2px 2px;
}

div.installer table td {
  font-size: 12px;
  padding: 2px;
}
div.installer table td.bullet {
  padding: 0 4px 0 0;
}

/* calendar */
table.monthCalendar {
  width: 100%;
  font-size: 12px;
}
table.monthCalendar thead th {
  text-align: center;
  padding: 2px;
  border: 1px solid <?php echo PAPAYA_BORDERCOLOR_UP; ?>;
  border-bottom-color: <?php echo PAPAYA_BORDERCOLOR_DOWN; ?>;
  border-right-color: <?php echo PAPAYA_BORDERCOLOR_DOWN; ?>;
}
table.monthCalendar tbody th {
  text-align: right;
  padding: 2px;
  border: 1px solid <?php echo PAPAYA_BORDERCOLOR_UP; ?>;
  border-bottom-color: <?php echo PAPAYA_BORDERCOLOR_DOWN; ?>;
  border-right-color: <?php echo PAPAYA_BORDERCOLOR_DOWN; ?>;
}
table.monthCalendar tbody td {
  background-color: <?php echo PAPAYA_BGCOLOR_LISTVIEW_ODD; ?>;
  text-align: right;
  padding: 2px;
}
table.monthCalendar tbody td a {
  text-decoration: none;
  color: <?php echo PAPAYA_FGCOLOR_LISTVIEW; ?>;
}
table.monthCalendar tbody td.filled {
  background-color: <?php echo PAPAYA_BGCOLOR_LISTVIEW_EVEN; ?>;
}
table.monthCalendar tbody td.selected {
  background-color: <?php echo PAPAYA_BGCOLOR_HIGHLIGHT; ?>;
  color: <?php echo PAPAYA_FGCOLOR_HIGHLIGHT; ?>;
}
table.monthCalendar tbody td.selected a {
  color: <?php echo PAPAYA_FGCOLOR_HIGHLIGHT; ?>;
}

/* style selections - works only on page not in input/select/textarea */
::-moz-selection {
  background-color: <?php echo PAPAYA_BGCOLOR_HIGHLIGHT; ?>;
  color: <?php echo PAPAYA_FGCOLOR_HIGHLIGHT; ?>;
}
::selection {
  background-color: <?php echo PAPAYA_BGCOLOR_HIGHLIGHT; ?>;
  color: <?php echo PAPAYA_FGCOLOR_HIGHLIGHT; ?>;
}

/* messaging */
div.messageQuote {
  margin: 2px;
  padding-left: 10px;
  border-left: 2px solid <?php echo PAPAYA_BORDERCOLOR_SHEET_SPACE; ?>;
  color: <?php echo PAPAYA_BORDERCOLOR_SHEET_SPACE; ?>;
}

/******************************************************************************
   Form Controls - Refactoring
******************************************************************************/
form.dialog .buttons {
  text-align: right;
  clear: both;
}
form.dialog .buttonsBottom {
  border-top: 1px solid <?php echo PAPAYA_BORDERCOLOR_DOWN; ?>;
  margin-top: 2px;
}
form.dialog div.buttonsBottom .buttonsArtWork {
  border-top: 1px solid <?php echo PAPAYA_BORDERCOLOR_UP; ?>;
  padding: 2px;
}
form.dialog .buttonsTop {
  border-bottom: 1px solid <?php echo PAPAYA_BORDERCOLOR_UP; ?>;
  margin-bottom: 2px;
}
form.dialog .buttonsTop .buttonsArtWork {
  border-bottom: 1px solid <?php echo PAPAYA_BORDERCOLOR_DOWN; ?>;
  padding: 2px;
}
form.dialog .buttons .button {
  border: 1px solid <?php echo PAPAYA_BORDERCOLOR_UP; ?>;
  border-bottom-color: <?php echo PAPAYA_BORDERCOLOR_DOWN; ?>;
  border-right-color: <?php echo PAPAYA_BORDERCOLOR_DOWN; ?>;
  background-color: <?php echo PAPAYA_BGCOLOR_WORKAREA; ?>;
  cursor: pointer;
}
form.dialog .buttons .left {
  float: left;
}
form.dialog .buttons .right {
  float: right;
}
form.dialog .scaleable {
  width: 100%;
}
form.dialog .caption a {
  color: <?php echo PAPAYA_FGCOLOR_WORKAREA; ?>;
  text-decoration: none;
}
form.dialog .hint {
  font-size: 11px;
  padding: 1px;
}
form.dialog .hintText {
  font-size: 14px;
  padding: 2px;
  border: 1px dashed <?php echo PAPAYA_BORDERCOLOR_DOWN; ?>;
  background-color: <?php echo PAPAYA_BGCOLOR_INPUT; ?>;
  color: <?php echo PAPAYA_FGCOLOR_INPUT_DISABLED; ?>;
}
form.dialog a.hintSwitch {
  cursor: pointer;
}
form.dialog img.hintMarker  {
  margin: 3px;
  float: right;
}

div.dialogCheckboxes div.option,
div.dialogRadio div.option {
  display: inline-block;
}
/* popIn - lightbox with iframe */
.popIn {
  display: none;
}

/* context aware fields */
table.dialogField {
  width: 100%;
  border-collapse: collapse;
}
table.dialogField td {
  margin: 0;
  padding: 0;
  line-height: 1em;
}
table.dialogField .field {
  width: 100%;
}
table.dialogField .action span.information {
  padding: 0;
  margin: 0 1em;
}
table.dialogField .action input {
  margin-right: 0.2em;
}
table.dialogField .action input.filter {
  width: 8em;

}
table.dialogField .action button.icon {
  border: none;
  padding: 0;
  margin: 0 1em;
  width: 24px;
  cursor: pointer;
}
table.dialogField .action button.icon img {
  border: none;
  margin: auto;
  margin-top: 1px;
  padding: 0;
  width: 16px;
  height: 16px;
}
table.dialogField .information {
  font-size: 12px;
  line-height: 20px;
  padding: 4px 0;
  color: <?php echo PAPAYA_FGCOLOR_DIALOG; ?>;
  overflow: hidden;
  padding-top: 0;
}
table.dialogField .information .icon {
  float: left;
  height: 48px;
  line-height: 48px;
  margin: 0.2em;
  margin-right: 1em;
  text-align: center;
  width: 48px;
}
table.dialogField .information img {
  display: inline;
}
table.dialogField .information .title {
  font-weight: bold;
  padding: 0.2em;
}
table.dialogField .information .description {
  font-weight: normal;
  padding: 0.2em;
}

/* dialog control buttons (context buttons) */
div.dialogControlButtons {
  float: right;
}
div.dialogControlButtons img {
  margin: 0.2em;
  cursor: pointer;
}

/** old js field context elements */

div.dialogCounterLabel {
  font-size: 12px;
  line-height: 20px;
  padding: 0 3px;
}