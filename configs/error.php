<?php

//成功
define('SUCCESS_CODE', 0);
define('SUCCESS_MESSAGE', '成功');


//数据库故障
define('DATABASE_ERROR_CODE', 7011);
define('DATABASE_ERROR_MESSAGE', '数据库故障');

//权限不足
define('RANK_ERROR_CODE', 7012);
define('RANK_ERROR_MESSAGE', '权限不足');

//接口不存在
define('API_UNAVAILABLE_CODE', 7013);
define('API_UNAVAILABLE_MESSAGE', '接口不存在');

//非法参数
define('ILLEGAL_PARAMETER_CODE', 7014);
define('ILLEGAL_PARAMETER_MESSAGE', '非法参数');

//缺失参数
define('LOSE_PARAMETER_CODE', 7015);
define('LOSE_PARAMETER_MESSAGE', '缺失参数');


//用户已存在
define('USER_EXIST_CODE', 7021);
define('USER_EXIST_MESSAGE', '用户已存在');

//密码错误
define('WRONG_PASSWORD_CODE', 7022);
define('WRONG_PASSWORD_MESSAGE', '密码错误');

//用户已被禁用
define('USER_SUSPEND_CODE', 7023);
define('USER_SUSPEND_MESSAGE', '用户已被禁用');

//用户未登录
define('USER_OFFLINE_CODE', 7024);
define('USER_OFFLINE_MESSAGE', '用户未登录');

//用户不存在
define('USER_DO_NOT_EXIST_CODE', 7025);
define('USER_DO_NOT_EXIST_MESSAGE', '用户不存在');


//任务不允许被修改
define('TASK_CAN_NOT_BE_MODIFIED_CODE', 7031);
define('TASK_CAN_NOT_BE_MODIFIED_MESSAGE', '任务不允许被修改');

//任务不允许被修改
define('TASK_OVER_LIMIT_CODE', 7032);
define('TASK_OVER_LIMIT_MESSAGE', '任务数量超过限制');

//任务不存在
define('TASK_NOT_FOUND_CODE', 7033);
define('TASK_NOT_FOUND_MESSAGE', '任务不存在');


//验证码无效
define('SMS_CODE_INVALID_CODE', 7041);
define('SMS_CODE_INVALID_MESSAGE', '验证码无效');

//验证码请求过于频繁
define('SMS_CODE_CAN_NOT_BE_APPLIED_CODE', 7042);
define('SMS_CODE_CAN_NOT_BE_APPLIED_MESSAGE', '验证码请求过于频繁');