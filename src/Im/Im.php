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
     * @throws cccdlException
     */
    public function __construct($sdkappid, $key, $identifier)
    {
        $this->sdkappid = $sdkappid;
        $this->key = $key;
        $this->identifier = $identifier;
        $this->usersig = $this->getSign($identifier);
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
     * 32位无符号整数随机数，取值范围0 - 4294967295
     * @return int 32位无符号整数随机数
     */
    private function getRandom()
    {
        return rand(0, 4294967295);
    }

    /**
     * 生成签名
     *
     * @param string $identifier 用户账号
     * @return string 签名字符串
     * @throws cccdlException
     */
    public function getSign($identifier)
    {
        return $this->__getSign($identifier, $this->expire, '', false);
    }

    /**
     * 带 userbuf 生成签名。
     * @param string $identifier 用户账号
     * @param string $userBuf 用户数据
     * @return string 签名字符串
     * @throws cccdlException
     */
    public function genSigWithUserBuf($identifier, $userBuf)
    {
        return $this->__getSign($identifier, $this->expire, $userBuf, true);
    }


    /**
     * 生成签名。
     * @param string $identifier 用户账号
     * @param int $expire 过期时间，单位秒，默认 180 天
     * @param string $userBuf base64 编码后的 userbuf
     * @param bool $userBufEnabled 是否开启 userbuf
     * @return string 签名字符串
     * @throws cccdlException
     */
    private function __getSign($identifier, $expire, $userBuf = '', $userBufEnabled = false)
    {
        $currTime = time();

        $signArray = [
            'TLS.ver' => '2.0',
            'TLS.identifier' => strval($identifier),
            'TLS.sdkappid' => intval($this->sdkappid),
            'TLS.expire' => intval($expire),
            'TLS.time' => intval($currTime)
        ];

        $base64UserBuf = '';

        if ($userBufEnabled) {
            $base64UserBuf = base64_encode($userBuf);
            $signArray['TLS.userbuf'] = strval($base64UserBuf);
        }

        $signArray['TLS.sig'] = $this->hmacSha256($identifier, $currTime, $expire, $base64UserBuf, $userBufEnabled);

        if ($signArray['TLS.sig'] === false) {
            throw new cccdlException('加密签名错误');
        }

        $jsonStrSign = json_encode($signArray);
        if ($jsonStrSign === false) {
            throw new cccdlException('转换json错误');
        }


        $compressed = gzcompress($jsonStrSign);
        if ($compressed === false) {
            throw new cccdlException('压缩签名失败');
        }


        return $this->base64UrlEncode($compressed);
    }

    /**
     * 验证签名。
     *
     * @param string $sign 签名内容
     * @param string $identifier 需要验证用户名，utf-8 编码
     * @param int $initTime 返回的生成时间，unix 时间戳
     * @param int $expireTime 返回的有效期，单位秒
     * @param string $errorMsg 失败时的错误信息
     * @return boolean 验证是否成功
     */
    private function verifySign($sign, $identifier, &$initTime, &$expireTime, &$errorMsg)
    {
        $userBuf = '';
        return $this->__verifySign($sign, $identifier, $initTime, $expireTime, $userBuf, $errorMsg);
    }

    /**
     * 带 userbuf 验证签名。
     * @param string $sig 签名内容
     * @param string $identifier 需要验证用户名，utf-8 编码
     * @param int $initTime 返回的生成时间，unix 时间戳
     * @param int $expireTime 返回的有效期，单位秒
     * @param string $userBuf 返回的用户数据
     * @param string $errorMsg 失败时的错误信息
     * @return boolean 验证是否成功
     */
    private function verifySigWithUserBuf($sig, $identifier, &$initTime, &$expireTime, &$userBuf, &$errorMsg)
    {
        return $this->__verifySign($sig, $identifier, $initTime, $expireTime, $userBuf, $errorMsg);
    }

    /**
     * 验证签名
     * @param string $sign 签名内容
     * @param string $identifier 需要验证用户名，utf-8 编码
     * @param int $initTime 返回的生成时间，unix 时间戳
     * @param int $expireTime 返回的有效期，单位秒
     * @param string $userBuf 返回的用户数据
     * @param string $errorMsg 失败时的错误信息
     * @return bool 验证是否成功
     */
    private function __verifySign($sign, $identifier, &$initTime, &$expireTime, &$userBuf, &$errorMsg)
    {
        try {
            $errorMsg = '';
            $compressedSign = $this->base64UrlDecode($sign);
            $preLevel = error_reporting(E_ERROR);
            $uncompressedSign = gzuncompress($compressedSign);
            error_reporting($preLevel);

            if ($uncompressedSign === false) {
                throw new cccdlException('解压错误');
            }

            $signDoc = json_decode($uncompressedSign);
            if ($signDoc == false) {
                throw new cccdlException('json解密失败');
            }

            $signDoc = (array)$signDoc;

            if ($signDoc['TLS.identifier'] !== $identifier) {
                throw new cccdlException("需要验证用户名不匹配");
            }

            if ($signDoc['TLS.sdkappid'] != $this->sdkappid) {
                throw new cccdlException("appid不匹配");
            }

            $sign = $signDoc['TLS.sig'];
            if ($sign == false) {
                throw new cccdlException('sig字段丢失');
            }

            $initTime = $signDoc['TLS.time'];
            $expireTime = $signDoc['TLS.expire'];

            $currTime = time();
            if ($currTime > $initTime + $expireTime) {
                throw new cccdlException('签名过期');
            }

            $userbufEnabled = false;
            $base64Userbuf = '';
            if (isset($sig_doc['TLS.userbuf'])) {
                $base64Userbuf = $sig_doc['TLS.userbuf'];
                $userBuf = base64_decode($base64Userbuf);
                $userbufEnabled = true;
            }
            $sigCalculated = $this->hmacSha256($identifier, $initTime, $expireTime, $base64Userbuf, $userbufEnabled);

            if ($sign != $sigCalculated) {
                throw new cccdlException('验签失败');
            }

            return true;
        } catch (cccdlException $e) {
            $errorMsg = $e->getMessage();
            return false;
        }
    }

    /**
     * 使用 hmac sha256 生成 sig 字段内容，经过 base64 编码
     * @param string $identifier 用户名，utf-8 编码
     * @param int $currTime 当前生成 sig 的 unix 时间戳
     * @param int $expire 有效期，单位秒
     * @param string $base64UserBuf base64 编码后的 userbuf
     * @param string $userBufEnabled 是否开启 userbuf
     * @return string base64 后的 sig
     */
    private function hmacSha256($identifier, $currTime, $expire, $base64UserBuf = '', $userBufEnabled = '')
    {
        $content_to_be_signed = "TLS.identifier:" . $identifier . PHP_EOL
            . "TLS.sdkappid:" . $this->sdkappid . PHP_EOL
            . "TLS.time:" . $currTime . PHP_EOL
            . "TLS.expire:" . $expire . PHP_EOL;
        if ($userBufEnabled) {
            $content_to_be_signed .= "TLS.userbuf:" . $base64UserBuf . PHP_EOL;
        }
        return base64_encode(hash_hmac('sha256', $content_to_be_signed, $this->key, true));
    }

    /**
     * 用于 url 的 base64 encode 编码
     * '+' => '*', '/' => '-', '=' => '_'
     * @param string $string 需要编码的数据
     * @return string 编码后的base64串，失败返回false
     * @throws cccdlException
     */
    private function base64UrlEncode($string)
    {
        static $replace = ['+' => '*', '/' => '-', '=' => '_'];
        $base64 = base64_encode($string);
        if ($base64 === false) {
            throw new cccdlException('base64编码错误');
        }
        return str_replace(array_keys($replace), array_values($replace), $base64);
    }

    /**
     * 用于 url 的 base64 decode 解码
     * '+' => '*', '/' => '-', '=' => '_'
     * @param string $base64 需要解码的base64串
     * @return string 解码后的数据，失败返回false
     * @throws cccdlException
     */
    private function base64UrlDecode($base64)
    {
        static $replace = ['+' => '*', '/' => '-', '=' => '_'];
        $string = str_replace(array_values($replace), array_keys($replace), $base64);
        $result = base64_decode($string);
        if ($result == false) {
            throw new cccdlException('base64解码错误');
        }
        return $result;
    }


}