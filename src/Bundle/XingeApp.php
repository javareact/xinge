<?php

namespace Javareact\Xinge\Bundle;

use Javareact\Xinge\Collection\HashMap;
use Javareact\Xinge\Entity\Message;
use Javareact\Xinge\Entity\MessageIOS;
use Javareact\Xinge\Entity\TagTokenPair;
use Javareact\Xinge\Exceptions\Exception;
use Javareact\Xinge\Exceptions\InvalidArgumentException;

class XingeApp
{

    const RESTAPI_PUSHSINGLEDEVICE         = "http://openapi.xg.qq.com/v2/push/single_device";
    const RESTAPI_PUSHSINGLEACCOUNT        = "http://openapi.xg.qq.com/v2/push/single_account";
    const RESTAPI_PUSHACCOUNTLIST          = "http://openapi.xg.qq.com/v2/push/account_list";
    const RESTAPI_PUSHALLDEVICE            = "http://openapi.xg.qq.com/v2/push/all_device";
    const RESTAPI_PUSHTAGS                 = "http://openapi.xg.qq.com/v2/push/tags_device";
    const RESTAPI_QUERYPUSHSTATUS          = "http://openapi.xg.qq.com/v2/push/get_msg_status";
    const RESTAPI_QUERYDEVICECOUNT         = "http://openapi.xg.qq.com/v2/application/get_app_device_num";
    const RESTAPI_QUERYTAGS                = "http://openapi.xg.qq.com/v2/tags/query_app_tags";
    const RESTAPI_CANCELTIMINGPUSH         = "http://openapi.xg.qq.com/v2/push/cancel_timing_task";
    const RESTAPI_BATCHSETTAG              = "http://openapi.xg.qq.com/v2/tags/batch_set";
    const RESTAPI_BATCHDELTAG              = "http://openapi.xg.qq.com/v2/tags/batch_del";
    const RESTAPI_QUERYTOKENTAGS           = "http://openapi.xg.qq.com/v2/tags/query_token_tags";
    const RESTAPI_QUERYTAGTOKENNUM         = "http://openapi.xg.qq.com/v2/tags/query_tag_token_num";
    const RESTAPI_CREATEMULTIPUSH          = "http://openapi.xg.qq.com/v2/push/create_multipush";
    const RESTAPI_PUSHACCOUNTLISTMULTIPLE  = "http://openapi.xg.qq.com/v2/push/account_list_multiple";
    const RESTAPI_PUSHDEVICELISTMULTIPLE   = "http://openapi.xg.qq.com/v2/push/device_list_multiple";
    const RESTAPI_QUERYINFOOFTOKEN         = "http://openapi.xg.qq.com/v2/application/get_app_token_info";
    const RESTAPI_QUERYTOKENSOFACCOUNT     = "http://openapi.xg.qq.com/v2/application/get_app_account_tokens";
    const RESTAPI_DELETETOKENOFACCOUNT     = "http://openapi.xg.qq.com/v2/application/del_app_account_tokens";
    const RESTAPI_DELETEALLTOKENSOFACCOUNT = "http://openapi.xg.qq.com/v2/application/del_app_account_all_tokens";
    /** @var string v3接口 */
    const RESTAPI_BATCHOPERATEACCOUNT = "https://openapi.xg.qq.com/v3/device/account/batchoperate";

    const DEVICE_ALL      = 0;
    const DEVICE_BROWSER  = 1;
    const DEVICE_PC       = 2;
    const DEVICE_ANDROID  = 3;
    const DEVICE_IOS      = 4;
    const DEVICE_WINPHONE = 5;

    const ANDROID_STR = 'android';
    const IOS_STR     = 'ios';

    const IOSENV_PROD = 1;
    const IOSENV_DEV  = 2;

    const IOS_MIN_ID = 2200000000;

    private $m_appId;
    private $m_accessId;
    private $m_secretKey;

