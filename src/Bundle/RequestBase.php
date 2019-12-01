<?php

namespace Javareact\Xinge\Bundle;


use BlackBonjour\Stdlib\Util\HashMap;
use Javareact\Xinge\Exceptions\Exception;

class RequestBase
{
    /**
     * get请求方式
     */
    const METHOD_GET = 'get';
    /**
     * post请求方式
     */
    const METHOD_POST = 'post';

    /**
     * @param $url
     * @param array $params
     * @param string $method
     * @param array $extra_conf
     * @param bool $ssl
     * @return bool|mixed
     * @throws Exception
     */
    public function exec($url, HashMap $params, $method = self::METHOD_GET, $extra_conf = array(), $ssl = false)
    {
        //转成数组
        $params = http_build_query($params->toArray());
        //如果是get请求，直接将参数附在url后面
        if ($method == self::METHOD_GET) {
            $url .= (strpos($url, '?') === false ? '?' : '&') . $params;
        }

        //默认配置
        $curl_conf = array(
            CURLOPT_URL => $url, //请求url
            CURLOPT_HEADER => false, //不输出头信息
            CURLOPT_RETURNTRANSFER => true, //不输出返回数据
            CURLOPT_CONNECTTIMEOUT => 3, // 连接超时时间
        );

        if ($ssl === false) {
            $curl_conf[CURLOPT_SSL_VERIFYHOST] = false;
            $curl_conf[CURLOPT_SSL_VERIFYPEER] = false;
        }

        //配置post请求额外需要的配置项
        if ($method == self::METHOD_POST) {
            //使用post方式
            $curl_conf[CURLOPT_POST] = true;
            //post参数
            $curl_conf[CURLOPT_POSTFIELDS] = $params;
        }
        //添加额外的配置
        foreach ($extra_conf as $k => $v) {
            $curl_conf[$k] = $v;
        }
        //初始化一个curl句柄
        $curl_handle = curl_init();
        //设置curl的配置项
        curl_setopt_array($curl_handle, $curl_conf);
        //发起请求
        $data = curl_exec($curl_handle);
        $curl_error = curl_error($curl_handle);
        curl_close($curl_handle);
        if ($data === false) {
            throw new Exception('CURL ERROR: ' . $curl_error);
        }
        return $data;
    }

}