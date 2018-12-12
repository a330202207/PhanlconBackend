<?php
/**
 * @purpose: 错误常量
 * @author: NedRen<ned@pproject.co>
 * @date:2018/8/20
 * @version: 1.0
 */

namespace Apps\Librarys;

class ErrorCode
{
    const SUCCESS = 0; //成功
    const FAILED = -1; //失败

    const PAYMENT_CHANNEL_ERROR = 10001; //支付渠道
    const INVALID_PARAMETER_ERROR = 40001;//非法参数，无效参数
}