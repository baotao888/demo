'use strict';

/* Filters */
// 表头悬浮提示信息
angular.module('app')
    .filter('thTitle', function() {
        return function(title, type) {
            if (type == 'onduty') {
                if (title == '在职天数') title = '已在职时间|企业在职时间';
            }
            return title;
        }
    });