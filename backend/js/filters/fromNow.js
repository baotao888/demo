'use strict';

/* Filters */
// need load the moment.js to use this filter. 
angular.module('app')
  .filter('fromNow', function() {
    return function(date) {
        var obj = moment(date);
        if (obj.isValid()) return obj.fromNow();
        else return date;
    }
  });