    /**
     * XingeApp constructor.
     * @param string $appId
     * @param string $secretKey
     * @param string $accessId
     */
    public function __construct($accessId, $secretKey, $appId = '')
    {
        if (empty($accessId) || empty($secretKey)) {
            throw new InvalidArgumentException();
        }
        $this->m_appId     = $appId;
        $this->m_accessId  = $accessId;
        $this->m_secretKey = $secretKey;
    }

    /**
     * 析构函数
     */
    public function __destruct()
    {
    }

    /**
     * 判断是否为合法JSON字符串
     * @param $string
     * @return bool
     */
    public function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    /**
     * json转换为数组
     * @param $json
     * @return mixed
     */
    protected function json2Array($json)
    {
        $json = stripslashes($json);
        if (!$this->isJson($json)) {
            return $json;
        }
        return json_decode($json, true);
    }

    protected function ValidateMessageType()
    {
        if ($this->m_accessId < self::IOS_MIN_ID)
            return true;
        else
            return false;
    }

    private function ValidateMessageTypeIos($environment)
    {
        if ($this->m_accessId >= self::IOS_MIN_ID && ($environment == self::IOSENV_PROD || $environment == self::IOSENV_DEV))
            return true;
        else
            return false;
    }

    /**
     * 验证TOKEN
     * @param $token
     * @return bool
     */
    private function ValidateToken($token)
    {
        if ($this->m_accessId >= self::IOS_MIN_ID) {
            return strlen($token) == 64;
        } else {
            return (strlen($token) == 40 || strlen($token) == 64);
        }
    }

    /**
     * 增加公共参数
     * @return HashMap
     */
    public function InitParams()
    {
        $params = new HashMap();
        $params->put("access_id", $this->m_accessId);
        $params->put("timestamp", time());
        return $params;
    }

    /**
     * 请求接口V2
     * @param $url
     * @param $params
     * @return mixed
     */
    protected function callRestful($url, HashMap $params)
    {
        $paramsBase = new ParamsBase($params);
        $sign       = $paramsBase->generateSign(RequestBase::METHOD_POST, $url, $this->m_secretKey);
        $params->put('sign', $sign);
        $requestBase = new RequestBase();
        try {
            $response = $requestBase->exec(
                $url,
                $params,
                RequestBase::METHOD_POST
            );
        } catch (Exception $e) {
            return null;
        }
        $ret = $this->json2Array($response);
        return $ret;
    }

    /**
     * 请求接口V3
     * @param $url
     * @param $params
     * @return mixed
     */
    public function callRestfulV3($url, HashMap $params)
    {
        $requestBase     = new RequestBase();
        $extra_curl_conf = array(
            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
            CURLOPT_USERPWD  => $this->m_appId . ':' . $this->m_secretKey,
        );
        try {
            $response = $requestBase->execV3(
                $url,
                $params,
                RequestBase::METHOD_POST,
                $extra_curl_conf
            );
        } catch (Exception $e) {
            return null;
        }
        $ret = $this->json2Array($response);
        return $ret;
    }


    //简易API接口
    //详细API接口
    /**
     * 推送给指定设备，限Android系统使用
     *
     * @param string $deviceToken 目标设备token
     * @param Message $message 待推送的消息
     * @return array 服务器执行结果，JSON形式
     */
    public function pushSingleDevice(string $deviceToken, Message $message)
    {
        if (!$this->ValidateMessageType()) {
            return ['ret_code' => -1, 'err_msg' => 'message type error!'];
        }
        if (!$message->isValid()) {
            return ['ret_code' => -1, 'err_msg' => 'message invalid!'];
        }
        $params = new HashMap;
        $params->put("access_id", $this->m_accessId);
        $params->put("expire_time", $message->getExpireTime());
        $params->put("send_time", $message->getSendTime());
        $params->put("multi_pkg", $message->getMultiPkg());
        $params->put("device_token", $deviceToken);
        $params->put("message_type", $message->getType());
        $params->put("message", $message->toJson());
        $params->put("timestamp", time());
        return $this->callRestful(self:: RESTAPI_PUSHSINGLEDEVICE, $params);
    }

