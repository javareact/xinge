<?php


namespace Javareact\Auth\Bundle;


use Javareact\Xinge\Bundle\ClickAction;
use Javareact\Xinge\Bundle\Message;
use Javareact\Xinge\Bundle\MessageIOS;
use Javareact\Xinge\Bundle\ParamsBase;
use Javareact\Xinge\Bundle\RequestBase;
use Javareact\Xinge\Bundle\Style;
use Javareact\Xinge\Bundle\TagTokenPair;
use Javareact\Xinge\Exceptions\InvalidArgumentException;

class XingeApp
{

    const DEVICE_ALL      = 0;
    const DEVICE_BROWSER  = 1;
    const DEVICE_PC       = 2;
    const DEVICE_ANDROID  = 3;
    const DEVICE_IOS      = 4;
    const DEVICE_WINPHONE = 5;

    const IOSENV_PROD = 'product';
    const IOSENV_DEV  = 'dev';

    const IOS_MIN_ID = 2200000000;

    /**
     * 应用的接入Id
     * @var string
     */
    private $appId = '';

    /**
     * 应用的skey
     * @var string
     */
    private $secretKey = '';

    /**
     * 应用的skey
     * @var string
     */
    private $accessId = '';

    const RESTAPI_QUERYPUSHSTATUS          = 'http://openapi.xg.qq.com/v2/push/get_msg_status';
    const RESTAPI_QUERYDEVICECOUNT         = 'http://openapi.xg.qq.com/v2/application/get_app_device_num';
    const RESTAPI_QUERYTAGS                = 'http://openapi.xg.qq.com/v2/tags/query_app_tags';
    const RESTAPI_CANCELTIMINGPUSH         = 'http://openapi.xg.qq.com/v2/push/cancel_timing_task';
    const RESTAPI_BATCHSETTAG              = 'http://openapi.xg.qq.com/v2/tags/batch_set';
    const RESTAPI_BATCHDELTAG              = 'http://openapi.xg.qq.com/v2/tags/batch_del';
    const RESTAPI_QUERYTOKENTAGS           = 'http://openapi.xg.qq.com/v2/tags/query_token_tags';
    const RESTAPI_QUERYTAGTOKENNUM         = 'http://openapi.xg.qq.com/v2/tags/query_tag_token_num';
    const RESTAPI_QUERYINFOOFTOKEN         = 'http://openapi.xg.qq.com/v2/application/get_app_token_info';
    const RESTAPI_QUERYTOKENSOFACCOUNT     = 'http://openapi.xg.qq.com/v2/application/get_app_account_tokens';
    const RESTAPI_DELETETOKENOFACCOUNT     = 'http://openapi.xg.qq.com/v2/application/del_app_account_tokens';
    const RESTAPI_DELETEALLTOKENSOFACCOUNT = 'http://openapi.xg.qq.com/v2/application/del_app_account_all_tokens';

    /**
     * v3 接口
     */
    const RESTAPI_PUSH = 'https://openapi.xg.qq.com/v3/push/app';

    /**
     * XingeApp constructor.
     * @param $appId
     * @param $secretKey
     * @param string $accessId
     */
    public function __construct($appId, $secretKey, $accessId = '')
    {
        if (empty($appId) || empty($secretKey)) {
            throw new InvalidArgumentException();
        }
        $this->appId     = $appId;
        $this->secretKey = $secretKey;
        $this->accessId  = $accessId;
    }

    /**
     * 析构函数
     */
    public function __destruct()
    {
    }

    /**
     * 使用默认设置推送消息给单个android设备
     * @param $title
     * @param $content
     * @param $token
     * @return array|mixed
     * @throws \Javareact\Xinge\Exceptions\Exception
     */
    public function PushTokenAndroid($title, $content, $token)
    {
        $mess = new Message();
        $mess->setTitle($title);
        $mess->setContent($content);
        $mess->setType(Message::TYPE_NOTIFICATION);
        $mess->setStyle(new Style(0, 1, 1, 1, 0));
        $action = new ClickAction();
        $action->setActionType(ClickAction::TYPE_ACTIVITY);
        $mess->setAction($action);
        $ret = $this->PushSingleDevice($token, $mess);
        return $ret;
    }

