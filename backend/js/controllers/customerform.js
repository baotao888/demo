/**
 * 客户池表单
 */
app.controller('CustomerFormController', function($scope, $state, $stateParams, $modal, $http, $location, Errors, MessageWindow, CustomerFroms, CustomerCareers, Customer) {
    var vm = this;
    /*初始化*/
    vm.addcustomer = true;
    vm.careers = CustomerCareers;
    vm.contant = false;//表单验证标志位
    vm.customer = {
        "birthday": "1995-08-01",
        "career": "",
        "contact": "",
        "from": "",
        "from_value": "backend",
        "gender": 1,
        "hometown": "",
        "intetion": "",
        "mobile_1": "",
        "phone": "",
        "real_name": ""
    };
    vm.customer_data = {"wechat": "", "qq": "", "address": "", "email": ""};//客户详情
    vm.edit = false;
    vm.error = '';
    vm.froms = CustomerFroms;
    vm.operate = "新增";
    vm.submitting = false;

    /*手机号验证*/
    vm.phonever = function(num){
        var phone = '';
        if (num == 1){
            if (vm.customer.phone == undefined) return;
            phone = vm.customer.phone;
        }  else if (num == 2){
            if (vm.customer.mobile_1 == undefined) return;
            phone = vm.customer.mobile_1;
        }
        if (phone == '') return;
        var params = {phone : phone};
        if ($stateParams.id != null && $stateParams.id != ''){
            params = {phone: phone, cpid: $stateParams.id};
        }
        Customer.checkUnique(num, params).then(function (data) {
            vm.contant = data;
        });
    };

    vm.submitInfo = function(){
        vm.submitting = true;
        Customer.create({
            address : vm.customer_data.address,
            birthday : vm.customer.birthday,
            career : vm.customer.career,
            contact : vm.customer.contact,
            email : vm.customer_data.email,
            from : vm.customer.from_value,
            gender : vm.customer.gender,
            hometown : vm.customer.hometown,
            intetion : vm.customer.intetion,
            mobile : vm.customer.mobile_1,
            phone : vm.customer.phone,
            qq : vm.customer_data.qq,
            real_name : vm.customer.real_name,
            wechat : vm.customer_data.wechat
        }).then(function (response) {
            vm.open(Errors.common_success);
            Customer.goDetail(response.data.id);//新增客户之后跳转到客户详情
        }, function (response) {
            vm.open(Errors.sys_error);
            vm.error = '录入失败';
            vm.submitting = false;
        });
    }

    /*更新客户信息*/
    if ($stateParams.id != null && $stateParams.id != ''){
        Customer.detail($stateParams.id).then(function (response) {
            vm.customer = response.data;
            vm.customer_data = vm.customer.detail;
        });
        vm.addcustomer = false;
        vm.edit = true;
        vm.operate = "编辑";
        vm.submitInfo = function () {
            vm.submitting = true;
            //更新客户信息
            Customer.update({
                id : $stateParams.id,
                real_name : vm.customer.real_name,
                gender : vm.customer.gender,
                phone : vm.customer.phone,
                from : vm.customer.from_value,
                birthday : vm.customer.birthday,
                hometown : vm.customer.hometown,
                career : vm.customer.career,
                wechat : vm.customer_data.wechat,
                qq : vm.customer_data.qq,
                address : vm.customer_data.address,
                email : vm.customer_data.email,
                mobile : vm.customer.mobile_1
            }).then(function (response) {
                vm.open(Errors.common_success);
                vm.error = '已更新';
                vm.submitting = false;
                Customer.goDetail($stateParams.id);//更新客户之后跳转到客户详情
            }, function (response) {
                vm.open(Errors.sys_error);
                vm.error = '更新失败';
                vm.submitting = false;
            });
        }
    }

    /**
     * 消息提醒
     * @param msg 消息内容
     */
    vm.open = function (msg) {
        MessageWindow.open(msg);
    };

    /*日期插件*/
    $scope.today = function() {
        $scope.dt = new Date();
    };
    $scope.today();

    $scope.clear = function () {
        $scope.dt = null;
    };

    $scope.open = function($event) {
        $event.preventDefault();
        $event.stopPropagation();

        $scope.opened = true;
    };

    $scope.dateOptions = {
        formatYear: 'yy',
        startingDay: 1,
        class: 'datepicker'
    };

    $scope.format = 'yyyy-MM-dd';
});
