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

require_once(
    dirname(dirname(__FILE__)) .
    DIRECTORY_SEPARATOR . 'testenv' .
    DIRECTORY_SEPARATOR . 'bootstrap.php'
);

class   URITest
extends PHPUnit_Framework_TestCase
{
    public function testParsing()
    {
        $base   = new Erebot_URI("http://u:p@a:8080/b/c/d;p?q#r");
        $this->assertEquals("http",     $base->getScheme());
        $this->assertEquals("u:p",      $base->getUserInfo());
        $this->assertEquals("a",        $base->getHost());
        $this->assertEquals(8080,       $base->getPort());
        $this->assertEquals("/b/c/d;p", $base->getPath());
        $this->assertEquals("q",        $base->getQuery());
        $this->assertEquals("r",        $base->getFragment());
    }

    public function testToString()
    {
        $original   = "http://u:p@a:8080/b/c/d;p?q#r";
        $base       = new Erebot_URI($original);
        $this->assertEquals($original, (string) $base);
    }

    public function testNormalization()
    {
        $original   = "bAr://LOCALHOST/../a/b/./c/../d";
        $normed     = "bar://localhost/a/b/d";
        $uri        = new Erebot_URI($original);
        $this->assertEquals($original, $uri->toURI(FALSE));
        $this->assertEquals($normed, $uri->toURI(TRUE));
        $this->assertEquals($normed, $uri->toURI());
        $this->assertEquals($normed, (string) $uri);
    }

    public function normalResults()
    {
        return array(
            # 5.4.1.  Normal Examples
            array("g:h",        "g:h"),
            array("g",          "http://a/b/c/g"),
            array("./g",        "http://a/b/c/g"),
            array("g/",         "http://a/b/c/g/"),
            array("/g",         "http://a/g"),
            array("//g",        "http://g"),
            array("?y",         "http://a/b/c/d;p?y"),
            array("g?y",        "http://a/b/c/g?y"),
            array("#s",         "http://a/b/c/d;p?q#s"),
            array("g#s",        "http://a/b/c/g#s"),
            array("g?y#s",      "http://a/b/c/g?y#s"),
            array(";x",         "http://a/b/c/;x"),
            array("g;x",        "http://a/b/c/g;x"),
            array("g;x?y#s",    "http://a/b/c/g;x?y#s"),
            array("",           "http://a/b/c/d;p?q"),
            array(".",          "http://a/b/c/"),
            array("./",         "http://a/b/c/"),
            array("..",         "http://a/b/"),
            array("../",        "http://a/b/"),
            array("../g",       "http://a/b/g"),
            array("../..",      "http://a/"),
            array("../../",     "http://a/"),
            array("../../g",    "http://a/g"),

            # 5.4.2.  Abnormal Examples
            array("../../../g",     "http://a/g"),
            array("../../../../g",  "http://a/g"),
            array("/./g",           "http://a/g"),
            array("/../g",          "http://a/g"),
            array("g.",             "http://a/b/c/g."),
            array(".g",             "http://a/b/c/.g"),
            array("g..",            "http://a/b/c/g.."),
            array("..g",            "http://a/b/c/..g"),
            array("./../g",         "http://a/b/g"),
            array("./g/.",          "http://a/b/c/g/"),
            array("g/./h",          "http://a/b/c/g/h"),
            array("g/../h",         "http://a/b/c/h"),
            array("g;x=1/./y",      "http://a/b/c/g;x=1/y"),
            array("g;x=1/../y",     "http://a/b/c/y"),
            array("g?y/./x",        "http://a/b/c/g?y/./x"),
            array("g?y/../x",       "http://a/b/c/g?y/../x"),
            array("g#s/./x",        "http://a/b/c/g#s/./x"),
            array("g#s/../x",       "http://a/b/c/g#s/../x"),
            array("http:g",         "http:g"),
        );
    }

    /**
     * @dataProvider normalResults
     */
    public function testNormalResolution($reference, $targetURI)
    {
        $base   = new Erebot_URI("http://a/b/c/d;p?q");
        $target = $base->relative($reference);
        $this->assertEquals($targetURI, (string) $target, $reference);
    }
}

