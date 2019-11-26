<?php

namespace Javareact\Xinge\Bundle;


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
     * 发起一个get或post请求
     * @param string $url 请求的url
     * @param int $method 请求方式
     * @param array $params 请求参数
     * @param array $extra_conf curl配置, 高级需求可以用, 如
     * $extra_conf = array(
     *    CURLOPT_HEADER => true,
     *    CURLOPT_RETURNTRANSFER = false
     * )
     * @return bool|mixed 成功返回数据，失败返回false
     * @throws Exception
     */
    public function exec($url, $params = array(), $method = self::METHOD_GET, $extra_conf = array())
    {
        //如果是get请求，直接将参数附在url后面
        if ($method == self::METHOD_GET) {
            $params = is_array($params) ? http_build_query($params) : $params;
            $url    .= (strpos($url, '?') === false ? '?' : '&') . $params;
        }

        //默认配置
        $curl_conf = array(
            CURLOPT_URL            => $url, //请求url
            CURLOPT_HEADER         => false, //不输出头信息
            CURLOPT_RETURNTRANSFER => true, //不输出返回数据
            CURLOPT_CONNECTTIMEOUT => 3, // 连接超时时间
        );

        //配置post请求额外需要的配置项
        if ($method == self::METHOD_POST) {
            $params = is_array($params) ? json_encode($params) : $params;
            //使用post方式
            $curl_conf[CURLOPT_POST] = true;
            //post参数
            $curl_conf[CURLOPT_POSTFIELDS] = $params;
            //数据类型和长度设置
            $curl_conf[CURLOPT_HTTPHEADER] = array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($params),
            );
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
        curl_close($curl_handle);
        if ($data === false) {
            throw new Exception('CURL ERROR: ' . curl_error($curl_handle));
        }
        return $data;
    }

    /**
     * @param $url
     * @param array $params
     * @param string $method
     * @param array $extra_conf
     * @return bool|mixed
     * @throws Exception
     */
    public function execForOld($url, $params = array(), $method = self::METHOD_GET, $extra_conf = array())
    {
        $params = is_array($params) ? http_build_query($params) : $params;
        //如果是get请求，直接将参数附在url后面
        if ($method == self::METHOD_GET) {
            $url .= (strpos($url, '?') === false ? '?' : '&') . $params;
        }

        //默认配置
        $curl_conf = array(
            CURLOPT_URL            => $url, //请求url
            CURLOPT_HEADER         => false, //不输出头信息
            CURLOPT_RETURNTRANSFER => true, //不输出返回数据
            CURLOPT_CONNECTTIMEOUT => 3, // 连接超时时间
        );

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
        curl_close($curl_handle);
        if ($data === false) {
            throw new Exception('CURL ERROR: ' . curl_error($curl_handle));
        }
        return $data;
    }

}