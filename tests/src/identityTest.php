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

class   Erebot_Test_Identity
extends \Erebot\Identity
{
    static public function publicPatternize($pattern, $matchDot)
    {
        return self::patternize($pattern, $matchDot);
    }

    static public function publicCanonicalizeHost($host, $c10n, $uncompressed)
    {
        return self::canonicalizeHost($host, $c10n, $uncompressed);
    }
}

class   IdentityTest
extends Erebot_TestEnv_TestCase
{
    public function invalidMasks()
    {
        $masks = array(
            'foo!@',
            'foo@!',
            'foo!',
            'foo@',
            'foo!ident@',
            'foo!@host',
            'foo@host!ident',
            'foo!ident',
            'foo@host',
            'foo!ident@ho$t',
            'foo!ident@host.42',
            'foo!ident@1.2.3',
            'foo!ident@1.2.3.4.5',
            'foo!ident@1:2:3:4:5:6:7',
            'foo!ident@1:2:3:4:5:6:7:8:9',
        );
        $masks = array_map(function ($a) { return array($a); }, $masks);
        return $masks;
    }

    public function matchingData()
    {
        $masks = array(
            'foo!bar@127.0.0.1',
            'foo!bar@127.0.0.1/32',
            'foo!bar@127.0.0.*',
            'foo!bar@::ffff:127.0.0.1',
            'foo!bar@::ffff:127.0.0.1/128',
            'foo!bar@::ffff:127.0.0.*',
            '*!*@*',
            'FOO!*@*',
        );
        $masks = array_map(function ($a) { return array($a); }, $masks);
        return $masks;
    }

    public function patternizeData()
    {
        return array(
            # Input pattern,
            # Output pattern when $dotMatching = TRUE,
            # Output pattern when $dotMatching = FALSE.
            array('foo',    "foo",              "foo"),
            array('#',      "\\#",              "\\#"),
            array('.',      "\\.",              "\\."),
            array('?',      ".",                "[^\\.]"),
            array('*',      ".*",               "[^\\.]*"),
            array('\\',     "\\\\",             "\\\\"),
            array('\\.',    "\\\\\\.",          "\\\\\\."),
            array('\\?',    "\\\\.",            "\\\\[^\\.]"),
            array('\\*',    "\\\\.*",           "\\\\[^\\.]*"),
        );
    }

    public function canonicalizeData()
    {
        return array(
            # Canonicalization method
            # Input
            # Expected output

            # http://tools.ietf.org/html/rfc5952#section-4.1
            array(
                \Erebot\Interfaces\Identity::CANON_IPV6,
                '2001:0db8::0001',
                '2001:db8::1'
            ),
            array(
                \Erebot\Interfaces\Identity::CANON_IPV6,
                '2001:0db8:0000:4:5:6:7:8',
                '2001:db8:0:4:5:6:7:8'
            ),

            # http://tools.ietf.org/html/rfc5952#section-4.2.1
            array(
                \Erebot\Interfaces\Identity::CANON_IPV6,
                '2001:db8:0:0:0:0:2:1',
                '2001:db8::2:1'
            ),
            array(
                \Erebot\Interfaces\Identity::CANON_IPV6,
                '2001:db8::0:1',
                '2001:db8::1'
            ),

            # http://tools.ietf.org/html/rfc5952#section-4.2.2
            array(
                \Erebot\Interfaces\Identity::CANON_IPV6,
                '2001:db8:0:1:1:1:1:1',
                '2001:db8:0:1:1:1:1:1'
            ),
            array(
                \Erebot\Interfaces\Identity::CANON_IPV6,
                '2001:db8::1:1:1:1:1',
                '2001:db8:0:1:1:1:1:1'
            ),

            # http://tools.ietf.org/html/rfc5952#section-4.2.3
            array(
                \Erebot\Interfaces\Identity::CANON_IPV6,
                '2001:0:0:1:0:0:0:1',
                '2001:0:0:1::1'
            ),
            array(
                \Erebot\Interfaces\Identity::CANON_IPV6,
                '2001:db8:0:0:1:0:0:1',
                '2001:db8::1:0:0:1'
            ),

            # http://tools.ietf.org/html/rfc5952#section-4.3
            array(
                \Erebot\Interfaces\Identity::CANON_IPV6,
                '2001:db8:A:B:C:D:E:F',
                '2001:db8:a:b:c:d:e:f'
            ),

            # http://tools.ietf.org/html/rfc5952#section-5
            array(
                \Erebot\Interfaces\Identity::CANON_IPV4,
                '0:0:0:0:0:ffff:192.0.2.1',
                '::ffff:192.0.2.1'
            ),
            array(
                \Erebot\Interfaces\Identity::CANON_IPV4,
                '::',
                '::0.0.0.0'
            ),
        );
    }

    /**
     * @covers \Erebot\Identity
     */
    public function testNominalCase()
    {
        $identity = new \Erebot\Identity('foo!ident@host');
        $this->assertSame('foo',    (string) $identity);
        $this->assertSame('foo',    $identity->getNick());
        $this->assertSame('ident',  $identity->getIdent());
        $this->assertSame(
            'host',
            $identity->getHost(\Erebot\Interfaces\Identity::CANON_IPV4)
        );
    }

    /**
     * @covers \Erebot\Identity
     */
    public function testNominalCase2()
    {
        $identity = new \Erebot\Identity('foo');
        $this->assertSame('foo',    (string) $identity);
        $this->assertSame('foo',    $identity->getNick());
        $this->assertSame(NULL,     $identity->getIdent());
        $this->assertSame(
            NULL,
            $identity->getHost(\Erebot\Interfaces\Identity::CANON_IPV4)
        );
    }

    /**
     * @dataProvider        invalidMasks
     * @expectedException   \Erebot\InvalidValueException
     * @covers              \Erebot\Identity
     */
    public function testInvalidMasks($mask)
    {
        new \Erebot\Identity($mask);
    }

    /**
     * @dataProvider matchingData
     * @cover \Erebot\Identity::match
     */
    public function testMatching($pattern)
    {
        $identity = new \Erebot\Identity('foo!bar@127.0.0.1');
        $this->assertTrue(
            $identity->match($pattern, new \Erebot\IrcCollator\ASCII()),
            "Did not match '$pattern'"
        );
    }

    /**
     * @dataProvider    patternizeData
     * @cover           \Erebot\Identity::patternize
     */
    public function testPatternizeNoDotMatching($input, $expectedDot, $expectedNoDot)
    {
        $output = Erebot_Test_Identity::publicPatternize($input, FALSE);
        $this->assertSame('#^'.$expectedNoDot.'$#Di', $output);
    }

    /**
     * @dataProvider    patternizeData
     * @cover           \Erebot\Identity::patternize
     */
    public function testPatternizeDotMatching($input, $expectedDot, $expectedNoDot)
    {
        $output = Erebot_Test_Identity::publicPatternize($input, TRUE);
        $this->assertSame('#^'.$expectedDot.'$#Di', $output);
    }

    /**
     * @dataProvider    canonicalizeData
     * @cover           \Erebot\Identity::canonicalizeHost
     */
    public function testCanonicalize($c10n, $input, $expected)
    {
        $this->assertSame(
            $expected,
            Erebot_Test_Identity::publicCanonicalizeHost($input, $c10n, false)
        );
    }
}
