app.controller('RolePrivilegesController', ['$scope', '$http', '$stateParams', '$q', 'MessageWindow', 'Role', function ($scope, $http, $stateParams, $q, MessageWindow, Role) {
    $scope.list = [];
    $scope.checked = [];
    $scope.modules_length = {'checked':0, 'length':0};
    $scope.error = '';
    $scope.submitting = false;
    $scope.privileges = [];
    var deferred = $q.defer();
    var promise = deferred.promise;
    promise
        .then(function(val) {
            /*获取权限列表*/
            return $http.get('/api/admin/setting/privileges').success(function (response) {
                $scope.list =  response;
                angular.forEach($scope.list, function (mv, mi) {
                    var c_length = 0;
                    $scope.checked[mi] = {'checked' : false, 'length' : 0, 'checkedlength' : 0, 'controllers' : []};
                    angular.forEach(mv, function (cv, ci) {
                        c_length ++;
                        var a_length = 0;
                        $scope.checked[mi].controllers[ci] = {'checked' : false, 'length' : 0, 'checkedlength' : 0, 'actions' : []};
                        angular.forEach(cv, function (av, ai) {
                            a_length ++;
                            $scope.checked[mi].controllers[ci].actions[av] = {'checked' : false};
                        });
                        $scope.checked[mi].controllers[ci].length = a_length;
                    });
                    $scope.checked[mi].length = c_length;
                    $scope.modules_length.length ++;
                })
            });
        })
        .then(function() {
            /*获取角色权限*/
            return Role.get({id: $stateParams.id}, function(response){
                var pris = response.privileges;
                if (pris) {
                    angular.forEach(pris, function(privilege){
                        $scope.checked[privilege.module].controllers[privilege.controller].actions[privilege.action].checked = true;
                        $scope.checked[privilege.module].controllers[privilege.controller].checkedlength ++;
                        /*检查controller是否全选*/
                        if ($scope.checked[privilege.module].controllers[privilege.controller].checkedlength == $scope.checked[privilege.module].controllers[privilege.controller].length) {
                            $scope.checked[privilege.module].controllers[privilege.controller].checked = true;
                            $scope.checked[privilege.module].checkedlength ++;
                            /*检查modules是否全选*/
                            if ($scope.checked[privilege.module].checkedlength == $scope.checked[privilege.module].length) {
                                $scope.checked[privilege.module].checked = true;
                                $scope.modules_length.checked ++;
                                /*检查是否全选*/
                                if ($scope.modules_length.checked == $scope.modules_length.length) {
                                    $scope.select_all = true;
                                }
                            }
                        }
                    });
                }
            });
        });
    deferred.resolve('A');

    /*全选*/
    $scope.selectAll = function () {
        if($scope.select_all) {
            $scope.modules_length.checked = $scope.modules_length.length;
            angular.forEach($scope.list, function (mv, mi) {
                $scope.checked[mi].checked = true;
                $scope.checked[mi].checkedlength = $scope.checked[mi].length;
                angular.forEach(mv, function (cv, ci) {
                    $scope.checked[mi].controllers[ci].checked = true;
                    $scope.checked[mi].controllers[ci].checkedlength = $scope.checked[mi].controllers[ci].length;
                    angular.forEach(cv, function (av, ai) {
                        $scope.checked[mi].controllers[ci].actions[av].checked = true;
                    });
                });
            })
        }else {
            $scope.modules_length.checked = 0;
            angular.forEach($scope.list, function (mv, mi) {
                $scope.checked[mi].checked = false;
                $scope.checked[mi].checkedlength = 0;
                angular.forEach(mv, function (cv, ci) {
                    $scope.checked[mi].controllers[ci].checked = false;
                    $scope.checked[mi].controllers[ci].checkedlength = 0;
                    angular.forEach(cv, function (av, ai) {
                        $scope.checked[mi].controllers[ci].actions[av].checked = false;
                    });
                });
            })
        }
    };
    /*选择模块*/
    $scope.selectModule = function(m){
        angular.forEach($scope.list, function (mv, mi) {
            if (mi == m) {
                if($scope.checked[m].checked) {
                    $scope.checked[mi].checkedlength = $scope.checked[m].length;//模块选中
                    $scope.modules_length.checked ++;
                    /*检查是否全选*/
                    if ($scope.modules_length.checked == $scope.modules_length.length) {
                        $scope.select_all = true;
                    }
                }else{
                    $scope.checked[mi].checkedlength = 0;
                    /*取消全选*/
                    $scope.modules_length.checked --;
                    $scope.select_all = false;
                }
                angular.forEach(mv, function (cv, ci) {
                    if($scope.checked[m].checked) {
                        $scope.checked[mi].controllers[ci].checked = true;
                        $scope.checked[mi].controllers[ci].checkedlength = $scope.checked[mi].controllers[ci].length;
                    }else{
                        $scope.checked[mi].controllers[ci].checked = false;
                        $scope.checked[mi].controllers[ci].checkedlength = 0;
                    }
                    angular.forEach(cv, function (av, ai) {
                        if($scope.checked[m].checked) {
                            $scope.checked[mi].controllers[ci].actions[av].checked = true;
                        }else{
                            $scope.checked[mi].controllers[ci].actions[av].checked = false;
                        }
                    });
                });
            }
        });
    }
    /*选择控制器*/
    $scope.selectController = function (m, c){
        angular.forEach($scope.list, function (mv, mi) {
            if (mi == m) {
                angular.forEach(mv, function (cv, ci) {
                    if (ci == c) {
                        if($scope.checked[m].controllers[c].checked) {
                            $scope.checked[mi].controllers[ci].checked = true;
                            $scope.checked[mi].controllers[ci].checkedlength = $scope.checked[mi].controllers[ci].length;
                            /*module全选*/
                            $scope.checked[mi].checkedlength ++;
                            if ($scope.checked[mi].checkedlength == $scope.checked[mi].length) {
                                $scope.checked[mi].checked = true;
                                $scope.modules_length.checked ++;
                                /*检查是否全选*/
                                if ($scope.modules_length.checked == $scope.modules_length.length) {
                                    $scope.select_all = true;
                                }
                            }
                        }else{
                            $scope.checked[mi].controllers[ci].checked = false;
                            $scope.checked[mi].controllers[ci].checkedlength = 0;
                            /*取消modules全选*/
                            $scope.checked[mi].checkedlength --;
                            $scope.checked[mi].checked = false;
                            /*检查是否取消全选*/
                            if ($scope.checked[mi].checkedlength == $scope.checked[mi].length-1) {
                                $scope.modules_length.checked --;
                                $scope.select_all = false;
                            }
                        }
                        angular.forEach(cv, function (av, ai) {
                            if($scope.checked[m].controllers[c].checked) {
                                $scope.checked[mi].controllers[ci].actions[av].checked = true;
                            }else{
                                $scope.checked[mi].controllers[ci].actions[av].checked = false;
                            }
                        });
                    }
                });
            }
        });
    }
    /*选择动作*/
    $scope.selectOne = function (m, c, a) {
        angular.forEach($scope.list, function (mv, mi) {
            if (mi == m){
                angular.forEach(mv, function (cv, ci) {
                    if (c == ci){
                        angular.forEach(cv, function (av, ai) {
                            if (a == av) {
                                if ($scope.checked[m].controllers[c].actions[a].checked==false) {
                                    $scope.checked[mi].controllers[ci].checkedlength --;
                                    /*取消controller全选*/
                                    $scope.checked[mi].controllers[ci].checked = false;
                                    /*检查是否取消modules全选*/
                                    if ($scope.checked[mi].controllers[ci].checkedlength == $scope.checked[mi].controllers[ci].length-1) {
                                        $scope.checked[mi].checkedlength --;
                                        $scope.checked[mi].checked = false;
                                        /*检查是否取消全选*/
                                        if ($scope.checked[mi].checkedlength == $scope.checked[mi].length - 1) {
                                            $scope.modules_length.checked --;
                                            $scope.select_all = false;
                                        }
                                    }
                                } else {
                                    $scope.checked[mi].controllers[ci].checkedlength ++;
                                    /*检查controller是否全选*/
                                    if ($scope.checked[mi].controllers[ci].checkedlength == $scope.checked[mi].controllers[ci].length) {
                                        $scope.checked[mi].controllers[ci].checked = true;
                                        $scope.checked[mi].checkedlength ++;
                                        /*检查modules是否全选*/
                                        if ($scope.checked[mi].checkedlength == $scope.checked[mi].length) {
                                            $scope.checked[mi].checked = true;
                                            $scope.modules_length.checked ++;
                                            /*检查是否全选*/
                                            if ($scope.modules_length.checked == $scope.modules_length.length) {
                                                $scope.select_all = true;
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    }
                });
            }
        })
    }
    /*更新后台权限*/
    $scope.submitInfo = function () {
        $scope.submitting = true;
        var privileges = [];
        angular.forEach($scope.list, function (mv, mi) {
            angular.forEach(mv, function (cv, ci) {
                angular.forEach(cv, function (av, ai) {
                    if ($scope.checked[mi].controllers[ci].actions[av].checked) privileges.push({module:mi, controller:ci, action:av});
                });
            });
        })
        //更新基本信息
        Role.update({id:$stateParams.id}, {
            privileges: privileges
        },function(){
            $scope.error = '已更新';
            $scope.submitting = false;
            MessageWindow.open('更新成功');
        },function(){
            $scope.error = '更新失败';
            $scope.submitting = false;
            MessageWindow.open('更新失败');
        });
    }
}]);