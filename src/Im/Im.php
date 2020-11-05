<?php
declare(strict_types=1);

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
     * 内部服务名，不同的 serviceName 对应不同的服务类型
     * @var string
     */
    protected $serviceName;

    /**
     * 命令字，与 serviceName 组合用来标识具体的业务功能
     * @var string
     */
    protected $command;

    /**
     * App 在即时通信 IM 控制台获取的应用标识
     * @var string
     */
    private $sdkAppid;

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
    private $userSig;

    /**
     * 标识当前请求的随机数参数
     * @var string
     */
    private $random;

    /**
     * 请求格式
     * @var string
     */
    private $contentType = 'json';

    /**
     * 过期时间，单位秒，默认 180 天
     * @param int $expire
     */
    private $expire = 86400 * 180;

    /**
     * Auth constructor.
     * @param string $sdkAppid AppID
     * @param string $key 密钥
     * @param string $identifier 用户管理员账号
     * @throws cccdlException
     */
    public function __construct(string $sdkAppid, string $key, string $identifier)
    {
        $this->sdkAppid = $sdkAppid;
        $this->key = $key;
        $this->identifier = $identifier;
        $this->userSig = $this->getSign();
        $this->random = $this->getRandom();
    }

    /**
     * 生成需要请求的url
     * @return string
     */
    protected function getUrl(): string
    {
        return sprintf("%s/%s/%s/%s?sdkAppid=%d&identifier=%s&userSig=%s&random=%s&contentType=%s",
            $this->url,
            $this->ver,
            $this->serviceName,
            $this->command,
            $this->sdkAppid,
            $this->identifier,
            $this->userSig,
            $this->random,
            $this->contentType,
        );
    }

    /**
     * 生成签名
     * @param string $userBuf
     * @param bool $userBufEnabled
     * @return string 签名字符串
     * @throws cccdlException
     */
    public function getSign($userBuf = '', $userBufEnabled = false)
    {
        $currTime = time();

        $sigArray = [
            'TLS.ver' => '2.0',
            'TLS.identifier' => strval($this->identifier),
            'TLS.sdkappid' => intval($this->sdkAppid),
            'TLS.expire' => intval($this->expire),
            'TLS.time' => intval($currTime)
        ];

        $base64UserBuf = '';
        if (true == $userBufEnabled) {
            $base64UserBuf = base64_encode($userBuf);
            $sigArray['TLS.userbuf'] = strval($base64UserBuf);
        }

        $sigArray['TLS.sig'] = $this->hmacSha256($currTime, $base64UserBuf, $userBufEnabled);

        if ($sigArray['TLS.sig'] === false) {
            throw new cccdlException('base64_encode error');
        }

        $jsonStrSign = json_encode($sigArray);
        if ($jsonStrSign === false) {
            throw new cccdlException('json_encode error');
        }

        $compressed = gzcompress($jsonStrSign);
        if ($compressed === false) {
            throw new cccdlException('gzcompress error');
        }

        return $this->base64UrlEncode($compressed);


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
    private function base64UrlEncode(string $string): string
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
    private function base64UrlDecode(string $base64): string
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
     * @param $currTime
     * @param $base64UserBuf
     * @param bool $userBufEnabled
     * @return string base64 后的 sig
     */
    private function hmacSha256($currTime, $base64UserBuf, $userBufEnabled = false)
    {
        $contentToBeSigned = "TLS.identifier:" . $this->identifier . "\n"
            . "TLS.sdkappid:" . $this->sdkAppid . "\n"
            . "TLS.time:" . $currTime . "\n"
            . "TLS.expire:" . $this->expire . "\n";

        if ($userBufEnabled == true) {
            $contentToBeSigned .= "TLS.userbuf:" . $base64UserBuf . "\n";
        }

        return base64_encode(hash_hmac('sha256', $contentToBeSigned, $this->key, true));
    }


    /**
     * 带 userbuf 生成签名。
     * @param string $userbuf 用户数据
     * @param $userBufEnabled
     * @return string 签名字符串
     * @throws cccdlException
     */
    public function genSigWithUserBuf($userbuf, $userBufEnabled)
    {
        return $this->__genSig($userbuf, $userBufEnabled);
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
            $compressed_sig = $this->base64UrlDecode($sig);
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

            if ($sig_doc['TLS.sdkAppid'] != $this->sdkAppid) {
                throw new cccdlException("sdkAppid dosen't match");
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

            $userBufEnabled = false;
            $base64_userbuf = '';
            if (isset($sig_doc['TLS.userbuf'])) {
                $base64_userbuf = $sig_doc['TLS.userbuf'];
                $userbuf = base64_decode($base64_userbuf);
                $userBufEnabled = true;
            }
            $sigCalculated = $this->hmacsha256($init_time, $base64_userbuf, $userBufEnabled);

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