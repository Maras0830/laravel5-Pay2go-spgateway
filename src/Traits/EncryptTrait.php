<?php
namespace Maras0830\Pay2Go\Traits;

trait EncryptTrait
{
    public function encryptDataByAES($parameter)
    {
        $this->PostData_ = $this->create_mpg_aes_encrypt($parameter);
        $this->TradeInfo = $this->create_mpg_aes_encrypt($parameter);
    }

    function create_mpg_aes_encrypt($parameter = "")
    {
        $return_str = '';

        if (!empty($parameter)) {
            //將參數經過 URL ENCODED QUERY STRING
            $return_str = http_build_query($parameter);
        }

        return trim(bin2hex(openssl_encrypt($this->addpadding($return_str), 'aes-256-cbc', $this->HashKey, OPENSSL_RAW_DATA|OPENSSL_ZERO_PADDING, $this->HashIV)));
    }

    public function create_aes_decrypt($encrypt_string = "")
    {

        return $this->stripPadding(openssl_decrypt(hex2bin($encrypt_string),'AES-256-CBC', $this->HashKey, OPENSSL_RAW_DATA|OPENSSL_ZERO_PADDING, $this->HashIV));
    }

    public function encryptDataBySHA256()
    {
        $this->TradeSha = strtoupper(hash("sha256", 'HashKey='.$this->HashKey . '&' . $this->TradeInfo . '&HashIV=' . $this->HashIV));
    }

    public function stripPadding($string)
    {
        $slast = ord(substr($string, -1));

        $slastc = chr($slast);

        if (preg_match("/$slastc{" . $slast . "}/", $string)) {
            $string = substr($string, 0, strlen($string) - $slast);

            return $string;
        }

        return false;
    }

    public function addPadding($string, $blocksize = 32)
    {
        $len = strlen($string);

        $pad = $blocksize - ($len % $blocksize);

        $string .= str_repeat(chr($pad), $pad);

        return $string;
    }

    /**
     * @param $encrypt_period_string
     * @return mixed
     */
    public function decodeCallback($encrypt_period_string) : array
    {
        $decrypt_content = $this->create_aes_decrypt($encrypt_period_string);

        return json_decode($decrypt_content, true);
    }
}