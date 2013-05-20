<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>

  <xsl:import href="../../../_functions/text-excerpt.xsl"/>

  <xsl:param name="CATEG_TEASER_SHOW_TITLE" select="true()" />
  <xsl:param name="CATEG_TEASER_SHOW_TEASER" select="true()" />
  <xsl:param name="CATEG_TEASER_SHOW_THUMBNAIL" select="true()" />
  <xsl:param name="CATEG_TEASER_LINK_TITLE" select="true()" />
  <xsl:param name="CATEG_TEASER_LINK_TEASER" select="true()" />
  <xsl:param name="CATEG_TEASER_LINK_THUMBNAIL" select="true()" />
  <xsl:param name="CATEG_TEASER_TEASER_EXCERPT" select="true()" />
  <xsl:param name="CATEG_TEASER_TEASER_EXCERPT_LENGTH" select="'200'" />
  
  <xsl:template name="module-box-subtopics">
    <xsl:param name="subtopics"/>
    <xsl:param name="thumbnails" select="false()"/>
    <div class="subtopics">
      <ul>
        <xsl:for-each select="$subtopics/subtopic">
          <xsl:variable name="subtopicNo" select="./@no" />
          <xsl:variable name="subtopicClass">
            <xsl:text>subtopic</xsl:text>
            <xsl:if test="position() = 1"> first</xsl:if>
            <xsl:if test="position() = last()"> last</xsl:if>          
          </xsl:variable>
          <xsl:choose>
            <xsl:when test="$thumbnails">
              <xsl:call-template name="subtopic-list-element">
                <xsl:with-param name="subtopic" select="." />
                <xsl:with-param name="thumbnail" select="$thumbnails/thumb[@topic = $subtopicNo]/node()" />
                <xsl:with-param name="class" select="$subtopicClass" />
              </xsl:call-template>
            </xsl:when>
            <xsl:otherwise>
              <xsl:call-template name="subtopic-list-element">
                <xsl:with-param name="subtopic" select="." />
                <xsl:with-param name="class" select="$subtopicClass" />
              </xsl:call-template>            
            </xsl:otherwise>
          </xsl:choose>
        </xsl:for-each>
      </ul>
    </div>
  </xsl:template>
  
  <xsl:template name="subtopic-list-element">
    <xsl:param name="subtopic"/>
    <xsl:param name="thumbnail" select="false()" />
    <xsl:param name="class" select="'subtopic'" />
    <li class="{$class}">
      <xsl:if test="$CATEG_TEASER_SHOW_TITLE">
        <h2 class="title">
          <xsl:choose>
            <xsl:when test="$CATEG_TEASER_LINK_TITLE">
              <a href="{$subtopic/@href}">
                <xsl:value-of select="$subtopic/title" />
              </a>
            </xsl:when>
            <xsl:otherwise>
              <xsl:value-of select="$subtopic/title" />
            </xsl:otherwise>
          </xsl:choose>
        </h2>
      </xsl:if>
      <xsl:if test="$CATEG_TEASER_SHOW_THUMBNAIL and $thumbnail">
        <div class="image">
          <xsl:choose>
            <xsl:when test="$CATEG_TEASER_LINK_THUMBNAIL">
              <a href="{$subtopic/@href}">
                <img src="{$thumbnail/@src}" alt="{$thumbnail/@alt}" />
              </a>
            </xsl:when>
            <xsl:otherwise>
              <img src="{$thumbnail/@src}" alt="{$thumbnail/@alt}" />
            </xsl:otherwise>
          </xsl:choose>
        </div>
      </xsl:if>
      <xsl:if test="$CATEG_TEASER_SHOW_TEASER">
        <p class="text">
          <xsl:choose>
            <xsl:when test="$CATEG_TEASER_TEASER_EXCERPT">
              <xsl:call-template name="text-excerpt">
                <xsl:with-param name="text">
                  <xsl:value-of select="text" />
                </xsl:with-param>
                <xsl:with-param name="length" select="$CATEG_TEASER_TEASER_EXCERPT_LENGTH" />
              </xsl:call-template>
            </xsl:when>
            <xsl:otherwise>
              <xsl:value-of select="text" />
            </xsl:otherwise>
          </xsl:choose>
          <xsl:text> </xsl:text>
          <xsl:if test="$CATEG_TEASER_LINK_TEASER">
            <a href="{$subtopic/@href}">
              <xsl:call-template name="language-text">
                <xsl:with-param name="text" select="'MORE'" />
              </xsl:call-template>
              <xsl:text>&#8230;</xsl:text>
            </a>
          </xsl:if>
        </p>
      </xsl:if>
    </li>
  </xsl:template>
  
</xsl:stylesheet>
