<?php

namespace Javareact\Xinge\Bundle;

use BlackBonjour\Stdlib\Util\HashMap;
use Javareact\Xinge\Entity\Message;
use Javareact\Xinge\Entity\MessageIOS;
use Javareact\Xinge\Exceptions\Exception;
use Javareact\Xinge\Exceptions\InvalidArgumentException;

class XingeApp
{

    const RESTAPI_PUSHSINGLEDEVICE = "http://openapi.xg.qq.com/v2/push/single_device";
    const RESTAPI_PUSHSINGLEACCOUNT = "http://openapi.xg.qq.com/v2/push/single_account";
    const RESTAPI_PUSHACCOUNTLIST = "http://openapi.xg.qq.com/v2/push/account_list";
    const RESTAPI_PUSHALLDEVICE = "http://openapi.xg.qq.com/v2/push/all_device";
    const RESTAPI_PUSHTAGS = "http://openapi.xg.qq.com/v2/push/tags_device";
    const RESTAPI_QUERYPUSHSTATUS = "http://openapi.xg.qq.com/v2/push/get_msg_status";
    const RESTAPI_QUERYDEVICECOUNT = "http://openapi.xg.qq.com/v2/application/get_app_device_num";
    const RESTAPI_QUERYTAGS = "http://openapi.xg.qq.com/v2/tags/query_app_tags";
    const RESTAPI_CANCELTIMINGPUSH = "http://openapi.xg.qq.com/v2/push/cancel_timing_task";
    const RESTAPI_BATCHSETTAG = "http://openapi.xg.qq.com/v2/tags/batch_set";
    const RESTAPI_BATCHDELTAG = "http://openapi.xg.qq.com/v2/tags/batch_del";
    const RESTAPI_QUERYTOKENTAGS = "http://openapi.xg.qq.com/v2/tags/query_token_tags";
    const RESTAPI_QUERYTAGTOKENNUM = "http://openapi.xg.qq.com/v2/tags/query_tag_token_num";
    const RESTAPI_CREATEMULTIPUSH = "http://openapi.xg.qq.com/v2/push/create_multipush";
    const RESTAPI_PUSHACCOUNTLISTMULTIPLE = "http://openapi.xg.qq.com/v2/push/account_list_multiple";
    const RESTAPI_PUSHDEVICELISTMULTIPLE = "http://openapi.xg.qq.com/v2/push/device_list_multiple";
    const RESTAPI_QUERYINFOOFTOKEN = "http://openapi.xg.qq.com/v2/application/get_app_token_info";
    const RESTAPI_QUERYTOKENSOFACCOUNT = "http://openapi.xg.qq.com/v2/application/get_app_account_tokens";
    const RESTAPI_DELETETOKENOFACCOUNT = "http://openapi.xg.qq.com/v2/application/del_app_account_tokens";
    const RESTAPI_DELETEALLTOKENSOFACCOUNT = "http://openapi.xg.qq.com/v2/application/del_app_account_all_tokens";

    const DEVICE_ALL = 0;
    const DEVICE_BROWSER = 1;
    const DEVICE_PC = 2;
    const DEVICE_ANDROID = 3;
    const DEVICE_IOS = 4;
    const DEVICE_WINPHONE = 5;

    const IOSENV_PROD = 1;
    const IOSENV_DEV = 2;

    const IOS_MIN_ID = 2200000000;

    private $m_accessId;
    private $m_secretKey;

    /**
     * XingeApp constructor.
     * @param $appId
     * @param $secretKey
     * @param string $accessId
     */
    public function __construct($accessId, $secretKey)
    {
        if (empty($accessId) || empty($secretKey)) {
            throw new InvalidArgumentException();
        }
        $this->m_accessId = $accessId;
        $this->m_secretKey = $secretKey;
    }