    /**
     * 使用默认设置推送消息给单个ios设备
     * @param $content
     * @param $token
     * @param $environment
     * @return array|mixed
     * @throws \Javareact\Xinge\Exceptions\Exception
     */
    public function PushTokenIos($content, $token, $environment)
    {
        $mess = new MessageIOS();
        $mess->setAlert($content);
        $ret = $this->PushSingleDevice($token, $mess, $environment);
        return $ret;
    }

    /**
     * 使用默认设置推送消息给单个android版账户
     * @param $title
     * @param $content
     * @param $account
     * @return array|mixed
     * @throws \Javareact\Xinge\Exceptions\Exception
     */
    public function PushAccountAndroid($title, $content, $account)
    {
        $mess = new Message();
        $mess->setTitle($title);
        $mess->setContent($content);
        $mess->setType(Message::TYPE_NOTIFICATION);
        $mess->setStyle(new Style(0, 1, 1, 1, 0));
        $action = new ClickAction();
        $action->setActionType(ClickAction::TYPE_ACTIVITY);
        $mess->setAction($action);
        $ret = $this->PushSingleAccount(0, $account, $mess);
        return $ret;
    }

    /**
     * 使用默认设置推送消息给单个ios版账户
     * @param $content
     * @param $account
     * @param $environment
     * @return array|mixed
     * @throws \Javareact\Xinge\Exceptions\Exception
     */
    public function PushAccountIos($content, $account, $environment)
    {
        $mess = new MessageIOS();
        $mess->setAlert($content);
        $ret = $this->PushSingleAccount($account, $mess, $environment);
        return $ret;
    }

    /**
     * 使用默认设置推送消息给所有设备android版
     * @param $title
     * @param $content
     * @return array|mixed
     * @throws \Javareact\Xinge\Exceptions\Exception
     */
    public function PushAllAndroid($title, $content)
    {
        $mess = new Message();
        $mess->setTitle($title);
        $mess->setContent($content);
        $mess->setType(Message::TYPE_NOTIFICATION);
        $mess->setStyle(new Style(0, 1, 1, 1, 0));
        $action = new ClickAction();
        $action->setActionType(ClickAction::TYPE_ACTIVITY);
        $mess->setAction($action);
        $ret = $this->PushAllDevices($mess);
        return $ret;
    }

    /**
     * 使用默认设置推送消息给所有设备ios版
     * @param $content
     * @param $environment
     * @return array|mixed
     * @throws \Javareact\Xinge\Exceptions\Exception
     */
    public function PushAllIos($content, $environment)
    {
        $mess = new MessageIOS();
        $mess->setAlert($content);
        $ret = $this->PushAllDevices($mess, $environment);
        return $ret;
    }

    /**
     * 使用默认设置推送消息给标签选中设备android版
     * @param $title
     * @param $content
     * @param $tag
     * @return array|mixed
     * @throws \Javareact\Xinge\Exceptions\Exception
     */
    public function PushTagAndroid($title, $content, $tag)
    {
        $mess = new Message();
        $mess->setTitle($title);
        $mess->setContent($content);
        $mess->setType(Message::TYPE_NOTIFICATION);
        $mess->setStyle(new Style(0, 1, 1, 1, 0));
        $action = new ClickAction();
        $action->setActionType(ClickAction::TYPE_ACTIVITY);
        $mess->setAction($action);
        $ret = $this->PushTags(array($tag), 'OR', $mess);
        return $ret;
    }

    /**
     * 使用默认设置推送消息给标签选中设备ios版
     * @param $content
     * @param $tag
     * @param $environment
     * @return array|mixed
     * @throws \Javareact\Xinge\Exceptions\Exception
     */
    public function PushTagIos($content, $tag, $environment)
    {
        $mess = new MessageIOS();
        $mess->setAlert($content);
        $ret = $this->PushTags(array($tag), 'OR', $mess, $environment);
        return $ret;
    }

