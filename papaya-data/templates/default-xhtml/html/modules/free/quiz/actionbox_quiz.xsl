<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>

<xsl:import href="../../../base/dialogs.xsl" />

<xsl:param name="PAGE_LANGUAGE"></xsl:param>
<xsl:param name="LANGUAGE_MODULE_CURRENT" select="document(concat($PAGE_LANGUAGE, '.xml'))" />
<xsl:param name="LANGUAGE_MODULE_FALLBACK" select="document('en-US.xml')"/>

<xsl:template match="quiz">
  <form class="boxQuizQuestion" action="{@action}" mathod="post">
    <h2>
      <xsl:value-of select="question/@title" />
    </h2>
    <div class="questionExplanation">
      <xsl:apply-templates select="question/node()" />
    </div>
    <input type="hidden"
           name="{question/@fieldname}"
           value="{question/@id}" />

    <xsl:for-each select="answer">
      <input id="quiz_answer_{@id}" type="radio" name="{@fieldname}" value="{@id}" />
      <label for="quiz_answer_{@id}">
        <xsl:value-of select="text()" />
      </label>
      <div class="answerExplanation">
        <xsl:apply-templates select="explanation/node()" />
      </div>
    </xsl:for-each>

    <xsl:if test="reply">
      <div class="givenAnswerResponse">
        <h3>
          <xsl:call-template name="language-text">
            <xsl:with-param name="text">GIVEN_ANSWER_RESPONSE</xsl:with-param>
          </xsl:call-template>
        </h3>
        <xsl:apply-templates select="reply/node()" />
      </div>
    </xsl:if>

    <xsl:call-template name="dialog-submit-button">
      <xsl:with-param name="buttonValue">
        <xsl:call-template name="language-text">
          <xsl:with-param name="text">NEXT</xsl:with-param>
        </xsl:call-template>
      </xsl:with-param>
    </xsl:call-template>
  </form>
</xsl:template>

</xsl:stylesheet>