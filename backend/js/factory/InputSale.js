/**
 * 输入框
 */
angular.module('app').factory('InputSale', ['$modal',
    function ($modal) {
        return {
            /**
             * 打开弹出框
             * @param operate 操作类型
             * @param sales 订单号
             */
            open: function(type, operate, sales) {
                var selected = null;
                if (operate == 1) {
                    /*入账*/
                    selected = sales[0];
                } else if (operate == 2) {
                    /*删除*/
                    selected = sales[0];
                } else if (operate == 3) {
                    /*恢复*/
                    selected = sales[0];
                } else if (operate == 4) {
                    /*领补贴*/
                    selected = sales[0];
                } else if (operate == 5) {
                    /*领推荐费*/
                    selected = sales[0];
                } else if (operate == 6) {
                    /*导出*/
                    selected = sales[0];
                } else if (operate == 7) {
                    /*小时工补差价*/
                    selected = sales[0];
                } else if (operate == 8) {
                    /*继续入职*/
                    selected = sales[0];
                } else if (operate == 9) {
                    /*离职*/
                    selected = sales[0];
                }
                $modal.open({
                    templateUrl: 'tpl/modal_inputsale.html',
                    controller: 'ModalInputSaleController',
                    resolve: {
                        type: function () {
                            return type;
                        },
                        operate: function () {
                            return operate;
                        },
                        selectedCustomer: function() {
                            return selected;
                        }
                    }
                });
            }
        }
    }]
);
