<?php

namespace Xinge\Tests\Xinge;

use PHPUnit\Framework\TestCase;
use Javareact\Auth\Bundle\XingeApp;
use Javareact\Xinge\Bundle\ClickAction;
use Javareact\Xinge\Bundle\Message;
use Javareact\Xinge\Bundle\MessageIOS;
use Javareact\Xinge\Bundle\Style;
use Javareact\Xinge\Bundle\TagTokenPair;
use Javareact\Xinge\Bundle\TimeInterval;

class XingePushTest extends TestCase
{
    public $appId     = 'appId';
    public $secretKey = 'secretKey';
    public $accessId  = 'accessId';

    public function __construct($appId, $secretKey, $accessId)
    {
        $this->appId     = $appId;
        $this->secretKey = $secretKey;
        $this->accessId  = $accessId;
    }

    /**
     * 单个设备下发通知消息
     * @return array|mixed
     */
    public function testDemoPushSingleDeviceNotification()
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
        $ret = $push->PushSingleDevice('token', $mess);
        return ($ret);
    }

    /**
     * 单个设备下发透传消息       注：透传消息默认不展示
     * @return array|mixed
     */
    public function testDemoPushSingleDeviceMessage()
    {
        $push = new XingeApp($this->appId, $this->secretKey);
        $mess = new Message();
        $mess->setTitle('title');
        $mess->setContent('content');
        $mess->setExpireTime(86400);
        $mess->setSendTime(date('Y-m-d H:i:s'));
        $mess->setType(Message::TYPE_MESSAGE);
        $ret = $push->PushSingleDevice('token', $mess);
        return $ret;
    }

    /**
     * 下发IOS设备消息
     * @return array|mixed
     */
    public function testDemoPushSingleDeviceIOS()
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
        $ret = $push->PushSingleDevice('token', $mess, XingeApp::IOSENV_DEV);
        return $ret;
    }

    /**
     * 下发单个账号
     * @return array|mixed
     */
    public function testDemoPushSingleAccount()
    {
        $push = new XingeApp($this->appId, $this->secretKey);
        $mess = new Message();
        $mess->setType(Message::TYPE_NOTIFICATION);
        $mess->setTitle("title");
        $mess->setContent("中午");
        $mess->setExpireTime(86400);
        $mess->setSendTime(date('Y-m-d H:i:s'));
        $ret = $push->PushSingleAccount('joelliu', $mess);
        return ($ret);
    }

    /**
     * 下发多个账号， IOS下发多个账号参考DemoPushSingleAccountIOS进行相应修改
     * @return array|mixed
     */
    public function testDemoPushAccountList()
    {
        $push = new XingeApp($this->appId, $this->secretKey);
        $mess = new Message();
        $mess->setType(Message::TYPE_NOTIFICATION);
        $mess->setTitle("title");
        $mess->setContent("中午");
        $mess->setExpireTime(86400);
        $mess->setSendTime(date('Y-m-d H:i:s'));
        $accountList = array('joelliu', 'hoepeng');
        $ret         = $push->PushAccountList($accountList, $mess);
        return ($ret);
    }

    /**
     * 下发IOS账号消息
     * @return array|mixed
     */
    public function testDemoPushSingleAccountIOS()
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
        $ret = $push->PushSingleAccount('joelliu', $mess, XingeApp::IOSENV_DEV);
        return $ret;
    }

    /**
     * 下发所有设备
     * @return array|mixed
     */
    public function testDemoPushAllDevices()
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
        $ret = $push->PushAllDevices($mess);
        return ($ret);
    }

    /**
     * 下发标签选中设备
     * @return array|mixed
     */
    public function testDemoPushTags()
    {
        $push = new XingeApp($this->appId, $this->secretKey);
        $mess = new Message();
        $mess->setType(Message::TYPE_NOTIFICATION);
        $mess->setTitle("title");
        $mess->setContent("中午");
        $mess->setExpireTime(86400);
        $mess->setSendTime(date('Y-m-d H:i:s'));
        $tagList = array('Demo3', 'Demo2');
        $ret     = $push->PushTags($tagList, 'OR', $mess);
        return ($ret);
    }

    /**
     * 查询消息推送状态
     * @return array|mixed
     */
    public function testDemoQueryPushStatus()
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
    public function testDemoQueryDeviceCount()
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
    public function testDemoQueryTags()
    {
        $push = new XingeApp($this->appId, $this->secretKey, $this->accessId);
        $ret  = $push->QueryTags(0, 100);
        return ($ret);
    }

    /**
     * 查询某个tag下token的数量
     * @return array|mixed
     */
    public function testDemoQueryTagTokenNum()
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
    public function testDemoQueryTokenTags()
    {
        $push = new XingeApp($this->appId, $this->secretKey, $this->accessId);
        $ret  = $push->QueryTokenTags("token");
        return ($ret);
    }

    /**
     * 取消定时任务
     * @return array|mixed
     */
    public function testDemoCancelTimingPush()
    {
        $push = new XingeApp($this->appId, $this->secretKey, $this->accessId);
        $ret  = $push->CancelTimingPush("32");
        return ($ret);
    }

    /**
     * 设置标签
     * @return array|mixed
     */
    public function testDemoBatchSetTag()
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
    public function testDemoBatchDelTag()
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
    public function testDemoQueryInfoOfToken()
    {
        $push = new XingeApp($this->appId, $this->secretKey, $this->accessId);
        $ret  = $push->QueryInfoOfToken("token");
        return ($ret);
    }

    /**
     * 查询某个account绑定的token
     * @return array|mixed
     */
    public function testDemoQueryTokensOfAccount()
    {
        $push = new XingeApp($this->appId, $this->secretKey, $this->accessId);
        $ret  = $push->QueryTokensOfAccount("nickName");
        return ($ret);
    }

    /**
     * 删除某个account绑定的所有token
     * @return array|mixed
     */
    public function testDemoDeleteAllTokensOfAccount()
    {
        $push = new XingeApp($this->appId, $this->secretKey, $this->accessId);
        $ret  = $push->DeleteAllTokensOfAccount("nickName");
        return ($ret);
    }
}