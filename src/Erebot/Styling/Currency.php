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
 *      A class used to format currencies.
 */
class       Erebot_Styling_Currency
implements  Erebot_Interface_Styling_Currency
{
    /// Amount.
    protected $_value;

    /// Currency the amount is expressed in.
    protected $_currency;

    /**
     * Constructor.
     *
     * \param float $value
     *      An amount of some currency to format.
     *
     * \param string|NULL $currency
     *      (optional) The currency the amount is expressed in.
     *      This is used to pick the right monetary symbol and
     *      conventions to format the amount.
     *      The default is to use the currency associated with
     *      the translator given during the actual formatting
     *      operation.
     */
    public function __construct($value, $currency = NULL)
    {
        $this->_value       = $value;
        $this->_currency    = $currency;
    }

    /**
    /// \copydoc Erebot_Interface_Styling_Variable::render()
     *
     * \note
     *      If no currency was passed to this class' constructor,
     *      the currency associated with the translator's locale
     *      is used.
     */
    public function render(Erebot_Interface_I18n $translator)
    {
        $locale = $translator->getLocale(Erebot_Interface_I18n::LC_MONETARY);
        $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);
        $symbol = NumberFormatter::INTL_CURRENCY_SYMBOL;
        $currency = ($this->_currency !== NULL)
                    ? $this->_currency
                    : $formatter->getSymbol($symbol);
        return (string) $formatter->formatCurrency($this->_value, $currency);
    }

    /// \copydoc Erebot_Interface_Styling_Variable::getValue()
    public function getValue()
    {
        return $this->_value;
    }

    /// \copydoc Erebot_Interface_Styling_Currency::getCurrency()
    public function getCurrency()
    {
        return $this->_currency;
    }
}

