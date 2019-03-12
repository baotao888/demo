/**
 * 消息框
 */
angular.module('app').factory('MessageWindow', [
    '$modal',
    '$rootScope',
    function ($modal, $rootScope) {
        return {
            open: function(msg) {
                $modal.open({
                    templateUrl: 'tpl/modal.html',
                    controller: 'ModalInstanceController',
                    resolve: {
                        msg: function () {
                            return msg;
                        }
                    }
                });
            },
            redirect: function(msg, urls) {
                var scope = $rootScope.$new(true);
                scope.buttons = urls;
                $modal.open({
                    templateUrl: 'tpl/modal.html',
                    controller: 'ModalInstanceController',
                    scope: scope,
                    resolve: {
                        msg: function () {
                            return msg;
                        }
                    }
                });
            },
        }
    }]);
