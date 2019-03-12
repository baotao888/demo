'use strict';

angular.module('app').factory('JobModel', function () {
    return {
        type: 1,//返费类型
        amount: 0,//返费金额
        term: 1,//返费天数
        allowance: 0,//补贴金额
        conditions: [],//补贴条件
        ent_wage: 0,//企业每小时工资
        cp_wage: 0,//人选每小时工资
        onduty_type: 1//在职类型
    };
});
