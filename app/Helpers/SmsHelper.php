<?php

namespace App\Helpers;

use Exception;

use Twilio\Rest\Client;

class SmsHelper
{
    static function send_sms($sms, $to)
    {
        $account_sid = config('twilio.TWILIO_ACCOUNT_SID');
        $auth_token = config('twilio.TWILIO_AUTH_TOKEN');
        $twilio_number = config('twilio.TWILIO_NUMBER');

        $client = new Client(
            $account_sid,
            $auth_token
        );

        try {
            return $response = ($client->messages->create(
                $to,
                array(
                    'from' => $twilio_number,
                    'body' => $sms
                )
            ));
        } catch (Exception $e)
        {
            return ($e->getMessage());
        }
    }
}
