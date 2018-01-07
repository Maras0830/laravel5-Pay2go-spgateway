<?php
namespace Maras0830\Pay2Go;

use Carbon\Carbon;
use Maras0830\Pay2Go\Validation\OrderValidatesRequests;

/**
 * Class Pay2Go
 * @package Maras0830\Pay2Go
 */
class Pay2Go
{
    use OrderValidatesRequests;

    protected $CreditRed;
    protected $InstFlag;
    private $CheckValue;
    private $MerchantID;
    private $HashKey;
    private $HashIV;

    protected $Pay2GoUrl;
    protected $Version;
    protected $CREDIT;
    protected $TimeStamp;
    protected $UNIONPAY;
    protected $WEBATM;
    protected $VACC;
    protected $BARCODE;
    protected $CVS;
    protected $CUSTOM;
    protected $LangType;
    protected $RespondType;
    protected $TradeLimit;
    protected $ExpireDate;
    protected $ExpireTime;
    protected $OrderComment;
    protected $LoginType;
    protected $EmailModify;
    protected $ClientBackURL;
    protected $CustomerURL;
    protected $NotifyURL;
    protected $ReturnURL;
    protected $MerchantOrderNo;
    protected $ItemDesc;
    protected $Email;
    protected $Amt;

    /**
     * Pay2Go constructor.
     * @param $MerchantID
     * @param $HashKey
     * @param $HashIV
     */
    public function __construct($MerchantID = null, $HashKey = null, $HashIV = null)
    {
        $this->MerchantID = ($MerchantID != null ? $MerchantID : config('pay2go.MerchantID'));
        $this->HashKey = ($HashKey != null ? $HashKey : config('pay2go.HashKey'));
        $this->HashIV = ($HashIV != null ? $HashIV : config('pay2go.HashIV'));

        $this->setPay2GoUrl(config('pay2go.Debug'));
        $this->setExpireDate(config('pay2go.ExpireDays'));
        $this->setExpireTime(config('pay2go.ExpireTime'));
        $this->setLoginType(config('pay2go.LoginType'));
        $this->setVersion(config('pay2go.Version'));
        $this->setLangType(config('pay2go.LangType'));
        $this->setRespondType(config('pay2go.RespondType'));
        $this->setEmailModify(config('pay2go.EmailModify'));
        $this->setPaymentMethod(config('pay2go.paymentMethod'));
        $this->setTradeLimit(config('pay2go.TradeLimit'));
        $this->setOrderComment(config('pay2go.OrderComment'));
        $this->setClientBackURL(config('pay2go.ClientBackURL'));
        $this->setCustomerURL(config('pay2go.CustomerURL'));
        $this->setNotifyURL(config('pay2go.NotifyURL'));
        $this->setReturnURL(config('pay2go.ReturnURL'));

        $this->setTimeStamp();
        $this->MerchantID = $MerchantID;
        $this->HashKey = $HashKey;
        $this->HashIV = $HashIV;
    }

    /**
     * 設置訂單
     *
     * @param $order_id
     * @param $price
     * @param $descriptions
     * @param $email
     * @return $this
     */
    public function setOrder($order_id, $price, $descriptions, $email)
    {
        $this->MerchantOrderNo = $order_id;
        $this->Amt = $price;
        $this->ItemDesc = $descriptions;
        $this->Email = $email;

        return $this;
    }

    /**
     * 是否開啟測試模式
     *
     * @param $debug_mode
     */
    public function setPay2GoUrl($debug_mode)
    {
        if ($debug_mode)
            $this->Pay2GoUrl = 'https://ccore.spgateway.com/MPG/mpg_gateway';
        else
            $this->Pay2GoUrl = 'https://core.spgateway.com/MPG/mpg_gateway';
    }

