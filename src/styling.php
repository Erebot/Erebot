<?php

include_once(dirname(__FILE__).'/utils.php');

/**
 * \brief
 *      Provides styling (formatting) features.
 *
 *  Given a format string (a template), this class can perform
 *  styling on that template to produce complex messages.
 *
 *  Most of the interface for this class is the same as what
 *  the Smarty templating engine uses, so if you're familiar
 *  with Smarty, you should no problem using this class.
 *
 *  A template is composed of a single string, which may contain
 *  special markup to insert dynamic content, add formatting
 *  attributes to the text (like bold, underline, colors).
 *
 *  <table>
 *      <caption>Special markup in templates</caption>
 *
 *      <tr>
 *          <td>&lt;b&gt;...&lt;/b&gt;</td>
 *          <td>The text is rendered in <b>bold</b></td>
 *      </tr>
 *
 *      <tr>
 *          <td>&lt;u&gt;...&lt;/u&gt;</td>
 *          <td>The text is rendered <u>underlined</u></td>
 *      </tr>
 *
 *      <tr>
 *          <td>&lt;var <br>name="..."/&gt;</td>
 *          <td>This markup gets replaced by the content
 *              of the given variable</td>
 *      </tr>
 *
 *      <tr>
 *          <td>&lt;color <br>fg="..." <br>bg="..."&gt;...&lt;/color&gt;</td>
 *          <td>The text is rendered with the given foreground (fg)
 *              and background (bg) colors. The value of the fg and
 *              bg attributes may be either an integer (see the COLOR_*
 *              constants in this class) or the name of the color (again,
 *              supported colors are named after the COLOR_* constants).</td>
 *      </tr>
 *
 *      <tr>
 *          <td>&lt;for <br>from="..." <br>item="..." <br>key="..." <br>separator=", "
 *              <br>last_separator=" &amp; "&gt;...&lt;/for&gt;</td>
 *          <td>This markup loops over the associative array in the
 *              variable given by the from attribute. The key for each
 *              entry in that array is stored in the temporary variable
 *              whose name is given in the key attribute if given, while
 *              the associated value is stored in the temporary variable
 *              named by the item attribute. The value of the separator
 *              attribute is appended automatically between each entry
 *              of the array, except between the last two entries. The
 *              value of the last_separator attribute is used to separate
 *              the last two entries. By default, no temporary variable
 *              is created for the key, ", " is used as the main separator
 *              and " &amp; " is used as the last_separator.</td>
 *      </tr>
 *  </table>
 */
class   ErebotStyling
{
    protected $variables;
    protected $dom;

    const CODE_BOLD         = "\002";
    const CODE_COLOR        = "\003";
    const CODE_RESET        = "\017";
    const CODE_REVERSE      = "\026";
    const CODE_UNDERLINE    = "\037";

    /* mIRC/PIRCH colors */
    const COLOR_WHITE       = 0;
    const COLOR_BLACK       = 1;
    const COLOR_BLUE        = 2;
    const COLOR_NAVY_BLUE   = 2;
    const COLOR_DARK_BLUE   = 2;
    const COLOR_GREEN       = 3;
    const COLOR_DARK_GREEN  = 3;
    const COLOR_RED         = 4;
    const COLOR_BROWN       = 5;
    const COLOR_PURPLE      = 6;
    const COLOR_ORANGE      = 7;
    const COLOR_OLIVE       = 7;
    const COLOR_YELLOW      = 8;
    const COLOR_LIGHT_GREEN = 9;
    const COLOR_LIME_GREEN  = 9;
    const COLOR_CYAN        = 10;
    const COLOR_TEAL        = 10;
    const COLOR_DARK_CYAN   = 10;
    const COLOR_LIGHT_CYAN  = 11;
    const COLOR_AQUA_LIGHT  = 11;
    const COLOR_LIGHT_BLUE  = 12;
    const COLOR_ROYAL_BLUE  = 12;
    const COLOR_PINK        = 13;
    const COLOR_HOT_PINK    = 13;
    const COLOR_GRAY        = 14;
    const COLOR_DARK_GRAY   = 14;
    const COLOR_LIGHT_GRAY  = 15;

    /**
     * Construct a new styling object.
     *
     * \param $source
     *      The template which will be used to produce
     *      the final message.
     */
    public function __construct($source)
    {
        $source             = '<msg>'.$source.'</msg>';
        $this->dom          = new DomDocument();
        $this->variables    = array();
        $this->dom->loadXML($source);
    }

    /**
     * \TODO incomplete...
     */
    public function append($varname, $var, $merge = NULL)
    {
        if (!is_array($var)) {
            $this->variables[$varname][] = $var;
            return;
        }
    }

    /**
     * \TODO incomplete...
     */
    public function append_by_ref($varname, &$var, $merge = NULL)
    {
        if (!is_array($var)) {
            $this->variables[$varname][] =& $var;
        }
    }

    /**
     * Assign a value to a variable which will be
     * passed to the template.
     * Unlike ErebotStyling::assign_by_ref(), this
     * method assigns the variable by value.
     *
     * \param $name
     *      Name of the variable to assign.
     *
     * \param $value
     *      Value for that variable.
     */
    public function assign($name, $value)
    {
        $this->variables[$name] = $value;
    }

