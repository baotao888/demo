// config

var app =  
angular.module('app')
  .config(
    [ '$controllerProvider', '$compileProvider', '$filterProvider', '$provide',
	  function ($controllerProvider,   $compileProvider,   $filterProvider,   $provide) {
        
        // lazy controller, directive and service
        app.controller = $controllerProvider.register;
        app.directive  = $compileProvider.directive;
        app.filter     = $filterProvider.register;
        app.factory    = $provide.factory;
        app.service    = $provide.service;
        app.constant   = $provide.constant;
        app.value      = $provide.value;

        window.UEDITOR_HOME_URL = "/vendor/modules/ueditor/";
	  }
	])
  .config(['$translateProvider', function($translateProvider){
    // Register a loader for the static files
    // So, the module will search missing translation tables under the specified urls.
    // Those urls are [prefix][langKey][suffix].
    $translateProvider.useStaticFilesLoader({
      prefix: 'l10n/',
      suffix: '.js'
    });
    // Tell the module what language to use by default
    $translateProvider.preferredLanguage('zh_CN');
    // Tell the module to store the language in the local storage
    //$translateProvider.useLocalStorage();
  }]).config(function($httpProvider) {
    //后端请求权限验证
	$httpProvider.interceptors.push('AuthHandler');
  }).config(function (tokenCacheFactory, authServiceProvider) {
    //后端权限缓存
    authServiceProvider.setCacheFactory(tokenCacheFactory.localStorage('yl-crm-storage-token-key'));
});