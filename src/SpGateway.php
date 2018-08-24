<?php
namespace Maras0830\Pay2Go;

use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Maras0830\Pay2Go\Validation\OrderValidatesRequests;

/**
 * Class SpGateway
 * @package Maras0830\Pay2Go
 */
class SpGateway
{
    use OrderValidatesRequests;
    protected $Pay2GoUrl;

    private $MerchantID;
    private $TradeInfo;
    private $TradeSha;

    protected $RespondType;
    protected $TimeStamp;

    private $HashKey;
    private $HashIV;
    protected $Version;
    protected $LangType;
    protected $MerchantOrderNo;
    protected $Amt;
    protected $ItemDesc;
    protected $TradeLimit;
    protected $ExpireDate;
    protected $ReturnURL;
    protected $NotifyURL;
    protected $CustomerURL;
    protected $ClientBackURL;
    protected $Email;
    protected $EmailModify;
    protected $LoginType;
    protected $OrderComment;

    protected $CREDIT;
    protected $ANDROIDPAY;
    protected $SAMSUNGPAY;

    protected $InstFlag;

    protected $CreditRed;
    protected $UNIONPAY;
    protected $WEBATM;
    protected $VACC;
    protected $CVS;
    protected $BARCODE;
    protected $P2G;
    protected $CVSCOM;

