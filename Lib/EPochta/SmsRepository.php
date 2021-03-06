<?php
/**
 * Created by PhpStorm.
 * User: manyrus
 * Date: 29.01.14
 * Time: 20:49
 */

namespace Manyrus\SmsBundle\Lib\EPochta;


use Manyrus\SmsBundle\Entity\ErrorManager;
use Manyrus\SmsBundle\Entity\SmsMessage;
use Manyrus\SmsBundle\Lib\ApiErrors;
use Manyrus\SmsBundle\Lib\ApiType;
use Manyrus\SmsBundle\Lib\Base\ISmsRepository;
use Manyrus\SmsBundle\Lib\EntityCreator;
use Manyrus\SmsBundle\Lib\SmsException;
use Manyrus\SmsBundle\Lib\Status;


class SmsRepository extends BaseEPochtaRepository implements ISmsRepository{

    const SEND_SMS = 'sendSMS';
    const GET_PRICE = 'checkCampaignPriceGroup';
    const GET_STATUS = 'getCampaignDeliveryStats';
    const GET_COST = 'checkCampaignPriceGroup';



    /**
     * @var EntityCreator
     */
    private $entityCreator;

    /**
     * @param \Manyrus\SmsBundle\Lib\EntityCreator $entityCreator
     */
    public function setEntityCreator($entityCreator)
    {
        $this->entityCreator = $entityCreator;
    }


    /**
     * @param SmsMessage $sms
     * @throws \RuntimeException
     * @throws \Manyrus\SmsBundle\Lib\SmsException
     * @return mixed
     */
    public function send(SmsMessage $sms)
    {
        $request = array();
        $request['sender'] = $sms->getFrom();
        $request['text'] = $sms->getMessage();
        $request['phone'] = $sms->getTo();

        if(in_array(null, $request)) {
            throw new \RuntimeException();
        }

        $result = $this->sendRequest($request, self::SEND_SMS);

        if(!empty($result['error'])) {
            switch($result['code']) {
                case 304:
                    $exception= new SmsException(ApiErrors::LOW_BALANCE, $result['code']);
                    break;
                case 305:
                    $exception = new SmsException(ApiErrors::LOW_BALANCE, $result['code']);
                    break;
                case 103:
                    $exception = new SmsException(ApiErrors::LOW_BALANCE, $result['code']);
                    break;
                default:
                    $exception = new SmsException(ApiErrors::UNKNOWN_ERROR, $result['code']);
                    break;
            }
            throw $exception;
        }

        $sms->setMessageId($result['result']['id']);
        $sms->setStatus(Status::IN_PROCESS);
        return $sms;
    }

    /**
     * @param SmsMessage $sms
     * @throws \Manyrus\SmsBundle\Lib\SmsException
     * @return mixed
     */
    public function updateStatus(SmsMessage $sms)
    {
        $request = array();
        $request['id'] = $sms->getMessageId();

        $result = $this->sendRequest($request, self::GET_STATUS);

        if(!empty($result['error'])){
            if($result['error'] == 'error_invalid_id') {
                throw new SmsException(ApiErrors::BAD_ID, $result['code']);
            }
            throw new SmsException(ApiErrors::UNKNOWN_ERROR, $result['code']);
        }

        $status = $result['result']['status'][0];
        if($status == '0') {
            $sms->setStatus(Status::IN_PROCESS);
        } else if($status == 'SENT') {
            $sms->setStatus(Status::SENT);
        } else if($status == 'DELIVERED') {
            $sms->setStatus(Status::DELIVERED);
        } else if($status == 'UNDELIVERED') {
            $sms->setStatus(Status::UNDELIVERED);
        } elseif($status == 'SPAM') {
            $sms->setStatus(Status::SPAM);
        } elseif($status == 'INVALID_PHONE_NUMBER') {
            $sms->setStatus(Status::ERROR);
            $sms->setError($this->entityCreator->createError(ApiErrors::BAD_ADDRESSER, $sms));
        }

        return $sms;
    }

    /**
     * @param SmsMessage $message
     * @throws \Manyrus\SmsBundle\Lib\SmsException
     * @return mixed
     */
    public function updateCost(SmsMessage $message)
    {

        $request = array();
        $request['sender'] = $message->getFrom();
        $request['text'] = $message->getMessage();
        $request['phones'] = json_encode(array(array($message->getTo())));

        $result = $this->sendRequest($request, self::GET_PRICE);

        $message->setCost($result['result']['price']);

        return $message;
    }




    /**
     * @see Manyrus\SmsBundle\Lib\ApiType
     * @return mixed
     */
    public function getApiType()
    {
        return ApiType::EPOCHTA_API;
    }
}