    /**
     * 推送给指定设备，限iOS系统使用
     *
     * @param string $deviceToken 目标设备token
     * @param MessageIOS $message 待推送的消息
     * @param string $environment 推送的目标环境 必须是其中一种： {@link #IOSENV_PROD}生产环境 {@link #IOSENV_DEV}开发环境
     * @return array 服务器执行结果，JSON形式
     */
    public function pushSingleDeviceIos($deviceToken, MessageIOS $message, $environment)
    {
        if (!$this->ValidateMessageTypeIos($environment)) {
            return ['ret_code' => -1, 'err_msg' => 'message type or environment error!'];
        }
        if (!$message->isValid()) {
            return ['ret_code' => -1, 'err_msg' => 'message invalid!'];
        }
        $params = new HashMap;
        $params->put("access_id", $this->m_accessId);
        $params->put("expire_time", $message->getExpireTime());
        $params->put("send_time", $message->getSendTime());
        $params->put("device_token", $deviceToken);
        $params->put("message_type", $message->getType());
        $params->put("message", $message->toJson());
        $params->put("timestamp", time());
        $params->put("environment", $environment);
        if ($message->getLoopInterval() > 0 && $message->getLoopTimes() > 0) {
            $params->put("loop_interval", $message->getLoopInterval());
            $params->put("loop_times", $message->getLoopTimes());
        }
        return $this->callRestful(self:: RESTAPI_PUSHSINGLEDEVICE, $params);
    }
//

    /**
     * 推送给指定账号，限Android系统使用
     *
     * @param int $deviceType 设备类型，请填0
     * @param string $account 目标账号
     * @param Message $message 待推送的消息
     * @return array 服务器执行结果，JSON形式
     */
    public function pushSingleAccount($account, Message $message, $deviceType = 0)
    {
        if (!$this->ValidateMessageType()) {
            return ['ret_code' => -1, 'err_msg' => 'message type error!'];
        }
        if (!$message->isValid()) {
            return ['ret_code' => -1, 'err_msg' => 'message invalid!'];
        }
        $params = new HashMap;
        $params->put("access_id", $this->m_accessId);
        $params->put("expire_time", $message->getExpireTime());
        $params->put("send_time", $message->getSendTime());
        $params->put("multi_pkg", $message->getMultiPkg());
        $params->put("device_type", $deviceType);
        $params->put("account", $account);
        $params->put("message_type", $message->getType());
        $params->put("message", $message->toJson());
        $params->put("timestamp", time());
        return $this->callRestful(self:: RESTAPI_PUSHSINGLEACCOUNT, $params);
    }


    /**
     * 推送给指定账号，限iOS系统使用
     *
     * @param int $deviceType 设备类型，请填0
     * @param string $account 目标账号
     * @param MessageIOS $message 待推送的消息
     * @param int $environment 推送的目标环境 必须是其中一种： {@link #IOSENV_PROD}生产环境 {@link #IOSENV_DEV}开发环境
     * @return array 服务器执行结果，JSON形式
     */
    public function pushSingleAccountIos(string $account, MessageIOS $message, $environment, int $deviceType = 0)
    {
        if (!$this->ValidateMessageTypeIos($environment)) {
            return ['ret_code' => -1, 'err_msg' => 'message type or environment error!'];
        }
        if (!$message->isValid()) {
            return ['ret_code' => -1, 'err_msg' => 'message invalid!'];
        }
        $params = new HashMap;
        $params->put("access_id", $this->m_accessId);
        $params->put("expire_time", $message->getExpireTime());
        $params->put("send_time", $message->getSendTime());
        $params->put("device_type", $deviceType);
        $params->put("account", $account);
        $params->put("message_type", $message->getType());
        $params->put("message", $message->toJson());
        $params->put("timestamp", time());
        $params->put("environment", $environment);
        return $this->callRestful(self::RESTAPI_PUSHSINGLEACCOUNT, $params);
    }

