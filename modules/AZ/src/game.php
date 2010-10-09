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

include_once('modules/AZ/src/exceptions.php');

class AZ
{
    const WORD_FILTER = '/^[a-z0-9\\-\\.\\(\\)_\']+(?: [a-z0-9\\-\\.\\(\\)_\']+)?$/';
    static protected $availableLists = NULL;

    protected $loadedLists;
    protected $min;
    protected $max;
    protected $target;
    protected $attempts;
    protected $invalidWords;

    public function __construct($lists)
    {
        self::getAvailableLists();
        $wordlist = array();
        $this->loadedLists = array();
        foreach ($lists as &$list) {
            try {
                $this->loadWordlist($list);
                $wordlist = array_merge($wordlist, $this->loadedLists[$list]);
            }
            catch (EAZBadListName $e) {}
            catch (EAZUnreadableFile $e) {}
        }
        unset($list);

        $wordlist = array_unique($wordlist);
        if (count($wordlist) < 3)
            throw new EAZNotEnoughWords();

        $this->attempts     =
        $this->invalidWords = 0;
        $this->min          =
        $this->max          = NULL;
        $this->target       = $wordlist[array_rand($wordlist)];
    }

    public function __destruct()
    {
        unset($this->loadedLists);
    }

    static public function getAvailableLists()
    {
        if (self::$availableLists === NULL) {
            self::$availableLists = array();
            $files = scandir(dirname(__FILE__).'/wordlists/');
            foreach ($files as $file) {
                if (substr($file, -4) == '.txt')
                    self::$availableLists[] = substr($file, 0, -4);
            }
        }
        return self::$availableLists;
    }

    public function getLoadedListsNames()
    {
        return array_keys($this->loadedLists);
    }

    protected function filterWord($word)
    {
        return (bool) preg_match(self::WORD_FILTER, $word);
    }

    protected function loadWordlist($list)
    {
        if (isset($this->loadedLists[$list]))
            return;

        if (!in_array($list, self::$availableLists, TRUE))
            throw new EAZBadListName($list);

        $file = dirname(__FILE__).'/wordlists/'.$list.'.txt';
        if (!is_readable($file))
            throw new EAZUnreadableFile($file);

        $wordlist = file($file);
        if ($wordlist === FALSE)
            throw new EAZUnreadableFile($file);
        $wordlist = array_map('trim', $wordlist);

        $encoding = 'ASCII';
        if (isset($wordlist[0][0]) && $wordlist[0][0] == '#')
            $encoding = trim(substr(array_shift($wordlist), 1));
        $encodingArray = array_fill(0, count($wordlist), $encoding);

        $wordlist = array_map(array($this, 'toUTF8'), $wordlist, $encodingArray);
        $wordlist = array_map('strtolower', $wordlist);
        $wordlist = array_filter($wordlist, array($this, 'filterWord'));

        $this->loadedLists[$list] = $wordlist;
    }

    // Duplicated from ErebotUtils as we need a way to convert
    // to UTF-8 but without depending on Erebot's inner workings.
    static protected function isUTF8($text)
    {
        // From http://w3.org/International/questions/qa-forms-utf-8.html
        // Pointed out by bitseeker on http://php.net/utf8_encode
        return preg_match('%^(?:
              [\x09\x0A\x0D\x20-\x7E]            # ASCII
            | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
            |  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
            | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
            |  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
            |  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
            | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
            |  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
        )*$%xs', $text);
    }

    // Duplicated from ErebotUtils as we need a way to convert
    // to UTF-8 but without depending on Erebot's inner workings.
    static protected function toUTF8($text, $from='iso-8859-1')
    {
        if (self::isUTF8($text))
            return $text;

        if (!strcasecmp($from, 'iso-8859-1') &&
            function_exists('utf8_encode'))
            return utf8_encode($text);

        if (function_exists('iconv'))
            return iconv($from, 'UTF-8//TRANSLIT', $text);

        if (function_exists('recode'))
            return recode($from.'..utf-8', $text);

        if (function_exists('mb_convert_encoding'))
            return mb_convert_encoding($text, 'UTF-8', $from);

        if (function_exists('html_entity_decode'))
            return html_entity_decode(
                htmlentities($text, ENT_QUOTES, $from),
                ENT_QUOTES, 'UTF-8');

        return $text;
    }


    public function getMinimum()
    {
        return $this->min;
    }

    public function getMaximum()
    {
        return $this->max;
    }

    public function getTarget()
    {
        return $this->target;
    }

    public function getAttemptsCount()
    {
        return $this->attempts;
    }

    public function getInvalidWordsCount()
    {
        return $this->invalidWords;
    }

    public function proposeWord($word)
    {
        $word = strtolower($word);
        if (!preg_match(self::WORD_FILTER, $word))
            return NULL;

        if ($this->min !== NULL && strcmp($this->min, $word) >= 0)
            return NULL;

        if ($this->max !== NULL && strcmp($this->max, $word) <= 0)
            return NULL;

        $ok = FALSE;
        foreach ($this->loadedLists as &$list) {
            if (in_array($word, $list)) {
                $ok = TRUE;
                break;
            }
        }
        unset($list);

        if (!$ok) {
            $this->invalidWords++;
            throw new EAZInvalidWord($word);
        }

        $this->attempts++;
        $cmp = strcmp($this->target, $word);
        if (!$cmp)
            return TRUE;

        if ($cmp < 0)
            $this->max = $word;
        else
            $this->min = $word;

        return FALSE;
    }
}

?>
