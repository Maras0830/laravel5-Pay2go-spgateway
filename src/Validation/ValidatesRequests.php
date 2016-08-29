<?php

namespace Maras0830\Pay2Go;

use Maras0830\Pay2Go\Exceptions\TradeException;

/**
 * Class ValidatesRequests
 * @package Maras0830\Pay2Go
 */
trait ValidatesRequests
{
    /**
     * @throws TradeException
     */
    public function validate()
    {
        $required = ['MerchantID', 'RespondType',
                    'CheckValue', 'TimeStamp',
                    'Version', 'MerchantOrderNo',
                    'Amt', 'ItemDesc',
                    'Email', 'LoginType'];

        $valid_url_port = ['ReturnURL', 'NotifyURL', 'CustomerURL', 'ClientBackURL'];

        $this->isValidOnNull($required);

        $this->isValidOnInstFlag();

        $this->isValidOnPort($valid_url_port);
    }

    /**
     * 檢查必填欄位是否有空值
     *
     * @param $required
     * @throws TradeException
     */
    private function isValidOnNull($required)
    {
        foreach ($required as $field)
            if ($this->{$field} == null)
                throw new TradeException($field . ' is null.');
    }

    /**
     * 檢查信用卡分期數是否正確
     *
     * @throws TradeException
     */
    private function isValidOnInstFlag()
    {
        if (isset($this->InstFlag) and $this->InstFlag != null)
            if (!in_array($this->InstFlag, [1, 3, 6, 12, 18, 24]))
                throw new TradeException('InstFlag must be [1, 3, 6, 12, 18, 24].');
    }

    /**
     * 檢查 Port 是否正確設置
     *
     * @param $valid_url_port
     * @throws TradeException
     */
    private function isValidOnPort($valid_url_port)
    {
        foreach ($valid_url_port as $field)
            if ($this->{$field} != null)
                if (!(strpos($this->{$field}, "http://") !== false or strpos($this->{$field}, "https://") !== false))
                    throw new TradeException($field . ' must contain http or https port.');
    }
}
