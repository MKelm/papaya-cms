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

<xsl:template match="poll">

  <xsl:choose>
    <xsl:when test="@showdialog = 'yes'">
      <xsl:call-template name="show-poll-dialog">
      </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
      <xsl:call-template name="show-poll-results">
      </xsl:call-template>
    </xsl:otherwise>
  </xsl:choose>

</xsl:template>

<xsl:template name="show-poll-dialog">
  <form class="communityOnePollBox" id="{@id}" action="{@action}" method="post">
    <h2><xsl:value-of select="@title" /></h2>

    <input type="hidden" name="{@fieldname}" value="{@id}" />
    <xsl:if test="count(answer) &gt; 0">
      <ul class="communityOnePollAnswersListBox">
        <xsl:for-each select="answer">
          <li>
            <input class="radio" type="radio" name="{@fieldname}" value="{@id}" id="answerbox_id_{@id}"/>
            <label for="answerbox_id_{@id}"><xsl:value-of select="text()" /></label>
          </li>
        </xsl:for-each>
      </ul>
    </xsl:if>
    <button type="submit">Vote</button>
  </form>
</xsl:template>

<xsl:template name="show-poll-results">

  <div class="communityOnePollBox">
    <h2><xsl:value-of select="@title" /></h2>

    <xsl:variable name="maxResult">
      <xsl:call-template name="max">
        <xsl:with-param name="values" select="answer/@result" />
      </xsl:call-template>
    </xsl:variable>

    <xsl:if test="count(answer) &gt; 0">
      <ul class="communityOnePollAnswersList">
        <xsl:for-each select="answer">
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
      <xsl:value-of select="voted/text()" />
    </p>
    <xsl:if test="link">
      <a href="{link/@href}"><xsl:value-of select="link/text()" /></a>
    </xsl:if>
  </div>
</xsl:template>

</xsl:stylesheet>