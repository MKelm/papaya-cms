papaya PDF - Command structure


article

Root tag with language locale and encoding.

<article lang="en-US" charset="UTF-8">


article -> layout

Configures the layout of the pdf. This tag has no parameters


article -> layout -> fonts

A list of font defintions for the pdf.


article -> layout -> fonts -> font

A single font definition for the file. At least the default style should be defined.

<font name="ninjapenguin" default="fonts/NINJP.php" bold="fonts/NINJP.php" italic="fonts/NINJP.php" bolditalic="fonts/NINJP.php" />

The font name "user-symbol" has a special meaning - it overwrites the default symbol font (used for list bullets)

<font name="user-symbol" default="fonts/OpenSymbol.php"/>


article -> layout -> templates

A list of background templates (external, static pdf pages).


article -> layout -> templates -> template

Imports the elements a template page into the generated pdf. You have to define a name, the static pdf file and the page number.

<template name="cover" file="template/en.pdf" page="1" />


article -> layout -> pages

A list of page layout definitions.


article -> layout -> pages -> page

Defines a page layout definition. You have to provide a unique name. The names cover, final and toc are used for the special pages. The default mode is "columns" which uses one or more connected frames and hast an automatic page break. The mode "elements" defines a set of separate frames with no connection to each other and no automatic page break.

The attribute "template" is optional and defines the used background (imported from external, static pdf file). The attribute "font" defines the default font family, size and style for this page layout.

<page name="cover" mode="elements" template="cover" font="helvetica 12">

<page name="default" template="vertical" font="helvetica 12">


article -> layout -> pages -> page -> column

Defines a column inside a page layout definition. Columns are connected frames. If the end of the first column is reached the content flow goes to the start of the second column. The end of the last column triggers an automatic page break.

The margins are relative to the sides of the page. If one value if provided it is for all sides. Two values for top/bottom and left/right. Three values are top, left/right and bottom. Four values are top, right, bottom, left. Other elements or column do not change how the values are calculated.

The "align" attribute defines the default alignment inside the column. Allowed values are left, right, center and justify.

<column margin="35 110 20 8.4" align="left" font="helvetica 12"/>


article -> layout -> pages -> page -> element

For "elements" pages you need to define a frame for each element. The "for" attribute defined the element id, "margin", "font" and "align" are same like in <column>.

<element for="title" margin="90 50 115 10" align="right" font="helvetica 14 bold italic"/>


article -> layout -> pages -> page -> header

A special frame for the page header. Works like a <elements> tag, but fixed to the header contents.


article -> layout -> pages -> page -> footer

A special frame for the page footer. Works like a <elements> tag, but fixed to the footer contents.

<footer margin="280 8.4 10 8.4" align="right" font="helvetica 7"/>


article -> layout -> tags

Defines a list of tag styles.


article -> layout -> tags ->tag

Defines a tag style. You can set the style for predefined tags like "h1" or own tags.

<tag name="h3" font="helvetica 10 bold" fgcolor="#000000"/>
<tag name="mytag" font="helvetica 20" fgcolor="#FF0000"/>


article -> cover

Content elements for the cover page.


article -> cover -> element

One content element for the cover page. The id attribute defined the identifer for the layout.

<element id="title">TITLE</element>


article -> final

Content elements for the cover page.


article -> final -> element

See article -> cover -> element


article -> header

Content for the cover page.


article -> header -> pdf-title

Inserts current section title. Possible attributes are "prefix" and "suffix".


article -> header -> pdf-position

Insert current page and page count. Possible attributes are "prefix", "suffix" and "separator".


article -> header -> pdf-page

Inserts the current page number.


article -> header -> pdf-pagecount

Inserts the page count.

article -> footer 

See article -> header


article -> toc

Including this tag will generate a table of contents inside the pdf. The data for the toc is taken from the bookmarks outline. The toc start with the content of the page followed by an list of the bookmarks including page numbers. If the attribute "line" (a color) is provided, a line goes from the end of the bookmark text to the page number.

  <toc title="Table Of Contents" line="#C0C0C0">
    <h1>Table of contents</h1>
  </toc>

  
article -> content

The main content of the document. It is seperated in sections and imports.


article -> content -> section

This are the content parts. You can define a page layout using the "page" attribute. The attributes "break-before" and "break-after" trigger page breaks if they contain teh value "yes". Sections can have automatic page breaks (depends on the size of the actual content.). The "title" attribute of the first section on a page is used for the <pdf-title /> tag in header/footer elements.

<section page="default" title="Chapter 1" break-before="yes">


article -> content -> import

Import a page from an external pdf document into the output document. The "page" and the "title" attributes work like for the <section> tag. The attribute "file" defines the external file, "page-no" the page numbers in the external file. You can import a single page or a several pages, using a comma separated list.

<import page="default" title="Chapter 2" file="files/de.pdf" page-no="1,2">


article -> content -> import -> bookmark

To define entries in the bookmark list and the table of contents you can use <bookmark> tags. The attributes "title" and "level" define the text and indent of the bookmark. The target of the bookmark is defined using "page-no" and "position". The "page-no" is the page number in the "page-no" attribute of the <import> tag. The vertical offset of the book can be changed using "position".

<bookmark title="IMPORTED 1" page-no="2" level="0" position="200" />

Tags inside <section>

p   paragraph
b   bold
i   italic
br  line break

Headers

h1 - h5
    title   create a bookmark with this text to this header
    margin-bottom   margin to next element

Tables

table
  border (0|1)
  border-color
  padding (default 0.5)

tr

th, td
  align
  width
  min-width

Lists

ul
    bullet-chars  list bullet string
    
ol

li

Images

img
  src    file source
  dpi    dpi (default 120, larger dpi means smaller image)
  
Bookmarks
  
bookmark
  title   bookmark title
  level   bookmark level (default 0)
  page-start   (yes|no) bookmark page start not current y position
 




