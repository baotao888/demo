'use strict';

/* Filters */
// 返费类型
angular.module('app')
    .filter('allowanceType', function() {
        return function(type) {
            var text = '';
            if (type == 1) {
                text = '正式型';
            } else if (type==2) {
                text = '小时型';
            } else if (type==3) {
                text = '混合型';
            } else {
                text = '未知';
            }
            return text;
        }
    });