    /**
     * 推送给多个账号，限Android设备使用 <br/>
     * 如果目标账号数超过10000，建议改用{@link #pushAccountListMultiple}接口
     *
     * @param int $deviceType 设备类型，请填0
     * @param array $accoun $accountListtList 目标账号列表
     * @param Message $message 待推送的消息
     * @return array 服务器执行结果，JSON形式
     */
    public function pushAccountList(array $accountList, Message $message, int $deviceType = 0)
    {
        if (!$this->ValidateMessageType()) {
            return ['ret_code' => -1, 'err_msg' => 'message type error!'];
        }
        if (!$message->isValid()) {
            return ['ret_code' => -1, 'err_msg' => 'message invalid!'];
        }
        $params = new HashMap;
        $params->put("access_id", $this->m_accessId);
        $params->put("expire_time", $message->getExpireTime());
        $params->put("multi_pkg", $message->getMultiPkg());
        $params->put("device_type", $deviceType);
        $params->put("account_list", json_encode($accountList));
        $params->put("message_type", $message->getType());
        $params->put("message", $message->toJson());
        $params->put("timestamp", time());
        return $this->callRestful(self::RESTAPI_PUSHACCOUNTLIST, $params);
    }
//

    /**
     * 推送给多个账号，限iOS设备使用 <br/>
     * 如果目标账号数超过10000，建议改用{@link #pushAccountListMultiple}接口
     *
     * @param int $deviceType 设备类型，请填0
     * @param array $accountList 目标账号列表
     * @param MessageIOS $message 待推送的消息
     * @param int $environment 推送的目标环境 必须是其中一种： {@link #IOSENV_PROD}生产环境 {@link #IOSENV_DEV}开发环境
     * @return array 服务器执行结果，JSON形式
     */
    public function pushAccountListIos(array $accountList, MessageIOS $message, int $environment, int $deviceType = 0)
    {
        if (!$this->ValidateMessageTypeIos($environment)) {
            return ['ret_code' => -1, 'err_msg' => 'message type or environment error!'];
        }
        if (!$message->isValid()) {
            return ['ret_code' => -1, 'err_msg' => 'message invalid!'];
        }
        $params = new HashMap;
        $params->put("access_id", $this->m_accessId);
        $params->put("expire_time", $message->getExpireTime());
        $params->put("device_type", $deviceType);
        $params->put("account_list", json_encode($accountList));
        $params->put("message_type", $message->getType());
        $params->put("message", $message->toJson());
        $params->put("timestamp", time());
        $params->put("environment", $environment);
        return $this->callRestful(self::RESTAPI_PUSHACCOUNTLIST, $params);
    }

    /**
     * 推送给全量设备，限Android系统使用
     *
     * @param int $deviceType 请填0
     * @param Message $message 待推送的消息
     * @return array 服务器执行结果，JSON形式
     */
    public function pushAllDevice(Message $message, int $deviceType = 0)
    {
        if (!$this->ValidateMessageType()) {
            return ['ret_code' => -1, 'err_msg' => 'message type error!'];
        }
        if (!$message->isValid()) {
            return ['ret_code' => -1, 'err_msg' => 'message invalid!'];
        }
        $params = new HashMap;
        $params->put("access_id", $this->m_accessId);
        $params->put("expire_time", $message->getExpireTime());
        $params->put("send_time", $message->getSendTime());
        $params->put("multi_pkg", $message->getMultiPkg());
        $params->put("device_type", $deviceType);
        $params->put("message_type", $message->getType());
        $params->put("message", $message->toJson());
        $params->put("timestamp", time());
        if ($message->getLoopInterval() > 0 && $message->getLoopTimes() > 0) {
            $params->put("loop_interval", $message->getLoopInterval());
            $params->put("loop_times", $message->getLoopTimes());
        }
        return $this->callRestful(self::RESTAPI_PUSHALLDEVICE, $params);
    }

