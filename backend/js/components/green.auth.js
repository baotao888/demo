/******************************************
 *                                        *
 * Auth: green gerong                     *
 * Date: 2014                             *
 * blog: http://greengerong.github.io/    *
 * github: https://github.com/greengerong *
 *                                        *
 ******************************************/

'use strict';

angular.module("green.auth", []).factory("authInterceptor", ["$q", "authService",
  function($q, authService) {
    return {
      "request": function(config) {
        config.headers = config.headers || {};
        var token = authService.getToken() || {};
        angular.forEach(token, function(value, key) {
          if (!config.headers[key]) {
            config.headers[key] = value;
          }
        });
        return config || $q.when(config);
      }
    };
  }
]).constant("tokenCacheFactory", {
    "jsObject": function() {
      var tokenStorage;
      return [function() {
        return {
          save: function(token) {
            tokenStorage = angular.copy(token);
            return tokenStorage;
          },
          get: function() {
            return tokenStorage;
          },
          remove: function() {
            tokenStorage = null;
          }
        }
      }];
    },
    "localStorage": function(storageKey) {
      return ["$window", function($window) {
        return {
          save: function(token) {
            $window.localStorage.setItem(storageKey, angular.toJson(token));
            return token;
          },
          get: function() {
            var tokenStr = $window.localStorage.getItem(storageKey);
            return tokenStr ? angular.fromJson(tokenStr) : null;
          },
          remove: function() {
            $window.localStorage.removeItem(storageKey);
          }
        }
      }]
    },
    "sessionStorage": function(storageKey) {
      return ["$window", function($window) {
        return {
          save: function(token) {
            $window.sessionStorage.setItem(storageKey, angular.toJson(token));
            return token;
          },
          get: function() {
            var tokenStr = $window.sessionStorage.getItem(storageKey);
            return tokenStr ? angular.fromJson(tokenStr) : null;
          },
          remove: function() {
            $window.sessionStorage.removeItem(storageKey);
          }
        };
      }];
    },
    "cookie": function(storageKey) {
      return ["$cookieStore", function($cookieStore) {
        return {
          save: function(token) {
            $cookieStore.put(storageKey, angular.toJson(token));
            return token;
          },
          get: function() {
            var tokenStr = $cookieStore.get(storageKey);
            return tokenStr ? angular.fromJson(tokenStr) : null;
          },
          remove: function() {
            $cookieStore.remove(storageKey);
          }
        };
      }];
    }
  }).provider('authService', function() {
  var tokenCache, cacheFactory, self = this;

  self.setCacheFactory = function(factory) {
    cacheFactory = factory;
    return self;
  };

  self.$get = ['tokenCacheFactory', "$injector",
    function(tokenCacheFactory, $injector) {
      cacheFactory = cacheFactory || tokenCacheFactory.jsObject();
      tokenCache = $injector.invoke(cacheFactory);
      return {
        setToken: function(token) {
          return tokenCache.save(token);
        },
        getToken: function() {
          return tokenCache.get();
        },
        removeToken: function() {
          return tokenCache.remove();
        }
      };

    }
  ];
}).config(function($httpProvider) {
    $httpProvider.interceptors.push('authInterceptor');
  });
