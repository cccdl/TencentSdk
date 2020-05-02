<?php

namespace cccdl\tencent_sdk\Im;

use cccdl\tencent_sdk\Exception\cccdlException;
use cccdl\tencent_sdk\Traits\Request;

class Im
{
    use Request;

    /**
     * 请求域名
     * @var string
     */
    private $url = 'https://console.tim.qq.com';

    /**
     * 协议版本号
     * @var string
     */
    private $ver = 'v4';

    /**
     * 内部服务名，不同的 servicename 对应不同的服务类型
     * @var string
     */
    protected $servicename;

    /**
     * 命令字，与 servicename 组合用来标识具体的业务功能
     * @var string
     */
    protected $command;

    /**
     * App 在即时通信 IM 控制台获取的应用标识
     * @var string
     */
    private $sdkappid;

    /**
     * 密钥
     * @var string
     */
    private $key;

    /**
     * 用户名，调用 REST API 时必须为 App 管理员帐号
     * @var string
     */
    private $identifier;

    /**
     * 用户名对应的密码
     * @var string
     */
    private $usersig;

    /**
     * 标识当前请求的随机数参数
     * @var string
     */
    private $random;

    /**
     * 请求格式
     * @var string
     */
    private $contenttype = 'json';

    /**
     * 过期时间，单位秒，默认 180 天
     *
     * @param int $expire
     */
    private $expire = 86400 * 180;

    /**
     * Auth constructor.
     * @param string $sdkappid AppID
     * @param string $key 密钥
     * @param string $identifier 用户管理员账号
     */
    public function __construct($sdkappid, $key, $identifier)
    {
        $this->sdkappid = $sdkappid;
        $this->key = $key;
        $this->identifier = $identifier;
        $this->usersig = $this->genSig();
        $this->random = $this->getRandom();
    }

    /**
     * 生成需要请求的url
     * @return string
     */
    protected function getUrl()
    {
        return sprintf("%s/%s/%s/%s?sdkappid=%d&identifier=%s&usersig=%s&random=%s&contenttype=%s",
            $this->url,
            $this->ver,
            $this->servicename,
            $this->command,
            $this->sdkappid,
            $this->identifier,
            $this->usersig,
            $this->random,
            $this->contenttype,
        );
    }

    /**
     * 生成签名
     *
     * @return string 签名字符串
     * @throws cccdlException
     */
    public function genSig()
    {
        return $this->__genSig();
    }


    /**
     * 32位无符号整数随机数，取值范围0 - 4294967295
     * @return int 32位无符号整数随机数
     */
    private function getRandom()
    {
        return rand(0, 4294967295);
    }

    /**
     * 用于 url 的 base64 encode
     * '+' => '*', '/' => '-', '=' => '_'
     * @param string $string 需要编码的数据
     * @return string 编码后的base64串，失败返回false
     * @throws cccdlException
     */
    private function base64_url_encode($string)
    {
        static $replace = ['+' => '*', '/' => '-', '=' => '_'];
        $base64 = base64_encode($string);
        if ($base64 === false) {
            throw new cccdlException('base64_encode error');
        }
        return str_replace(array_keys($replace), array_values($replace), $base64);
    }

    /**
     * 用于 url 的 base64 decode
     * '+' => '*', '/' => '-', '=' => '_'
     * @param string $base64 需要解码的base64串
     * @return string 解码后的数据，失败返回false
     * @throws cccdlException
     */
    private function base64_url_decode($base64)
    {
        static $replace = ['+' => '*', '/' => '-', '=' => '_'];
        $string = str_replace(array_values($replace), array_keys($replace), $base64);
        $result = base64_decode($string);
        if ($result == false) {
            throw new cccdlException('base64_url_decode error');
        }
        return $result;
    }

    /**
     * 使用 hmac sha256 生成 sig 字段内容，经过 base64 编码
     * @param int $curr_time
     * @param $base64_userbuf
     * @param bool $userbuf_enabled
     * @return string base64 后的 sig
     */
    private function hmacsha256($curr_time, $base64_userbuf, $userbuf_enabled = false)
    {
        $content_to_be_signed = "TLS.identifier:" . $this->identifier . "\n"
            . "TLS.sdkappid:" . $this->sdkappid . "\n"
            . "TLS.time:" . $curr_time . "\n"
            . "TLS.expire:" . $this->expire . "\n";

        if (true == $userbuf_enabled) {
            $content_to_be_signed .= "TLS.userbuf:" . $base64_userbuf . "\n";
        }

        return base64_encode(hash_hmac('sha256', $content_to_be_signed, $this->key, true));
    }

