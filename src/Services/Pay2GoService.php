<?php
namespace Maras0830\Pay2Go\Services;
use Maras0830\Pay2Go\Pay2Go;

/**
 * Created by PhpStorm.
 * User: Maras
 * Date: 2016/9/6
 * Time: 下午4:12
 */

class Pay2GoService
{
    /**
     * Pay2GoService constructor.
     * @param null $MerchantID
     * @param null $HashKey
     * @param null $HashIV
     */
    public function __construct($MerchantID = null, $HashKey = null, $HashIV = null)
    {
        $this->pay2go = new Pay2Go(config('pay2go.MerchantID'), config('pay2go.HashKey'), config('pay2go.HashIV'));
    }

    /**
     * 設置訂單
     *
     * @param $order_id
     * @param $price
     * @param $descriptions
     * @param $email
     * @return Pay2Go
     */
    public function setOrder($order_id, $price, $descriptions, $email)
    {
        return $this->pay2go->setOrder($order_id, $price, $descriptions, $email);
    }

    /**
     * 送出訂單
     *
     * @return string
     */
    public function submitOrder()
    {
        return $this->pay2go->submitOrder();
    }

    /**
     * 信用卡請款
     *
     * @param $order_unique_id
     * @param $amt
     * @return string
     */
    public function requestPaymentPay($order_unique_id, $amt)
    {
        return $this->pay2go->requestPaymentPay($order_unique_id, $amt);
    }

}
