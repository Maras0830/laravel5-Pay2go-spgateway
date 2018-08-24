<?php

use Maras0830\Pay2Go\Pay2Go;

require_once ('vendor/autoload.php');

new test();

class test
{
    private $spgateway;

    /**
     * test constructor.
     */
    public function __construct()
    {
        $this->spgateway = new Pay2Go('MS34629683', 'BKEoKZTgRLnepgkoGfPJpPq0mIVovbjl', 'rP835hFAZ55rPkUS');

        $result = $this->spgateway->requestPaymentPay('1234567', 1234);

        var_dump($result);

        die();
//        return view('admin.cash.request_pay')->with(compact('result'));
    }



}