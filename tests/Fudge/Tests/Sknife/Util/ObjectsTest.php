<?php
namespace Fudge\Tests\Sknife\Util;

use Fudge\Sknife\Util\Objects;
use Fudge\Tests\Sknife\Util\Objects\DeepCopy;

/**
 * 
 * @author Yohann Marillet <yohann.marillet@gmail.com>
 * @since 17/01/14
 */
class ObjectsTest extends \PHPUnit_Framework_TestCase 
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
    public function testDeepCopy()
    {
        $o = new DeepCopy();
        /** @var DeepCopy $r */
        $r = Objects::deepCopy($o);

        $this->assertEquals($o, $r);
        $this->assertNotSame($o, $r);

        $this->assertEquals($o->getInjection(), $r->getInjection());
        $this->assertNotSame($o->getInjection(), $r->getInjection());
    }
}
 