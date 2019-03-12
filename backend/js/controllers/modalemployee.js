/**
 * 选择员工
 */
app.controller('ModalEmployeeController', ['$scope', '$modalInstance', '$http', '$state', 'operate', 'selectedUsers', 'Adviser', 'Errors', function(
    $scope,
    $modalInstance,
    $http,
    $state,
    operate,
    selectedUsers,
    Adviser,
    Errors
) {
    $scope.list = [];//员工列表
    $scope.employee = {};//选中的员工
    $scope.error = "";
    $scope.submitting = false;
    $scope.employee_group = [];

    /**
     * 加载顾问列表
     */
    $scope.load = function () {
        Adviser.list().then(function (response) {
            $scope.list = response.data;
        });
    }

    /**
     * 选择员工
     * @param item
     */
    $scope.selectEmployee = function(item){
        $scope.employee = item;
    }

    $scope.showMessage = function (data) {
        if (data) {
            $scope.error = Errors.distribute_success;
            $modalInstance.close(data);//成功之后关闭对话框返回成功标志
        } else {
            $scope.error = Errors.distribute_error;
            $scope.submitting = false;
        }
    }

    $scope.showError = function () {
        $scope.error = Errors.sys_error;
        $scope.submitting = false;
    }

    $scope.ok = function () {
        if ($scope.employee.id == undefined) {
            $scope.error = '请选择顾问';
            return;
        }
        $scope.submitting = true;
        if (operate == 1) {
            /*分配注册用户*/
            Adviser.distributeUser($scope.employee.id, selectedUsers).then(function (response) {
                $scope.showMessage(response.data);
            }, function () {
                $scope.showError();
            });
        } else if (operate == 3) {
            /*分配报名用户*/
            Adviser.distributeSignup($scope.employee.id, selectedUsers).then(function (response) {
                $scope.showMessage(response.data);
            }, function () {
                $scope.showError();
            });
        } else if (operate == 4) {
            /*分配客户池*/
            Adviser.distributeCustomer($scope.employee.id, selectedUsers).then(function (response) {
                $scope.showMessage(response.data);
            }, function () {
                $scope.showError();
            });
        }
        else if (operate == 97) {
            /*划转候选人*/
            Adviser.moveCandidate($scope.employee.id, selectedUsers).then(function (response) {
                if (response.data){
                    $scope.error = Errors.move_success;
                    $modalInstance.close(response.data);
                } else {
                    $scope.error = Errors.move_error;
                    $scope.submitting = false;
                }
            }, function () {
                $scope.showError();
            });
        }
    };
    $scope.cancel = function () {
        $modalInstance.dismiss('cancel');
    };

    $scope.load();//加载
}]);