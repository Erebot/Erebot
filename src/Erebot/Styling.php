<?php
/*
    This file is part of Erebot.

    Erebot is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Erebot is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Erebot.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * \brief
 *      Provides styling (formatting) features.
 *
 *  Given a format string (a template), this class can perform
 *  styling on that template to produce complex messages.
 *
 *  A template is composed of a single string, which may contain
 *  special markup to insert dynamic content, add formatting
 *  attributes to the text (like bold, underline, colors), etc.
 *
 *  <table>
 *      <caption>Special markup in templates</caption>
 *
 *      <tr>
 *          <th>Markup</th>
 *          <th>Role</th>
 *      </tr>
 *
 *      <tr>
 *          <td>&lt;b&gt;...&lt;/b&gt;</td>
 *          <td>The text is rendered in \b{bold}</td>
 *      </tr>
 *
 *      <tr>
 *          <td>&lt;u&gt;...&lt;/u&gt;</td>
 *          <td>The text is rendered \u{underlined}</td>
 *      </tr>
 *
 *      <tr>
 *          <td>&lt;var name="..."/&gt;</td>
 *          <td>This markup gets replaced by the content
 *              of the given variable</td>
 *      </tr>
 *
 *      <tr>
 *          <td>
 *              &lt;color<br/>
 *                  &nbsp;&nbsp;fg="..."<br/>
 *                  &nbsp;&nbsp;bg="..."&gt;<br/>
 *                  &nbsp;&nbsp;&nbsp;&nbsp;...<br/>
 *              &lt;/color&gt;
 *          </td>
 *          <td>The text is rendered with the given foreground (\a fg)
 *              and background (\a bg) colors. The value of the \a fg and
 *              \a bg attributes may be either an integer (see the COLOR_*
 *              constants in this class) or the name of the color (again,
 *              supported colors are named after the COLOR_* constants).</td>
 *      </tr>
 *
 *      <tr>
 *          <td>
 *              &lt;for<br/>
 *                  &nbsp;&nbsp;from="..."<br/>
 *                  &nbsp;&nbsp;item="..."<br/>
 *                  &nbsp;&nbsp;key="..."<br/>
 *                  &nbsp;&nbsp;sep=&quot;,&nbsp;&quot;<br/>
 *                  &nbsp;&nbsp;last=&quot;&nbsp;&amp;amp;&nbsp;&quot;&gt;<br/>
 *                  &nbsp;&nbsp;&nbsp;&nbsp;...<br/>
 *              &lt;/for&gt;
 *          </td>
 *          <td>This markup loops over the associative array in \a from.
 *              The key for each entry in that array is stored in the
 *              temporary variable named by the \a key attribute if given,
 *              while the associated value is stored in the temporary
 *              variable named by \a item. The value of \a sep (alias
 *              \a separator) is appended automatically between each entry
 *              of the array, except between the last two entries.
 *              The value of \a last (alias \a last_separator) is used
 *              to separate the last two entries.
 *              By default, no temporary variable is created for the key,
 *              ", " is used as the main \a separator and " & " is used as
 *              the \a last_separator.</td>
 *      </tr>
 *
 *      <tr>
 *          <td>
 *              &lt;plural var="..."&gt;<br/>
 *                  &nbsp;&nbsp;&lt;case form="..."&gt;<br/>
 *                      &nbsp;&nbsp;&nbsp;&nbsp;...<br/>
 *                  &nbsp;&nbsp;&lt;/case&gt;<br/>
 *              &lt;/plural&gt;
 *          </td>
 *          <td>Handles plurals. Depending on the value of the variable
 *              pointed by \a var, one of the cases will be used. The page at
 * http://unicode.org/cldr/data/charts/supplemental/language_plural_rules.html
 *              references every available form per language.</td>
 *      </tr>
 *  </table>
 */
