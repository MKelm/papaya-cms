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
    <xsl:with-param name="file">page_quiz.css</xsl:with-param>
  </xsl:call-template>
</xsl:template>

<xsl:template name="content-area">
  <xsl:param name="pageContent" select="content/topic"/>

  <xsl:choose>
    <xsl:when test="$pageContent/@module = 'content_quiz'">
      <xsl:call-template name="module-content-quiz">
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

<xsl:template name="module-content-quiz">
  <xsl:param name="pageContent" />

  <xsl:call-template name="module-content-topic">
    <xsl:with-param name="pageContent" select="$pageContent" />
  </xsl:call-template>

  <xsl:if test="$pageContent/teaser != ''">
    <xsl:apply-templates select="$pageContent/teaser/node()" />
  </xsl:if>

  <xsl:choose>
    <xsl:when test="$pageContent/quiz/question">
      <form class="contentQuizQuestion" action="{$pageContent/quiz/@action}" mathod="post">
        <h2>
          <xsl:value-of select="$pageContent/quiz/question/@title" />
        </h2>
        <div class="questionExplanation">
          <xsl:apply-templates select="$pageContent/quiz/question/node()" />
        </div>
        <input type="hidden"
               name="{$pageContent/quiz/question/@fieldname}"
               value="{$pageContent/quiz/question/@id}" />

        <xsl:for-each select="$pageContent/quiz/answer">
          <input id="quiz_answer_{@id}" type="radio" name="{@fieldname}" value="{@id}" />
          <label for="quiz_answer_{@id}">
            <xsl:value-of select="text/text()" />
          </label>
          <div class="answerExplanation">
            <xsl:apply-templates select="explanation/node()" />
          </div>
        </xsl:for-each>

        <xsl:call-template name="dialog-submit-button">
          <xsl:with-param name="buttonValue">
            <xsl:call-template name="language-text">
              <xsl:with-param name="text">NEXT</xsl:with-param>
            </xsl:call-template>
          </xsl:with-param>
        </xsl:call-template>
      </form>
    </xsl:when>
    <xsl:when test="$pageContent/quiz/summary">
      <xsl:for-each select="$pageContent/quiz/summary/question">
        <div class="oneAnswer">
          <h2><xsl:value-of select="title" /></h2>
          <div class="answerText">
            <xsl:apply-templates select="text/node()" />
          </div>
          <div class="answerLink">
            <xsl:apply-templates select="link/node()" />
          </div>
          <div class="givenAnswer">
            <h3>
              <xsl:call-template name="language-text">
                <xsl:with-param name="text">GIVEN_ANSWER</xsl:with-param>
              </xsl:call-template>
            </h3>
            <p>
              <xsl:value-of select="given_answer" />
            </p>
          </div>
          <div class="givenAnswerResponse">
            <h3>
              <xsl:call-template name="language-text">
                <xsl:with-param name="text">GIVEN_ANSWER_RESPONSE</xsl:with-param>
              </xsl:call-template>
            </h3>
            <xsl:apply-templates select="reply/node()" />
          </div>
          <div class="answerExplanation">
            <h3>
              <xsl:call-template name="language-text">
                <xsl:with-param name="text">EXPLANATION</xsl:with-param>
              </xsl:call-template>
            </h3>
            <xsl:apply-templates select="explanation/node()" />
          </div>

        </div>
      </xsl:for-each>
    </xsl:when>
    <xsl:otherwise>

    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

</xsl:stylesheet>