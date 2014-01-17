<?php
namespace Fudge\Tests\Sknife\Util;

use Fudge\Sknife\Util\Arrays;
use Fudge\Tests\Sknife\Util\Arrays\ReassignKeyFromObject;
use Fudge\Tests\Sknife\Util\Arrays\ReassignKeyFromObjectProtected;
use Fudge\Tests\Sknife\Util\Arrays\ReassignKeyFromObjectPublic;

/**
 * Test suite for Fudge\Sknife\Util\Arrays
 *
 * @author Yohann Marillet <yohann.marillet@gmail.com>
 * @since 17/01/14
 */
class ArraysTest extends \PHPUnit_Framework_TestCase
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
     * @expectedException \Fudge\Sknife\Exception\ForeachableException
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public function testFlattenWithNonArray()
    {
        Arrays::flatten('fakeParam', 'key');
    }

    /**
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public function testFlattenEmptyArray()
    {
        $result = Arrays::flatten([], 'key');
        $this->assertSame([], $result);
    }

    /**
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public function testFlattenValuesOnly()
    {
        $param = [
                [
                        'fruit' => 'apple',
                        'car' => 'mercedes',
                ],
                [
                        'fruit' => 'orange',
                        'car' => 'renault',
                ],
                [
                        'fruit' => 'lemon',
                        'car' => 'skoda',
                ]
        ];
        $result = Arrays::flatten($param, 'fruit');
        $this->assertSame(['apple', 'orange', 'lemon'], $result);
    }

    /**
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public function testFlattenWithKeys()
    {
        $param = [
                [
                        'fruit' => 'apple',
                        'car' => 'mercedes',
                ],
                [
                        'fruit' => 'orange',
                        'car' => 'renault',
                ],
                [
                        'fruit' => 'lemon',
                        'car' => 'skoda',
                ]
        ];
        $result = Arrays::flatten($param, 'car', 'fruit');
        $this->assertSame(['apple' => 'mercedes', 'orange' => 'renault', 'lemon' => 'skoda'], $result);
    }

    /**
     * @expectedException \Fudge\Sknife\Exception\ForeachableException
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public function testReassignKeyWithNonArray()
    {
        Arrays::reassignKey('fakeParam', 'key');
    }

    /**
     * @expectedException \Fudge\Sknife\Exception\BusinessException
     * @expectedExceptionMessage Key "" does not exist in the second dimension of this array
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public function testReassignKeyWithEmptyKey()
    {
        $param = [
                [
                        'fruit' => 'apple',
                        'car' => 'mercedes',
                ],
                [
                        'fruit' => 'orange',
                        'car' => 'renault',
                ],
                [
                        'fruit' => 'lemon',
                        'car' => 'skoda',
                ]
        ];
        Arrays::reassignKey($param, '');
    }

    /**
     * @expectedException \Fudge\Sknife\Exception\BusinessException
     * @expectedExceptionMessage Key "tree" does not exist in the second dimension of this array
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public function testReassignKeyWithUnknownKey()
    {
        $param = [
                [
                        'fruit' => 'apple',
                        'car' => 'mercedes',
                ],
                [
                        'fruit' => 'orange',
                        'car' => 'renault',
                ],
                [
                        'fruit' => 'lemon',
                        'car' => 'skoda',
                ]
        ];
        Arrays::reassignKey($param, 'tree');
    }

    /**
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public function testReassignKey()
    {
        $param = [
                [
                        'fruit' => 'apple',
                        'car' => 'mercedes',
                ],
                [
                        'fruit' => 'orange',
                        'car' => 'renault',
                ],
                [
                        'fruit' => 'lemon',
                        'car' => 'skoda',
                ]
        ];
        $result = Arrays::reassignKey($param, 'fruit');
        $this->assertSame(
                [
                        'apple' => ['fruit' => 'apple', 'car' => 'mercedes',],
                        'orange' => ['fruit' => 'orange','car' => 'renault',],
                        'lemon' => ['fruit' => 'lemon','car' => 'skoda',]
                ],
                $result
        );
    }

    /**
     * @expectedException \Fudge\Sknife\Exception\ForeachableException
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public function testReassignKeyFromObjectsWithNonArray()
    {
        Arrays::reassignKeyFromObjects('fakeParam', 'key');
    }

    /**
     * @param string $type
     *
     * @return array
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    protected function getReassignKeyObjectArray($type='protected') {
        if($type=='public') {
            $o1 = new ReassignKeyFromObjectPublic();
        } else {
            $o1 = new ReassignKeyFromObjectProtected();
        }

        $o2 = clone $o1;
        $o3 = clone $o1;

        if($type=='public') {
            $o1->fruit = 'apple';
            $o1->car = 'mercedes';
            $o2->fruit = 'orange';
            $o2->car = 'renault';
            $o3->fruit = 'lemon';
            $o3->car = 'skoda';
        } else {
            $o1->setFruit('apple')->setCar('mercedes');
            $o2->setFruit('orange')->setCar('renault');
            $o3->setFruit('lemon')->setCar('skoda');
        }


        return [$o1,$o2,$o3];
    }

    /**
     * @expectedException \Fudge\Sknife\Exception\BusinessException
     * @expectedExceptionMessage Property "" does not exist in the second dimension of this array
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public function testReassignKeyFromObjectWithEmptyKey()
    {
        $param = $this->getReassignKeyObjectArray();
        Arrays::reassignKeyFromObjects($param, '');
    }

    /**
     * @expectedException \Fudge\Sknife\Exception\BusinessException
     * @expectedExceptionMessage Property "tree" does not exist in the second dimension of this array
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public function testReassignKeyFromObjectWithUnknownKey()
    {
        $param = $this->getReassignKeyObjectArray();
        Arrays::reassignKeyFromObjects($param, 'tree');
    }

    /**
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public function testReassignKeyFromObjectsWithProtectedObject()
    {
        $param = $this->getReassignKeyObjectArray();
        $result = Arrays::reassignKeyFromObjects($param, 'fruit');
        $this->assertSame(
                [
                        'apple' => $param[0],
                        'orange' => $param[1],
                        'lemon' => $param[2],
                ],
                $result
        );
    }

    /**
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public function testReassignKeyFromObjectsWithPublicObject()
    {
        $param = $this->getReassignKeyObjectArray('public');
        $result = Arrays::reassignKeyFromObjects($param, 'fruit');
        $this->assertSame(
                [
                        'apple' => $param[0],
                        'orange' => $param[1],
                        'lemon' => $param[2],
                ],
                $result
        );
    }
}
 