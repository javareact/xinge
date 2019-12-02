# Tencent Xinge Push

A simple Composer port for the [Tencent Xinge Push](http://xg.qq.com) PHP SDK.

- [Official Documentation](http://developer.qq.com/wiki/xg/)
- [Usage Samples](docs)

## Installation

```sh
composer require javareact/xinge
```

## Modifications

This package makes the following changes against the official SDK

## 概述
[信鸽](http://xg.qq.com) 是腾讯云提供的一款支持**百亿级**消息的移动App推送平台，开发者可以调用Java SDK访问信鸽推送服务。

目前最新的SDK版本是v2，请及时更新SDK版本以获取最新的功能特性。

## 接口说明
信鸽提供的主要推送和查询接口包括3种

### 创建推送任务
- pushSingleDevice 推送消息给单个设备
- pushSingleAccount 推送消息给单个账号
- pushAccountList 推送消息给多个账号
- pushAllDevice 推送消息给单个app的所有设备
- pushTags 推送消息给tags指定的设备
- createMultipush创建大批量推送消息
- pushAccountListMultiple推送消息给大批量账号(可多次)
- pushDeviceListMultiple推送消息给大批量设备(可多次)

### 异步查询推送状态
- queryPushStatus查询群发消息发送状态
- cancelTimingPush取消尚未推送的定时消息

### 查询/更改账户和标签
- queryDeviceCount查询应用覆盖的设备数
- queryTags 查询应用的tags
- BatchSetTag 批量为token设置标签
- BatchDelTag 批量为token删除标签
- queryTokenTags 查询token的tags
- queryTagTokenNum 查询tag下token的数目
- queryInfoOfToken 查询token的相关信息
- queryTokensOfAccount 查询account绑定的token
- deleteTokenOfAccount 删除account绑定的token
- deleteAllTokensOfAccount 删除account绑定的所有token
- setAccountByToken 根据token设置account