    /**
     * 設定金流方式
     *
     * @param $payments_config_array
     * @return $this
     */
    public function setPaymentMethod($payments_config_array)
    {
        $this->CREDIT = $payments_config_array['CREDIT']['enable'] ? 1 : 0;
        $this->CreditRed = ($this->CREDIT and $payments_config_array['CREDIT']['CreditRed']) ? 1 : 0;
        $this->InstFlag = ($this->CREDIT and $payments_config_array['CREDIT']['InstFlag']) ? $payments_config_array['CREDIT']['InstFlag'] : 0;

        $this->UNIONPAY = $payments_config_array['UNIONPAY']? $payments_config_array['UNIONPAY'] : 0;
        $this->WEBATM = $payments_config_array['WEBATM'] ? 1 : 0;
        $this->VACC = $payments_config_array['VACC'] ? 1 : 0;
        $this->CVS = $payments_config_array['CVS'] ? 1 : 0;
        $this->BARCODE = $payments_config_array['BARCODE'] ? 1 : 0;

        return $this;
    }

    /**
     * 設定版本 (預設為 1.2)
     *
     * @param $version
     */
    private function setVersion($version)
    {
        $this->Version = $version;
    }

    /**
     * 設定語言 (預設為 zh-tw)
     *
     * @param $lang
     */
    public function setLangType($lang)
    {
        $this->LangType = $lang;
    }

    /**
     * 設定回傳型態 (預設為json)
     *
     * @param $response_type
     */
    public function setRespondType($response_type = 'json')
    {
        $this->RespondType = $response_type;
    }

    /**
     * 設定交易限制秒數
     *
     * @param null $trade_limit
     */
    public function setTradeLimit($trade_limit = null)
    {
        $this->TradeLimit = $trade_limit != null ? $trade_limit : 0;
    }

    /**
     * 設定過期日
     *
     * @param int $expireDays
     */
    public function setExpireDate($expireDays = 7)
    {
        $this->ExpireDate = date('Ymd',strtotime(Carbon::today()->addDay($expireDays)));
    }

    /**
     * 設定過期時間
     *
     * @param string $expireTime
     */
    public function setExpireTime($expireTime = '235959')
    {
        $this->ExpireTime = $expireTime;
    }

    /**
     * 設定交易完成後連結
     *
     * @param $return_url
     * @return $this
     */
    public function setReturnURL($return_url)
    {
        $this->ReturnURL = $return_url;

        return $this;
    }

    /**
     * 設定交易通知連結
     *
     * @param $notify_url
     * @return $this
     */
    public function setNotifyURL($notify_url)
    {
        if ($notify_url != null)
            $this->NotifyURL = $notify_url;

        return $this;
    }

    /**
     * 設定客製化連結
     *
     * @param $customer_url
     * @return $this
     */
    public function setCustomerURL($customer_url)
    {
        if ($customer_url != null)
            $this->CustomerURL = $customer_url;

        return $this;
    }

    /**
     * 設定交易失敗連結
     *
     * @param $client_back_url
     * @return $this
     */
    public function setClientBackURL($client_back_url)
    {
        if ($client_back_url != null)
            $this->ClientBackURL = $client_back_url;

        return $this;
    }

    /**
     * 設定 email 是否可在交易時修改
     *
     * @param $email_modify
     */
    public function setEmailModify($email_modify = false)
    {
        $this->EmailModify = $email_modify ? 1 : 0;
    }

    /**
     * 設定是否要登入智付寶
     *
     * @param $login_type
     */
    public function setLoginType($login_type = false)
    {
        $this->LoginType = $login_type ? 1 : 0;
    }

    /**
     * 設定商店備註
     *
     * @param $order_comment
     * @return $this
     */
    public function setOrderComment($order_comment)
    {
        $this->OrderComment = $order_comment != null ? $order_comment : '';

        return $this;
    }

    /**
     * 設定交易時間
     */
    public function setTimeStamp()
    {
        $this->TimeStamp = time();
    }

    /**
     * 商品敘述
     *
     * @return mixed
     */
    public function getItemOrder()
    {
        return $this->ItemDesc;
    }

    /**
     * 取得 POST URL
     *
     * @return mixed
     */
    public function getPay2GoUrl()
    {
        return $this->Pay2GoUrl;
    }

