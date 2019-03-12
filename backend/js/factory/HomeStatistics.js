/**
 * 首页数据统计
 */
angular.module('app').factory('HomeStatistics', [
    '$http',
    function ($http) {
        return {
            /*本月人选统计*/
            candidateMonth: function () {
                return $http.get('/api/index/home/monthcandidate');
            },
            /*今日人选统计*/
            candidateToday: function () {
                return $http.get('/api/index/home/todaycandidate');
            },
            /*本周人选统计*/
            candidateWeek: function () {
                return $http.get('/api/index/home/weekcandidate');
            },
            /*本季度人选统计*/
            candidateQuarter: function () {
                return $http.get('/api/index/home/quartercandidate');
            }
        }
    }]
);