    /**
     * 推送给全量设备，限iOS系统使用
     *
     * @param int $deviceType 设备类型，请填0
     * @param MessageIOS $message 待推送的消息
     * @param int $environment 推送的目标环境 必须是其中一种： {@link #IOSENV_PROD}生产环境 {@link #IOSENV_DEV}开发环境
     * @return array 服务器执行结果，JSON形式
     */
    public function pushAllDeviceIos(MessageIOS $message, int $environment, int $deviceType = 0)
    {
        if (!$this->ValidateMessageTypeIos($environment)) {
            return ['ret_code' => -1, 'err_msg' => 'message type or environment error!'];
        }
        if (!$message->isValid()) {
            return ['ret_code' => -1, 'err_msg' => 'message invalid!'];
        }
        $params = new HashMap;
        $params->put("access_id", $this->m_accessId);
        $params->put("expire_time", $message->getExpireTime());
        $params->put("send_time", $message->getSendTime());
        $params->put("device_type", $deviceType);
        $params->put("message_type", $message->getType());
        $params->put("message", $message->toJson());
        $params->put("timestamp", time());
        $params->put("environment", $environment);
        if ($message->getLoopInterval() > 0 && $message->getLoopTimes() > 0) {
            $params->put("loop_interval", $message->getLoopInterval());
            $params->put("loop_times", $message->getLoopTimes());
        }
        return $this->callRestful(self::RESTAPI_PUSHALLDEVICE, $params);
    }


    /**
     * 推送给多个tags对应的设备，限Android系统使用
     *
     * @param int deviceType 设备类型，请填0
     * @param array tagList 指定推送的tag列表
     * @param string  tagOp 多个tag的运算关系，取值必须是下面之一： AND OR
     * @param message 待推送的消息
     * @return array 服务器执行结果，JSON形式
     */
    public function pushTags($tagList, $tagOp, Message $message, int $deviceType = 0)
    {
        if (!$this->ValidateMessageType()) {
            return ['ret_code' => -1, 'err_msg' => 'message type error!'];
        }
        if (!$message->isValid() || count($tagList) == 0 || (!$tagOp == "AND" && !$tagOp == "OR")) {
            return ['ret_code' => -1, 'err_msg' => 'param invalid!'];
        }
        $params = new HashMap;
        $params->put("access_id", $this->m_accessId);
        $params->put("expire_time", $message->getExpireTime());
        $params->put("send_time", $message->getSendTime());
        $params->put("multi_pkg", $message->getMultiPkg());
        $params->put("device_type", $deviceType);
        $params->put("message_type", $message->getType());
        $params->put("tags_list", json_encode($tagList));
        $params->put("tags_op", $tagOp);
        $params->put("message", $message->toJson());
        $params->put("timestamp", time());
        if ($message->getLoopInterval() > 0 && $message->getLoopTimes() > 0) {
            $params->put("loop_interval", $message->getLoopInterval());
            $params->put("loop_times", $message->getLoopTimes());
        }
        return $this->callRestful(self::RESTAPI_PUSHTAGS, $params);
    }
//

    /**
     * 推送给多个tags对应的设备，限iOS系统使用
     *
     * @param int $deviceType 设备类型，请填0
     * @param array $tagList 指定推送的tag列表
     * @param string $tagOp 多个tag的运算关系，取值必须是下面之一： AND OR
     * @param MessageIOS $message 待推送的消息
     * @param int $environment 推送的目标环境 必须是其中一种： {@link #IOSENV_PROD}生产环境 {@link #IOSENV_DEV}开发环境
     * @return array 服务器执行结果，JSON形式
     */
    public function pushTagsIos(array $tagList, $tagOp, MessageIOS $message, int $environment, int $deviceType = 0)
    {
        if (!$this->ValidateMessageTypeIos($environment)) {
            return ['ret_code' => -1, 'err_msg' => 'message type or environment error!'];
        }
        if (!$message->isValid() || count($tagList) == 0 || (!$tagOp == 'AND' && !$tagOp == 'OR')) {
            return ['ret_code' => -1, 'err_msg' => 'param invalid!'];
        }
        $params = new HashMap;
        $params->put("access_id", $this->m_accessId);
        $params->put("expire_time", $message->getExpireTime());
        $params->put("send_time", $message->getSendTime());
        $params->put("device_type", $deviceType);
        $params->put("message_type", $message->getType());
        $params->put("tags_list", json_encode($tagList));
        $params->put("tags_op", $tagOp);
        $params->put("message", $message->toJson());
        $params->put("timestamp", time());
        $params->put("environment", $environment);
        if ($message->getLoopInterval() > 0 && $message->getLoopTimes() > 0) {
            $params->put("loop_interval", $message->getLoopInterval());
            $params->put("loop_times", $message->getLoopTimes());
        }
        return $this->callRestful(self::RESTAPI_PUSHTAGS, $params);
    }
//

