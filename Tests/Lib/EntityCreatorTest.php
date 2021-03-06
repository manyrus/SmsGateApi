<?php
/**
 * Created by PhpStorm.
 * User: manyrus
 * Date: 03.03.14
 * Time: 20:24
 */

namespace Manyrus\SmsBundle\Tests\Lib;


use Manyrus\SmsBundle\Lib\EntityCreator;

class EntityCreatorTest extends \PHPUnit_Framework_TestCase {

    public function testCreator() {
        $smsError = $this->getMock('Manyrus\SmsBundle\Entity\SmsError');
        $smsMessage = $this->getMock('Manyrus\SmsBundle\Entity\SmsMessage');

        $entityCreator = new EntityCreator(get_class($smsError), get_class($smsMessage));
        $this->assertEquals($smsError, $entityCreator->createError());
        $this->assertEquals($smsMessage, $entityCreator->createSms());
    }

    public function testException() {
        $smsMessage = $this->getMock('Manyrus\SmsBundle\Entity\SmsMessage');

        $this->setExpectedException('\RuntimeException');
        new EntityCreator('Manyrus\SmsBundle\Entity\SmsError', $smsMessage);
    }

    public function testException2() {
        $smsError = $this->getMock('Manyrus\SmsBundle\Entity\SmsError');

        $this->setExpectedException('\RuntimeException');
        new EntityCreator($smsError, 'Manyrus\SmsBundle\Entity\SmsMessage');
    }
} 