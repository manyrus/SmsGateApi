<?php
/**
 * Created by PhpStorm.
 * User: manyrus
 * Date: 02.02.14
 * Time: 21:43
 */

namespace Manyrus\SmsBundle\Lib\Event;


use Manyrus\SmsBundle\Entity\SmsMessage;
use Manyrus\SmsBundle\Lib\SmsException;
use Symfony\Component\EventDispatcher\Event;

class SmsEvent extends Event{
    /**
     * @var SmsMessage
     */
    private $message;

    /**
     * @var SmsException
     */
    private $exception;

    function __construct($message, $exception = null)
    {
        $this->message = $message;
        $this->exception = $exception;
    }

    /**
     * @return SmsMessage
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return \Manyrus\SmsBundle\Lib\SmsException
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * @param \Manyrus\SmsBundle\Lib\SmsException $exception
     */
    public function setException($exception)
    {
        $this->exception = $exception;
    }


}