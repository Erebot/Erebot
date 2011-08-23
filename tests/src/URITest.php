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

class   URITest
extends PHPUnit_Framework_TestCase
{
    /**
     * @covers Erebot_URI
     */
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

    /**
     * @covers Erebot_URI::__toString
     */
    public function testToString()
    {
        $original   = "http://u:p@a:8080/b/c/d;p?q#r";
        $base       = new Erebot_URI($original);
        $this->assertEquals($original, (string) $base);
    }

    /**
     * @covers Erebot_URI
     */
    public function testCaseNormalization()
    {
        // The scheme and host components must be lowercased.
        // The dot segments must be handled correctly.
        $original   = "bAr://LOCALHOST/../a/b/./c/../d";
        $normed     = "bar://localhost/a/b/d";
        $uri        = new Erebot_URI($original);
        $this->assertEquals($original, $uri->toURI(TRUE));
        $this->assertEquals($normed, $uri->toURI(FALSE));
        $this->assertEquals($normed, $uri->toURI());
        $this->assertEquals($normed, (string) $uri);
    }

    /**
     * @covers Erebot_URI
     */
    public function testPercentEncodingNormalisation()
    {
        // The hexadecimal digits used for percent-encoded characters
        // must be UPPERCASED.
        // Percent-encoded characters belonging to the "unreserved" set
        // must be replaced by their actual representation.
        $uri = new Erebot_URI(
            "http://%41%20%3a%62:%63%40%64@".
            "loc%61l%2dhost.example%2ecom/".
            "%7e%2e%2f/foobar"
        );
        $this->assertEquals(
            "http://A%20%3Ab:c%40d@local-host.example.com/~.%2F/foobar",
            (string) $uri
        );
    }
}

class   RelativeURITest
extends PHPUnit_Framework_TestCase
{
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
     * @dataProvider    normalResults
     * @covers          Erebot_URI::relative
     */
    public function testNormalResolution($reference, $targetURI)
    {
        $base   = new Erebot_URI("http://a/b/c/d;p?q");
        $target = $base->relative($reference);
        $this->assertEquals($targetURI, (string) $target, $reference);
    }
}

class   ParseURLTest
extends PHPUnit_Framework_TestCase
{
    public function userinfoProvider()
    {
        return array(
            array("a", "a", NULL),
            array("a:", "a", NULL),     // Theorically, pass should be "" here.
            array("a:b:c", "a", "b:c"), // Invalid based on the RFC.
            array(":b", NULL, "b"),     // Also invalid.
        );
    }

    /**
     * @dataProvider    userinfoProvider
     * @covers          Erebot_URI::asParsedURL
     */
    public function testParseURLCompatibilityQuirks($userinfo, $user, $pass)
    {
        $uri = new Erebot_URI("http://".$userinfo."@localhost/");

        // Try requesting those specific components first.
        if ($user !== NULL)
            $this->assertEquals($user, $uri->asParsedURL(PHP_URL_USER));
        else
            $this->assertNull($uri->asParsedURL(PHP_URL_USER));

        if ($pass !== NULL)
            $this->assertEquals($pass, $uri->asParsedURL(PHP_URL_PASS));
        else
            $this->assertNull($uri->asParsedURL(PHP_URL_PASS));


        // Now try with a global retrieval.
        $components = $uri->asParsedURL();
        if ($user !== NULL) {
            $this->assertEquals($user, $components['user']);
            $this->assertEquals($user, $components[PHP_URL_USER]);
        }
        else {
            $this->assertFalse(isset($components['user']));
            $this->assertFalse(isset($components[PHP_URL_USER]));
        }

        if ($pass !== NULL) {
            $this->assertEquals($pass, $components['pass']);
            $this->assertEquals($pass, $components[PHP_URL_PASS]);
        }
        else {
            $this->assertFalse(isset($components['pass']));
            $this->assertFalse(isset($components[PHP_URL_PASS]));
        }
    }
}

class   HostTest
extends PHPUnit_Framework_TestCase
{
    public function validProvider()
    {
        $values = array();
        $data   = array(
            '[2a01:e35:2e30:c120::28]',                 // irc.secours.iiens.net
            '[2a01:e34:ee8f:6730:201:2ff:fe01:e964]',   // erebot.net
            '[::1]',                                    // ip6-loopback
            "localhost",
            "127.0.0.1",
        );
        foreach ($data as $host)
            $values[] = array($host);
        return $values;
    }

    /**
     * @dataProvider    validProvider
     * @covers          Erebot_URI
     */
    public function testNoPort($host)
    {
        try {
            $uri = new Erebot_URI('http://'.$host.'/');
        }
        catch (Erebot_InvalidValueException $e) {
            $this->fail("'".$host."' and no port: ".$e->getMessage());
        }
    }

    /**
     * @dataProvider    validProvider
     * @covers          Erebot_URI
     */
    public function testWithPort($host)
    {
        try {
            $uri = new Erebot_URI('http://'.$host.':42/');
        }
        catch (Erebot_InvalidValueException $e) {
            $this->fail("'".$host."' and a port: ".$e->getMessage());
        }
    }
}