    /**
     * 析构函数
     */
    public function __destruct()
    {
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

    protected function ValidateMessageType($message)
    {
        if ($this->m_accessId < self::IOS_MIN_ID)
            return true;
        else
            return false;
    }

    private function ValidateMessageTypeIos(MessageIOS $message, $environment)
    {
        if ($this->m_accessId >= self::IOS_MIN_ID && ($environment == self::IOSENV_PROD || $environment == self::IOSENV_DEV))
            return true;
        else
            return false;
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
        $sign = $paramsBase->generateSign(RequestBase::METHOD_POST, $url, $this->m_secretKey);
        $params['sign'] = $sign;
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
        if (!$this->ValidateMessageType($message)) {
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
     * @param string deviceToken 目标设备token
     * @param MessageIOS $message 待推送的消息
     * @param string $environment 推送的目标环境 必须是其中一种： {@link #IOSENV_PROD}生产环境 {@link #IOSENV_DEV}开发环境
     * @return array 服务器执行结果，JSON形式
     */
    public function pushSingleDeviceIos($deviceToken, MessageIOS $message, $environment)
    {
        if (!$this->ValidateMessageTypeIos($message, $environment)) {
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
//    /**
//     * 推送给指定账号，限Android系统使用
//     *
//     * @param deviceType 设备类型，请填0
//     * @param account 目标账号
//     * @param message 待推送的消息
//     * @return array 服务器执行结果，JSON形式
//     */
//    public JSONObject pushSingleAccount(int deviceType, String account, Message message) {
//    if (!$this->ValidateMessageType(message)) {
//        return new JSONObject("{'ret_code':-1,'err_msg':'message type error!'}");
//    }
//    if (!message . isValid()) {
//        return ['ret_code' => -1, 'err_msg' => 'message invalid!'];
//    }
//    Map < String, Object > params = new HashMap < String, Object > ();
//        $params->put("access_id", this . m_accessId);
//        $params->put("expire_time", message . getExpireTime());
//        $params->put("send_time", message . getSendTime());
//        $params->put("multi_pkg", message . getMultiPkg());
//        $params->put("device_type", deviceType);
//        $params->put("account", account);
//        $params->put("message_type", message . getType());
//        $params->put("message", message . toJson());
//        $params->put("timestamp",time());
//
//        return callRestful(XingeApp . RESTAPI_PUSHSINGLEACCOUNT, params);
//    }
//
//    /**
//     * 推送给指定账号，限iOS系统使用
//     *
//     * @param deviceType 设备类型，请填0
//     * @param account 目标账号
//     * @param message 待推送的消息
//     * @param environment 推送的目标环境 必须是其中一种： {@link #IOSENV_PROD}生产环境 {@link #IOSENV_DEV}开发环境
//     * @return array 服务器执行结果，JSON形式
//     */
//    public JSONObject pushSingleAccount(int deviceType, String account, MessageIOS message, int environment) {
//    if (!$this->ValidateMessageType(message, environment)) {
//        return new JSONObject("{'ret_code':-1,'err_msg':'message type or environment error!'}");
//    }
//    if (!message . isValid()) {
//        return ['ret_code' => -1, 'err_msg' => 'message invalid!'];
//    }
//    Map < String, Object > params = new HashMap < String, Object > ();
//        $params->put("access_id", this . m_accessId);
//        $params->put("expire_time", message . getExpireTime());
//        $params->put("send_time", message . getSendTime());
//        $params->put("device_type", deviceType);
//        $params->put("account", account);
//        $params->put("message_type", message . getType());
//        $params->put("message", message . toJson());
//        $params->put("timestamp",time());
//        $params->put("environment", environment);
//
//        return callRestful(XingeApp . RESTAPI_PUSHSINGLEACCOUNT, params);
//    }
//
//    /**
//     * 推送给多个账号，限Android设备使用 <br/>
//     * 如果目标账号数超过10000，建议改用{@link #pushAccountListMultiple}接口
//     *
//     * @param deviceType 设备类型，请填0
//     * @param accountList 目标账号列表
//     * @param message 待推送的消息
//     * @return array 服务器执行结果，JSON形式
//     */
//    public JSONObject pushAccountList(int deviceType, List<String > accountList, Message message) {
//    if (!$this->ValidateMessageType(message)) {
//        return new JSONObject("{'ret_code':-1,'err_msg':'message type error!'}");
//    }
//    if (!message . isValid()) {
//        return ['ret_code' => -1, 'err_msg' => 'message invalid!'];
//    }
//    Map < String, Object > params = new HashMap < String, Object > ();
//        $params->put("access_id", this . m_accessId);
//        $params->put("expire_time", message . getExpireTime());
//        $params->put("multi_pkg", message . getMultiPkg());
//        $params->put("device_type", deviceType);
//        $params->put("account_list", new JSONArray(accountList) . toString());
//        $params->put("message_type", message . getType());
//        $params->put("message", message . toJson());
//        $params->put("timestamp",time());
//
//        return callRestful(XingeApp . RESTAPI_PUSHACCOUNTLIST, params);
//    }
//
//    /**
//     * 推送给多个账号，限iOS设备使用 <br/>
//     * 如果目标账号数超过10000，建议改用{@link #pushAccountListMultiple}接口
//     *
//     * @param deviceType 设备类型，请填0
//     * @param accountList 目标账号列表
//     * @param message 待推送的消息
//     * @param environment 推送的目标环境 必须是其中一种： {@link #IOSENV_PROD}生产环境 {@link #IOSENV_DEV}开发环境
//     * @return array 服务器执行结果，JSON形式
//     */
//    public JSONObject pushAccountList(int deviceType, List<String > accountList, MessageIOS message, int environment) {
//    if (!$this->ValidateMessageType(message, environment)) {
//        return new JSONObject("{'ret_code':-1,'err_msg':'message type or environment error!'}");
//    }
//    if (!message . isValid()) {
//        return ['ret_code' => -1, 'err_msg' => 'message invalid!'];
//    }
//    Map < String, Object > params = new HashMap < String, Object > ();
//        $params->put("access_id", this . m_accessId);
//        $params->put("expire_time", message . getExpireTime());
//        $params->put("device_type", deviceType);
//        $params->put("account_list", new JSONArray(accountList) . toString());
//        $params->put("message_type", message . getType());
//        $params->put("message", message . toJson());
//        $params->put("timestamp",time());
//        $params->put("environment", environment);
//
//        return callRestful(XingeApp . RESTAPI_PUSHACCOUNTLIST, params);
//    }
//
//    /**
//     * 推送给全量设备，限Android系统使用
//     *
//     * @param deviceType 请填0
//     * @param message 待推送的消息
//     * @return array 服务器执行结果，JSON形式
//     */
//    public JSONObject pushAllDevice(int deviceType, Message message) {
//    if (!$this->ValidateMessageType(message)) {
//        return new JSONObject("{'ret_code':-1,'err_msg':'message type error!'}");
//    }
//    if (!message . isValid()) {
//        return ['ret_code' => -1, 'err_msg' => 'message invalid!'];
//    }
//    Map < String, Object > params = new HashMap < String, Object > ();
//        $params->put("access_id", this . m_accessId);
//        $params->put("expire_time", message . getExpireTime());
//        $params->put("send_time", message . getSendTime());
//        $params->put("multi_pkg", message . getMultiPkg());
//        $params->put("device_type", deviceType);
//        $params->put("message_type", message . getType());
//        $params->put("message", message . toJson());
//        $params->put("timestamp",time());
//
//        if (message . getLoopInterval() > 0 && message . getLoopTimes() > 0) {
//            $params->put("loop_interval", message . getLoopInterval());
//            $params->put("loop_times", message . getLoopTimes());
//        }
//
//        return callRestful(XingeApp . RESTAPI_PUSHALLDEVICE, params);
//    }
//
//    /**
//     * 推送给全量设备，限iOS系统使用
//     *
//     * @param deviceType 设备类型，请填0
//     * @param message 待推送的消息
//     * @param environment 推送的目标环境 必须是其中一种： {@link #IOSENV_PROD}生产环境 {@link #IOSENV_DEV}开发环境
//     * @return array 服务器执行结果，JSON形式
//     */
//    public JSONObject pushAllDevice(int deviceType, MessageIOS message, int environment) {
//    if (!$this->ValidateMessageType(message, environment)) {
//        return new JSONObject("{'ret_code':-1,'err_msg':'message type or environment error!'}");
//    }
//    if (!message . isValid()) {
//        return ['ret_code' => -1, 'err_msg' => 'message invalid!'];
//    }
//    Map < String, Object > params = new HashMap < String, Object > ();
//        $params->put("access_id", this . m_accessId);
//        $params->put("expire_time", message . getExpireTime());
//        $params->put("send_time", message . getSendTime());
//        $params->put("device_type", deviceType);
//        $params->put("message_type", message . getType());
//        $params->put("message", message . toJson());
//        $params->put("timestamp",time());
//        $params->put("environment", environment);
//
//        if (message . getLoopInterval() > 0 && message . getLoopTimes() > 0) {
//            $params->put("loop_interval", message . getLoopInterval());
//            $params->put("loop_times", message . getLoopTimes());
//        }
//
//        return callRestful(XingeApp . RESTAPI_PUSHALLDEVICE, params);
//    }
//
//    /**
//     * 推送给多个tags对应的设备，限Android系统使用
//     *
//     * @param deviceType 设备类型，请填0
//     * @param tagList 指定推送的tag列表
//     * @param tagOp 多个tag的运算关系，取值必须是下面之一： AND OR
//     * @param message 待推送的消息
//     * @return array 服务器执行结果，JSON形式
//     */
//    public JSONObject pushTags(int deviceType, List<String > tagList, String tagOp, Message message) {
//    if (!$this->ValidateMessageType(message)) {
//        return new JSONObject("{'ret_code':-1,'err_msg':'message type error!'}");
//    }
//    if (!message . isValid() || tagList . size() == 0 || (!tagOp . equals("AND") && !tagOp . equals("OR"))) {
//        return new JSONObject("{'ret_code':-1,'err_msg':'param invalid!'}");
//    }
//    Map < String, Object > params = new HashMap < String, Object > ();
//        $params->put("access_id", this . m_accessId);
//        $params->put("expire_time", message . getExpireTime());
//        $params->put("send_time", message . getSendTime());
//        $params->put("multi_pkg", message . getMultiPkg());
//        $params->put("device_type", deviceType);
//        $params->put("message_type", message . getType());
//        $params->put("tags_list", new JSONArray(tagList) . toString());
//        $params->put("tags_op", tagOp);
//        $params->put("message", message . toJson());
//        $params->put("timestamp",time());
//
//        if (message . getLoopInterval() > 0 && message . getLoopTimes() > 0) {
//            $params->put("loop_interval", message . getLoopInterval());
//            $params->put("loop_times", message . getLoopTimes());
//        }
//
//        return callRestful(XingeApp . RESTAPI_PUSHTAGS, params);
//    }
//
//    /**
//     * 推送给多个tags对应的设备，限iOS系统使用
//     *
//     * @param deviceType 设备类型，请填0
//     * @param tagList 指定推送的tag列表
//     * @param tagOp 多个tag的运算关系，取值必须是下面之一： AND OR
//     * @param message 待推送的消息
//     * @param environment 推送的目标环境 必须是其中一种： {@link #IOSENV_PROD}生产环境 {@link #IOSENV_DEV}开发环境
//     * @return array 服务器执行结果，JSON形式
//     */
//    public JSONObject pushTags(int deviceType, List<String > tagList, String tagOp, MessageIOS message, int environment) {
//    if (!$this->ValidateMessageType(message, environment)) {
//        return new JSONObject("{'ret_code':-1,'err_msg':'message type or environment error!'}");
//    }
//    if (!message . isValid() || tagList . size() == 0 || (!tagOp . equals("AND") && !tagOp . equals("OR"))) {
//        return new JSONObject("{'ret_code':-1,'err_msg':'param invalid!'}");
//    }
//    Map < String, Object > params = new HashMap < String, Object > ();
//        $params->put("access_id", this . m_accessId);
//        $params->put("expire_time", message . getExpireTime());
//        $params->put("send_time", message . getSendTime());
//        $params->put("device_type", deviceType);
//        $params->put("message_type", message . getType());
//        $params->put("tags_list", new JSONArray(tagList) . toString());
//        $params->put("tags_op", tagOp);
//        $params->put("message", message . toJson());
//        $params->put("timestamp",time());
//        $params->put("environment", environment);
//
//        if (message . getLoopInterval() > 0 && message . getLoopTimes() > 0) {
//            $params->put("loop_interval", message . getLoopInterval());
//            $params->put("loop_times", message . getLoopTimes());
//        }
//
//        return callRestful(XingeApp . RESTAPI_PUSHTAGS, params);
//    }
//
//    /**
//     * 创建大批量推送消息，后续可调用{@link #pushAccountListMultiple}或{@link #pushDeviceListMultiple}接口批量添加设备，限Android系统使用<br/>
//     * 此接口创建的任务不支持定时推送
//     *
//     * @param message 待推送的消息
//     * @return array 服务器执行结果，JSON形式
//     */
//    public JSONObject createMultipush(Message message) {
//    if (!$this->ValidateMessageType(message)) {
//        return new JSONObject("{'ret_code':-1,'err_msg':'message type error!'}");
//    }
//    if (!message . isValid()) {
//        return ['ret_code' => -1, 'err_msg' => 'message invalid!'];
//    }
//    Map < String, Object > params = new HashMap < String, Object > ();
//        $params->put("access_id", this . m_accessId);
//        $params->put("expire_time", message . getExpireTime());
//        $params->put("multi_pkg", message . getMultiPkg());
//        $params->put("message_type", message . getType());
//        $params->put("message", message . toJson());
//        $params->put("timestamp",time());
//
//        return callRestful(XingeApp . RESTAPI_CREATEMULTIPUSH, params);
//    }
//
//    /**
//     * 创建大批量推送消息，后续可调用{@link #pushAccountListMultiple}或{@link #pushDeviceListMultiple}接口批量添加设备，限iOS系统使用<br/>
//     * 此接口创建的任务不支持定时推送
//     *
//     * @param message 待推送的消息
//     * @param environment 推送的目标环境 必须是其中一种： {@link #IOSENV_PROD}生产环境 {@link #IOSENV_DEV}开发环境
//     * @return array 服务器执行结果，JSON形式
//     */
//    public JSONObject createMultipush(MessageIOS message, int environment) {
//    if (!$this->ValidateMessageType(message, environment)) {
//        return new JSONObject("{'ret_code':-1,'err_msg':'message type or environment error!'}");
//    }
//    if (!message . isValid()) {
//        return ['ret_code' => -1, 'err_msg' => 'message invalid!'];
//    }
//    Map < String, Object > params = new HashMap < String, Object > ();
//        $params->put("access_id", this . m_accessId);
//        $params->put("expire_time", message . getExpireTime());
//        $params->put("message_type", message . getType());
//        $params->put("message", message . toJson());
//        $params->put("timestamp",time());
//        $params->put("environment", environment);
//
//        return callRestful(XingeApp . RESTAPI_CREATEMULTIPUSH, params);
//    }
//
//    /**
//     * 推送消息给大批量账号，可对同一个pushId多次调用此接口，限Android系统使用 <br/>
//     * 建议用户采用此接口自行控制发送时间
//     *
//     * @param pushId {@link #createMultipush}返回的push_id
//     * @param accountList 账号列表，数量最多为1000个
//     * @return array 服务器执行结果，JSON形式
//     */
//    public JSONObject pushAccountListMultiple(long pushId, List<String > accountList) {
//    if (pushId <= 0) {
//        return new JSONObject("{'ret_code':-1,'err_msg':'pushId invalid!'}");
//    }
//    Map < String, Object > params = new HashMap < String, Object > ();
//        $params->put("access_id", this . m_accessId);
//        $params->put("push_id", pushId);
//        $params->put("account_list", new JSONArray(accountList) . toString());
//        $params->put("timestamp",time());
//
//        return callRestful(XingeApp . RESTAPI_PUSHACCOUNTLISTMULTIPLE, params);
//    }
//
//    /**
//     * 推送消息给大批量设备，可对同一个pushId多次调用此接口，限Android系统使用 <br/>
//     * 建议用户采用此接口自行控制发送时间
//     *
//     * @param pushId {@link #createMultipush}返回的push_id
//     * @param deviceList 设备列表，数量最多为1000个
//     * @return array 服务器执行结果，JSON形式
//     */
//    public JSONObject pushDeviceListMultiple(long pushId, List<String > deviceList) {
//    if (pushId <= 0) {
//        return new JSONObject("{'ret_code':-1,'err_msg':'pushId invalid!'}");
//    }
//    Map < String, Object > params = new HashMap < String, Object > ();
//        $params->put("access_id", this . m_accessId);
//        $params->put("push_id", pushId);
//        $params->put("device_list", new JSONArray(deviceList) . toString());
//        $params->put("timestamp",time());
//
//        return callRestful(XingeApp . RESTAPI_PUSHDEVICELISTMULTIPLE, params);
//    }
//
//    /**
//     * 查询群发消息的状态，可同时查询多个pushId状态
//     *
//     * @param pushIdList 各类推送任务返回的push_id，可以一次查询多个
//     * @return array 服务器执行结果，JSON形式
//     */
//    public JSONObject queryPushStatus(List<String> pushIdList) {
//    Map < String, Object > params = new HashMap < String, Object > ();
//        $params->put("access_id", this . m_accessId);
//        $params->put("timestamp",time());
//        JSONArray jArray = new JSONArray();
//        for (String pushId : pushIdList) {
//            JSONObject js = new JSONObject();
//            js . put("push_id", pushId);
//            jArray . put(js);
//        }
//        $params->put("push_ids", jArray . toString());
//
//        return callRestful(XingeApp . RESTAPI_QUERYPUSHSTATUS, params);
//    }
//
//    /**
//     * 查询应用覆盖的设备数
//     *
//     * @return array 服务器执行结果，JSON形式
//     */
//    public JSONObject queryDeviceCount(){
//Map < String, Object > params = new HashMap < String, Object > ();
//        $params->put("access_id", this . m_accessId);
//        $params->put("timestamp",time());
//
//        return callRestful(XingeApp . RESTAPI_QUERYDEVICECOUNT, params);
//    }
//
//    /**
//     * 查询应用当前所有的tags
//     *
//     * @param start 从哪个index开始
//     * @param limit 限制结果数量，最多取多少个tag
//     * @return array 服务器执行结果，JSON形式
//     */
//    public JSONObject queryTags(int start, int limit) {
//    Map < String, Object > params = new HashMap < String, Object > ();
//        $params->put("access_id", this . m_accessId);
//        $params->put("start", start);
//        $params->put("limit", limit);
//        $params->put("timestamp",time());
//
//        return callRestful(XingeApp . RESTAPI_QUERYTAGS, params);
//    }
//
//    /**
//     * 查询应用所有的tags，如果超过100个，取前100个
//     *
//     * @return array 服务器执行结果，JSON形式
//     */
//    public JSONObject queryTags(){
//        return queryTags(0, 100);
//    }
//
//    /**
//     * 查询带有指定tag的设备数量
//     *
//     * @param tag 指定的标签
//     * @return array 服务器执行结果，JSON形式
//     */
//    public JSONObject queryTagTokenNum(String tag) {
//    Map < String, Object > params = new HashMap < String, Object > ();
//        $params->put("access_id", this . m_accessId);
//        $params->put("tag", tag);
//        $params->put("timestamp",time());
//
//        return callRestful(XingeApp . RESTAPI_QUERYTAGTOKENNUM, params);
//    }
//
//    /**
//     * 查询设备下所有的tag
//     *
//     * @param deviceToken 目标设备token
//     * @return array 服务器执行结果，JSON形式
//     */
//    public JSONObject queryTokenTags(String deviceToken) {
//    Map < String, Object > params = new HashMap < String, Object > ();
//        $params->put("access_id", this . m_accessId);
//        $params->put("device_token", deviceToken);
//        $params->put("timestamp",time());
//
//        return callRestful(XingeApp . RESTAPI_QUERYTOKENTAGS, params);
//    }
//
//    /**
//     * 取消尚未推送的定时任务
//     *
//     * @param pushId 各类推送任务返回的push_id
//     * @return array 服务器执行结果，JSON形式
//     */
//    public JSONObject cancelTimingPush(String pushId) {
//    Map < String, Object > params = new HashMap < String, Object > ();
//        $params->put("access_id", this . m_accessId);
//        $params->put("push_id", pushId);
//        $params->put("timestamp",time());
//
//        return callRestful(XingeApp . RESTAPI_CANCELTIMINGPUSH, params);
//    }
//
//    /**
//     * 批量为token设备标签，每次调用最多输入20个pair
//     *
//     * @param tagTokenPairs 指定token对应的指定tag
//     * @return array 服务器执行结果，JSON形式
//     */
//    public JSONObject BatchSetTag(List<TagTokenPair> tagTokenPairs) {
//
//    for (TagTokenPair pair : tagTokenPairs) {
//        if (!this . ValidateToken(pair . token)) {
//            String returnMsgJsonStr = String . format("{\"ret_code\":-1,\"err_msg\":\"invalid token %s\"}", pair . token);
//                return new JSONObject(returnMsgJsonStr);
//            }
//    }
//
//        Map < String, Object > params = this . InitParams();
//
//        List<List> tag_token_list = new ArrayList < List>();
//
//        for (TagTokenPair pair : tagTokenPairs) {
//            List<String > singleTagToken = new ArrayList < String>();
//            singleTagToken . add(pair . tag);
//            singleTagToken . add(pair . token);
//
//            tag_token_list . add(singleTagToken);
//        }
//
//        $params->put("tag_token_list", new JSONArray(tag_token_list) . toString());
//
//        return callRestful(XingeApp . RESTAPI_BATCHSETTAG, params);
//    }
//
//    /**
//     * 批量为token删除标签，每次调用最多输入20个pair
//     *
//     * @param tagTokenPairs 指定token对应的指定tag
//     * @return array 服务器执行结果，JSON形式
//     */
//    public JSONObject BatchDelTag(List<TagTokenPair> tagTokenPairs) {
//
//    for (TagTokenPair pair : tagTokenPairs) {
//        if (!this . ValidateToken(pair . token)) {
//            String returnMsgJsonStr = String . format("{\"ret_code\":-1,\"err_msg\":\"invalid token %s\"}", pair . token);
//                return new JSONObject(returnMsgJsonStr);
//            }
//    }
//
//        Map < String, Object > params = this . InitParams();
//
//        List<List> tag_token_list = new ArrayList < List>();
//
//        for (TagTokenPair pair : tagTokenPairs) {
//            List<String > singleTagToken = new ArrayList < String>();
//            singleTagToken . add(pair . tag);
//            singleTagToken . add(pair . token);
//
//            tag_token_list . add(singleTagToken);
//        }
//
//        $params->put("tag_token_list", new JSONArray(tag_token_list) . toString());
//
//        return callRestful(XingeApp . RESTAPI_BATCHDELTAG, params);
//    }
//
//    /**
//     * 查询token相关的信息，包括最近一次活跃时间，离线消息数等
//     *
//     * @param deviceToken 目标设备token
//     * @return array 服务器执行结果，JSON形式
//     */
//    public JSONObject queryInfoOfToken(String deviceToken) {
//    Map < String, Object > params = new HashMap < String, Object > ();
//        $params->put("access_id", this . m_accessId);
//        $params->put("device_token", deviceToken);
//        $params->put("timestamp",time());
//
//        return callRestful(XingeApp . RESTAPI_QUERYINFOOFTOKEN, params);
//    }
//
//    /**
//     * 查询账号绑定的token
//     *
//     * @param account 目标账号
//     * @return array 服务器执行结果，JSON形式
//     */
//    public JSONObject queryTokensOfAccount(String account) {
//    Map < String, Object > params = new HashMap < String, Object > ();
//        $params->put("access_id", this . m_accessId);
//        $params->put("account", account);
//        $params->put("timestamp",time());
//
//        return callRestful(XingeApp . RESTAPI_QUERYTOKENSOFACCOUNT, params);
//    }
//
//    /**
//     * 删除指定账号和token的绑定关系（token仍然有效）
//     *
//     * @param account 目标账号
//     * @param deviceToken 目标设备token
//     * @return array 服务器执行结果，JSON形式
//     */
//    public JSONObject deleteTokenOfAccount(String account, String deviceToken) {
//    Map < String, Object > params = new HashMap < String, Object > ();
//        $params->put("access_id", this . m_accessId);
//        $params->put("account", account);
//        $params->put("device_token", deviceToken);
//        $params->put("timestamp",time());
//
//        return callRestful(XingeApp . RESTAPI_DELETETOKENOFACCOUNT, params);
//    }
//
//    /**
//     * 删除指定账号绑定的所有token（token仍然有效）
//     *
//     * @param account 目标账号
//     * @return array 服务器执行结果，JSON形式
//     */
//    public JSONObject deleteAllTokensOfAccount(String account) {
//    Map < String, Object > params = new HashMap < String, Object > ();
//        $params->put("access_id", this . m_accessId);
//        $params->put("account", account);
//        $params->put("timestamp",time());
//
//        return callRestful(XingeApp . RESTAPI_DELETEALLTOKENSOFACCOUNT, params);
//    }

}