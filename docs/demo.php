<?php

use Javareact\Auth\Bundle\XingeApp;
use Javareact\Xinge\Bundle\ClickAction;
use Javareact\Xinge\Bundle\Message;
use Javareact\Xinge\Bundle\MessageIOS;
use Javareact\Xinge\Bundle\Style;
use Javareact\Xinge\Bundle\TagTokenPair;
use Javareact\Xinge\Bundle\TimeInterval;

require __DIR__ . '..' . DIRECTORY_SEPARATOR . 'autoload.php';


class Demo
{
    public $appId     = 'appId';
    public $secretKey = 'secretKey';
    public $accessId  = 'accessId';

    /**
     * 单个设备下发通知消息
     * @return array|mixed
     */
    public function DemoPushSingleDeviceNotification()
    {
        $push = new XingeApp($this->appId, $this->secretKey);
        $mess = new Message();
        $mess->setType(Message::TYPE_NOTIFICATION);
        $mess->setTitle("title");
        $mess->setContent("中午");
        $mess->setExpireTime(86400);
        $mess->setSendTime(date('Y-m-d H:i:s'));
        #含义：样式编号0，响铃，震动，不可从通知栏清除，不影响先前通知
        $style  = new Style(0, 1, 1, 0, 0);
        $action = new ClickAction();
        $action->setActionType(ClickAction::TYPE_URL);
        $action->setUrl("http://xg.qq.com");
        #打开url需要用户确认
        $action->setComfirmOnUrl(1);
        $custom = array('key1' => 'value1', 'key2' => 'value2');
        $mess->setStyle($style);
        $mess->setAction($action);
        $mess->setCustom($custom);
        $acceptTime1 = new TimeInterval(0, 0, 23, 59);
        $mess->addAcceptTime($acceptTime1);
        try {
            $ret = $push->PushSingleDevice('token', $mess);
        } catch (\Javareact\Xinge\Exceptions\Exception $e) {
        }
        return ($ret);
    }

    /**
     * 单个设备下发透传消息       注：透传消息默认不展示
     * @return array|mixed
     */
    public function DemoPushSingleDeviceMessage()
    {
        $push = new XingeApp($this->appId, $this->secretKey);
        $mess = new Message();
        $mess->setTitle('title');
        $mess->setContent('content');
        $mess->setExpireTime(86400);
        $mess->setSendTime(date('Y-m-d H:i:s'));
        $mess->setType(Message::TYPE_MESSAGE);
        try {
            $ret = $push->PushSingleDevice('token', $mess);
        } catch (\Javareact\Xinge\Exceptions\Exception $e) {
        }
        return $ret;
    }

    /**
     * 下发IOS设备消息
     * @return array|mixed
     */
    public function DemoPushSingleDeviceIOS()
    {
        $push = new XingeApp('88311062e1e79', '66ddd9c5913269c2d4c9659328db29b7');
        $mess = new MessageIOS();
        $mess->setType(MessageIOS::TYPE_APNS_NOTIFICATION);
        $mess->setTitle('title');
        $mess->setContent('content');
        $mess->setExpireTime(86400);
        $mess->setSendTime(date('Y-m-d H:i:s'));
        //$mess->setAlert(array('key1'=>'value1'));
        $mess->setBadge(1);
        $mess->setSound("beep.wav");
        $custom = array('key1' => 'value1', 'key2' => 'value2');
        $mess->setCustom($custom);
        $acceptTime = new TimeInterval(0, 0, 23, 59);
        $mess->addAcceptTime($acceptTime);
        // $raw = '{"xg_max_payload":1,"accept_time":[{"start":{"hour":"20","min":"0"},"end":{"hour":"23","min":"59"}}],"aps":{"alert":"="}}';
        // $mess->setRaw($raw);
        try {
            $ret = $push->PushSingleDevice('token', $mess, XingeApp::IOSENV_DEV);
        } catch (\Javareact\Xinge\Exceptions\Exception $e) {
        }
        return $ret;
    }

    /**
     * 下发单个账号
     * @return array|mixed
     */
    public function DemoPushSingleAccount()
    {
        $push = new XingeApp($this->appId, $this->secretKey);
        $mess = new Message();
        $mess->setType(Message::TYPE_NOTIFICATION);
        $mess->setTitle("title");
        $mess->setContent("中午");
        $mess->setExpireTime(86400);
        $mess->setSendTime(date('Y-m-d H:i:s'));
        try {
            $ret = $push->PushSingleAccount('joelliu', $mess);
        } catch (\Javareact\Xinge\Exceptions\Exception $e) {
        }
        return ($ret);
    }

