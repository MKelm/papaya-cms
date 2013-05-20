<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>
<!--
  @papaya:modules actionbox_categteaser, actionbox_categteaserthumb, actionbox_tagteaser, actionbox_tagteaserthumb
-->

  <xsl:import href="./base/boxes.xsl"/>
  <xsl:import href="./modules/_base/box_categteaser.xsl"/>

  <xsl:param name="CATEG_TEASER_SHOW_TITLE" select="true()" />
  <xsl:param name="CATEG_TEASER_SHOW_TEASER" select="true()" />
  <xsl:param name="CATEG_TEASER_SHOW_THUMBNAIL" select="true()" />
  <xsl:param name="CATEG_TEASER_LINK_TITLE" select="true()" />
  <xsl:param name="CATEG_TEASER_LINK_TEASER" select="true()" />
  <xsl:param name="CATEG_TEASER_LINK_THUMBNAIL" select="true()" />
  <xsl:param name="CATEG_TEASER_TEASER_EXCERPT" select="true()" />
  <xsl:param name="CATEG_TEASER_TEASER_EXCERPT_LENGTH" select="'200'" />

  <xsl:template match="/*">
    <xsl:choose>
      <xsl:when test="name() = 'categteaserthumb'">
        <xsl:call-template name="module-box-subtopics">
          <xsl:with-param name="subtopics" select="subtopics" />
          <xsl:with-param name="thumbnails" select="subtopicthumbs"/>
        </xsl:call-template>
      </xsl:when>
      <xsl:when test="name() = 'subtopics'">
        <xsl:call-template name="module-box-subtopics">
          <xsl:with-param name="subtopics" select="." />
        </xsl:call-template>
      </xsl:when>
    </xsl:choose>
  </xsl:template>

</xsl:stylesheet>