    /**
     * Assign a value to a variable which will be
     * passed to the template.
     * Unlike ErebotStyling::assign(), this method
     * assigns the variable by reference.
     *
     * \param $name
     *      Name of the variable to assign.
     *
     * \param $value
     *      Value for that variable, as a reference.
     */
    public function assign_by_ref($name, &$value)
    {
        $this->variables[$name] =& $value;
    }

    /**
     * Unsets any previous value assigned to
     * the templates' variables.
     */
    public function clear_all_assign()
    {
        unset($this->variables);
        $this->variables = array();
    }

    /**
     * Unsets any previous value assigned to
     * a given variable.
     *
     * \param $varname
     *      Name of the variable to unset.
     */
    public function clear_assign($varname)
    {
        unset($this->variables[$varname]);
    }

    /**
     * Renders the template using assigned
     * variables.
     *
     * \return
     *      Returns a string appropriate for
     *      sending over the network to the
     *      IRC server.
     */
    public function render()
    {
        $attributes = array(
            'underline' => 0,
            'bold'      => 0,
            'bg'        => NULL,
            'fg'        => NULL,
        );
        $variables  =   $this->variables;
        $result     =   $this->parseNode(
                            $this->dom->documentElement,
                            $attributes,
                            $variables
                        );
        $pattern    =   '@'.
                        '\\003,(?![01])'.                   # 1st parenthesis
                        '|'.
                        '\\003(?:[0-9]{2})?,(?:[0-9]{2})?(?:\\002\\002)?(?=\\003)'.
                        '|'.
                        '(\\003(?:[0-9]{2})?,)\\002\\002(?![0-9])'.
                        '|'.
                        '(\\003[0-9]{2})\\002\\002(?!,)'.   # 2nd parenthesis
                        '@';
        $replace    = '\\1\\2';
        $result     = preg_replace($pattern, $replace, $result);
        return $result;
    }

    /**
     * Returns either all variables assigned to the template,
     * or the value assigned to a particular variable.
     *
     * \param $varname
     *      If given, this must be the name of a variable
     *      assigned to the template.
     *
     * \return
     *      If $varname was given, returns the current value
     *      assigned to the variable which goes by the name.
     *      Otherwise, returns all variables currently assigned
     *      to this template, as an associative array mapping
     *      the variables' names to their values.
     */
    public function get_template_vars($varname = NULL)
    {
        if ($varname === NULL)
            return $this->variables;
        return $this->variables[$varname];
    }

    protected function parseNode($node, &$attributes, $variables)
    {
        $result     = '';
        $saved      = $attributes;
        $pattern    = '/^[a-z_][a-z_0-9]*$/i';

        if ($node->nodeType == XML_TEXT_NODE)
            return $node->nodeValue;

        if ($node->nodeType != XML_ELEMENT_NODE) {
            var_dump($node->nodeType);
            return '';
        }

        // Pre-handling.
        switch ($node->tagName) {
            case 'var':
                $varname = $node->getAttribute('name');
                if (!preg_match($pattern, $varname))
                    throw new EErebotInvalidValue();
                return (string) $variables[$varname];

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
                            $attributes[$color] = sprintf(
                                '%02d', ErebotUtils::getVStatic(
                                $this, 'COLOR_'.strtoupper($value)));
                            if ($attributes[$color] != $saved[$color])
                                $colors[$pos] = $attributes[$color];
                        }
                        catch (EErebotNotFound $e) {
                            throw new EErebotInvalidValue(
                                        'Invalid color "'.$value.'"');
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

        // Handle children & loops.
        if ($node->tagName == 'for') {
            $savedVariables = $variables;
            $separator      = array(', ', ' & ');

            $attrNode       = $node->getAttributeNode('separator');
            if ($attrNode !== FALSE)
                $separator[0] = $separator[1] = $attrNode->nodeValue;

            $attrNode       = $node->getAttributeNode('last_separator');
            if ($attrNode !== FALSE)
                $separator[1] = $attrNode->nodeValue;

            $loopKey        = $node->getAttribute('key');
            if (!preg_match($pattern, $loopKey))
                $loopKey = NULL;

            $loopItem       = $node->getAttribute('item');
            if (!preg_match($pattern, $loopItem))
                throw new EErebotInvalidValue();

            $loopFrom       = $node->getAttribute('from');
            if (!preg_match($pattern, $loopFrom))
                throw new EErebotInvalidValue();

            $count          = count($variables[$loopFrom]);
            reset($variables[$loopFrom]);
            for ($i = 1; $i < $count; $i++) {
                if ($i > 1)
                    $result .= $separator[0];

                $item = each($variables[$loopFrom]);
                if ($loopKey !== NULL)
                    $variables[$loopKey] = $item['key'];
                $variables[$loopItem] = $item['value'];

                $result .= $this->parseChildren($node, $attributes, $variables);
            }

            $item = each($variables[$loopFrom]);
            if ($loopKey !== NULL)
                $variables[$loopKey] = $item['key'];
            $variables[$loopItem] = $item['value'];
            if ($count > 1)
                $result .= $separator[1];

            $result .= $this->parseChildren($node, $attributes, $variables);
            $variables = $savedVariables;
        }
        else
            $result .= $this->parseChildren($node, $attributes, $variables);

        // Post-handling.
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

    private function parseChildren($node, &$attributes, $variables)
    {
        $result = '';
        for (   $child = $node->firstChild;
                $child != NULL;
                $child = $child->nextSibling) {
            $result .=  $this->parseNode($child, $attributes, $variables);
        }
        return $result;
    }
}

?>
