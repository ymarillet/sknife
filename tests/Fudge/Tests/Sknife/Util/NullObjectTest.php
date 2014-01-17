<?php
namespace Fudge\Tests\Sknife\Util;

use Doctrine\Tests\Common\Annotations\Null;
use Fudge\Sknife\Util\NullObject;

/**
 *
 * @author Yohann Marillet <yohann.marillet@gmail.com>
 * @since 17/01/14
 */
class NullObjectTest extends \PHPUnit_Framework_TestCase
{
    /** @var NullObject */
    protected $nullObject;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->nullObject = new NullObject();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        unset($this->nullObject);
    }

    /**
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public function testArrayAccess()
    {
        $this->assertFalse(isset($this->nullObject[0]));
        $this->assertEquals(new NullObject(), $this->nullObject[0]);
    }

    /**
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public function testCountable()
    {
        $this->assertSame(0, count($this->nullObject));
    }

    /**
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public function testIterator()
    {
        $nullObject = new NullObject();
        $this->assertEquals($nullObject, $this->nullObject->next());
        $this->assertEquals($nullObject, $this->nullObject->current());
        $this->assertSame(0, $this->nullObject->key());
        $this->assertFalse($this->nullObject->valid());
    }

    /**
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public function testFunction() {
        $nullObject = new NullObject();
        $this->assertEquals($nullObject, $this->nullObject->unknownFunction());
        $this->assertEquals($nullObject, NullObject::unknownStaticFunction());
    }

    /**
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public function testProperty() {
        $nullObject = new NullObject();
        $this->assertEquals($nullObject, $this->nullObject->unknownProperty);
    }

    /**
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public function testCast() {
        $this->assertSame('', strval($this->nullObject));
    }

    /**
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public function testPropertyTesters() {
        $this->assertSame(false, isset($this->nullObject->unknownProperty));
        $this->assertSame(true, empty($this->nullObject->unknownProperty));
    }
}
 