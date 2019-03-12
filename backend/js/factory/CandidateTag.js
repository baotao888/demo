/**
 * 人选标签操作
 */
angular.module('app').factory('CandidateTag', [
    '$modal',
    '$http',
    function ($modal, $http) {
        return {
            /*打开标签弹出框*/
            open: function (candidate, operate) {
                var tagModal = $modal.open({
                    templateUrl: 'tpl/modal_tag.html',
                    controller: 'ModalTagController',
                    resolve: {
                        /*客户编号*/
                        selectedCustomer: function() {
                            return candidate;
                        },
                        /*标签类型*/
                        operate: function() {
                            return operate;
                        }
                    }
                });
                return tagModal.result;
            },
            /*获取人选标签*/
            get: function (id, type) {
                var data = type==1?{cpid: id}:{id: id};
                return $http({
                    method: 'get',
                    url: '/api/customer/candidate/tag',
                    params: data
                });
            },
            /*添加标签*/
            add: function (id, tag, type) {
                if (tag !=  '') {
                    var data = type==1?{tag: tag, cpid: id}:{tag: tag, id: id};
                    return $http({
                        method: 'post',
                        url: '/api/customer/candidate/addtag',
                        data: data
                    });
                }
            },
            /*删除标签*/
            remove: function (id, tag, type) {
                var data = type==1?{tag: tag, cpid: id}:{tag: tag, id: id};
                return $http({
                    method: 'post',
                    url: '/api/customer/candidate/deletetag',
                    data: data
                });
            },
            /*标签管理*/
            list: function (tag) {
                var params = {tag: tag};
                return $http({
                    method: 'get',
                    url: '/api/customer/candidate/tags',
                    params: params
                });
            }
        }
    }]
);
