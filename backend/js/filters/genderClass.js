'use strict';

/* Filters */
// 性别格式化
angular.module('app')
    .filter('genderClass', function() {
        return function(gender, type) {
            var text = 0;
            if (type == 1) {
                text = gender==1?'男':'女';
            } else if (type==2) {
                text = gender==1?9:10;//头像
            } else if (type==3) {
                text = gender==1?'先生':'女士';//称呼
            } else {
                text = gender==1?'icon-user':'icon-user-female';
            }
            return text;
        }
    });