    /**
     * 設置 檢核碼
     *
     * @return string
     */
    public function setCheckValue()
    {
        $check_field = array(
            "Amt" => $this->Amt,
            "MerchantID" => $this->MerchantID,
            "MerchantOrderNo" => $this->MerchantOrderNo,
            "TimeStamp" => $this->TimeStamp,
            "Version" => $this->Version
        );

        ksort($check_field);
        $check_field_str = http_build_query($check_field);
        $checkValue = 'HashKey='.$this->HashKey . '&' . $check_field_str . '&HashIV=' . $this->HashIV;

        $this->CheckValue = strtoupper(hash("sha256", $checkValue));
    }

    public function getHashKey()
    {
        return $this->HashKey;
    }

    public function getHashIV()
    {
        return $this->HashIV;
    }

    public function getMerchantID()
    {
        return $this->MerchantID;
    }

    public function getMerchantOrderNo()
    {
        return $this->MerchantOrderNo;
    }

    public function getResponseType()
    {
        return $this->RespondType;
    }

    public function getVersion()
    {
        return $this->Version;
    }

    public function getAmt()
    {
        return $this->Amt;
    }

    /**
     * 送出訂單
     *
     * @return string
     */
    public function submitOrder()
    {
        $this->setCheckValue();

        $result = $this->setOrderSubmitForm();

        // 驗證欄位是否正確設置
        $this->orderValidates();

        return $result;
    }

    /**
     * 信用卡請款
     *
     * @param $MerchantOrderNo
     * @param $amt
     * @return string
     */
    public function requestPaymentPay($MerchantOrderNo, $amt)
    {
        $this->setOrder($MerchantOrderNo, $amt, null, null);

        $request_pay = new RequestCreditPay($this);

        $request_pay->setRequestPayData()->setCreditRequestPayOrCloseByMerchantOrderNo('Pay');

        $result = $this->setRequestPaymentForm($request_pay);

        return $result;
    }

    /**
     * 信用卡退款
     *
     * @param $MerchantOrderNo
     * @param $amt
     * @return string
     */
    public function requestPaymentClose($MerchantOrderNo, $amt)
    {
        $this->setOrder($MerchantOrderNo, $amt, null, null);

        $request_pay = new RequestCreditPay($this);

        $request_pay->setRequestPayData()->setCreditRequestPayOrCloseByMerchantOrderNo('Close');

        $result = $this->setRequestPaymentForm($request_pay);

        return $result;
    }

    /**
     * 設置請款或退款的表單
     *
     * @param $request_pay
     * @return string
     */
    private function setRequestPaymentForm($request_pay)
    {
        $result = '<form name="Pay2go" id="order_form" method="post" action='. $request_pay->getRequestCreditPayUrl(config('pay2go.Debug')) .'>';

        foreach ($request_pay->getProperties() as $key => $value) {
            if ($key != "pay2Go") {
                if (is_array($value)) {
                    foreach ($value as $sub_key => $sub_value)
                        $result .= '<input type="hidden" name="' . $key . '["' . $sub_key . '"]" value="' . $sub_value . '">';
                } else {
                    $result .= '<input type="hidden" name="' . $key . '" value="' . $value . '">';
                }
            }
        }

        $result .= '</form><script type="text/javascript">document.getElementById(\'order_form\').submit();</script>';

        return $result;
    }

    /**
     * 設置訂單新增的表單
     *
     * @return string
     */
    private function setOrderSubmitForm()
    {
        $result = '<form name="Pay2go" id="order_form" method="post" action='.$this->getPay2GoUrl().'>';

        foreach($this as $key => $value) {
            if ($key != 'Pay2GoUrl' and !is_null($value) and count($value) != 0)
                $result .= '<input type="hidden" name="'. $key .'" value="' . $value . '">';
        }

        $result .= '</form><script type="text/javascript">document.getElementById(\'order_form\').submit();</script>';

        return $result;
    }

}
