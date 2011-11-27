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

class       Erebot_Styling_Currency
implements  Erebot_Interface_Styling_Variable
{
    protected $_value;
    protected $_currency;

    public function __construct($value, $currency = NULL)
    {
        $this->_value       = $value;
        $this->_currency    = $currency;
    }

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

    public function getValue()
    {
        return $this->_value;
    }

    public function getCurrency()
    {
        return $this->_currency;
    }
}

