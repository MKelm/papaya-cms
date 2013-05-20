<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>
<!--
  @papaya:modules actionbox_login, actionbox_login_handle
-->

<xsl:import href="./base/boxes.xsl" />
  <xsl:template match="youtubebox">
    <xsl:param name="boxContent" select="." />
    <xsl:call-template name="youtube-player">
      <xsl:with-param name="boxContent" select="$boxContent" />
    </xsl:call-template>
  </xsl:template>
  
  <xsl:template name="youtube-player">
    <xsl:param name="boxContent" />
    <xsl:variable name="url" select="$boxContent/player/@url"/>
    <xsl:variable name="videoId" select="$boxContent/player/@videoId" />
    <xsl:variable name="autoplay" select="$boxContent/player/@autoplay" />
    <xsl:variable name="rel" select="$boxContent/player/@rel"/>
    <xsl:variable name="info" select="$boxContent/player/@info"/>
    <xsl:variable name="controls" select="$boxContent/player/@controls"/>
    <h2>
      <xsl:value-of select="$boxContent/title" />
    </h2>
    <iframe 
      width="{$boxContent/player/@width}"
      height="{$boxContent/player/@height}"
      src="{$url}/embed/{$videoId}?autoplay={$autoplay}&amp;rel={$rel}&amp;showinfo={$info}&amp;controls={$controls}"
      frameborder="0"
      >
      <xsl:text> </xsl:text>
    </iframe>
    <div class="videoText">
      <xsl:text> </xsl:text>
      <xsl:apply-templates select="$boxContent/text/node()" />
    </div>
  </xsl:template>

</xsl:stylesheet>
