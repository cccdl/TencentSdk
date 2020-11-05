<?php

namespace cccdl\tencent_sdk\Im;


use cccdl\tencent_sdk\Exception\cccdlException;
use cccdl\tencent_sdk\Traits\Request;

/**
 * 账号管理
 * Class imOpenLoginSvc
 * @package cccdl\tencent_sdk\Im
 */
class imOpenLoginSvc extends Im
{

    /**
     * 内部服务名，不同的 serviceName 对应不同的服务类型
     * @var string
     */
    protected $serviceName = 'im_open_login_svc';

    /**
     * 导入单个帐号
     * 本接口用于将 App 自有帐号导入即时通信 IM 帐号系统，为该帐号创建一个对应的内部 ID，使该帐号能够使用即时通信 IM 服务。
     * @param String $Identifier 用户名，长度不超过32字节
     * @param string $nickName 昵称
     * @param String $FaceUrl 用户头像 URL
     * @return array
     * @throws cccdlException
     */
    public function accountImport(string $Identifier, $nickName = '', $FaceUrl = '')
    {
        $this->command = 'account_import';

        $url = $this->getUrl();

        $param['Identifier'] = (string)$Identifier;

        if (!empty($Nick)) {
            $param['Nick'] = (string)$nickName;
        }

        if (!empty($FaceUrl)) {
            $param['FaceUrl'] = (string)$FaceUrl;
        }

        return Request::post($url, $param);
    }

    /**
     * 导入多个帐号
     * 本接口用于批量将 App 自有帐号导入即时通信 IM 帐号系统，为该帐号创建一个对应的内部 ID，使该帐号能够使用即时通信 IM 服务。
     * @param array $Accounts 用户名，单个用户名长度不超过32字节，单次最多导入100个用户名
     * @return array
     * @throws cccdlException
     */
    public function multiAccountImport(array $Accounts)
    {
        $this->command = 'multiaccount_import';

        $url = $this->getUrl();

        $param['Accounts'] = $Accounts;

        return Request::post($url, $param);
    }

    /**
     * 删除帐号
     * 仅支持删除体验版帐号。
     * 帐号删除时，该用户的关系链、资料等数据也会被删除。
     * 帐号删除后，该用户的数据将无法恢复，请谨慎使用该接口。
     * @param array $DeleteItem 请求删除的帐号数组，单次请求最多支持100个帐号,【仅支持体验版】
     * @return array
     * @throws cccdlException
     */
    public function accountDelete(array $DeleteItem)
    {
        $this->command = 'account_delete';

        $url = $this->getUrl();

        foreach ($DeleteItem as $item) {
            $param['DeleteItem'][] = ['UserID' => (string)$item];
        }

        return Request::post($url, $param);
    }

    /**
     * 查询帐号
     * 用于查询自有帐号是否已导入即时通信 IM，支持批量查询。
     * @param array $CheckItem 请求检查的帐号对象数组，单次请求最多支持100个帐号
     * @return array
     * @throws cccdlException
     */
    public function accountCheck(array $CheckItem)
    {
        $this->command = 'account_check';

        $url = $this->getUrl();

        foreach ($CheckItem as $item) {
            $param['CheckItem'][] = ['UserID' => (string)$item];
        }

        return Request::post($url, $param);
    }

    /**
     * 失效帐号登录态
     * 本接口适用于将 App 用户帐号的登录态（例如 UserSig）失效。
     * 例如，开发者判断一个用户为恶意帐号后，可以调用本接口将该用户当前的登录态失效，这样用户使用历史 UserSig 登录即时通信 IM 会失败。
     * @param string $Identifier
     * @return array
     * @throws cccdlException
     */
    public function kick($Identifier)
    {
        $this->command = 'kick';

        $url = $this->getUrl();

        $param['Identifier'] = (string)$Identifier;

        return Request::post($url, $param);
    }

    /**
     * 查询帐号在线状态
     * 获取用户当前的登录状态。
     * @param array $toAccount 需要查询这些 UserID 的登录状态，一次最多查询500个 UserID 的状态
     * @param int $isNeedDetail 是否需要返回详细的登录平台信息。0表示不需要，1表示需要
     * @return array
     * @throws cccdlException
     */
    public function queryState(array $toAccount, $isNeedDetail = 0)
    {
        $this->serviceName = 'openim';
        $this->command = 'querystate';

        $url = $this->getUrl();

        $param['To_Account'] = $toAccount;
        $param['IsNeedDetail'] = $isNeedDetail;

        return Request::post($url, $param);
    }

}