    /**
     * 推送消息给单个设备
     * @param $deviceToken
     * @param $message
     * @param string $environment
     * @return array|mixed
     * @throws \Javareact\Xinge\Exceptions\Exception
     */
    public function PushSingleDevice($deviceToken, $message, $environment = XingeApp::IOSENV_DEV)
    {
        $ret = array('ret_code' => -1, 'err_msg' => 'message not valid');
        if (!($message instanceof Message) && !($message instanceof MessageIOS)) {
            return $ret;
        }
        if ($message instanceof MessageIOS) {
            if ($environment != XingeApp::IOSENV_DEV && $environment != XingeApp::IOSENV_PROD) {
                $ret['err_msg'] = "ios message environment invalid";
                return $ret;
            }
        }
        // if (!$message->isValid()) return $ret;
        $params                  = array();
        $params['audience_type'] = 'token';
        $params['token_list']    = array($deviceToken);
        $params['expire_time']   = $message->getExpireTime();
        $params['send_time']     = $message->getSendTime();
        if ($message instanceof Message) {
            $params['platform']  = 'android'; //android：安卓, ios：苹果, all：安卓&&苹果，仅支持全量推送和标签推送
            $params['multi_pkg'] = $message->getMultiPkg();
        }
        if ($message instanceof MessageIOS) {
            $params['platform']    = 'ios';
            $params['environment'] = $environment;
        }
        $params['message_type'] = $message->getType();
        $params['message']      = $message->toJson();

        $params['timestamp'] = time();
        $params['seq']       = time();

        return $this->callRestful(self::RESTAPI_PUSH, $params);
    }

    /**
     * 推送消息给单个账户
     * @param $account
     * @param $message
     * @param string $environment
     * @return array|mixed
     * @throws \Javareact\Xinge\Exceptions\Exception
     */
    public function PushSingleAccount($account, $message, $environment = XingeApp::IOSENV_DEV)
    {
        $ret = array('ret_code' => -1);
        if (!is_string($account) || empty($account)) {
            $ret['err_msg'] = 'account not valid';
            return $ret;
        }
        if (!($message instanceof Message) && !($message instanceof MessageIOS)) {
            $ret['err_msg'] = 'message is not android or ios';
            return $ret;
        }
        if ($message instanceof MessageIOS) {
            if ($environment != XingeApp::IOSENV_DEV && $environment != XingeApp::IOSENV_PROD) {
                $ret['err_msg'] = "ios message environment invalid";
                return $ret;
            }
        }
        $params                  = array();
        $params['audience_type'] = 'account';
        $params['account_list']  = array($account);
        $params['expire_time']   = $message->getExpireTime();
        $params['send_time']     = $message->getSendTime();
        if ($message instanceof Message) {
            $params['platform']  = 'android'; //android：安卓, ios：苹果, all：安卓&&苹果，仅支持全量推送和标签推送
            $params['multi_pkg'] = $message->getMultiPkg();
        }
        if ($message instanceof MessageIOS) {
            $params['platform']    = 'ios';
            $params['environment'] = $environment;
        }
        $params['message_type'] = $message->getType();
        $params['message']      = $message->toJson();
        $params['timestamp']    = time();
        $params['seq']          = time();

        return $this->callRestful(self::RESTAPI_PUSH, $params);
    }

