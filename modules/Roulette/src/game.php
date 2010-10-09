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

include_once('modules/Roulette/src/exceptions.php');

class   Roulette
{
    protected $last_shooter;
    protected $shoot_count;
    protected $shoot_to_bang;
    protected $nb_chambers;

    const STATE_NORMAL      = 'normal';
    const STATE_RELOAD      = 'reload';
    const STATE_BANG        = 'bang';

    public function __construct($nb_chambers)
    {
        $this->setChambersCount($nb_chambers);
    }

    public function next($shooter)
    {
        if ($shooter == $this->last_shooter)
            throw new ERouletteCannotGoTwiceInARow();

        $this->last_shooter = $shooter;
        $this->shoot_count++;

        if ($this->shoot_count == $this->nb_chambers-1 &&
            $this->shoot_to_bang == $this->nb_chambers) {
            $this->reset();
            return self::STATE_RELOAD;
        }

        if ($this->shoot_count == $this->shoot_to_bang) {
            $this->reset();
            return self::STATE_BANG;
        }

        return self::STATE_NORMAL;
    }

    public function reset()
    {
        $this->shoot_to_bang    = $this->getRandom($this->nb_chambers);
        $this->shoot_count      = 0;
        $this->last_shooter     = NULL;
    }

    protected function getRandom($max)
    {
        return mt_rand(1, $max);
    }

    public function setChambersCount($nb_chambers)
    {
        if (!is_int($nb_chambers) || $nb_chambers < 2)
            throw new ERouletteAtLeastTwoChambers();

        $this->nb_chambers = $nb_chambers;
        $this->reset();
    }

    public function getPassedChambersCount()
    {
        return $this->shoot_count;
    }

    public function getChambersCount()
    {
        return $this->nb_chambers;
    }
}

?>
