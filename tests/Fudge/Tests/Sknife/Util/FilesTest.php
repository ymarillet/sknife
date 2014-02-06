<?php
namespace Fudge\Tests\Sknife\Util;

use Fudge\Sknife\Util\Files;

/**
 * 
 * @author Yohann Marillet <yohann.marillet@gmail.com>
 * @since 18/01/14
 */
class FilesTest extends \PHPUnit_Framework_TestCase
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
    public function testSanitizeInvalidCharacter()
    {
        $this->assertSame('', Files::sanitize(''));
    }
}
 