    /**
     * Pay2Go constructor.
     * @param $MerchantID
     * @param $HashKey
     * @param $HashIV
     */
    public function __construct($MerchantID = null, $HashKey = null, $HashIV = null)
    {
        $this->MerchantID = ($MerchantID != null ? $MerchantID : Config::get('pay2go.MerchantID'));
        $this->HashKey = ($HashKey != null ? $HashKey : Config::get('pay2go.HashKey'));
        $this->HashIV = ($HashIV != null ? $HashIV : Config::get('pay2go.HashIV'));

        $this->setPay2GoUrl(Config::get('pay2go.Debug', true));
        $this->setRespondType(Config::get('pay2go.RespondType', 'JSON'));

        $this->setExpireDate(Config::get('pay2go.ExpireDays', 7));
        $this->setLoginType(Config::get('pay2go.LoginType', false));
        $this->setVersion(Config::get('pay2go.Version', '1.4'));
        $this->setLangType(Config::get('pay2go.LangType', 'zh-tw'));
        $this->setEmailModify(Config::get('pay2go.EmailModify', false));
        $this->setPaymentMethod(Config::get('pay2go.paymentMethod', $this->getDefaultPaymentMethod()));
        $this->setTradeLimit(Config::get('pay2go.TradeLimit', null));
        $this->setOrderComment(Config::get('pay2go.OrderComment', 'store comment'));
        $this->setClientBackURL(Config::get('pay2go.ClientBackURL', null));
        $this->setCustomerURL(Config::get('pay2go.CustomerURL', null));
        $this->setNotifyURL(Config::get('pay2go.NotifyURL', null));
        $this->setReturnURL(Config::get('pay2go.ReturnURL', null));

        $this->setTimeStamp();
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

    public function encryptDataByAES()
    {
        $parameter = [
            'MerchantID' =>  $this->MerchantID,
            'RespondType' => $this->RespondType,
            'TimeStamp' => $this->TimeStamp,
            'Version' => $this->Version,
            'MerchantOrderNo' =>  $this->MerchantOrderNo,
            'Amt' => $this->Amt,
            'ItemDesc' => 'UnitTest'
        ];

        $parameter = [
            'RespondType' => $this->RespondType,
            'TimeStamp' => 1400137200,
            'Version' => '1.0',
        ];

        $this->create_mpg_aes_encrypt($parameter);

        $this->TradeInfo = trim(bin2hex(openssl_encrypt(
            $this->addpadding(http_build_query($parameter)),
            'aes-256-cbc',
            $this->HashKey,
            OPENSSL_RAW_DATA|OPENSSL_ZERO_PADDING,
            $this->HashIV
        )));
    }


    public function encryptDataByAES_()
    {
        $parameter = [
            'RespondType' => $this->RespondType,
            'TimeStamp' => $this->TimeStamp,
            'Version' => '1.0',
        ];

        $this->create_mpg_aes_encrypt($parameter);

        $this->TradeInfo = trim(bin2hex(openssl_encrypt(
            $this->addpadding(http_build_query($parameter)),
            'aes-256-cbc',
            $this->HashKey,
            OPENSSL_RAW_DATA|OPENSSL_ZERO_PADDING,
            $this->HashIV
        )));
    }


    function create_mpg_aes_encrypt ($parameter = "")
    {
        $return_str = '';

        if (!empty($parameter)) {
            //將參數經過 URL ENCODED QUERY STRING
            $return_str = http_build_query($parameter);
        }

        return trim(bin2hex(openssl_encrypt($this->addpadding($return_str), 'aes-256-cbc', $this->getHashKey(), OPENSSL_RAW_DATA|OPENSSL_ZERO_PADDING, $this->getHashIV())));
    }

    public function create_aes_decrypt($parameter = "")
    {

        return $this->strippadding(openssl_decrypt(hex2bin($parameter),'AES-256-CBC', $this->getHashKey(), OPENSSL_RAW_DATA|OPENSSL_ZERO_PADDING, $this->getHashIV()));
    }

    public  function strippadding($string)
    {
        $slast = ord(substr($string, -1));

        $slastc = chr($slast);

        $pcheck = substr($string, -$slast);

        if (preg_match("/$slastc{" . $slast . "}/", $string)) {
            $string = substr($string, 0, strlen($string) - $slast);

            return $string;
        }

        return false;
    }

    public function encryptDataBySHA256()
    {
        $this->TradeSha = strtoupper(hash("sha256", 'HashKey='.$this->HashKey . '&' . $this->TradeInfo . '&HashIV=' . $this->HashIV));
    }

    public function addpadding($string, $blocksize = 32)
    {
        $len = strlen($string);
        $pad = $blocksize - ($len % $blocksize);
        $string .= str_repeat(chr($pad), $pad);
        return $string;
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
     * @throws Exceptions\TradeException
     */
    public function submitOrder()
    {
        $this->encryptDataByAES();

        $this->encryptDataBySHA256();

//        dd($this);
        $result = $this->setOrderSubmitForm();

//        dd($result);

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
        $result = '<form name="Pay2go" id="order_form" method="post" action='. $request_pay->getRequestCreditPayUrl(Config::get('pay2go.Debug')) .'>';

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

        $result .= '<input type="hidden" name="MerchantID" value="'. $this->MerchantID .'">';
        $result .= '<input type="hidden" name="TradeInfo" value="'. $this->TradeInfo .'">';
        $result .= '<input type="hidden" name="TradeSha" value="'. $this->TradeSha .'">';
        $result .= '<input type="hidden" name="Version" value="'. $this->Version .'">';

        $result .= '</form><script type="text/javascript">document.getElementById(\'order_form\').submit();</script>';

        return $result;
    }

    private function getDefaultPaymentMethod()
    {
        return [

            /*
             * 信用卡支付
             * enable: 是否啟用信用卡支付
             * CreditRed: 是否啟用紅利
             * InstFlag: 是否啟用分期
             *
             * 0: 不啟用
             * 1: 啟用全部分期
             * 3: 分 3 期
             * 6: 分 6 期功能
             * 12: 分 12 期功能
             * 18: 分 18 期功能
             * 24: 分 24 期功能
             * 以逗號方式開啟多種分期
             */
            'CREDIT' => [
                'enable' => true,
                'CreditRed' => false,
                'InstFlag' => 0,
            ],
            'ANDROIDPAY' => false,
            'SAMSUNGPAY' => false,
            'UNIONPAY' => false,
            'WEBATM' => false,
            'VACC' => false,
            'CVS' => false,
            'BARCODE' => false,
            'P2G' => false,
        ];
    }

}