    /**
     * 创建大批量推送消息，后续可调用{@link #pushAccountListMultiple}或{@link #pushDeviceListMultiple}接口批量添加设备，限Android系统使用<br/>
     * 此接口创建的任务不支持定时推送
     *
     * @param Message $message 待推送的消息
     * @return array 服务器执行结果，JSON形式
     */
    public function createMultipush(Message $message)
    {
        if (!$this->ValidateMessageType()) {
            return ['ret_code' => -1, 'err_msg' => 'message type error!'];
        }
        if (!$message->isValid()) {
            return ['ret_code' => -1, 'err_msg' => 'message invalid!'];
        }
        $params = new HashMap;
        $params->put("access_id", $this->m_accessId);
        $params->put("expire_time", $message->getExpireTime());
        $params->put("multi_pkg", $message->getMultiPkg());
        $params->put("message_type", $message->getType());
        $params->put("message", $message->toJson());
        $params->put("timestamp", time());

        return $this->callRestful(self::RESTAPI_CREATEMULTIPUSH, $params);
    }

    /**
     * 创建大批量推送消息，后续可调用{@link #pushAccountListMultiple}或{@link #pushDeviceListMultiple}接口批量添加设备，限iOS系统使用<br/>
     * 此接口创建的任务不支持定时推送
     *
     * @param MessageIOS $message 待推送的消息
     * @param int $environment 推送的目标环境 必须是其中一种： {@link #IOSENV_PROD}生产环境 {@link #IOSENV_DEV}开发环境
     * @return array 服务器执行结果，JSON形式
     */
    public function createMultipushIos(MessageIOS $message, int $environment)
    {
        if (!$this->ValidateMessageTypeIos($environment)) {
            return ['ret_code' => -1, 'err_msg' => 'message type or environment error!'];
        }
        if (!$message->isValid()) {
            return ['ret_code' => -1, 'err_msg' => 'message invalid!'];
        }
        $params = new HashMap;
        $params->put("access_id", $this->m_accessId);
        $params->put("expire_time", $message->getExpireTime());
        $params->put("message_type", $message->getType());
        $params->put("message", $message->toJson());
        $params->put("timestamp", time());
        $params->put("environment", $environment);
        return $this->callRestful(self::RESTAPI_CREATEMULTIPUSH, $params);
    }


    /**
     * 推送消息给大批量账号，可对同一个pushId多次调用此接口，限Android系统使用 <br/>
     * 建议用户采用此接口自行控制发送时间
     *
     * @param int $pushId {@link #createMultipush}返回的push_id
     * @param array $accountList 账号列表，数量最多为1000个
     * @return array 服务器执行结果，JSON形式
     */
    public function pushAccountListMultiple($pushId, array $accountList)
    {
        if ($pushId <= 0) {
            return ['ret_code' => -1, 'err_msg' => 'pushId invalid!'];
        }
        $params = new HashMap;
        $params->put("access_id", $this->m_accessId);
        $params->put("push_id", $pushId);
        $params->put("account_list", json_encode($accountList));
        $params->put("timestamp", time());
        return $this->callRestful(self::RESTAPI_PUSHACCOUNTLISTMULTIPLE, $params);
    }

