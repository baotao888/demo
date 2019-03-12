'use strict';

/* Filters */
// need load the moment.js to use this filter. 
angular.module('app')
    .filter('frontendBackendDate', function() {
        return function(date, type) {
            var str_date = date;
            if (type == 'b2f') {
                /*后端转前端*/
                str_date = date?date * 1000:'';//时间戳*1000
            } else {
                /*前端转后端*/
            }
            return str_date;
        }
    });