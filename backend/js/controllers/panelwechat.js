app.controller('PanelWechatController', function($scope, $http, MessageWindow, ConfirmWindow) {
	$scope.error = "";
    $scope.updateMenu = function (){
        ConfirmWindow.open("确定要更新微信自定义菜单吗").then(function(){
            $http.get('/api/index/wechat/updateMenu').success(function (response) {
                $scope.error = response;
                MessageWindow.open($scope.error);
            });
        });

	}
});