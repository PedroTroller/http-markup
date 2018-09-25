Feature: Parse markup file and transform them

  Scenario: Parse markdown format
    When I send a markup file with content type "text/markdown" containing
    """
    # The title

    ## The subtitle
    """
    Then I should get the following html
    """
    <h1>The title</h1>

    <h2>The subtitle</h2>
    """

  Scenario Outline: Parse textile format
    When I send a markup file with content type "<mime type>" containing
    """
    h2. Textile

    * is a _shorthand syntax_ used to generate valid HTML,
    * is *easy* to read and *easy* to write,
    * can generate complex pages,
    * including headers, quotes, lists, tables, and figures.

    p{font-size:0.8em}. *TxStyle* is a documentation project of Textile for "Textpattern CMS":http://textpattern.com.
    """
    Then I should get the following html
    """
    <h2>Textile</h2>
    <ul>
    <li>is a <em>shorthand syntax</em> used to generate valid <span class="caps">HTML</span>,</li>
    <li>is <strong>easy</strong> to read and <strong>easy</strong> to write,</li>
    <li>can generate complex pages,</li>
    <li>including headers, quotes, lists, tables, and figures.</li>
    </ul>
    <p style="font-size:0.8em;"><strong>TxStyle</strong> is a documentation project of Textile for <a href="http://textpattern.com">Textpattern <span class="caps">CMS</span></a>.</p>
    """

    Examples:
      | mime type    |
      | text/txstyle |
      | text/textile |

  Scenario: Parse rdoc format
    When I send a markup file with content type "text/rdoc" containing
    """
    # Foo.
    #
    # @example
    #
    #   y
    #   g
    #
    # @param [String] param_name The xx and xx.
    #
    # @see http://url.com
    #
    # @return [true] if so
    """
    Then I should get the following html
    """

    <p># Foo. # # @example # #   y #   g # # @param [String] param_name The xx and
    xx. # # @see <a href="http://url.com">url.com</a> # # @return [true] if so</p>
    """

  Scenario Outline: Parse org mode format
    When I send a markup file with content type "<mime type>" containing
    """
    * Test Agenda

    <2017-03-10 Fri>
    * test agenda
    SCHEDULED: <2017-07-19 Wed>
    """
    Then I should get the following html
    """
    <h1>Test Agenda</h1>
    <p>&lt;2017-03-10 Fri&gt;</p>
    <h1>test agenda</h1>
    """

    Examples:
      | mime type    |
      | text/orgmode |
      | text/org     |

  Scenario Outline: Parse rst format
    When I send a markup file with content type "<mime type>" containing
    """
    To localize a postal address, a minimum set of properties are required :

     - ``streetAddress``
     - ``addressLocality``
     - ``postalCode``
     - ``addressCountry``

    If at least one of these properties is not available, then the ``address`` attribute is mandatory.

    Without this kind of informations, then a ``PostalAddress`` is useless.

    The ``name`` property of a ``PostalAddress`` is optionnal.
    """
    Then I should get the following html
    """
    <p>To localize a postal address, a minimum set of properties are required :</p>
    <blockquote>
    <ul class="simple">
    <li><code>streetAddress</code></li>
    <li><code>addressLocality</code></li>
    <li><code>postalCode</code></li>
    <li><code>addressCountry</code></li>
    </ul>
    </blockquote>
    <p>If at least one of these properties is not available, then the <code>address</code> attribute is mandatory.</p>
    <p>Without this kind of informations, then a <code>PostalAddress</code> is useless.</p>
    <p>The <code>name</code> property of a <code>PostalAddress</code> is optionnal.</p>
    """

    Examples:
      | mime type             |
      | text/rst              |
      | text/restructuredtext |
