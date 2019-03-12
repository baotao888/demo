/**
 * 选择职位
 */
app.controller('ModalJobController', [
    '$scope',
    '$modalInstance',
    '$http',
    '$state',
    '$filter',
    'operate',
    'selectedCustomers',
    'JobChoice',
    'MessageWindow',
    'Errors',
    function($scope, $modalInstance, $http, $state, $filter, operate, selectedCustomers, JobChoice, MessageWindow, Errors) {
    var today = $filter('date')(new Date(), 'yyyy-MM-dd');
    $scope.before = ''; // 一周之前的时间
    $scope.before_open = false;
    $scope.current = $filter('date')(new Date(), 'yyyy-MM-dd');
    var date;
    $scope.list = [];//职位列表
    $scope.listarr = [];
    $scope.jobList = [];
    $scope.validlist = [];
    $scope.job = {};//选中的职位

    for(var i=0; i<=6; i++) {
      date = new Date().getTime()-i*86400000;
      date = $filter('date')(date, 'yyyy-MM-dd');
      $scope.validlist.push(date);
    }

    $scope.loadCompany = function(date) {
        today = date;
      $http({method: 'get', url:'/api/recruit?date='+date}).success(function(res) {
          $scope.jobList = res;
      })
    };

    $scope.searchBefore = function () {
        today = $filter('date')($scope.before, 'yyyy-MM-dd');;
        $http({method: 'get', url:'/api/recruit?date='+today}).success(function(res) {
            $scope.jobList = res;
        })
    };

    $scope.selectJob = function(item){
        $scope.job = item;
        $scope.current = today;
    };

    $scope.ok = function () {
        if ($scope.job.id == undefined || $scope.job.id == null) {
            MessageWindow.open('请选择职位');
            $modalInstance.dismiss(false);
            return;
        }
        if (operate == 2) {
            /*人选报名*/
            $http({
                method: 'post',
                url: '/api/customer/candidate/signup',
                data: {job: $scope.job.id, "customers/a": selectedCustomers, date: today}
            }).success(function (){
                MessageWindow.open(Errors.common_success);
                $state.go('app.candidate.list', {type:'signup'});
            }).error(function () {
                MessageWindow.open(Errors.sys_error);
            });
        } else if (operate == 6) {
            /*离职人选再次报名*/
            $http({
                method: 'post',
                url: '/api/customer/candidate/resignup',
                data: {job: $scope.job.id, "customers/a": selectedCustomers, date: today}
            }).success(function (){
                MessageWindow.open(Errors.common_success);
                $state.go('app.candidate.list', {type:'signup'});
            }).error(function () {
                MessageWindow.open(Errors.sys_error);
            });
        } else if (operate == 7) {
            /*已报名或者接站人选重新报名*/
            $http({
                method: 'post',
                url: '/api/customer/candidate/updateSignup',
                data: {job: $scope.job.id, "customers/a": selectedCustomers, date: today}
            }).success(function (){
                MessageWindow.open(Errors.common_success);
                $state.go('app.candidate.list', {type:'signup'});
            }).error(function () {
                MessageWindow.open(Errors.sys_error);
            });
        }
		$modalInstance.close($scope.job.id);
    };

    $scope.cancel = function () {
        $modalInstance.dismiss('cancel');
    };

    $scope.loadCompany(today);

    /**
     * 日期显示
     */
    $scope.open = function($event) {
        $scope.before_open = true;
        $event.preventDefault();
        $event.stopPropagation();
    };
}]);