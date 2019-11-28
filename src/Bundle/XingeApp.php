<?php

namespace Javareact\Xinge\Bundle;

use Javareact\Xinge\Exceptions\Exception;
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

    /******************************
     *           V2可用           *
     *****************************/
    /**
     *  查询消息状态
     */
    const RESTAPI_QUERYPUSHSTATUS = 'http://openapi.xg.qq.com/v2/push/get_msg_status';
    /**
     * 查询应用覆盖的设备Token总数
     */
    const RESTAPI_QUERYDEVICECOUNT = 'http://openapi.xg.qq.com/v2/application/get_app_device_num';

    /**
     * 查询全部标签
     */
    const RESTAPI_QUERYTAGS = 'http://openapi.xg.qq.com/v2/tags/query_app_tags';
    /**
     * 取消推送
     */
    const RESTAPI_CANCELTIMINGPUSH = 'http://openapi.xg.qq.com/v2/push/cancel_timing_task';

    /**
     * 批量新增标签
     */
    const RESTAPI_BATCHSETTAG = 'http://openapi.xg.qq.com/v2/tags/batch_set';

    /**
     * 批量删除标签
     */
    const RESTAPI_BATCHDELTAG = 'http://openapi.xg.qq.com/v2/tags/batch_del';

    /**
     * 查询单个指定设备的标签
     */
    const RESTAPI_QUERYTOKENTAGS = 'http://openapi.xg.qq.com/v2/tags/query_token_tags';

    /**
     * 查询单个指定标签的Token总数
     */
    const RESTAPI_QUERYTAGTOKENNUM = 'http://openapi.xg.qq.com/v2/tags/query_tag_token_num';

    /**
     * 查询指定设备Token的注册状态
     */
    const RESTAPI_QUERYINFOOFTOKEN = 'http://openapi.xg.qq.com/v2/application/get_app_token_info';

    /**
     * 查询单个账号关联的设备
     */
    const RESTAPI_QUERYTOKENSOFACCOUNT = 'http://openapi.xg.qq.com/v2/application/get_app_account_tokens';

    /**
     * 删除单个账号关联的单个设备Token
     */
    const RESTAPI_DELETETOKENOFACCOUNT = 'http://openapi.xg.qq.com/v2/application/del_app_account_tokens';

    /**
     * 删除单个账号关联的全部设备Token
     */
    const RESTAPI_DELETEALLTOKENSOFACCOUNT = 'http://openapi.xg.qq.com/v2/application/del_app_account_all_tokens';

    /******************************
     *           V3可用           *
     *****************************/

    /**
     * 账号绑定与解绑（异步批量操作）
     */
    const RESTAPI_ACCOUNT_BATCHOPERATE = 'https://openapi.xg.qq.com/v3/device/account/batchoperate';

    /**
     * 账号-设备绑定查询（实时批量操作）
     */
    const RESTAPI_ACCOUNT_QUERY = 'https://openapi.xg.qq.com/v3/device/account/query';

    /**
     * Tag API
     */
    const RESTAPI_TAG = 'https://openapi.xg.qq.com/v3/device/tag';

    /**
     * 发送
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
     * 请求新版接口V3
     * @param $url
     * @param $params
     * @return mixed
     */
    protected function callRestful($url, $params)
    {
        //$paramsBase      = new ParamsBase($params);
        $extra_curl_conf = array(
            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
            CURLOPT_USERPWD  => $this->appId . ':' . $this->secretKey,//V3采用基础鉴权的方式
        );
        $requestBase     = new RequestBase();
        try {
            $response = $requestBase->exec(
                $url,
                $params,
                RequestBase::METHOD_POST,
                $extra_curl_conf
            );
        } catch (Exception $e) {
            //todo 记录日志
            return null;
        }
        $ret = $this->json2Array($response);
        return $ret;
    }

    /**
     * 请求旧版接口V2
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
        try {
            $response = $requestBase->execForOld(
                $url,
                $params,
                RequestBase::METHOD_POST
            );
        } catch (Exception $e) {
            //todo 记录日志
            return null;
        }
        $ret = $this->json2Array($response);
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
     * 批量新增标签
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
     * 批量删除标签
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
     * 查询指定设备Token的注册状态
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
     * 查询单个账号关联的设备
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
     * 删除单个账号关联的单个设备Token
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
     * 删除单个账号关联的全部设备Token
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

    /**
     * Token追加设置Account
     * @param $token
     * @param $account_list
     * @param $platform
     * @param int $account_type
     * @return array|mixed
     */
    public function AppendAccountByToken(string $token, array $account_list, string $platform, int $account_type = 0)
    {
        $ret = array('ret_code' => -1);
        if (empty($token) || empty($account_list) || empty($platform)) {
            $ret['err_msg'] = 'token or account_list or platform is not valid';
            return $ret;
        }
        $params                  = array();
        $params['operator_type'] = 1;
        $params['platform']      = $platform;
        $lists                   = [];
        $account_lists           = [];
        if ($account_list) {
            foreach ($account_list as $item) {
                $account_lists[] = [
                    'account'      => $item,
                    'account_type' => $account_type
                ];
            }
        }
        array_push($lists, [
            'token'        => $token,
            'account_list' => $account_lists
        ]);
        $params['token_accounts'] = json_encode($lists);
        return $this->callRestful(self::RESTAPI_ACCOUNT_BATCHOPERATE, $params);
    }

    /**
     * Token覆盖绑定Account
     * @param $token
     * @param $account_list
     * @param $platform
     * @param int $account_type
     * @return array|mixed
     */
    public function OverrideAccountByToken(string $token, array $account_list, string $platform, int $account_type = 0)
    {
        $ret = array('ret_code' => -1);
        if (empty($token) || empty($account_list) || empty($platform)) {
            $ret['err_msg'] = 'token or account_list or platform is not valid';
            return $ret;
        }
        $params                  = array();
        $params['operator_type'] = 2;
        $params['platform']      = $platform;
        $lists                   = [];
        $account_lists           = [];
        if ($account_list) {
            foreach ($account_list as $item) {
                $account_lists[] = [
                    'account'      => $item,
                    'account_type' => $account_type
                ];
            }
        }
        array_push($lists, [
            'token'        => $token,
            'account_list' => $account_lists
        ]);
        $params['token_accounts'] = json_encode($lists);
        return $this->callRestful(self::RESTAPI_ACCOUNT_BATCHOPERATE, $params);
    }

    /**
     * Token删除绑定Account
     * @param string $token
     * @param array $account_list 账号列表
     * @param string $platform
     * @param int $account_type
     * @return array|mixed
     */
    public function DelAccountByToken(string $token, array $account_list, string $platform, int $account_type = 0)
    {
        $ret = array('ret_code' => -1);
        if (empty($token) || empty($account_list) || empty($platform)) {
            $ret['err_msg'] = 'token or account_list or platform is not valid';
            return $ret;
        }
        $params                  = array();
        $params['operator_type'] = 3;
        $params['platform']      = $platform;
        $lists                   = [];
        $account_lists           = [];
        if ($account_list) {
            foreach ($account_list as $item) {
                $account_lists[] = [
                    'account'      => $item,
                    'account_type' => $account_type
                ];
            }
        }
        array_push($lists, [
            'token'        => $token,
            'account_list' => $account_lists
        ]);
        $params['token_accounts'] = json_encode($lists);
        return $this->callRestful(self::RESTAPI_ACCOUNT_BATCHOPERATE, $params);
    }

    /**
     * Token删除所有绑定Account
     * @param $token_list
     * @param $platform
     * @param int $account_type
     * @return array|mixed
     */
    public function DelAllAccountByToken($token_list, $platform)
    {
        $ret = array('ret_code' => -1);
        if (empty($token_list) || empty($platform)) {
            $ret['err_msg'] = 'token_list or platform is not valid';
            return $ret;
        }
        $params                  = array();
        $params['operator_type'] = 4;
        $params['platform']      = $platform;
        $params['account_list']  = json_encode($token_list);
        return $this->callRestful(self::RESTAPI_ACCOUNT_BATCHOPERATE, $params);
    }

    /**
     * Account删除所有绑定Token
     * @param $account_list
     * @param string $platform android|ios
     * @param int $account_type
     * @return array|mixed
     */
    public function DelTokenByAccount($account_list, $platform, $account_type = 0)
    {
        $ret = array('ret_code' => -1);
        if (empty($account_list) || empty($platform)) {
            $ret['err_msg'] = 'account_list or platform is not valid';
            return $ret;
        }
        $params                  = array();
        $params['operator_type'] = 5;
        $params['platform']      = $platform;
        $mergeAccount            = [];
        foreach ($account_list as $item) {
            $mergeAccount[] = [
                'account'      => $item,
                'account_type' => $account_type,
            ];
        }
        $params['account_list'] = json_encode($mergeAccount);
        return $this->callRestful(self::RESTAPI_ACCOUNT_BATCHOPERATE, $params);
    }

    /**
     * 根据account批量查询对应token
     * @param array $account_list
     * @param string $platform android|ios
     * @param int $account_type 账号类型
     * @return array|mixed
     */
    public function QueryTokenByAccount(array $account_list, $platform, $account_type = 0)
    {
        $ret = array('ret_code' => -1);
        if (empty($account_list) || empty($platform)) {
            $ret['err_msg'] = 'account_list or platform is not valid';
            return $ret;
        }
        $params                  = array();
        $params['operator_type'] = 1;
        $params['platform']      = $platform;
        $mergeAccount            = [];
        foreach ($account_list as $item) {
            $mergeAccount[] = [
                'account'      => $item,
                'account_type' => $account_type,
            ];
        }
        $params['account_list'] = json_encode($mergeAccount);
        return $this->callRestful(self::RESTAPI_ACCOUNT_QUERY, $params);
    }

    /**
     * 根据token查询account
     * @param $token_list
     * @param string $platform android|ios
     * @return array|mixed
     */
    public function QueryAccountByToken(array $token_list, $platform)
    {
        $ret = array('ret_code' => -1);
        if (empty($token_list) || empty($platform)) {
            $ret['err_msg'] = 'token_list or platform is not valid';
            return $ret;
        }
        $params                  = array();
        $params['operator_type'] = 2;
        $params['platform']      = $platform;
        $params['token_list']    = json_encode($token_list);
        return $this->callRestful(self::RESTAPI_ACCOUNT_QUERY, $params);
    }

    /**
     * 清空指定设备所有tag
     * @param $deviceToken
     * @param $platform
     * @return array|mixed
     */
    public function ClearTags($deviceToken, $platform)
    {
        $ret = array('ret_code' => -1);
        if (empty($deviceToken)) {
            $ret['err_msg'] = 'deviceToken is not valid';
            return $ret;
        }
        $params                  = array();
        $params['operator_type'] = 5;
        $params['platform']      = $platform;
        $params['token_list']    = json_encode([$deviceToken]);
        return $this->callRestful(self::RESTAPI_TAG, $params);
    }
}