    /**
     * 推送消息给多个账户
     * @param $tokenList
     * @param $message
     * @param string $environment
     * @return array|mixed
     * @throws \Javareact\Xinge\Exceptions\Exception
     */
    public function PushTokenList($tokenList, $message, $environment = XingeApp::IOSENV_DEV)
    {
        $ret = array('ret_code' => -1);
        if (!is_array($tokenList) || empty($tokenList)) {
            $ret['err_msg'] = 'tokenList not valid';
            return $ret;
        }
        if (!($message instanceof Message) && !($message instanceof MessageIOS)) {
            $ret['err_msg'] = 'message is not android or ios';
            return $ret;
        }
        if ($message instanceof MessageIOS) {
            if ($environment != XingeApp::IOSENV_DEV && $environment != XingeApp::IOSENV_PROD) {
                $ret['err_msg'] = "ios message environment invalid";
                return $ret;
            }
        }
        $params                  = array();
        $params['audience_type'] = 'token_list';
        $params['token_list']    = $tokenList;
        $params['expire_time']   = $message->getExpireTime();
        $params['send_time']     = $message->getSendTime();
        if ($message instanceof Message) {
            $params['platform']  = 'android'; //android：安卓, ios：苹果, all：安卓&&苹果，仅支持全量推送和标签推送
            $params['multi_pkg'] = $message->getMultiPkg();
        }
        if ($message instanceof MessageIOS) {
            $params['platform']    = 'ios';
            $params['environment'] = $environment;
        }
        $params['message_type'] = $message->getType();
        $params['message']      = $message->toJson();
        $params['timestamp']    = time();
        $params['seq']          = time();

        return $this->callRestful(self::RESTAPI_PUSH, $params);
    }

    /**
     * 推送消息给多个账户
     * @param $accountList
     * @param $message
     * @param string $environment
     * @return array|mixed
     * @throws \Javareact\Xinge\Exceptions\Exception
     */
    public function PushAccountList($accountList, $message, $environment = XingeApp::IOSENV_DEV)
    {
        $ret = array('ret_code' => -1);
        if (!is_array($accountList) || empty($accountList)) {
            $ret['err_msg'] = 'accountList not valid';
            return $ret;
        }
        if (!($message instanceof Message) && !($message instanceof MessageIOS)) {
            $ret['err_msg'] = 'message is not android or ios';
            return $ret;
        }
        if ($message instanceof MessageIOS) {
            if ($environment != XingeApp::IOSENV_DEV && $environment != XingeApp::IOSENV_PROD) {
                $ret['err_msg'] = "ios message environment invalid";
                return $ret;
            }
        }
        $params                  = array();
        $params['audience_type'] = 'account';
        $params['account_list']  = $accountList;
        $params['expire_time']   = $message->getExpireTime();
        $params['send_time']     = $message->getSendTime();
        if ($message instanceof Message) {
            $params['platform']  = 'android'; //android：安卓, ios：苹果, all：安卓&&苹果，仅支持全量推送和标签推送
            $params['multi_pkg'] = $message->getMultiPkg();
        }
        if ($message instanceof MessageIOS) {
            $params['platform']    = 'ios';
            $params['environment'] = $environment;
        }
        $params['message_type'] = $message->getType();
        $params['message']      = $message->toJson();
        $params['timestamp']    = time();
        $params['seq']          = time();

        return $this->callRestful(self::RESTAPI_PUSH, $params);
    }

    /**
     * 推送消息给APP所有设备
     * @param $message
     * @param string $environment
     * @return array|mixed
     * @throws \Javareact\Xinge\Exceptions\Exception
     */
    public function PushAllDevices($message, $environment = XingeApp::IOSENV_DEV)
    {
        $ret = array('ret_code' => -1, 'err_msg' => 'message not valid');

        if (!($message instanceof Message) && !($message instanceof MessageIOS)) {
            return $ret;
        }

        if ($message instanceof MessageIOS) {
            if ($environment != XingeApp::IOSENV_DEV && $environment != XingeApp::IOSENV_PROD) {
                $ret['err_msg'] = "ios message environment invalid";
                return $ret;
            }
        }
        $params                  = array();
        $params['audience_type'] = 'all';
        $params['expire_time']   = $message->getExpireTime();
        $params['send_time']     = $message->getSendTime();
        if ($message instanceof Message) {
            $params['platform']  = 'android'; //android：安卓, ios：苹果, all：安卓&&苹果，仅支持全量推送和标签推送
            $params['multi_pkg'] = $message->getMultiPkg();
        }
        if ($message instanceof MessageIOS) {
            $params['platform']    = 'ios';
            $params['environment'] = $environment;
        }
        $params['message_type'] = $message->getType();
        $params['message']      = $message->toJson();
        $params['timestamp']    = time();
        $params['seq']          = time();

        if (!is_null($message->getLoopInterval()) && $message->getLoopInterval() > 0
            && !is_null($message->getLoopTimes()) && $message->getLoopTimes() > 0
        ) {
            $params['loop_interval'] = $message->getLoopInterval();
            $params['loop_times']    = $message->getLoopTimes();
        }

        return $this->callRestful(self::RESTAPI_PUSH, $params);
    }

