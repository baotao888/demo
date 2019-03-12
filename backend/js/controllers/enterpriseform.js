app.controller('EnterpriseFormController', function($scope, $state, $http, Errors, $stateParams, MessageWindow) {
    var vm = this;
    /*初始化*/
    vm.error = '';
    vm.submitting = false;
    vm.natures = ["合资","外资","民营"];
    vm.industrys = ["电子","汽车","服装"];
    vm.tag = ['五险一金', '认证', '推荐', '包食宿', '高补贴', '加班多', '高薪', '五险', '商业保险', '世界五百强'];
    vm.enterprise = {"id":"", "enterprise_name":"", "description":"", "industry":"", "tag":"", "scale":"", "nature":""};//企业详情

    /*获取企业内容，更新企业信息*/
    if ($stateParams.id != null && $stateParams.id != ''){
        $http.get('/api/job/enterprise/read', {params:{id:$stateParams.id}}).success(function (response) {
            vm.enterprise = response;
        });
        vm.submitInfo = function () {
            vm.submitting = true;
            //更新基本信息
            $http.post('/api/job/enterprise/update', {
                id:$stateParams.id,
                enterprise_name: vm.enterprise.enterprise_name,
                description: vm.enterprise.description,
                industry: vm.enterprise.industry,
                tag: vm.enterprise.tag,
                scale: vm.enterprise.scale,
                nature: vm.enterprise.nature
            }).success(function () {
                vm.error = '已更新';
                vm.submitting = false;
                MessageWindow.open('更新成功');
            });
        }
    }
});
