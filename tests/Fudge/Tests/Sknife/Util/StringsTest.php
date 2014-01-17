<?php

namespace Fudge\Tests\Sknife\Util;

use Fudge\Sknife\Util\Strings;

/**
 * Test suite for Fudge\Sknife\Util\Strings
 *
 * @author Yohann Marillet <yohann.marillet@gmail.com>
 * @since 17/01/14
 */
class StringsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {

    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {

    }

    /**
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public function testToCamelFromLowerCase()
    {
        $this->assertSame('FromLowerCase', Strings::toCamel('from lower case'));
    }

    /**
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public function testToCamelFromUpperCase()
    {
        $this->assertSame('FromUpperCase', Strings::toCamel('FROM UPPER CASE'));
    }

    /**
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public function testToCamelFromMixedCase()
    {
        $this->assertSame('FromMixedCase', Strings::toCamel('From mIXED cAsE'));
    }

    /**
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public function testToCamelWithNumbers()
    {
        $this->assertSame('WithNumbers123', Strings::toCamel('with numbers 123'));
    }

    /**
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public function testToCamelWithInvalidChars()
    {
        $this->assertSame('WithInvalidChars', Strings::toCamel('with ! invalid $ chars'));
    }

    /**
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public function testToCamelWithInvalidStartChar()
    {
        $this->assertSame('_WithInvalidStartChar', Strings::toCamel('*with invalid start char'));
    }

    /**
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public function testToCamelWithMultipleInvalidStartChar()
    {
        $this->assertSame('____WithMultipleInvalidStartChar', Strings::toCamel('$!%*with multiple invalid start char'));
    }

    /**
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public function testToCamelWithUnicodeStartChar()
    {
        $this->assertSame('___WithUnicodeStartChar', Strings::toCamel('ù with unicode start char'));
    }

    /**
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public function testToCamelWithSpacesOrInvalidCharsInTheEnd()
    {
        $this->assertSame(
                'WithSpacesOrInvalidCharsInTheEnd',
                Strings::toCamel('with spaces or invalid chars in the end * ')
        );
    }

    /**
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public function testToCamelWithDifferentStartChar()
    {
        $this->assertSame('!!!!WithDifferentStartChar', Strings::toCamel('$!%*with different start char', '!'));
    }

    /**
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public function testToLowerCamel()
    {
        $this->assertSame('testLowerCamel', Strings::toLowerCamel('Test lower camel'));
    }

    /**
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public function testGetClassOrTypeWithScalars()
    {
        $this->assertSame('string', Strings::getClassOrType('test'));
        $this->assertSame('integer', Strings::getClassOrType(1));
        $this->assertSame('array', Strings::getClassOrType([]));
        $this->assertSame('double', Strings::getClassOrType(42.6));
    }

    /**
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public function testGetClassOrTypeWithObject()
    {
        $this->assertSame(get_class($this), Strings::getClassOrType($this));
    }

    /**
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public function testSlugify()
    {
        $this->assertSame('slugify-me--', Strings::slugify('Slugify me !'));
    }

    /**
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public function testUnslugify()
    {
        $this->assertSame('Slugify me  ', Strings::unslugify('slugify-me--'));
    }

    /**
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public function testToGetterBase()
    {
        $this->assertSame('getField', Strings::toGetter('field'));
        $this->assertSame('getFieldWithUnderscore', Strings::toGetter('field_with_underscore'));
    }

    /**
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public function testToGetterWithUnderscores()
    {
        $this->assertSame('getFieldWithUnderscore', Strings::toGetter('field_with_underscore'));
    }

    /**
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public function testToSetterBase()
    {
        $this->assertSame('setField', Strings::toSetter('field'));
    }

    /**
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public function testToSetterWithUnderscores()
    {
        $this->assertSame('setFieldWithUnderscore', Strings::toSetter('field_with_underscore'));
    }
}
 