'use strict';

/* Controllers */
// signin controller
app.controller('SigninFormController', function($scope, $state, $http, Token, Errors, authService, $localStorage) {
    var vm = this;
    vm.user = {};
    vm.authError = null;
	vm.submitting = false;
    vm.login = function() {
		vm.submitting = true;
        vm.authError = '登录中，请稍候……';
        // Try to login
        Token.save({
            username: vm.user.username,
            password: vm.user.password
        },function(response) {
            authService.setToken({'yl-crm-token':response.token});//登录成功之后保存token
            vm.loadProfile();
            $state.go('app.index');//跳转到首页
        }, function() {
			vm.submitting = false;
            vm.authError = Errors.signin;
        });
    };
    /*获取个人信息*/
    vm.loadProfile = function(){
        /*获取用户信息*/
        $http.get('/api/index').then(function(response){
            $localStorage.userProfile = response.data;
        });
    }
  })
;