    /**
     * 下发多个账号， IOS下发多个账号参考DemoPushSingleAccountIOS进行相应修改
     * @return array|mixed
     */
    public function DemoPushAccountList()
    {
        $push = new XingeApp($this->appId, $this->secretKey);
        $mess = new Message();
        $mess->setType(Message::TYPE_NOTIFICATION);
        $mess->setTitle("title");
        $mess->setContent("中午");
        $mess->setExpireTime(86400);
        $mess->setSendTime(date('Y-m-d H:i:s'));
        $accountList = array('joelliu', 'hoepeng');
        try {
            $ret = $push->PushAccountList($accountList, $mess);
        } catch (\Javareact\Xinge\Exceptions\Exception $e) {
        }
        return ($ret);
    }

    /**
     * 下发IOS账号消息
     * @return array|mixed
     */
    public function DemoPushSingleAccountIOS()
    {
        $push = new XingeApp('88311062e1e79', '66ddd9c5913269c2d4c9659328db29b7');
        $mess = new MessageIOS();
        $mess->setType(MessageIOS::TYPE_APNS_NOTIFICATION);
        $mess->setTitle('title');
        $mess->setContent('content');
        $mess->setExpireTime(86400);
        $mess->setSendTime(date('Y-m-d H:i:s'));
        //$mess->setAlert(array('key1'=>'value1'));
        $mess->setBadge(1);
        $mess->setSound("beep.wav");
        $custom = array('key1' => 'value1', 'key2' => 'value2');
        $mess->setCustom($custom);
        $acceptTime1 = new TimeInterval(0, 0, 23, 59);
        $mess->addAcceptTime($acceptTime1);
        try {
            $ret = $push->PushSingleAccount('joelliu', $mess, XingeApp::IOSENV_DEV);
        } catch (\Javareact\Xinge\Exceptions\Exception $e) {
        }
        return $ret;
    }

    /**
     * 下发所有设备
     * @return array|mixed
     */
    public function DemoPushAllDevices()
    {
        $push = new XingeApp($this->appId, $this->secretKey);
        $mess = new Message();
        $mess->setType(Message::TYPE_NOTIFICATION);
        $mess->setTitle("title");
        $mess->setContent("中午");
        $mess->setExpireTime(86400);
        $mess->setSendTime(date('Y-m-d H:i:s'));
        #含义：样式编号0，响铃，震动，不可从通知栏清除，不影响先前通知
        $style  = new Style(0, 1, 1, 0, 0);
        $action = new ClickAction();
        $action->setActionType(ClickAction::TYPE_URL);
        $action->setUrl("http://xg.qq.com");
        #打开url需要用户确认
        $action->setComfirmOnUrl(1);
        $mess->setStyle($style);
        $mess->setAction($action);
        $acceptTime1 = new TimeInterval(0, 0, 23, 59);
        $mess->addAcceptTime($acceptTime1);
        try {
            $ret = $push->PushAllDevices($mess);
        } catch (\Javareact\Xinge\Exceptions\Exception $e) {
        }
        return ($ret);
    }

    /**
     * 下发标签选中设备
     * @return array|mixed
     */
    public function DemoPushTags()
    {
        $push = new XingeApp($this->appId, $this->secretKey);
        $mess = new Message();
        $mess->setType(Message::TYPE_NOTIFICATION);
        $mess->setTitle("title");
        $mess->setContent("中午");
        $mess->setExpireTime(86400);
        $mess->setSendTime(date('Y-m-d H:i:s'));
        $tagList = array('Demo3', 'Demo2');
        try {
            $ret = $push->PushTags($tagList, 'OR', $mess);
        } catch (\Javareact\Xinge\Exceptions\Exception $e) {
        }
        return ($ret);
    }

    /**
     * 查询消息推送状态
     * @return array|mixed
     */
    public function DemoQueryPushStatus()
    {
        $push       = new XingeApp($this->appId, $this->secretKey, $this->accessId);
        $pushIdList = array('31', '32');
        $ret        = $push->QueryPushStatus($pushIdList);
        return ($ret);
    }

    /**
     * 查询设备数量
     * @return mixed
     */
    public function DemoQueryDeviceCount()
    {
        $push = new XingeApp($this->appId, $this->secretKey, $this->accessId);
        $ret  = $push->QueryDeviceCount();
        return ($ret);
    }

