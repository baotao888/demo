'use strict';

angular.module('app').constant('Errors', {
    signin: '账户或密码错误',
    required: '此项不能为空',
    same: '此项必须与密码相同',
    params: '参数错误',
    recognize_error_1: '操作失败, 您的人选库容已满',
    recognize_error_2: '操作失败, 今日优先认领客户名额已满',
    sys_error: '操作失败，系统繁忙',
    common_error: '操作失败',
    common_success: '操作成功',
    recognize_error_3: '操作失败, 认领该客户时间未到，请阅读【系统使用规则】中的【不可认领客户】章节',
    remain_limit_error_1: '保留人选数量已达人选保留上限',
    distribute_success: '分配成功',
    distribute_error: '分配失败，该顾问库容已满',
    move_success: '划转成功',
    move_error: '划转失败，该顾问库容已满'
});
