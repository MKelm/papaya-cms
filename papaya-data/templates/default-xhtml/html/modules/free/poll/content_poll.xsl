<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>

<xsl:import href="../../../../_functions/min-max.xsl"/>

<xsl:param name="PAGE_LANGUAGE"></xsl:param>
<xsl:param name="LANGUAGE_MODULE_CURRENT" select="document(concat($PAGE_LANGUAGE, '.xml'))" />
<xsl:param name="LANGUAGE_MODULE_FALLBACK" select="document('en-US.xml')"/>

<xsl:template name="page-styles">
  <xsl:call-template name="link-style">
    <xsl:with-param name="file">page_poll.css</xsl:with-param>
  </xsl:call-template>
</xsl:template>

<xsl:template name="content-area">
  <xsl:param name="pageContent" select="content/topic"/>
  <xsl:choose>
    <xsl:when test="$pageContent/@module = 'content_poll'">
      <xsl:call-template name="module-content-poll">
        <xsl:with-param name="pageContent" select="$pageContent"/>
      </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
      <xsl:call-template name="module-content-default">
        <xsl:with-param name="pageContent" select="$pageContent"/>
      </xsl:call-template>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template name="module-content-poll">
  <xsl:param name="pageContent"/>
  <xsl:call-template name="module-content-topic">
    <xsl:with-param name="pageContent" select="$pageContent" />
  </xsl:call-template>

  <xsl:call-template name="poll-messages">
    <xsl:with-param name="pageContent" select="$pageContent" />
  </xsl:call-template>

  <xsl:call-template name="show-one-poll" >
    <xsl:with-param name="pageContent" select="$pageContent" />
  </xsl:call-template>

  <xsl:call-template name="show-category">
    <xsl:with-param name="pageContent" select="$pageContent" />
  </xsl:call-template>

</xsl:template>

<xsl:template name="poll-messages">
  <xsl:param name="pageContent" select="/page/content/topic" />

  <xsl:if test="$pageContent/error">
    <div class="error">
      <xsl:apply-templates select="$pageContent/error/node()" />
    </div>
  </xsl:if>
  <xsl:if test="$pageContent/message">
    <div class="message">
      <xsl:apply-templates select="$pageContent/message/node()" />
    </div>
  </xsl:if>
</xsl:template>

<xsl:template name="show-category">
  <xsl:param name="pageContent" select="/page/content/topic" />

  <xsl:if test="count($pageContent/categ/poll) &gt; 0">
    <h2 class="pollCategory">
      <xsl:call-template name="language-text">
        <xsl:with-param name="text">POLL_LIST_TITLE</xsl:with-param>
      </xsl:call-template>
    </h2>
    <ul>
      <xsl:for-each select="$pageContent/categ/poll">
        <li>
          <a href="{@href}">
            <xsl:value-of select="@status" />
            <xsl:text>: </xsl:text>
            <xsl:value-of select="@title" />
          </a>
        </li>
      </xsl:for-each>
    </ul>
  </xsl:if>
</xsl:template>

<xsl:template name="show-one-poll">
  <xsl:param name="pageContent" select="/page/content/topic" />

  <xsl:if test="$pageContent/poll">
    <xsl:choose>
      <xsl:when test="$pageContent/poll/@showdialog = 'yes'">
        <xsl:call-template name="show-poll-dialog">
          <xsl:with-param name="pageContent" select="$pageContent" />
        </xsl:call-template>
      </xsl:when>
      <xsl:otherwise>
        <xsl:call-template name="show-poll-results">
          <xsl:with-param name="pageContent" select="$pageContent" />
        </xsl:call-template>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:if>
</xsl:template>

<xsl:template name="show-poll-dialog">
  <xsl:param name="pageContent" select="/page/content/topic" />

  <form class="communityOnePoll" id="{$pageContent/poll/@id}" action="{$pageContent/poll/@action}" method="post">
    <h2><xsl:value-of select="$pageContent/poll/@title" /></h2>

    <input type="hidden" name="{$pageContent/poll/@fieldname}" value="{$pageContent/poll/@id}" />
    <xsl:if test="$pageContent/poll/answer">
      <ul>
		    <xsl:for-each select="$pageContent/poll/answer">
		      <li>
			      <xsl:call-template name="dialog-element-radio">
			        <xsl:with-param name="elementId">
			          <xsl:text>answerid_</xsl:text>
			          <xsl:value-of select="@id" />
			        </xsl:with-param>
			        <xsl:with-param name="element" select="." />
			        <xsl:with-param name="labelText" select="text()" />
			        <xsl:with-param name="elementName" select="@fieldname" />
			        <xsl:with-param name="elementValue" select="@id" />
			      </xsl:call-template>
			    </li>
		    </xsl:for-each>
		  </ul>
	  </xsl:if>
    <xsl:call-template name="dialog-submit-button">
      <xsl:with-param name="buttonValue">Vote</xsl:with-param>
    </xsl:call-template>
  </form>
</xsl:template>

<xsl:template name="show-poll-results">
  <xsl:param name="pageContent" select="/page/content/topic" />

  <div class="communityOnePoll">
    <h2><xsl:value-of select="$pageContent/poll/@title" /></h2>

    <xsl:variable name="maxResult">
      <xsl:call-template name="max">
        <xsl:with-param name="values" select="$pageContent/poll/answer/@result" />
      </xsl:call-template>
    </xsl:variable>

    <xsl:if test="count($pageContent/poll/answer) &gt; 0">
      <ul class="communityOnePollAnswersList">
        <xsl:for-each select="$pageContent/poll/answer">
          <li>
            <xsl:value-of select="text()" />
            <span>
              <xsl:value-of select="@result" />
            </span>
            <div class="bar">
              <div class="barResult" style="width: {@result * 100 div $maxResult}%;"></div>
            </div>
          </li>
        </xsl:for-each>
      </ul>
    </xsl:if>
    <p class="resultCount">
      <span>
        <xsl:call-template name="language-text">
          <xsl:with-param name="text">ONE_POLL_RESULT_COUNT</xsl:with-param>
        </xsl:call-template>
      </span>
      <xsl:text>: </xsl:text>
      <xsl:value-of select="$pageContent/poll/voted/text()" />
    </p>
  </div>
</xsl:template>

</xsl:stylesheet>
