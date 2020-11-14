<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/comfig.php';

use cccdl\tencent_sdk\Exception\cccdlException;
use cccdl\tencent_sdk\Im\OpenIm;

try {


    $im = new OpenIm($appId, $key, $identifier);


    //额外配置参数 不需要直接去除就好了
    //TODO 批量发送没有 MsgLifeTime、MsgTimeStamp、批量发送没有这个字段
    $options = [

        //1：把消息同步到 From_Account 在线终端和漫游上
        //2：消息不同步至 From_Account
        //若不填写默认情况下会将消息存 From_Account 漫游
        'SyncOtherMachine' => 1,

        //离线推送信息配置，具体可参考
        'OfflinePushInfo' => [
            //0表示推送，1表示不离线推送。
            'PushFlag' => 0,

            //离线推送标题。该字段为 iOS 和 Android 共用。
            'Title' => '推送标题',

            //离线推送内容。该字段会覆盖上面各种消息元素 TIMMsgElement 的离线推送展示文本。
            //若发送的消息只有一个 TIMCustomElem 自定义消息元素，该 Desc 字段会覆盖 TIMCustomElem 中的 Desc 字段。如果两个 Desc 字段都不填，将收不到该自定义消息的离线推送。
            'Desc' => '这是离线推送内容',

            //离线推送透传内容。由于国内各 Android 手机厂商的推送平台要求各不一样，请保证此字段为 JSON 格式，否则可能会导致收不到某些厂商的离线推送。

            'Ext' => "这是透传的内容",


            'AndroidInfo' => [
                //Android 离线推送声音文件路径。
                'Sound' => 'android.mp3',

                //华为手机 EMUI 10.0 及以上的通知渠道字段。
                'HuaWeiChannelID' => '通知渠道字段',

                //小米手机 MIUI 10 及以上的通知类别（Channel）适配字段。
                'XiaoMiChannelID' => '通知渠道字段',

                //OPPO 手机 Android 8.0 及以上的 NotificationChannel 通知适配字段。
                'OPPOChannelID' => '通知渠道字段',

                //Google 手机 Android 8.0 及以上的通知渠道字段。Google 推送新接口（上传证书文件）支持 channel id ，旧接口（填写服务器密钥）不支持。
                'GoogleChannelID' => 'channel id',

            ],
            'ApnsInfo' => [
                //这个字段缺省或者为0表示需要计数，为1表示本条消息不需要计数，即右上角图标数字不增加。
                'BadgeMode' => 0,

                //该字段用于标识 APNs 推送的标题，若填写则会覆盖最上层 Title。
                'Title' => '覆盖上面推送标题',

                //该字段用于标识 APNs 推送的子标题。
                'SubTitle' => 'APNs 推送的子标题',

                //	该字段用于标识 APNs 携带的图片地址，当客户端拿到该字段时，可以通过下载图片资源的方式将图片展示在弹窗上。
                'Image' => 'www.image.com',

            ]
        ]

    ];


    //文本消息
    $msg = [
        'Text' => 'hello world11111111111'
    ];
    $res = $im->batchSendMsg('1000001', ['user5', 'user1'], OpenIm::TXT, $msg, $options);
    var_dump($res);
    var_dump($res['data']);

    //地理位置
    $msg = [
        'Desc' => 'someinfo',
        'Latitude' => 29.340656774469956,
        'Longitude' => 116.77497920478824
    ];
    $res = $im->batchSendMsg('1000001', ['user5', 'user1'], OpenIm::LOCATION, $msg, $options);
    var_dump($res);
    var_dump($res['data']);

    //表情消息
    $msg = [
        'Index' => 1,
        'Data' => 'content'
    ];
    $res = $im->batchSendMsg('1000001', ['user5', 'user1'], OpenIm::FACE, $msg, $options);
    var_dump($res);
    var_dump($res['data']);

    //自定义消息元素
    $msg = [
        'Data' => 'message',
        'Desc' => 'notification',
        'Ext' => 'url',
        'Sound' => 'dingdong.aiff'
    ];
    $res = $im->batchSendMsg('1000001', ['user5', 'user1'], OpenIm::CUSTOM, $msg, $options);
    var_dump($res);
    var_dump($res['data']);

} catch (cccdlException $e) {
    echo $e->getCode();
    echo '----';
    echo $e->getMessage();
}

