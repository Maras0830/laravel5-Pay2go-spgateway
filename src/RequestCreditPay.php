<?php
/**
 * Created by PhpStorm.
 * User: Maras
 * Date: 2016/9/5
 * Time: 下午1:24
 */

namespace Maras0830\Pay2Go;


/**
 * Class RequestCreditPay
 * @package Maras0830\Pay2Go
 */
class RequestCreditPay
{
    protected $MerchantID_;
    protected $PostData_;
    private $pay2Go;

    /**
     * RequestCreditPay constructor.
     * @param Pay2Go $pay2Go
     */
    public function __construct(Pay2Go $pay2Go)
    {
        $this->pay2Go = $pay2Go;

    }

    /**
     * @return $this
     */
    public function setRequestPayData()
    {
        $this->MerchantID_ = $this->pay2Go->getMerchantID();
        $this->PostData_['RespondType'] = $this->pay2Go->getResponseType();
        $this->PostData_['Version'] = $this->pay2Go->getVersion();

        return $this;
    }

    /**
     * @param int|string $CloseType
     * @return $this
     */
    public function setCreditRequestPayOrCloseByMerchantOrderNo($CloseType = 'Close')
    {
        $this->PostData_['Amt'] = $this->pay2Go->getAmt();
        $this->PostData_['MerchantOrderNo'] = $this->pay2Go->getMerchantOrderNo();
        $this->PostData_['TimeStamp'] = time();
        $this->PostData_['IndexType'] = 1;
        $this->PostData_['TradeNo'] = '';
        $this->PostData_['CloseType'] = $CloseType == 'Pay' ? 1 : 2;

        $post_data_str = http_build_query($this->PostData_);

        $this->PostData_ = trim(bin2hex(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $this->pay2Go->getHashKey(),
            $this->addPadding($post_data_str), MCRYPT_MODE_CBC, $this->pay2Go->getHashIV())));

        return $this;
    }

    /**
     * @param bool $debug_mode
     * @return string
     */
    public function getRequestCreditPayUrl($debug_mode = true)
    {
        return $debug_mode ? 'https://cweb.pay2go.com/API/CreditCard/Close' : 'https://web.pay2go.com/API/CreditCard/Close';
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        return get_object_vars($this);
    }

    /**
     * @param $string
     * @param int $blocksize
     * @return string
     */
    public function addPadding($string, $blocksize = 32) {
        $len = strlen($string);
        $pad = $blocksize - ($len % $blocksize);
        $string .= str_repeat(chr($pad), $pad);
        return $string;
    }


}