    /**
     * 推送消息给指定tags的设备
     * 若要推送的tagList只有一项，则tagsOp应为OR
     * @param $tagList
     * @param $tagsOp
     * @param $message
     * @param string $environment
     * @return array|mixed
     * @throws \Javareact\Xinge\Exceptions\Exception
     */
    public function pushTags($tagList, $tagsOp, $message, $environment = XingeApp::IOSENV_DEV)
    {
        $ret = array('ret_code' => -1, 'err_msg' => 'message not valid');
        if (!is_array($tagList) || empty($tagList)) {
            $ret['err_msg'] = 'tagList not valid';
            return $ret;
        }
        if (!is_string($tagsOp) || ($tagsOp != 'AND' && $tagsOp != 'OR')) {
            $ret['err_msg'] = 'tagsOp not valid';
            return $ret;
        }

        if (!($message instanceof Message) && !($message instanceof MessageIOS)) {
            return $ret;
        }

        if ($message instanceof MessageIOS) {
            if ($environment != XingeApp::IOSENV_DEV && $environment != XingeApp::IOSENV_PROD) {
                $ret['err_msg'] = "ios message environment invalid";
                return $ret;
            }
        }

        $params                  = array();
        $params['audience_type'] = 'tag';
        $params['tag_list']      = array(
            'tags' => $tagList,
            'op'   => $tagsOp,
        );
        $params['expire_time']   = $message->getExpireTime();
        $params['send_time']     = $message->getSendTime();
        if ($message instanceof Message) {
            $params['platform']  = 'android'; //android：安卓, ios：苹果, all：安卓&&苹果，仅支持全量推送和标签推送
            $params['multi_pkg'] = $message->getMultiPkg();
        }
        if ($message instanceof MessageIOS) {
            $params['platform']    = 'ios';
            $params['environment'] = $environment;
        }
        $params['message_type'] = $message->getType();
        $params['message']      = $message->toJson();
        $params['timestamp']    = time();
        $params['seq']          = time();

        if (!is_null($message->getLoopInterval()) && $message->getLoopInterval() > 0
            && !is_null($message->getLoopTimes()) && $message->getLoopTimes() > 0
        ) {
            $params['loop_interval'] = $message->getLoopInterval();
            $params['loop_times']    = $message->getLoopTimes();
        }

        return $this->callRestful(self::RESTAPI_PUSH, $params);
    }

    /**
     * 查询消息推送状态
     * @param array $pushIdList pushId(string)数组
     * @return array|mixed
     */
    public function QueryPushStatus($pushIdList)
    {
        $ret    = array('ret_code' => -1);
        $idList = array();
        if (!is_array($pushIdList) || empty($pushIdList)) {
            $ret['err_msg'] = 'pushIdList not valid';
            return $ret;
        }
        foreach ($pushIdList as $pushId) {
            $idList[] = array('push_id' => $pushId);
        }
        $params              = array();
        $params['access_id'] = $this->accessId;
        $params['push_ids']  = json_encode($idList);
        $params['timestamp'] = time();

        return $this->callRestfulForOld(self::RESTAPI_QUERYPUSHSTATUS, $params);
    }

    /**
     * 查询应用覆盖的设备数
     */
    public function QueryDeviceCount()
    {
        $params              = array();
        $params['access_id'] = $this->accessId;
        $params['timestamp'] = time();

        return $this->callRestfulForOld(self::RESTAPI_QUERYDEVICECOUNT, $params);
    }

