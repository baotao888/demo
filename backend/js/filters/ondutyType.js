'use strict';

/* Filters */
// 在职类型
angular.module('app')
    .filter('ondutyType', function() {
        return function(type) {
            var text = '在职';
            if (type == 1) {
                text = '在职打卡';
            }
            return text;
        }
    });