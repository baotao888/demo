app.controller('OrganizationTreeController', function($scope, $timeout, Organization, $http, MessageWindow) {
    var tree;
    $scope.my_tree_handler = function(branch) {
        var _ref;
        $scope.output = "当前组织: " + branch.label;
        if (branch.data != null){
            $scope.current_organization.id = branch.id;
            $scope.current_organization.org_name = branch.data.value.org_name;
            $scope.current_organization.description = branch.data.description;
            $scope.current_organization.nickname = branch.data.value.nickname;
            $scope.current_organization.listorder = branch.data.value.listorder;
            $scope.current_organization.is_adviser = branch.data.value.is_adviser?true:false;
        }
        if ((_ref = branch.data) != null ? _ref.description : void 0) {
            return $scope.output += '【' + branch.data.description + '】';
        }
    };
    $scope.new_organization = {'org_name':"", "description":"", "nickname":""};
    $scope.current_organization = {'id':'', 'org_name':"", "description":"", "nickname":""};
    $scope.my_data = [];
    $scope.my_tree = tree = {};
    /**
     * 加载数结构
     * @returns {*}
     */
    $scope.try_async_load = function() {
        $scope.doing_async = true;
        $http.get('/api/index/organization/tree').success(function(response){
            $scope.my_data = response;
            $scope.doing_async = false;
        });
    };
    /**
     * 添加组织机构
     */
    $scope.try_adding_a_branch = function() {
        var b;
        b = tree.get_selected_branch();
        if ($scope.new_organization.org_name=='' || $scope.new_organization.nickname==''){
            MessageWindow.open('确认已填写组织名称和门派昵称？');
            return;
        } else if(b==undefined || b==null || b.id==0) {
            MessageWindow.open('请选择上级组织！');
            return;
        }
        Organization.save({
            parent_id: b.id,
            org_name: $scope.new_organization.org_name,
            description: $scope.new_organization.description,
            nickname: $scope.new_organization.nickname,
            is_adviser: $scope.current_organization.is_adviser?1:0
        },function(response) {
            //$scope.output = $scope.new_organization.org_name + "，添加成功";
            MessageWindow.open('添加成功');
            return tree.add_branch(b, {
                label: $scope.new_organization.org_name,
                data: {
                    nickname: $scope.new_organization.nickname,
                    description: $scope.new_organization.description
                }
            });
        }, function() {
            //$scope.output = "添加失败";
            MessageWindow.open('添加失败');
        });
    };
    /**
     * 更新组织架构
     */
    $scope.editing_current_branch = function(){
        var b;
        b = tree.get_selected_branch();
        if(b==undefined || b==null || b.id==0) {
            MessageWindow.open('请选择组织！');
            return;
        }
        Organization.update({id:b.id},{
            org_name: $scope.current_organization.org_name,
            description: $scope.current_organization.description,
            nickname: $scope.current_organization.nickname,
            listorder: $scope.current_organization.listorder,
            is_adviser: $scope.current_organization.is_adviser?1:0
        },function(response) {
            MessageWindow.open('更新成功');
            $scope.try_async_load();
        }, function() {
            MessageWindow.open('更新失败');
        });
    }
    return $scope.try_async_load();
});