'use strict';

/**
 * 后台接口
 */
angular.module('app').constant('API', {
    root: '/api',
    //获取人选列表
    candidateData: function (type) {
        var url = this.root + '/customer/candidate';
        if (type == 'intention') {
            url += '/myIntention';//意向人选
        } else if (type == 'signup') {
            url += '/mySignup';//报名人选
        } else if (type == 'onduty') {
            url += '/myOnduty';//在职人选
        } else if (type == 'outduty') {
            url += '/myoutduty';//离职人选
        } else if (type == 'meet') {
            url += '/mymeet';//接站人选
        } else if (type == 'other') {
            url += '/myOther';//其他人选
        } else {
            url += '';//所有候选人
        }
        return url;
    },
    //人选操作
    candidateOperate: function (operate) {
        var url = this.root + '/customer/candidate';
        if (operate == 1) {
            url += '/intention';//意向
        } else if (operate == 5) {
            url += '/outduty';//离职
        } else if (operate == 99) {
            url += '/depose';//丢弃
        } else {
            url = '';
        }
        return url;
    },
    //客户详情
    customerPoolDetail: function () {
        return this.root + '/customer/pool/detail';
    },
    //客户分配历史
    customerAssignHistory: function () {
        return this.root + '/customer/pool/assignhistory';
    },
    //客户工作经验
    customerWorkHistory: function () {
        return this.root + '/customer/pool/workhistory';
    },
    //客户联系记录
    customerContactLog: function () {
        return this.root + '/customer/contact';
    },
    //客户日志
    customerLog: function () {
        return this.root + '/customer/pool/history';
    },
    //客户池操作
    customerPoolOperate: function (operate) {
        var url = this.root + '/customer/pool';
        if (operate == 3) {
            url += '/recognize';//认领
        } else if (operate == 2) {
            url += '/release';// 释放
        } else {
            url = '';
        }
        return url;
    },
    //删除客户
    deleteCustomer: function () {
        return this.root + '/customer/pool/delete';
    },
    //客户唯一性验证
    checkCustomerUnique: function () {
        return this.root + '/customer/pool/check';
    },
    //新增客户
    createCustomer: function () {
        return this.root + '/customer/pool/save';
    },
    //客户信息更新
    updateCustomer: function () {
        return this.root + '/customer/pool/update';
    },
    //客户池列表
    customerList: function (type) {
        var url = this.root + '/customer/pool';
        if (type == 'signned') {
            url += '/signned';//已分配客户
        } else if (type == 'unsignned') {
            url += '/unsignned';//未分配
        } else if (type == 'my') {
            url += '/my';//可认领客户
        } else {
            url += '';//公海客户池
        }
        return url;
    },
    //呼入注册人选
    allCallinUsers: function () {
        return this.root + '/user/callin/users';
    },
    //呼入职位报名人选
    allCallinApplicants: function () {
        return this.root + '/user/callin/applicants';
    },
    //顾问呼入注册人选
    callinUser: function () {
        return this.root + '/index/index/myRegister';
    },
    //顾问呼入报名人选
    callinApplication: function () {
        return this.root + '/index/index/mySignup';
    },
    //顾问未确认人选
    callinUnsure: function () {
        return this.root + '/user/callin/callinUnsure';
    }
});

