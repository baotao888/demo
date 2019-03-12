/**
 * 客户
 */
angular.module('app').factory('Customer', ['$modal', '$http', '$q', '$location', 'API', 'MessageWindow', 'Errors',
    function ($modal, $http, $q, $location, API, MessageWindow, Errors) {
        return {
            /**
             * 打开详情弹出框
             * @param id 客户编号
             */
            open: function (id) {
                $modal.open({
                    templateUrl: 'tpl/modal_customer.html',
                    controller: 'ModalCustomerController as vm',
                    size: 'lg',
                    resolve: {
                        customerId : function() {
                            return id;
                        }
                    }
                });
            },
            //详情
            detail: function (id) {
                return $http({method: 'get', url: API.customerPoolDetail(), params: {'id': id}});
            },
            //分配历史
            assignHistory: function (id) {
                return $http({method: 'get', url: API.customerAssignHistory(), params: {'id': id}});
            },
            //工作经验
            workHistory: function (id) {
                return $http({method: 'get', url: API.customerWorkHistory(), params: {'id': id}});
            },
            //联系日志
            contactLog: function (id) {
                return $http({method: 'get', url: API.customerContactLog(), params: {'customer': id}});
            },
            //日志
            log: function (id) {
                return $http({method: 'get', url: API.customerLog(), params: {'id': id}});
            },
            //认领客户
            recognize: function (ids) {
                var deferred = $q.defer(),
                    promise = deferred.promise;
                $http({method: 'post', url: API.customerPoolOperate(3), data: {'customerpools/a': ids}}).success(function(req){
                    if (req==1){
                        MessageWindow.open(Errors.common_success);
                        deferred.resolve(true);//返回成功
                    } else if (req==0) {
                        MessageWindow.open(Errors.recognize_error_1);
                        deferred.resolve(false);
                    } else if (req==-1) {
                        MessageWindow.open(Errors.recognize_error_2);
                        deferred.resolve(false);
                    } else if (req==-2) {
                        MessageWindow.open(Errors.recognize_error_3);
                        deferred.resolve(false);
                    } else {
                        MessageWindow.open(Errors.common_error);
                        deferred.resolve(false);
                    }
                }).error(function(req){
                    MessageWindow.open(Errors.sys_error);
                    deferred.resolve(false);
                });
                return promise;
            },
            //释放客户到公海客户池
            release: function (arr_id) {
                var deferred = $q.defer(),
                    promise = deferred.promise;
                $http({method: 'post', url: API.customerPoolOperate(2), data:{'customerpools/a': arr_id}}).success(function(req){
                    if (req){
                        MessageWindow.open(Errors.common_success);
                        deferred.resolve(true);//返回成功
                    } else {
                        MessageWindow.open(Errors.common_error);
                        deferred.resolve(false);
                    }
                }).error(function(req){
                    MessageWindow.open(Errors.sys_error);
                    deferred.resolve(false);
                });
                return promise;
            },
            //删除客户
            drop: function (id) {
                var deferred = $q.defer(),
                    promise = deferred.promise;
                $http({method: 'post', url: API.deleteCustomer(), data:{'id': id}}).success(function(req){
                    if (req){
                        MessageWindow.open(Errors.common_success);
                        deferred.resolve(true);//返回成功
                    } else {
                        MessageWindow.open(Errors.common_error);
                        deferred.resolve(false);
                    }
                }).error(function(req){
                    MessageWindow.open(Errors.sys_error);
                    deferred.resolve(false);
                });
                return promise;
            },
            //客户唯一性验证
            checkUnique: function (num, param) {
                var deferred = $q.defer(),
                    promise = deferred.promise;
                $http({method: 'get', url: API.checkCustomerUnique(), params: param}).success(function(response){
                    var verphone = response;
                    if (verphone != '') {
                        var column,
                            message;
                        if (num == 1){
                            column = '联系电话';
                        } else if (num == 2) {
                            column = '备用电话';
                        }
                        if (verphone.owner) {
                            message = '该'+column+'和顾问<' + verphone.owner + '>名下人选【' + verphone.real_name + '】的联系电话相同，不能录入';
                            MessageWindow.open(message);
                        } else {
                            message = '该'+column+'和客户【' + verphone.real_name + '】的联系电话相同，不能录入。此客户还没有顾问认领，点击客户详情右上角认领按钮可认领';
                            MessageWindow.redirect(message, [{href: '#/app/customer/' + verphone.id + '/detail', value: '详情'}]);
                        }
                        deferred.resolve(true);
                    } else {
                        deferred.resolve(false);
                    }
                });
                return promise;
            },
            //新增客户
            create: function (param) {
                return $http({method: 'post', url: API.createCustomer(), data: param});
            },
            //更新客户
            update: function (param) {
                return $http({method: 'post', url: API.updateCustomer(), data: param});
            },
            //进入客户详情
            goDetail: function (id) {
                $location.path('/app/customer/' + id + '/detail');
            },
            //显示客户列表
            list: function (type, searchText, currentPage, pageSize, isDetail, startTime, endTime) {
                var params = {},
                    reg = new RegExp("[\\u4E00-\\u9FFF]+","g"),
                    regp = /^[+-]?\d+(\.\d+)?$|^$|^(\d+|\-){7,}$/,
                    url = API.customerList(type);
                if(searchText == ''){
                    params = {page: currentPage, pagesize: pageSize};
                } else if(reg.test(searchText)){
                    params = {page: currentPage, pagesize: pageSize, real_name: searchText};
                }else if(regp.test(searchText)){
                    params = {page: currentPage, pagesize: pageSize, phone: searchText};
                }else{
                    params = {page: currentPage, pagesize: pageSize, from: searchText};
                }
                if(isDetail) {
                    params = {page: currentPage, pagesize: pageSize, distribute_time_s: startTime, distribute_time_e: endTime};
                }
                return $http({method: 'get', url: url, params: params});
            }
        }
    }]
);
