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
 *      Abstract filter that matches events based on their content (text).
 *
 * Subclasses must provide the logic for the matching algorithm
 * by overriding the _match() method.
 */
abstract class  Erebot_Event_Match_TextAbstract
implements      Erebot_Interface_Event_Match
{
    /// Pattern used in comparisons, as a string.
    protected $_pattern;

    /// Boolean or NULL indicating whether a prefix is required or not.
    protected $_requirePrefix;

    /**
     * Creates a new instance of this filter.
     *
     * \param string $pattern
     *      Pattern to use in text comparisons.
     *
     * \param bool|NULL $requirePrefix
     *      (optional) Whether a prefix will be required (TRUE),
     *      allowed (NULL) or disallowed (FALSE).
     *      The default is to prohibit the use of a prefix.
     *
     * \raise Erebot_InvalidValueException
     *      The given value for $pattern or $requirePrefix is invalid.
     */
    public function __construct($pattern, $requirePrefix = FALSE)
    {
        $this->setPattern($pattern);
        $this->setPrefixRequirement($requirePrefix);
    }

    /**
     * Returns the pattern associated with this filter.
     *
     * \retval string
     *      Pattern associated with this filter.
     */
    public function getPattern()
    {
        return $this->_pattern;
    }

    /**
     * Sets the pattern associated with this filter.
     *
     * \param string $pattern
     *      Pattern to use in text comparisons.
     *
     * \raise Erebot_InvalidValueException
     *      The given value for $pattern is invalid.
     */
    public function setPattern($pattern)
    {
        if (!Erebot_Utils::stringifiable($pattern))
            throw new Erebot_InvalidValueException('Pattern must be a string');

        $this->_pattern = $pattern;
    }

    /**
     * Returns the prefix requirement constraint for this filter.
     *
     * \retval bool|NULL
     *      Either TRUE if a prefix is required,
     *      NULL if a prefix is allowed,
     *      FALSE if a prefix is disallowed.
     */
    public function getPrefixRequirement()
    {
        return $this->_requirePrefix;
    }

    /**
     * Sets the constraint on prefix requirement.
     *
     * \param bool|NULL $requirePrefix
     *      (optional) Whether a prefix will be required (TRUE),
     *      allowed (NULL) or disallowed (FALSE).
     *      The default is to prohibit the use of a prefix.
     *
     * \raise Erebot_InvalidValueException
     *      The given value for $requirePrefix is invalid.
     */
    public function setPrefixRequirement($requirePrefix = FALSE)
    {
        if ($requirePrefix !== NULL && !is_bool($requirePrefix))
            throw new Erebot_InvalidValueException(
                '$requirePrefix must be a boolean or NULL'
            );

        $this->_requirePrefix = $requirePrefix;
    }

    // Documented in the interface.
    public function match(Erebot_Interface_Event_Generic $event)
    {
        if (!($event instanceof Erebot_Interface_Event_Text))
            return FALSE;

        $prefix = $event
            ->getConnection()->getConfig(NULL)
            ->getMainCfg()->getCommandsPrefix();

        $result = $this->_match($prefix, $event->getText());
        if (!is_bool($result))
            throw new Erebot_InvalidValueException('Invalid return value');
        return $result;
    }

    /**
     * Actual method used to make the comparison against
     * the incoming event. This method is passed the contents
     * of the event and may use the values of the $_pattern
     * and $_requirePrefix instance attributes to make the
     * comparison.
     *
     * \param string $prefix
     *      Current prefix for commands, as defined
     *      in the configuration file.
     *
     * \param string $text
     *      Content of the incoming event.
     *
     * \retval bool
     *      TRUE if the event's content passes the filter,
     *      FALSE otherwise.
     */
    abstract protected function _match($prefix, $text);
}