    /**
     * 查询应用标签
     * @param int $start
     * @param int $limit
     * @return array|mixed
     */
    public function QueryTags($start = 0, $limit = 100)
    {
        $ret = array('ret_code' => -1);
        if (!is_int($start) || !is_int($limit)) {
            $ret['err_msg'] = 'start or limit not valid';
            return $ret;
        }
        $params              = array();
        $params['access_id'] = $this->accessId;
        $params['start']     = $start;
        $params['limit']     = $limit;
        $params['timestamp'] = time();

        return $this->callRestfulForOld(self::RESTAPI_QUERYTAGS, $params);
    }

    /**
     * 查询标签下token数量
     * @param $tag
     * @return array|mixed
     */
    public function QueryTagTokenNum($tag)
    {
        $ret = array('ret_code' => -1);
        if (!is_string($tag)) {
            $ret['err_msg'] = 'tag is not valid';
            return $ret;
        }
        $params              = array();
        $params['access_id'] = $this->accessId;
        $params['tag']       = $tag;
        $params['timestamp'] = time();

        return $this->callRestfulForOld(self::RESTAPI_QUERYTAGTOKENNUM, $params);
    }

    /**
     * 查询token的标签
     * @param $deviceToken
     * @return array|mixed
     */
    public function QueryTokenTags($deviceToken)
    {
        $ret = array('ret_code' => -1);
        if (!is_string($deviceToken)) {
            $ret['err_msg'] = 'deviceToken is not valid';
            return $ret;
        }
        $params                 = array();
        $params['access_id']    = $this->accessId;
        $params['device_token'] = $deviceToken;
        $params['timestamp']    = time();

        return $this->callRestfulForOld(self::RESTAPI_QUERYTOKENTAGS, $params);
    }

    /**
     * 取消定时发送
     * @param $pushId
     * @return array|mixed
     */
    public function CancelTimingPush($pushId)
    {
        $ret = array('ret_code' => -1);
        if (!is_string($pushId) || empty($pushId)) {
            $ret['err_msg'] = 'pushId not valid';
            return $ret;
        }
        $params              = array();
        $params['access_id'] = $this->accessId;
        $params['push_id']   = $pushId;
        $params['timestamp'] = time();

        return $this->callRestfulForOld(self::RESTAPI_CANCELTIMINGPUSH, $params);
    }

    /**
     * json转换为数组
     * @param $json
     * @return mixed
     */
    protected function json2Array($json)
    {
        $json = stripslashes($json);
        return json_decode($json, true);
    }

    /**
     * @param $url
     * @param $params
     * @return mixed
     * @throws \Javareact\Xinge\Exceptions\Exception
     */
    protected function callRestful($url, $params)
    {
        $paramsBase      = new ParamsBase($params);
        $extra_curl_conf = array(
            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
            CURLOPT_USERPWD  => $this->appId . ':' . $this->secretKey,
        );

        $requestBase = new RequestBase();
        $ret         = $this->json2Array($requestBase->exec(
            $url,
            $params,
            RequestBase::METHOD_POST,
            $extra_curl_conf
        ));

        return $ret;
    }

    /**
     * @param $url
     * @param $params
     * @return mixed
     */
    protected function callRestfulForOld($url, $params)
    {
        $paramsBase     = new ParamsBase($params);
        $sign           = $paramsBase->generateSign(RequestBase::METHOD_POST, $url, $this->secretKey);
        $params['sign'] = $sign;
        $requestBase    = new RequestBase();
        $ret            = $this->json2Array($requestBase->execForOld(
            $url,
            $params,
            RequestBase::METHOD_POST
        ));

        return $ret;
    }

    /**
     * @param $token
     * @return bool
     */
    private function ValidateToken($token)
    {
        if ($this->accessId >= 2200000000) {
            return strlen($token) == 64;
        } else {
            return (strlen($token) == 40 || strlen($token) == 64);
        }
    }

    /**
     * @return array
     */
    public function InitParams()
    {

        $params              = array();
        $params['access_id'] = $this->accessId;
        $params['timestamp'] = time();

        return $params;
    }