    /**
     * 推送消息给大批量设备，可对同一个pushId多次调用此接口，限Android系统使用 <br/>
     * 建议用户采用此接口自行控制发送时间
     *
     * @param int $pushId {@link #createMultipush}返回的push_id
     * @param array $deviceList 设备列表，数量最多为1000个
     * @return array 服务器执行结果，JSON形式
     */
    public function pushDeviceListMultipleIos($pushId, array $deviceList)
    {
        if ($pushId <= 0) {
            return ['ret_code' => -1, 'err_msg' => 'pushId invalid!'];
        }
        $params = new HashMap;
        $params->put("access_id", $this->m_accessId);
        $params->put("push_id", $pushId);
        $params->put("device_list", json_encode($deviceList));
        $params->put("timestamp", time());
        return $this->callRestful(self::RESTAPI_PUSHDEVICELISTMULTIPLE, $params);
    }
//

    /**
     * 查询群发消息的状态，可同时查询多个pushId状态
     *
     * @param array $pushIdList 各类推送任务返回的push_id，可以一次查询多个
     * @return array 服务器执行结果，JSON形式
     */
    public function queryPushStatus($pushIdList)
    {
        $params = new HashMap;
        $params->put("access_id", $this->m_accessId);
        $params->put("timestamp", time());
        $jArray = [];
        foreach ($pushIdList as $item) {
            $jArray[] = [
                'push_id' => $item
            ];
        }
        $params->put("push_ids", json_encode($jArray));
        return $this->callRestful(self::RESTAPI_QUERYPUSHSTATUS, $params);
    }
//

    /**
     * 查询应用覆盖的设备数
     *
     * @return array 服务器执行结果，JSON形式
     */
    public function queryDeviceCount()
    {
        $params = new HashMap;
        $params->put("access_id", $this->m_accessId);
        $params->put("timestamp", time());
        return $this->callRestful(self::RESTAPI_QUERYDEVICECOUNT, $params);
    }
//

    /**
     * 查询应用当前所有的tags
     *
     * @param int $start 从哪个index开始
     * @param int $limit 限制结果数量，最多取多少个tag
     * @return array 服务器执行结果，JSON形式
     */
    public function queryTags(int $start = 0, int $limit = 100)
    {
        $params = new HashMap;
        $params->put("access_id", $this->m_accessId);
        $params->put("start", $start);
        $params->put("limit", $limit);
        $params->put("timestamp", time());
        return $this->callRestful(self::RESTAPI_QUERYTAGS, $params);
    }


    /**
     * 查询带有指定tag的设备数量
     *
     * @param string $tag 指定的标签
     * @return array 服务器执行结果，JSON形式
     */
    public function queryTagTokenNum(string $tag)
    {
        $params = new HashMap;
        $params->put("access_id", $this->m_accessId);
        $params->put("tag", $tag);
        $params->put("timestamp", time());
        return $this->callRestful(self::RESTAPI_QUERYTAGTOKENNUM, $params);
    }
//

    /**
     * 查询设备下所有的tag
     *
     * @param string $deviceToken 目标设备token
     * @return array 服务器执行结果，JSON形式
     */
    public function queryTokenTags(string $deviceToken)
    {
        $params = new HashMap;
        $params->put("access_id", $this->m_accessId);
        $params->put("device_token", $deviceToken);
        $params->put("timestamp", time());
        return $this->callRestful(self::RESTAPI_QUERYTOKENTAGS, $params);
    }

    /**
     * 取消尚未推送的定时任务
     *
     * @param string $pushId 各类推送任务返回的push_id
     * @return array 服务器执行结果，JSON形式
     */
    public function cancelTimingPush(string $pushId)
    {
        $params = new HashMap;
        $params->put("access_id", $this->m_accessId);
        $params->put("push_id", $pushId);
        $params->put("timestamp", time());
        return $this->callRestful(self::RESTAPI_CANCELTIMINGPUSH, $params);
    }