class       Erebot_Styling
implements  Erebot_Interface_Styling
{
    /// Translator to use to improve rendering.
    protected $_translator;

    /// Maps some scalar types to a typed variable.
    protected $_cls;

    /**
     * Construct a new styling object.
     *
     * \param string $source
     *      The template which will be used to produce
     *      the final message.
     *
     * \param Erebot_Interface_I18n $translator
     *      A translator object, used to determine the correct
     *      pluralization rules.
     */
    public function __construct(Erebot_Interface_I18n $translator)
    {
        $this->_translator  = $translator;
        $this->_cls = array(
            'int'       => 'Erebot_Styling_Integer',
            'float'     => 'Erebot_Styling_Float',
            'string'    => 'Erebot_Styling_String',
        );
    }

    /**
     * Returns the class used to wrap scalar types.
     *
     * \param string $type
     *      Name of a scalar type that can be wrapped
     *      by this class automatically. Must be one
     *      of "int", "string" or "float".
     *
     * \retval string
     *      Name of the class that can be used to wrap
     *      variables of the given type.
     */
    public function getClass($type)
    {
        if (!isset($this->_cls[$type]))
            throw new Erebot_InvalidValueException('Invalid type');
        return $this->_cls[$type];
    }

    /**
     * Sets the class to use to wrap a certain scalar type.
     *
     * \param string $type
     *      Name of a scalar type that can be wrapped
     *      by this class automatically. Must be one
     *      of "int", "string" or "float".
     *
     * \param string $cls
     *      Name of the class that can be used to wrap
     *      variables of the given type.
     */
    public function setClass($type, $cls)
    {
        if (!isset($this->_cls[$type]))
            throw new Erebot_InvalidValueException('Invalid type');
        if (!is_string($cls)) {
            throw new Erebot_InvalidValueException(
                'Expected a string for the class'
            );
        }
        if (!class_exists($cls))
            throw new Erebot_InvalidValueException('Class not found');
        if (!($cls instanceof Erebot_Interface_Styling_Variable)) {
            throw new Erebot_InvalidValueException(
                'Must be a subclass of Erebot_Interface_Styling_Variable'
            );
        }
        $this->_cls[$type] = $cls;
    }

    /// \copydoc Erebot_Interface_Styling::_()
    public function _($template, array $vars = array())
    {
        $source = $this->_translator->_($template);
        return $this->render($source, $vars);
    }

    /// \copydoc Erebot_Interface_Styling::render()
    public function render($template, array $vars = array())
    {
        // For basic strings that don't contain any markup,
        // we try to be as efficient as possible.
        if (strpos($template, '<') === FALSE)
            return $template;

        $attributes = array(
            'underline' => 0,
            'bold'      => 0,
            'bg'        => NULL,
            'fg'        => NULL,
        );

        $variables = array();
        foreach ($vars as $name => $var)
            $variables[$name] = $this->_wrapScalar($var);

        $dom = self::_parseTemplate($template);
        $result = $this->_parseNode(
            $dom->documentElement,
            $attributes,
            $variables
        );

        $pattern =  '@'.
                    '\\003,(?![01])'.
                    '|'.
                    '\\003(?:[0-9]{2})?,(?:[0-9]{2})?(?:\\002\\002)?(?=\\003)'.
                    '|'.
                    '(\\003(?:[0-9]{2})?,)\\002\\002(?![0-9])'.
                    '|'.
                    '(\\003[0-9]{2})\\002\\002(?!,)'.
                    '@';
        $replace    = '\\1\\2';
        $result     = preg_replace($pattern, $replace, $result);
        return $result;
    }

    /// \copydoc Erebot_Interface_Styling::getTranslator
    public function getTranslator()
    {
        return $this->_translator;
    }

    protected function _wrapScalar($var)
    {
        if (is_object($var)) {
            if ($var instanceof Erebot_Interface_Styling_Variable)
                return $var;

            throw new Erebot_InvalidValueException(
                'Variables must be scalars or instances of '.
                'Erebot_Interface_Styling_Variable'
            );
        }

        if (is_array($var))
            return $var;

        if (is_string($var))
            $cls = $this->_cls['string'];
        else if (is_int($var))
            $cls = $this->_cls['int'];
        else if (is_float($var))
            $cls = $this->_cls['float'];
        return new $cls($var);
    }

    static protected function _parseTemplate($source)
    {
        $source =
            '<msg xmlns="http://www.erebot.net/xmlns/erebot/styling">'.
            $source.
            '</msg>';

        $dataDir = '@data_dir@';
        // Running from the repository or PHAR.
        if ($dataDir == '@'.'data_dir'.'@') {
            $dataDir = dirname(dirname(dirname(__FILE__))) .
                        DIRECTORY_SEPARATOR . 'data';
            // Running from PHAR.
            if (!strncmp(__FILE__, 'phar://', 7)) {
                $dataDir .=
                    DIRECTORY_SEPARATOR . 'pear.erebot.net' .
                    DIRECTORY_SEPARATOR . 'Erebot';
            }
        }
        else
            $dataDir .= DIRECTORY_SEPARATOR . 'peat.erebot.net' .
                        DIRECTORY_SEPARATOR . 'Erebot';

        $schema = $dataDir . DIRECTORY_SEPARATOR . 'styling.rng';

        $dom    =   new Erebot_DOM();
        $ue     = libxml_use_internal_errors(TRUE);
        $dom->loadXML($source);
        $valid  = $dom->relaxNGValidate($schema);
        $errors = $dom->getErrors();
        libxml_use_internal_errors($ue);

        if (!$valid || count($errors)) {
            // Some unpredicted error occurred,
            // show some (hopefully) useful information.
            $errmsg     =   print_r($errors, TRUE);
            $logging    =&  Plop::getInstance();
            $logger     =   $logging->getLogger(__FILE__);
            $logger->error($errmsg);
            throw new Erebot_InvalidValueException(
                'Error while validating the message'
            );
        }
        return $dom;
    }

    /**
     * This is the main parsing method.
     *
     * \param DOMNode $node
     *      The node being parsed.
     *
     * \param array $attributes
     *      Array of styling attributes.
     *
     * \param array $vars
     *      Template variables that can be injected in the return.
     *
     * \retval string
     *      Parsing result, with styles applied as appropriate.
     */
    protected function _parseNode($node, &$attributes, $vars)
    {
        $result     = '';
        $saved      = $attributes;

        if ($node->nodeType == XML_TEXT_NODE)
            return $node->nodeValue;

        if ($node->nodeType != XML_ELEMENT_NODE)
            return '';

        // Pre-handling.
        switch ($node->tagName) {
            case 'var':
                $varname = $node->getAttribute('name');
                if (!($vars[$varname] instanceof
                    Erebot_Interface_Styling_Variable))
                    return (string) $vars[$varname];
                return $vars[$varname]->render($this->_translator);

            case 'u':
                if (!$attributes['underline'])
                    $result .= self::CODE_UNDERLINE;
                $attributes['underline'] = 1;
                break;

            case 'b':
                if (!$attributes['bold'])
                    $result .= self::CODE_BOLD;
                $attributes['bold'] = 1;
                break;

            case 'color':
                $colors     = array('', '');
                $mapping    = array('fg', 'bg');

                foreach ($mapping as $pos => $color) {
                    $value = $node->getAttribute($color);
                    if ($value != '') {
                        $value = str_replace(array(' ', '-'), '_', $value);
                        try {
                            if (!ctype_digit($value))
                                $value = Erebot_Utils::getVStatic(
                                    $this, 'COLOR_'.strtoupper($value)
                                );
                            $attributes[$color] = sprintf('%02d', $value);
                            if ($attributes[$color] != $saved[$color])
                                $colors[$pos] = $attributes[$color];
                        }
                        catch (Erebot_NotFoundException $e) {
                            throw new Erebot_InvalidValueException(
                                'Invalid color "'.$value.'"'
                            );
                        }
                    }
                }

                $code = implode(',', $colors);
                if ($colors[0] != '' && $colors[1] != '')
                    $result .= self::CODE_COLOR.$code;
                else if ($code != ',')
                    $result .= self::CODE_COLOR.rtrim($code, ',').
                                self::CODE_BOLD.self::CODE_BOLD;
                break;
        }

        // Handle loops.
        if ($node->tagName == 'for') {
            $savedVariables = $vars;
            $separator      = array(', ', ' & ');

            foreach (array('separator', 'sep') as $attr) {
                $attrNode       = $node->getAttributeNode($attr);
                if ($attrNode !== FALSE) {
                    $separator[0] = $separator[1] = $attrNode->nodeValue;
                    break;
                }
            }

            foreach (array('last_separator', 'last') as $attr) {
                $attrNode       = $node->getAttributeNode($attr);
                if ($attrNode !== FALSE) {
                    $separator[1] = $attrNode->nodeValue;
                    break;
                }
            }

            $loopKey    = $node->getAttribute('key');
            $loopItem   = $node->getAttribute('item');
            $loopFrom   = $node->getAttribute('from');
            $count      = count($vars[$loopFrom]);
            reset($vars[$loopFrom]);

            for ($i = 1; $i < $count; $i++) {
                if ($i > 1)
                    $result .= $separator[0];

                $item = each($vars[$loopFrom]);
                if ($loopKey !== NULL) {
                    $cls = $this->_cls['string'];
                    $vars[$loopKey] = new $cls($item['key']);
                }
                $vars[$loopItem] = $this->_wrapScalar($item['value']);

                $result .= $this->_parseChildren(
                    $node,
                    $attributes,
                    $vars
                );
            }

            $item = each($vars[$loopFrom]);
            if ($loopKey !== NULL) {
                $cls = $this->_cls['string'];
                $vars[$loopKey] = new $cls($item['key']);
            }
            $vars[$loopItem] = $this->_wrapScalar($item['value']);
            if ($count > 1)
                $result .= $separator[1];

            $result .= $this->_parseChildren($node, $attributes, $vars);
            $vars = $savedVariables;
        }

        // Handle plurals.
        else if ($node->tagName == 'plural') {
            /* We don't need the full set of features/complexity/bugs
             * ICU contains. Here, we use a simple "plural" formatter
             * to detect the right plural form to use. The formatting
             * steps are done before without using ICU. */
            $attrNode = $node->getAttributeNode('var');
            if ($attrNode === FALSE)
                throw new Erebot_InvalidValueException(
                    'No variable name given'
                );
            $value = (int) $vars[$attrNode->nodeValue]->getValue();
            $subcontents = array();
            $pattern = '{0,plural,';
            for (   $child = $node->firstChild;
                    $child != NULL;
                    $child = $child->nextSibling) {
                if ($child->nodeType != XML_ELEMENT_NODE ||
                    $child->tagName != 'case')
                    continue;
                // See this class documentation for a link
                // which lists available forms for each language.
                $form = $child->getAttribute('form');
                $subcontents[$form] = $this->_parseNode(
                    $child, $attributes, $vars
                );
                $pattern .= $form.'{'.$form.'} ';
            }
            $pattern .= '}';
            $locale = $this->_translator->getLocale(
                Erebot_Interface_I18n::LC_MESSAGES
            );
            $formatter = new MessageFormatter($locale, $pattern);
            // HACK: PHP <= 5.3.3 returns NULL when the pattern in invalid
            // instead of throwing an exception.
            // See http://bugs.php.net/bug.php?id=52776
            if ($formatter === NULL)
                throw new Erebot_InvalidValueException('Invalid plural forms');
            $correctForm = $formatter->format(array($value));
            $result .= $subcontents[$correctForm];
        }

        // Handle childrens.
        else
            $result .= $this->_parseChildren($node, $attributes, $vars);

        // Post-handling : restore old state.
        switch ($node->tagName) {
            case 'u':
                if (!$saved['underline'])
                    $result .= self::CODE_UNDERLINE;
                $attributes['underline'] = 0;
                break;

            case 'b':
                if (!$saved['bold'])
                    $result .= self::CODE_BOLD;
                $attributes['bold'] = 0;
                break;

            case 'color':
                $colors     = array('', '');
                $mapping    = array('fg', 'bg');

                foreach ($mapping as $pos => $color) {
                    if ($attributes[$color] != $saved[$color])
                        $colors[$pos] = $saved[$color];
                    $attributes[$color] = $saved[$color];
                }

                $code = implode(',', $colors);
                if ($colors[0] != '' && $colors[1] != '')
                    $result .= self::CODE_COLOR.$code;
                else if ($code != ',')
                    $result .= self::CODE_COLOR.rtrim($code, ',').
                                self::CODE_BOLD.self::CODE_BOLD;
                break;
        }

        return $result;
    }

    /**
     * This method is used to apply the parsing method
     * to children of an XML node.
     *
     * \param DOMNode $node
     *      The node being parsed.
     *
     * \param array $attributes
     *      Array of styling attributes.
     *
     * \param array $vars
     *      Template variables that can be injected in the result.
     *
     * \retval string
     *      Parsing result, with styles applied as appropriate.
     */
    private function _parseChildren($node, &$attributes, $vars)
    {
        $result = '';
        for (   $child = $node->firstChild;
                $child != NULL;
                $child = $child->nextSibling) {
            $result .=  $this->_parseNode($child, $attributes, $vars);
        }
        return $result;
    }
}

