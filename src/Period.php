<?php
namespace Maras0830\Pay2Go;

use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Maras0830\Pay2Go\Traits\EncryptTrait;
use Maras0830\Pay2Go\Validation\OrderValidatesRequests;

/**
 * Class Period
 * @package Maras0830\Pay2Go
 */
class Period
{
    use OrderValidatesRequests, EncryptTrait;

    protected $Pay2GoUrl;

    private $MerchantID;
    private $PostData_;

    private $HashKey;
    private $HashIV;

    protected $RespondType;
    protected $TimeStamp;
    protected $Version;

    protected $MerOrderNo;
    protected $ProdDesc;
    protected $PeriodAmt;
    protected $PeriodType;
    protected $PeriodPoint;
    protected $PeriodStartType;
    protected $PeriodTimes;
    protected $PeriodMemo;

    protected $ReturnURL;
    protected $PayerEmail;
    protected $EmailModify;

    protected $PaymentInfo;
    protected $OrderInfo;
    protected $NotifyURL;
    protected $BackURL;

    /**
     * Pay2Go constructor.
     * @param $MerchantID
     * @param $HashKey
     * @param $HashIV
     */
    public function __construct($MerchantID = null, $HashKey = null, $HashIV = null)
    {
        $this->setPay2GoPeriodUrl(Config::get('pay2go.Debug', true));

        $this->MerchantID = ($MerchantID != null ? $MerchantID : Config::get('pay2go.MerchantID'));
        $this->HashKey = ($HashKey != null ? $HashKey : Config::get('pay2go.HashKey'));
        $this->HashIV = ($HashIV != null ? $HashIV : Config::get('pay2go.HashIV'));

        $this->setRespondType(Config::get('pay2go.RespondType', 'JSON'));
        $this->setTimeStamp();
        $this->setVersion(Config::get('pay2go.Period.Version', '1.0'));

        $this->setEmailModify(Config::get('pay2go.EmailModify', false));

        $this->setPaymentInfo(Config::get('pay2go.Period.PaymentInfo', false));
        $this->setOrderInfo(Config::get('pay2go.Period.OrderInfo', true));

        $this->setReturnURL(Config::get('pay2go.ReturnURL', null));
        $this->setClientBackURL(Config::get('pay2go.ClientBackURL', null));
        $this->setNotifyURL(Config::get('pay2go.NotifyURL', null));
//        $this->setNotifyURL('https://requestbin.fullcontact.com/1klchpq1');
    }

    /**
     * 定期定額扣款
     *
     * @param $email
     * @param $order_number
     * @param string $description
     * @param $per_price
     * @param string $type
     * @param string $check_point
     * @param int $period_times
     * @param int $first_authorization_type
     * @param string $memo
     * @return Period
     */
    public function setPeriod($email, $order_number, $description, $per_price, $type = 'M', $check_point = '01', $period_times = 999, $first_authorization_type = 2, $memo = '')
    {
        $this->MerOrderNo = $order_number;
        $this->ProdDesc = $description;
        $this->PeriodAmt = $per_price;
        $this->PeriodType = $type;
        $this->PeriodPoint = $check_point;
        $this->PeriodStartType = $first_authorization_type;
        $this->PeriodTimes = $period_times;
        $this->PeriodMemo = $memo;

        $this->PayerEmail = $email;

        return $this;
    }

    /**
     * 是否開啟測試模式
     *
     * @param $debug_mode
     */
    public function setPay2GoPeriodUrl($debug_mode)
    {
        if ($debug_mode)
            $this->Pay2GoUrl = 'https://ccore.spgateway.com/MPG/period';
        else
            $this->Pay2GoUrl = 'https://core.spgateway.com/MPG/period';
    }

    /**
     * 設定版本 (預設為 1.0)
     *
     * @param $version
     */
    private function setVersion($version)
    {
        $this->Version = $version;
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
     * 設定交易失敗連結
     *
     * @param $client_back_url
     * @return $this
     */
    public function setClientBackURL($client_back_url)
    {
        if ($client_back_url != null)
            $this->BackURL = $client_back_url;

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
     * 設定交易時間
     */
    public function setTimeStamp()
    {
        $this->TimeStamp = time();
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
     * 送出
     *
     * @return string
     */
    public function submit()
    {
        $parameter = [
            'RespondType' => $this->RespondType,
            'TimeStamp' => $this->TimeStamp,
            'Version' => '1.0',
            'MerOrderNo' => $this->MerOrderNo,
            'ProdDesc' => $this->ProdDesc,
            'PeriodAmt' => $this->PeriodAmt,
            'PeriodType' => $this->PeriodType,
            'PeriodPoint' => $this->PeriodPoint,
            'PeriodStartType' => $this->PeriodStartType,
            'PeriodTimes' => $this->PeriodTimes,
            'ReturnURL' => $this->ReturnURL,
            'PeriodMemo' => $this->PeriodMemo,
            'PayerEmail' => $this->PayerEmail,
            'EmailModify' => $this->EmailModify,
            'PaymentInfo' => $this->PaymentInfo,
            'OrderInfo' => $this->OrderInfo,
            'NotifyURL' => $this->NotifyURL,
            'BackURL' => $this->BackURL,
        ];

        $this->encryptDataByAES($parameter);

//        dd($this->PostData_);

        $result = $this->setOrderSubmitForm();

//        $this->orderValidates();

        return $result;
    }

    /**
     * 設置訂單新增的表單
     *
     * @return string
     */
    private function setOrderSubmitForm()
    {
        $result = '<form name="Pay2go" id="period_form" method="post" action='.$this->getPay2GoUrl().'>';

        $result .= '<input type="hidden" name="MerchantID_" value="'. $this->MerchantID .'">';
        $result .= '<input type="hidden" name="PostData_" value="'. $this->PostData_ .'">';

        $result .= '</form><script type="text/javascript">document.getElementById(\'period_form\').submit();</script>';

        return $result;
    }

    /**
     * @param $enable_payment_info
     */
    private function setPaymentInfo($enable_payment_info)
    {
        $this->PaymentInfo = $enable_payment_info ? 'Y' : 'N';
    }

    /**
     * @param $enable_order_info
     */
    private function setOrderInfo($enable_order_info)
    {
        $this->OrderInfo = $enable_order_info ? 'Y' : 'N';
    }

    /**
     * @return mixed|null
     */
    public function getMerchantID()
    {
        return $this->MerchantID;
    }

    /**
     * @return mixed
     */
    public function getResponseType()
    {
        return $this->RespondType;
    }

    /**
     * @return mixed
     */
    public function getVersion()
    {
        return $this->Version;
    }
}