    /**
     * 批量新增标签
     * @param array $tagTokenPairs
     * @return array|mixed
     */
    public function BatchSetTag(array $tagTokenPairs)
    {
        foreach ($tagTokenPairs as $pair) {
            if (!($pair instanceof TagTokenPair)) {
                return ['ret_code' => -1, 'err_msg' => 'tag-token pair type error!'];
            }
            if (!$this->ValidateToken($pair->token)) {
                return ['ret_code' => -1, 'err_msg' => sprintf("invalid token %s", $pair->token)];
            }
        }
        $params         = $this->InitParams();
        $tag_token_list = array();
        foreach ($tagTokenPairs as $pair) {
            array_push($tag_token_list, array($pair->tag, $pair->token));
        }
        $params->put('tag_token_list', json_encode($tag_token_list));
        return $this->callRestful(self::RESTAPI_BATCHSETTAG, $params);
    }
//

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
        $params         = $this->InitParams();
        $tag_token_list = array();
        foreach ($tagTokenPairs as $pair) {
            array_push($tag_token_list, array($pair->tag, $pair->token));
        }
        $params->put('tag_token_list', json_encode($tag_token_list));
        return $this->callRestful(self::RESTAPI_BATCHDELTAG, $params);
    }

    /**
     * 查询token相关的信息，包括最近一次活跃时间，离线消息数等
     *
     * @param string deviceToken 目标设备token
     * @return array 服务器执行结果，JSON形式
     */
    public function queryInfoOfToken(string $deviceToken)
    {
        $params = new HashMap;
        $params->put("access_id", $this->m_accessId);
        $params->put("device_token", $deviceToken);
        $params->put("timestamp", time());
        return $this->callRestful(self::RESTAPI_QUERYINFOOFTOKEN, $params);
    }
//

    /**
     * 查询账号绑定的token
     *
     * @param string $account 目标账号
     * @return array 服务器执行结果，JSON形式
     */
    public function queryTokensOfAccount(string $account)
    {
        $params = new HashMap;
        $params->put("access_id", $this->m_accessId);
        $params->put("account", $account);
        $params->put("timestamp", time());
        return $this->callRestful(self::RESTAPI_QUERYTOKENSOFACCOUNT, $params);
    }
//

    /**
     * 删除指定账号和token的绑定关系（token仍然有效）
     *
     * @param string $account 目标账号
     * @param string $deviceToken 目标设备token
     * @return array 服务器执行结果，JSON形式
     */
    public function deleteTokenOfAccount(string $account, string $deviceToken)
    {
        $params = new HashMap;
        $params->put("access_id", $this->m_accessId);
        $params->put("account", $account);
        $params->put("device_token", $deviceToken);
        $params->put("timestamp", time());
        return $this->callRestful(self::RESTAPI_DELETETOKENOFACCOUNT, $params);
    }

    /**
     * 删除指定账号绑定的所有token（token仍然有效）
     *
     * @param string $account 目标账号
     * @return array 服务器执行结果，JSON形式
     */
    public function deleteAllTokensOfAccount(string $account)
    {
        $params = new HashMap;
        $params->put("access_id", $this->m_accessId);
        $params->put("account", $account);
        $params->put("timestamp", time());
        return $this->callRestful(self::RESTAPI_DELETEALLTOKENSOFACCOUNT, $params);
    }

    /**
     * 根据token设置账号
     * @param string $account
     * @param string $deviceToken
     * @param string $platform
     * @return mixed
     */
    public function setAccountByToken(string $account, string $deviceToken, $platform = self::ANDROID_STR)
    {
        $params = new HashMap;
        $params->put("operator_type", 2);
        $params->put("platform", $platform);
        $params->put("token_accounts", [
            [
                'token'        => $deviceToken,
                'account_list' => [
                    [
                        'account'      => $account,
                        'account_type' => 0
                    ]
                ]
            ]
        ]);
        return $this->callRestfulV3(self::RESTAPI_BATCHOPERATEACCOUNT, $params);
    }

}