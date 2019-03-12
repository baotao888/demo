'use strict';
angular.module('app').factory('AuthHandler', function AuthHandlerFactory($q, $injector, $rootScope, authService) {
  return {
    responseError: function (rejection) {
      // 如果服务器返回了401 unauthorized，那么就表示需要登录
      if (rejection.status === 401) {
          authService.removeToken();//清空token
          $rootScope.$state.go('access.signin');
          return $q.reject(rejection);
      } else if (rejection.status === 500) {
          console.log(rejection.data);
          return $q.reject(rejection);
      } else {
        // 其它错误不用管，留给其它interceptor去处理
        return $q.reject(rejection);
      }
    }
  };
});
