/**
 * 标签操作
 */
app.controller('ModalTagController', [
    '$scope',
    '$modalInstance',
    '$http',
    '$state',
    'selectedCustomer',
    'operate',
    'CandidateTag',
    function($scope, $modalInstance, $http, $state, selectedCustomer, operate, CandidateTag) {
        $scope.items = [];//标签列表
        $scope.tag = '';

        /*获取标签*/
        $scope.loadTag = function () {
            CandidateTag.get(selectedCustomer, operate).then(function (req) {
                $scope.items = req.data;
            });
        };

        /*
         * 添加标签
         * 无需回调，忽略接口返回值，加快前端相应
         * */
        $scope.addTag = function () {
            var tag = $scope.tag;
            tag && $scope.items.push(tag);
            $scope.tag = '';
            CandidateTag.add(selectedCustomer, tag, operate);
        };

        /*
         * 删除标签
         * 无需回调，忽略接口返回值，加快前端相应
         * */
        $scope.deleteTag = function (index) {
            var tag = $scope.items[index];
            $scope.items.splice(index, 1);
            CandidateTag.remove(selectedCustomer, tag, operate);
        };

        $scope.ok = function () {
            $modalInstance.close('ok');
        };

        $scope.loadTag();//加载
    }
]);