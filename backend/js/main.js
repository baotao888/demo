'use strict';

/* Controllers */

angular.module('app')
  .controller('AppCtrl', ['$scope', '$translate', '$localStorage', '$window', '$http', 'authService', '$state', '$interval', '$timeout', '$location',
    function($scope, $translate, $localStorage, $window, $http, authService, $state, $interval, $timeout, $location) {
      // add 'ie' classes to html
      var isIE = !!navigator.userAgent.match(/MSIE/i);
      isIE && angular.element($window.document.body).addClass('ie');
      isSmartDevice( $window ) && angular.element($window.document.body).addClass('smart');

      // config
      $scope.app = {
        name: 'yldagong.com',
        version: '2.1.2',
        // for chart colors
        color: {
          primary: '#7266ba',
          info:    '#23b7e5',
          success: '#27c24c',
          warning: '#fad733',
          danger:  '#f05050',
          light:   '#e8eff0',
          dark:    '#3a3f51',
          black:   '#1c2b36'
        },
        settings: {
          themeID: 1,
          navbarHeaderColor: 'bg-black',
          navbarCollapseColor: 'bg-white-only',
          asideColor: 'bg-black',
          headerFixed: true,
          asideFixed: false,
          asideFolded: false,
          asideDock: false,
          container: false
        }
      }

      // save settings to local storage
      if ( angular.isDefined($localStorage.settings) ) {
        $scope.app.settings = $localStorage.settings;
      } else {
        $localStorage.settings = $scope.app.settings;
      }
      $scope.$watch('app.settings', function(){
        if( $scope.app.settings.asideDock  &&  $scope.app.settings.asideFixed ){
          // aside dock and fixed must set the header fixed.
          $scope.app.settings.headerFixed = true;
        }
        // save to local storage
        $localStorage.settings = $scope.app.settings;
      }, true);

      // angular translate
      $scope.lang = { isopen: false };
      $scope.langs = {en:'English', de_DE:'German', it_IT:'Italian', zh_CN:'Chinese'};
      $scope.selectLang = $scope.langs[$translate.proposedLanguage()] || "English";
      $scope.setLang = function(langKey, $event) {
        // set the current lang
        $scope.selectLang = $scope.langs[langKey];
        // You can change the language during runtime
        $translate.use(langKey);
        $scope.lang.isopen = !$scope.lang.isopen;
      };

      function isSmartDevice( $window )
      {
          // Adapted from http://www.detectmobilebrowsers.com
          var ua = $window['navigator']['userAgent'] || $window['navigator']['vendor'] || $window['opera'];
          // Checks for iOs, Android, Blackberry, Opera Mini, and Windows mobile devices
          return (/iPhone|iPod|iPad|Silk|Android|BlackBerry|Opera Mini|IEMobile/).test(ua);
      }
      /*默认用户信息*/
      $scope.user = {
          menu:{web:false},
          profile:{
              real_name:"姓名…",
              pos_nme:"职位…",
              nickname:"花名…",
              is_manager:0,
              employee_id:0,
              avatar: 'img/a1.jpg',
              gender:1,
              pos_level:''
          }
      };
      $scope.loadProfile = function(){
          if(angular.isDefined($localStorage.userProfile)){
              $scope.user = $localStorage.userProfile;
       }
      }
      $timeout(function(){
          $scope.loadProfile();
      }, 1000 * 30);
      //默认用户消息
      $scope.message = {count:0, list:[]};
	  $scope.loadTimeDate = function(){
	 	if(authService.getToken() != null){
			/*获取最新消息*/
			$http.get('/api/index/index/message', {
			  params: {
				size: 5
			  }
			}).then(function(res){
			  $scope.message = res.data;
			});
            if ($scope.user.menu.web){
                /*获取动态总数*/
                $http.get('/api/index/index/latest').success(function(response){
                    $scope.latest = response;
                });
            }
            $scope.loadProfile();
		}
	  }
	  $scope.loadTimeDate();
	  $interval(function () {
		$scope.loadTimeDate();
	  }, 1000 * 60 * 5);
	  
	  /*搜索*/
	  $scope.searchSetting = {};
	  $scope.searchCustomer = function(){
        $localStorage.searchSetting = $scope.searchSetting;
	  	$state.go('app.candidate.list');
	  };

	  /*收藏*/
	  $scope.favoriteSetting = {'title':'', 'doing':false};
	  $scope.showFavorite = function (){
	      var flag = true;
	      angular.forEach($scope.user.favorite, function (value) {
	          if (value.url == $location.url()) {
                  flag = false;
              }
          });
          if (flag) $scope.favoriteSetting.doing = true;
      }
	  $scope.addFavorite = function () {
          var url = $location.url();
          var title = $scope.favoriteSetting.title;
          /*提交服务器*/
          $http.get('/api/index/index/addFavorite', {
              params: {
                  url: url,
                  title: title
              }
          }).then(function(res){
              $localStorage.userProfile.favorite.push({'url':url, 'title':title});//更新本地缓存
              $scope.favoriteSetting.doing = false;
              $scope.favoriteSetting.title = '';
          });
      }
      $scope.cancelFavorite = function () {
          var url = $location.url();
          /*提交服务器*/
          $http.get('/api/index/index/deleteFavorite', {
              params: {
                  url: url
              }
          }).then(function(res){
              $scope.favoriteSetting.doing = false;
          });
      }
      $scope.deleteFavorite = function (index, url) {
          /*提交服务器*/
          $http.get('/api/index/index/deleteFavorite', {
              params: {
                  url: url
              }
          }).then(function(res){
              $localStorage.userProfile.favorite.splice(index, 1);//更新本地缓存
          });
      }
  }]);