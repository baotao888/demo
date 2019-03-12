'use strict';

/* Filters */
// 导航悬浮样式
angular.module('app')
    .filter('statusClass', function() {
        return function(value, type) {
            var color_class = 'btn-primary';
            if (type == 1) {
                color_class = value?'btn-default':'btn-info';
            }else{
                color_class = value?'btn-success':'btn-danger';
            }
            return color_class;
        }
    });