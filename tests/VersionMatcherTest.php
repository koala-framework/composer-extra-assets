<?php
namespace Kwf\ComposerExtraAssets;

class VersionMatcherTest extends \PHPUnit_Framework_TestCase
{
    public function versions()
    {
        return array(
            array('1.11.9', '1.11.9', '~1.11'),
            array(false, '2.0.0', '~1.11'),
            array(false, '~2.0', '~1.11'),
            array(false, '~2.0', '1.11.1'),
            array('~1.12', '~1.12', '~1.11'),
            array('~1.12', '~1.11', '~1.12'),
            array('~1.12', '>1.11', '~1.12'),
            array('>2.0', '>1.11', '>2.0'),
            array('>1.11', '>1.11', '*'),
            array('>1.11', '*', '>1.11'),
            array('*', '*', '*'),
            array('foo/foo#master', 'foo/foo#master', 'foo/foo#master'),
            array(false, '1.0', 'foo/foo#master'),
            array('4.2.1-883', '4.2.1-883', '4.2.*'),
        );
    }

    /**
    * @dataProvider versions
    */
    public function testVersions($expectedVersion, $v1, $v2)
    {
        $this->assertEquals($expectedVersion, VersionMatcher::matchVersions($v1, $v2));
    }
}