    /**
     * @param $tagTokenPairs
     * @return array|mixed
     */
    public function BatchSetTag($tagTokenPairs)
    {
        $ret = array('ret_code' => -1);

        foreach ($tagTokenPairs as $pair) {
            if (!($pair instanceof TagTokenPair)) {
                $ret['err_msg'] = 'tag-token pair type error!';
                return $ret;
            }
            if (!$this->ValidateToken($pair->token)) {
                $ret['err_msg'] = sprintf("invalid token %s", $pair->token);
                return $ret;
            }
        }
        $params = $this->InitParams();

        $tag_token_list = array();
        foreach ($tagTokenPairs as $pair) {
            array_push($tag_token_list, array($pair->tag, $pair->token));
        }
        $params['tag_token_list'] = json_encode($tag_token_list);

        return $this->callRestfulForOld(self::RESTAPI_BATCHSETTAG, $params);
    }

    /**
     * @param $tagTokenPairs
     * @return array|mixed
     */
    public function BatchDelTag($tagTokenPairs)
    {
        $ret = array('ret_code' => -1);

        foreach ($tagTokenPairs as $pair) {
            if (!($pair instanceof TagTokenPair)) {
                $ret['err_msg'] = 'tag-token pair type error!';
                return $ret;
            }
            if (!$this->ValidateToken($pair->token)) {
                $ret['err_msg'] = sprintf("invalid token %s", $pair->token);
                return $ret;
            }
        }
        $params = $this->InitParams();

        $tag_token_list = array();
        foreach ($tagTokenPairs as $pair) {
            array_push($tag_token_list, array($pair->tag, $pair->token));
        }
        $params['tag_token_list'] = json_encode($tag_token_list);

        return $this->callRestfulForOld(self::RESTAPI_BATCHDELTAG, $params);
    }

    /**
     * @param $deviceToken
     * @return array|mixed
     */
    public function QueryInfoOfToken($deviceToken)
    {
        $ret = array('ret_code' => -1);
        if (!is_string($deviceToken)) {
            $ret['err_msg'] = 'deviceToken is not valid';
            return $ret;
        }
        $params                 = array();
        $params['access_id']    = $this->accessId;
        $params['device_token'] = $deviceToken;
        $params['timestamp']    = time();

        return $this->callRestfulForOld(self::RESTAPI_QUERYINFOOFTOKEN, $params);
    }

    /**
     * @param $account
     * @return array|mixed
     */
    public function QueryTokensOfAccount($account)
    {
        $ret = array('ret_code' => -1);
        if (!is_string($account)) {
            $ret['err_msg'] = 'account is not valid';
            return $ret;
        }
        $params              = array();
        $params['access_id'] = $this->accessId;
        $params['account']   = $account;
        $params['timestamp'] = time();

        return $this->callRestfulForOld(self::RESTAPI_QUERYTOKENSOFACCOUNT, $params);
    }

    /**
     * @param $account
     * @param $deviceToken
     * @return array|mixed
     */
    public function DeleteTokenOfAccount($account, $deviceToken)
    {
        $ret = array('ret_code' => -1);
        if (!is_string($account) || !is_string($deviceToken)) {
            $ret['err_msg'] = 'account or deviceToken is not valid';
            return $ret;
        }
        $params                 = array();
        $params['access_id']    = $this->accessId;
        $params['account']      = $account;
        $params['device_token'] = $deviceToken;
        $params['timestamp']    = time();

        return $this->callRestfulForOld(self::RESTAPI_DELETETOKENOFACCOUNT, $params);
    }

    /**
     * @param $account
     * @return array|mixed
     */
    public function DeleteAllTokensOfAccount($account)
    {
        $ret = array('ret_code' => -1);
        if (!is_string($account)) {
            $ret['err_msg'] = 'account is not valid';
            return $ret;
        }
        $params              = array();
        $params['access_id'] = $this->accessId;
        $params['account']   = $account;
        $params['timestamp'] = time();

        return $this->callRestfulForOld(self::RESTAPI_DELETEALLTOKENSOFACCOUNT, $params);
    }

}