    /**
     * 查询标签
     *
     * @return array|mixed
     */
    public function DemoQueryTags()
    {
        $push = new XingeApp($this->appId, $this->secretKey, $this->accessId);
        $ret  = $push->QueryTags(0, 100);
        return ($ret);
    }

    /**
     * 查询某个tag下token的数量
     * @return array|mixed
     */
    public function DemoQueryTagTokenNum()
    {
        $push = new XingeApp($this->appId, $this->secretKey, $this->accessId);
        $ret  = $push->QueryTagTokenNum("tag");
        return ($ret);
    }

    /**
     *
     * 查询某个token的标签
     * @return array|mixed
     */
    public function DemoQueryTokenTags()
    {
        $push = new XingeApp($this->appId, $this->secretKey, $this->accessId);
        $ret  = $push->QueryTokenTags("token");
        return ($ret);
    }

    /**
     * 取消定时任务
     * @return array|mixed
     */
    public function DemoCancelTimingPush()
    {
        $push = new XingeApp($this->appId, $this->secretKey, $this->accessId);
        $ret  = $push->CancelTimingPush("32");
        return ($ret);
    }

    /**
     * 设置标签
     * @return array|mixed
     */
    public function DemoBatchSetTag()
    {
        // 切记把这里的示例tag和示例token修改为你的真实tag和真实token
        $pairs = array();
        array_push($pairs, new TagTokenPair("tag1", "token00000000000000000000000000000000001"));
        array_push($pairs, new TagTokenPair("tag1", "token00000000000000000000000000000000001"));

        $push = new XingeApp($this->appId, $this->secretKey, $this->accessId);
        $ret  = $push->BatchSetTag($pairs);
        return $ret;
    }

    /**
     * 删除标签
     * @return array|mixed
     */
    public function DemoBatchDelTag()
    {
        // 切记把这里的示例tag和示例token修改为你的真实tag和真实token
        $pairs = array();
        array_push($pairs, new TagTokenPair("tag1", "token00000000000000000000000000000000001"));
        array_push($pairs, new TagTokenPair("tag1", "token00000000000000000000000000000000001"));

        $push = new XingeApp($this->appId, $this->secretKey, $this->accessId);
        $ret  = $push->BatchDelTag($pairs);
        return $ret;
    }

    /**
     * 查询某个token的信息
     * @return array|mixed
     */
    public function DemoQueryInfoOfToken()
    {
        $push = new XingeApp($this->appId, $this->secretKey, $this->accessId);
        $ret  = $push->QueryInfoOfToken("token");
        return ($ret);
    }

    /**
     * 查询某个account绑定的token
     * @return array|mixed
     */
    public function DemoQueryTokensOfAccount()
    {
        $push = new XingeApp($this->appId, $this->secretKey, $this->accessId);
        $ret  = $push->QueryTokensOfAccount("nickName");
        return ($ret);
    }

    /**
     * 删除某个account绑定的所有token
     * @return array|mixed
     */
    public function DemoDeleteAllTokensOfAccount()
    {
        $push = new XingeApp($this->appId, $this->secretKey, $this->accessId);
        $ret  = $push->DeleteAllTokensOfAccount("nickName");
        return ($ret);
    }
}

$demoInstance = new Demo();
var_dump($demoInstance->DemoPushSingleDeviceNotification());
var_dump($demoInstance->DemoPushSingleDeviceMessage());
var_dump($demoInstance->DemoPushSingleDeviceIOS());
var_dump($demoInstance->DemoPushSingleAccount());
var_dump($demoInstance->DemoPushAccountList());
var_dump($demoInstance->DemoPushSingleAccountIOS());
var_dump($demoInstance->DemoPushAllDevices());
var_dump($demoInstance->DemoPushTags());
var_dump($demoInstance->DemoQueryPushStatus());
var_dump($demoInstance->DemoQueryDeviceCount());
var_dump($demoInstance->DemoQueryTags());
var_dump($demoInstance->DemoQueryTagTokenNum());
var_dump($demoInstance->DemoQueryTokenTags());
var_dump($demoInstance->DemoCancelTimingPush());
var_dump($demoInstance->DemoBatchDelTag());
var_dump($demoInstance->DemoBatchSetTag());
var_dump($demoInstance->DemoPushAccountListMultipleNotification());
var_dump($demoInstance->DemoPushDeviceListMultipleNotification());
var_dump($demoInstance->DemoQueryInfoOfToken());
var_dump($demoInstance->DemoQueryTokensOfAccount());
var_dump($demoInstance->DemoDeleteTokenOfAccount());
var_dump($demoInstance->DemoDeleteAllTokensOfAccount());