    /**
     * 生成签名。
     *
     * @param string $userbuf base64 编码后的 userbuf
     * @param bool $userbuf_enabled 是否开启 userbuf
     * @return string 签名字符串
     * @throws cccdlException
     */
    private function __genSig($userbuf = '', $userbuf_enabled = false)
    {
        $curr_time = time();

        $sig_array = [
            'TLS.ver' => '2.0',
            'TLS.identifier' => strval($this->identifier),
            'TLS.sdkappid' => intval($this->sdkappid),
            'TLS.expire' => intval($this->expire),
            'TLS.time' => intval($curr_time)
        ];

        $base64_userbuf = '';
        if (true == $userbuf_enabled) {
            $base64_userbuf = base64_encode($userbuf);
            $sig_array['TLS.userbuf'] = strval($base64_userbuf);
        }

        $sig_array['TLS.sig'] = $this->hmacsha256($curr_time, $base64_userbuf, $userbuf_enabled);

        if ($sig_array['TLS.sig'] === false) {
            throw new cccdlException('base64_encode error');
        }

        $json_str_sig = json_encode($sig_array);
        if ($json_str_sig === false) {
            throw new cccdlException('json_encode error');
        }

        $compressed = gzcompress($json_str_sig);
        if ($compressed === false) {
            throw new cccdlException('gzcompress error');
        }

        return $this->base64_url_encode($compressed);
    }

    /**
     * 带 userbuf 生成签名。
     * @param string $userbuf 用户数据
     * @param $userbuf_enabled
     * @return string 签名字符串
     * @throws cccdlException
     */
    public function genSigWithUserBuf($userbuf, $userbuf_enabled)
    {
        return $this->__genSig($userbuf, $userbuf_enabled);
    }


    /**
     * 验证签名。
     *
     * @param string $sig 签名内容
     * @param int $init_time 返回的生成时间，unix 时间戳
     * @param string $userbuf 返回的用户数据
     * @param string $error_msg 失败时的错误信息
     * @return boolean 验证是否成功
     */
    private function __verifySig($sig, &$init_time, &$userbuf, &$error_msg)
    {
        try {
            $error_msg = '';
            $compressed_sig = $this->base64_url_decode($sig);
            $pre_level = error_reporting(E_ERROR);
            $uncompressed_sig = gzuncompress($compressed_sig);
            error_reporting($pre_level);

            if ($uncompressed_sig === false) {
                throw new cccdlException('gzuncompress error');
            }

            $sig_doc = json_decode($uncompressed_sig);
            if ($sig_doc == false) {
                throw new cccdlException('json_decode error');
            }

            $sig_doc = (array)$sig_doc;
            if ($sig_doc['TLS.identifier'] !== $this->identifier) {
                throw new cccdlException("identifier dosen't match");
            }

            if ($sig_doc['TLS.sdkappid'] != $this->sdkappid) {
                throw new cccdlException("sdkappid dosen't match");
            }

            $sig = $sig_doc['TLS.sig'];
            if ($sig == false) {
                throw new cccdlException('sig field is missing');
            }

            $init_time = $sig_doc['TLS.time'];
            $expire_time = $sig_doc['TLS.expire'];

            $curr_time = time();
            if ($curr_time > $init_time + $expire_time) {
                throw new cccdlException('sig expired');
            }

            $userbuf_enabled = false;
            $base64_userbuf = '';
            if (isset($sig_doc['TLS.userbuf'])) {
                $base64_userbuf = $sig_doc['TLS.userbuf'];
                $userbuf = base64_decode($base64_userbuf);
                $userbuf_enabled = true;
            }
            $sigCalculated = $this->hmacsha256($init_time, $base64_userbuf, $userbuf_enabled);

            if ($sig != $sigCalculated) {
                throw new cccdlException('verify failed');
            }

            return true;
        } catch (cccdlException $ex) {
            $error_msg = $ex->getMessage();
            return false;
        }
    }


    /**
     * 带 userbuf 验证签名。
     *
     * @param string $sig 签名内容
     * @param int $init_time 返回的生成时间，unix 时间戳
     * @param string $error_msg 失败时的错误信息
     * @return boolean 验证是否成功
     */
    public function verifySig($sig, &$init_time, &$error_msg)
    {
        $userbuf = '';
        return $this->__verifySig($sig, $init_time, $userbuf, $error_msg);
    }

    /**
     * 验证签名
     * @param string $sig 签名内容
     * @param int $init_time 返回的生成时间，unix 时间戳
     * @param string $userbuf 返回的用户数据
     * @param string $error_msg 失败时的错误信息
     * @return boolean 验证是否成功
     */
    public function verifySigWithUserBuf($sig, &$init_time, &$userbuf, &$error_msg)
    {
        return $this->__verifySig($sig, $init_time, $userbuf, $error_msg);
    }


}