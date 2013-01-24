<?php
/*
    This file is part of Erebot, a modular IRC bot written in PHP.

    Copyright © 2010 François Poirotte

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
 *      A class used to format floating-point values.
 */
class       Erebot_Styling_Float
implements  Erebot_Interface_Styling_Float
{
    /// The float-point value to format.
    protected $_value;

    /**
     * Constructor.
     *
     * \param float $value
     *      The floating-point value to format.
     */
    public function __construct($value)
    {
        $this->_value = $value;
    }

    /// \copydoc Erebot_Interface_Styling_Variable::render()
    public function render(Erebot_Interface_I18n $translator)
    {
        $locale = $translator->getLocale(Erebot_Interface_I18n::LC_NUMERIC);
        $formatter = new NumberFormatter($locale, NumberFormatter::DECIMAL);
        $formatter->setAttribute(NumberFormatter::MIN_FRACTION_DIGITS, 0);
        $formatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, 100);
        $result = (string) $formatter->format($this->_value);
        return $result;
    }

    /// \copydoc Erebot_Interface_Styling_Variable::getValue()
    public function getValue()
    {
        return $this->_value;
    }
}

