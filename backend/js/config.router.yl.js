'use strict';

/**
 * Config for the router
 */
angular.module('app')
    .run([
        '$rootScope',
        '$state',
        '$stateParams',
        'authService',
        function ($rootScope, $state, $stateParams, authService) {
            $rootScope.$state = $state;
            $rootScope.$stateParams = $stateParams;
            // Redirect to login if route requires auth and you're not logged in
            $rootScope.$on('$stateChangeStart', function (event, next) {
                //console.log(authService.getToken());[debug]
                if(next.name == 'access.signin'){
                    if(authService.getToken() != null){
                        $state.go('app.index');//已经登录跳转到首页
                        event.preventDefault();
                    }
                } else if (next.name == 'access.signout'){
                    //登出
                    authService.removeToken();
                    $state.go('access.signin');
                    event.preventDefault();
                } else {
                    if(authService.getToken() == null){
                        $state.go('access.signin');//未登录跳转到登录页
                        event.preventDefault();
                    }
                }
            });
        }
    ])
    .config([
        '$stateProvider',
        '$urlRouterProvider',
        function ($stateProvider, $urlRouterProvider) {
            $urlRouterProvider.otherwise('/access/signin');
            $stateProvider
                .state('app', {
                    abstract: true,
                    url: '/app',
                    templateUrl: 'tpl/app.html'
                })
                .state('app.index', {
                    url: '/index',
                    templateUrl: 'tpl/app_index.html',
                    controller: 'MonthDashboardController as vm',
                    resolve: {
                        deps: [
                            '$ocLazyLoad',
                            function ($ocLazyLoad) {
                                return $ocLazyLoad.load(['js/controllers/monthhome.js', 'js/factory/HomeStatistics.js']);
                            }
                        ]
                    }
                })
                .state('app.dashboard-week', {
                    url: '/weekdashboard',
                    templateUrl: 'tpl/app_dashboard_week.html',
                    controller: 'WeekDashboardController as vm',
                    resolve: {
                        deps: [
                            '$ocLazyLoad',
                            function ($ocLazyLoad) {
                                return $ocLazyLoad.load(['js/services/sao/Index.js', 'js/controllers/weekchart.js']);
                            }
                        ]
                    }
                })
                .state('app.dashboard-month', {
                    url: '/index',
                    templateUrl: 'tpl/app_index.html',
                    controller: 'FlotChartDemoController as vm',
                    resolve: {
                        deps: [
                            '$ocLazyLoad',
                            function ($ocLazyLoad) {
                                return $ocLazyLoad.load(['js/services/sao/Index.js', 'js/controllers/chart.js']);
                            }
                        ]
                  }
                })
                /*人选管理*/
                .state('app.candidate', {
                    url: '/candidate',
                    template: '<div ui-view class="fade-in"></div>'
                })
                .state('app.candidate.list', {
                    url: '/list/:type',
                    templateUrl: 'tpl/candidate_list.html',
                    controller: 'CandidateListController as vm',
                    resolve: {
                        deps: [
                            'uiLoad',
                            '$ocLazyLoad',
                            function (uiLoad, $ocLazyLoad) {
                                return $ocLazyLoad.load(['toaster', 'ui.select']).then(function () {
                                    return uiLoad.load([
                                        'vendor/libs/moment-with-locales.min.js',
                                        'js/constants/API.js',
                                        'js/factory/Adviser.js',
                                        'js/factory/CallinCustomer.js',
                                        'js/factory/CandidateRemain.js',
                                        'js/factory/CandidateTag.js',
                                        'js/factory/CandidateTask.js',
                                        'js/factory/CandidateTop.js',
                                        'js/factory/ConfirmWindow.js',
                                        'js/factory/Contact.js',
                                        'js/factory/Customer.js',
                                        'js/factory/InputWindow.js',
                                        'js/factory/JobChoice.js',
                                        'js/factory/MessageWindow.js',
                                        'js/filters/allowanceType.js',
                                        'js/filters/ondutyType.js',
                                        'js/filters/fromNow.js',
                                        'js/filters/genderClass.js',
                                        'js/filters/statusClass.js',
                                        'js/filters/thTitle.js',
                                        'js/services/sao/AdviserOrganizations.js',
                                        'js/services/sao/OperateButtons.js',
                                        'js/controllers/candidatelist.js',
                                        'js/controllers/modal.js',
                                        'js/controllers/modalcall.js',
                                        'js/controllers/modalcustomer.js',
                                        'js/controllers/modalemployee.js',
                                        'js/controllers/modalinput.js',
                                        'js/controllers/modalintention.js',
                                        'js/controllers/modaljob.js',
                                        'js/controllers/modalsure.js',
                                        'js/controllers/modaltag.js',
                                    ]);
                                });
                            }
                        ]
                    }
                })
                /*业绩管理*/
                .state('app.performance', {
                    url: '/performance',
                    template: '<div ui-view class="fade-in"></div>'
                })
                /*业绩管理-----本月*/
                .state('app.performance.salelist', {
                    url: '/salelist/:type',
                    templateUrl: 'tpl/performance_salelist.html',
                    controller: 'PerformanceSaleController as vm',
                    resolve: {
                        deps: [
                            'uiLoad',
                            '$ocLazyLoad',
                            function (uiLoad, $ocLazyLoad) {
                                return $ocLazyLoad.load('ui.select').then(function() {
                                    return uiLoad.load([
                                        'js/controllers/performancesalelist.js',
                                        'js/filters/thTitle.js',
                                        'js/factory/Enterprise.js',
                                        'js/factory/Labourservice.js',
                                        'js/services/sao/AdviserOrganizations.js',
                                        'js/factory/MessageWindow.js',
                                        'js/factory/InputSale.js',
                                        'js/controllers/modal.js',
                                        'js/controllers/modalinputsale.js',
                                        'js/controllers/orderdetail.js',
                                        'js/services/sao/OperateButtons.js',
                                        ]);
                                })
                            }
                        ]
                    }
                })
                .state('app.order', {
                    url: '/order',
                    template: '<div ui-view class="fade-in"></div>'
                })
                .state('app.order.detail', {
                    url: '/:id/detail',
                    templateUrl: 'tpl/order_detail.html',
                    controller: 'OrderDetailController as vm',
                    resolve: {
                        deps: [
                            '$ocLazyLoad',
                            function ($ocLazyLoad) {
                                return $ocLazyLoad.load('ui.select').then(function() {
                                    return $ocLazyLoad.load([
                                        'js/controllers/orderdetail.js'
                                        ]);
                                })
                            }
                        ]
                    }
                })
                .state('app.candidate.alert', {
                    url: '/alert',
                    templateUrl: 'tpl/candidate_alert.html',
                    controller: 'CandidateAlertController as vm',
                    resolve: {
                        deps: [
                            'uiLoad',
                            '$ocLazyLoad',
                            function(uiLoad,$ocLazyLoad){
                                return $ocLazyLoad.load('toaster').then(function () {
                                    return uiLoad.load([
                                        'js/controllers/candidatealert.js',
                                        'js/controllers/modal.js',
                                        'js/controllers/modalemployee.js',
                                        'js/controllers/modaljob.js',
                                        'js/controllers/modaltag.js',
                                        'js/controllers/modalinput.js',
                                        'js/controllers/modalcall.js',
                                        'js/factory/CandidateRemain.js',
                                        'js/factory/MessageWindow.js',
                                        'js/factory/CandidateTag.js',
                                        'js/factory/CandidateTask.js',
                                        'js/factory/Contact.js',
                                        'js/controllers/modalcustomer.js',
                                        'js/factory/Customer.js',
                                        'js/constants/API.js',
                                        'js/filters/genderClass.js',
                                        'vendor/libs/moment-with-locales.min.js',
                                        'js/filters/fromNow.js'
                                    ]);
                                });
                            }
                        ]
                    }
                })
                //联系日志=>今日
                .state('app.candidate.contact', {
                    url: '/contact',
                    templateUrl: 'tpl/contact_list.html',
                    controller: 'CandidateContactController as vm',
                    resolve: {
                        deps: [
                            '$ocLazyLoad',
                            function ($ocLazyLoad) {
                                return $ocLazyLoad.load(['js/controllers/contactlist.js', 'js/services/sao/OperateButtons.js']);
                            }
                        ]
                    }
                })
                .state('app.candidate.contactform', {
                    url: '/:id/contactform',
                    templateUrl: 'tpl/contact_form.html',
                    controller: 'ContactFormController as vm',
                    resolve: {
                        deps: [
                            '$ocLazyLoad',
                            function ($ocLazyLoad) {
                                return $ocLazyLoad.load('toaster').then(function () {
                                    return $ocLazyLoad.load(['js/controllers/contactform.js', 'js/factory/Customer.js', 'js/factory/MessageWindow.js', 'js/constants/API.js']);
                                });
                            }
                        ]
                    }
                })
                //联系日志=>全部
                .state('app.candidate.contacttable', {
                    url: '/contacttable',
                    templateUrl: 'tpl/contact_table.html',
                    controller: 'ContactTableController as vm',
                    resolve: {
                        deps: [
                            '$ocLazyLoad',
                            function ($ocLazyLoad) {
                                return $ocLazyLoad.load(['js/controllers/contacttable.js', 'js/services/sao/OperateButtons.js']);
                            }
                        ]
                    }
                })
                //联系日志=>部门
                .state('app.candidate.contactstatistics', {
                    url: '/contactstatistics',
                    templateUrl: 'tpl/contact_statistics.html',
                    controller: 'ContactStatisticsController as vm',
                    resolve: {
                        deps: [
                            '$ocLazyLoad',
                            function ($ocLazyLoad) {
                                return $ocLazyLoad.load(['js/controllers/contactstatistics.js', 'js/services/sao/OperateButtons.js', 'js/services/sao/AdviserOrganizations.js']);
                            }
                        ]
                    }
                })
                .state('app.candidate.awardform', {
                    url: '/:id/awardform',
                    templateUrl: 'tpl/award_form.html',
                    controller: 'AwardFormController as vm',
                    resolve: {
                        deps: [
                            'uiLoad',
                            function (uiLoad) {
                                return uiLoad.load(['js/controllers/modal.js','js/controllers/awardform.js']);
                            }
                        ]
                    }
                })
                .state('app.candidate.import', {
                    url: '/import',
                    templateUrl: 'tpl/candidate_import.html',
                    controller: 'CandidateImportController as vm',
                    resolve: {
                        deps: [
                            '$ocLazyLoad',
                            function ($ocLazyLoad) {
                                return $ocLazyLoad.load('angularFileUpload').then(function () {
                                    return $ocLazyLoad.load(['js/controllers/candidateimport.js', 'js/services/sao/AdviserOrganizations.js']);
                                });
                            }
                        ]
                    }
                })
                /*客户池*/
                .state('app.customer', {
                    url: '/customer',
                    template: '<div ui-view class="fade-in"></div>'
                })
                .state('app.customer.list', {
                    url: '/list/:type',
                    templateUrl: 'tpl/customer_list.html',
                    controller: 'CustomerListController as vm',
                    resolve: {
                        deps: [
                            'uiLoad',
                            function (uiLoad) {
                                return uiLoad.load([
                                    'js/services/sao/Employee.js',
                                    'js/controllers/modal.js',
                                    'js/controllers/modalemployee.js',
                                    'js/controllers/customerlist.js',
                                    'js/services/sao/OperateButtons.js',
                                    'js/factory/MessageWindow.js',
                                    'js/controllers/modalcustomer.js',
                                    'js/factory/Customer.js',
                                    'js/constants/API.js',
                                    'js/factory/ConfirmWindow.js',
                                    'js/controllers/modalsure.js',
                                    'js/factory/Adviser.js',
                                    'vendor/libs/moment-with-locales.min.js',
                                    'js/filters/fromNow.js',
                                    'js/filters/genderClass.js',
                                    'js/filters/frontendBackendDate.js'
                                ]);
                            }
                        ]
                    }
                })
                .state('app.customer.form', {
                    url: '/:id/form',
                    templateUrl: 'tpl/customer_form.html',
                    controller: 'CustomerFormController as vm',
                    resolve: {
                        deps: [
                            'uiLoad',
                            function (uiLoad) {
                                return uiLoad.load([
                                    'js/constants/API.js',
                                    'js/controllers/customerform.js',
                                    'js/controllers/modal.js',
                                    'js/factory/Customer.js',
                                    'js/factory/MessageWindow.js',
                                    'js/values/CustomerCareers.js',
                                    'js/values/CustomerFroms.js',
                                ]);
                            }
                        ]
                    }
                })
                .state('app.customer.detail', {
                    url: '/:id/detail',
                    templateUrl: 'tpl/customer_detail.html',
                    controller: 'CustomerDetailController as vm',
                    resolve: {
                        deps: [
                            'uiLoad',
                            function (uiLoad) {
                                return uiLoad.load([
                                    'js/controllers/customerdetail.js',
                                    'js/factory/MessageWindow.js',
                                    'js/controllers/modal.js',
                                    'js/factory/Customer.js',
                                    'js/constants/API.js',
                                    'js/filters/genderClass.js'
                                ]);
                            }
                        ]
                    }
                })
                .state('app.customer.import', {
                    url: '/import',
                    templateUrl: 'tpl/customer_import.html',
                    controller: 'CustomerImportController as vm',
                    resolve: {
                        deps: [
                            '$ocLazyLoad',
                            function ($ocLazyLoad) {
                                return $ocLazyLoad.load('angularFileUpload').then(function () {
                                    return $ocLazyLoad.load('js/controllers/customerimport.js');
                                });
                            }
                        ]
                    }
                })
                /* 文章 */
                .state('app.article', {
                    url: '/article',
                    template: '<div ui-view class="fade-in"></div>',
                    resolve: {
                        deps: [
                            'uiLoad',
                            function (uiLoad) {
                                return uiLoad.load(['js/services/sao/Article.js']);
                            }
                        ]
                    }
                })
                .state('app.article.list', {
                    url: '/list',
                    templateUrl: 'tpl/article_list.html',
                    controller: 'ArticleListController as vm',
                    resolve: {
                        deps: [
                            'uiLoad',
                            function (uiLoad) {
                                return uiLoad.load(['js/controllers/articlelist.js']);
                            }
                        ]
                    }
                })
                .state('app.article.form', {
                    url: '/:id/editor',
                    templateUrl: 'tpl/article_form.html',
                    controller: 'ArticleFormController as vm',
                    resolve: {
                        deps: [
                            '$ocLazyLoad',
                            function ($ocLazyLoad) {
                                return $ocLazyLoad.load('ng.ueditor').then(function () {
                                    return $ocLazyLoad.load(['js/controllers/modal.js', 'js/controllers/articleform.js']);
                                });
                            }
                        ]
                    }
                })
                .state('app.article.show', {
                    url: '/:id/show',
                    templateUrl: 'tpl/article_show.html',
                    controller: 'ArticleShowController as vm',
                    resolve: {
                        deps: [
                            'uiLoad',
                            function (uiLoad) {
                                return uiLoad.load('js/controllers/articleshow.js');
                            }
                        ]
                    }
                })
                .state('app.article.thumb', {
                    url: '/:id/thumb',
                    templateUrl: 'tpl/article_thumb.html',
                    controller: 'ArticleThumbController as vm',
                    resolve: {
                        deps: [
                            '$ocLazyLoad',
                            function ($ocLazyLoad) {
                                return $ocLazyLoad.load('ngImgCrop').then(function () {
                                    return $ocLazyLoad.load('js/controllers/articlethumb.js');
                                });
                            }
                        ]
                    }
                })
                /* 职位*/
                .state('app.job', {
                    url: '/job',
                    template: '<div ui-view class="fade-in"></div>',
                    resolve: {
                        deps: [
                            'uiLoad',
                            function (uiLoad) {
                                return uiLoad.load(['js/services/sao/Job.js']);
                            }
                        ]
                    }
                })
                .state('app.job.list', {
                    url: '/list',
                    templateUrl: 'tpl/job_list.html',
                    controller: 'JobListController as vm',
                    resolve: {
                        deps: [
                            'uiLoad',
                            function (uiLoad) {
                                return uiLoad.load(['js/controllers/joblist.js']);
                            }
                        ]
                    }
                })
                .state('app.job.form', {
                    url: '/:id/editor',
                    templateUrl: 'tpl/job_form.html',
                    controller: 'JobFormController as vm',
                    resolve: {
                        deps: [
                            '$ocLazyLoad',
                            function ($ocLazyLoad) {
                                return $ocLazyLoad.load('ng.ueditor').then(function () {
                                    return $ocLazyLoad.load([
                                        'js/controllers/jobform.js',
                                        'js/controllers/modal.js',
                                        'js/factory/Enterprise.js',
                                        'js/factory/MessageWindow.js',
                                        'js/values/Cities.js',
                                        'js/values/JobCategories.js',
                                        'js/values/JobStatus.js',
                                        'js/values/JobType.js'
                                    ]);
                                });
                            }
                        ]
                    }
                })
                .state('app.job.show', {
                    url: '/:id/show',
                    templateUrl: 'tpl/job_show.html',
                    controller: 'JobShowController as vm',
                    resolve: {
                        deps: [
                            'uiLoad',
                            function (uiLoad) {
                                return uiLoad.load('js/controllers/jobshow.js');
                            }
                        ]
                    }
                })
                .state('app.job.tag', {
                    url: '/:id/tag',
                    templateUrl: 'tpl/job_tag_add.html',
                    controller: 'JobTagAddController as vm',
                    resolve: {
                        deps: [
                            '$ocLazyLoad',
                            function ($ocLazyLoad) {
                                return $ocLazyLoad.load('ui.select').then(function () {
                                    return $ocLazyLoad.load(['js/controllers/jobtagselect.js', 'js/values/JobTag.js']);
                                });
                            }
                        ]
                    }
                })
                .state('app.job.cover', {
                    url: '/:id/cover',
                    templateUrl: 'tpl/job_cover.html',
                    controller: 'JobCoverController as vm',
                    resolve: {
                        deps: [
                            '$ocLazyLoad',
                            function ($ocLazyLoad) {
                                return $ocLazyLoad.load('ngImgCrop').then(function () {
                                    return $ocLazyLoad.load('js/controllers/imgcrop.js');
                                });
                            }
                        ]
                    }
                })
                .state('app.job.images', {
                    url: '/:id/images',
                    templateUrl: 'tpl/job_images.html',
                    resolve: {
                        deps: [
                            '$ocLazyLoad',
                            function ($ocLazyLoad) {
                                return $ocLazyLoad.load('angularFileUpload').then(function () {
                                    return $ocLazyLoad.load('js/controllers/job-images.js');
                                });
                            }
                        ]
                    }
                })
                .state('app.job.enterprise', {
                    url: '/enterprise',
                    templateUrl: 'tpl/enterprise_list.html',
                    controller: 'EnterpriseListController as vm',
                    resolve: {
                        deps: [
                            'uiLoad',
                            function (uiLoad) {
                                return uiLoad.load(['js/controllers/enterpriselist.js', 'js/factory/Enterprise.js']);
                            }
                        ]
                    }
                })
                .state('app.job.enterpriseform', {
                    url: '/:id/enterpriseform',
                    templateUrl: 'tpl/enterprise_form.html',
                    controller: 'EnterpriseFormController as vm',
                    resolve: {
                        deps: [
                            '$ocLazyLoad',
                            function ($ocLazyLoad) {
                                return $ocLazyLoad.load('ui.select').then(function(){
                                    return $ocLazyLoad.load(['js/controllers/enterpriseform.js',,'js/factory/MessageWindow.js','js/controllers/modal.js']);
                                });
                            }
                        ]
                    }
                })
                .state('app.job.recommend', {
                    url: '/recommend',
                    templateUrl: 'tpl/job_recommend.html',
                    controller: 'JobRecommendController as vm',
                    resolve: {
                        deps: [
                            'uiLoad',
                            function (uiLoad) {
                                return uiLoad.load(['js/controllers/jobrecommend.js']);
                            }
                        ]
                    }
                })
                .state('app.job.recommenddata', {
                    url: '/recommenddata/:id',
                    templateUrl: 'tpl/job_recommenddata.html',
                    controller: 'JobrecommendDataController as vm',
                    resolve: {
                        deps: [
                            'uiLoad',
                            function (uiLoad) {
                                return uiLoad.load(['js/factory/JobChoice.js', 'js/controllers/jobrecommenddata.js', 'js/controllers/modal.js', 'js/factory/MessageWindow.js']);
                            }
                        ]
                    }
                })
                .state('app.job.recommendlist', {
                    url: '/recommendlist/:id',
                    templateUrl: 'tpl/job_recommendlist.html',
                    controller: 'JobrecommendListController as vm',
                    resolve: {
                        deps: [
                            'uiLoad',
                            function (uiLoad) {
                                return uiLoad.load(['js/controllers/jobrecommendlist.js', 'js/controllers/modal.js', 'js/factory/MessageWindow.js']);
                            }
                        ]
                    }
                })
                /* 用户 */
                .state('app.user', {
                    url: '/user',
                    template: '<div ui-view class="fade-in"></div>',
                    resolve: {
                        deps: [
                            'uiLoad',
                            function (uiLoad) {
                                return uiLoad.load('js/services/sao/User.js');
                            }
                        ]
                    }
                })
                .state('app.user.list', {
                    url: '/list/:type',
                    templateUrl: 'tpl/user_list.html',
                    controller: 'UserListController as vm',
                    resolve: {
                        deps: [
                            '$ocLazyLoad',
                            function ($ocLazyLoad) {
                                return $ocLazyLoad.load('ngGrid').then(function(){
                                    return $ocLazyLoad.load([
                                        'js/constants/API.js',
                                        'js/factory/Adviser.js',
                                        'js/factory/CallinCustomer.js',
                                        'js/factory/MessageWindow.js',
                                        'js/controllers/modal.js',
                                        'js/controllers/modalemployee.js',
                                        'js/controllers/userlist.js',
                                        'js/services/sao/Employee.js',
                                        'js/services/sao/OperateButtons.js',
                                    ]);
                                });
                            }
                        ]
                    }
                })
                .state('app.user.invite', {
                    url: '/invite',
                    templateUrl: 'tpl/invite_list.html',
                    controller: 'InviteListController as vm',
                    resolve: {
                        deps: [
                            '$ocLazyLoad',
                            function ($ocLazyLoad) {
                                return $ocLazyLoad.load('ngGrid').then(function(){
                                    return $ocLazyLoad.load([
                                        'js/services/sao/Employee.js',
                                        'js/factory/MessageWindow.js',
                                        'js/controllers/modal.js',
                                        'js/controllers/modalemployee.js',
                                        'js/controllers/invitelist.js',
                                        'js/factory/Adviser.js'
                                    ]);
                                });
                            }
                        ]
                    }
                })
                .state('app.user.signup', {
                    url: '/signup/:type',
                    templateUrl: 'tpl/user_signup.html',
                    controller: 'UserSignupController as vm',
                    resolve: {
                        deps: [
                            '$ocLazyLoad',
                            function ($ocLazyLoad) {
                                return $ocLazyLoad.load('ngGrid').then(function(){
                                    return $ocLazyLoad.load([
                                        'js/constants/API.js',
                                        'js/controllers/modal.js',
                                        'js/controllers/modalemployee.js',
                                        'js/controllers/usersignup.js',
                                        'js/services/sao/Employee.js',
                                        'js/services/sao/OperateButtons.js',
                                        'js/factory/CallinCustomer.js',
                                        'js/factory/MessageWindow.js',
                                        'js/factory/Adviser.js'
                                    ]);
                                });
                            }
                        ]
                    }
                })
                /* 用户 */
                .state('app.callin', {
                    url: '/callin',
                    template: '<div ui-view class="fade-in"></div>',
                    resolve: {
                        deps: [
                            'uiLoad',
                            function (uiLoad) {
                                return uiLoad.load([
                                    'js/constants/API.js',
                                    'js/factory/Adviser.js',
                                    'js/factory/CallinCustomer.js',
                                    'js/factory/MessageWindow.js',
                                    'js/controllers/modal.js',
                                    'js/controllers/modalemployee.js',
                                    'js/services/sao/Employee.js',
                                ]);
                            }
                        ]
                    }
                })
                .state('app.callin.user', {
                    url: '/user/:type',
                    templateUrl: 'tpl/callin_user.html',
                    controller: 'CallinUserController as vm',
                    resolve: {
                        deps: [
                            '$ocLazyLoad',
                            function ($ocLazyLoad) {
                                return $ocLazyLoad.load('ngGrid').then(function(){
                                    return $ocLazyLoad.load([
                                        'js/controllers/callinuser.js'
                                    ]);
                                });
                            }
                        ]
                    }
                })
                .state('app.callin.applicant', {
                    url: '/applicant/:type',
                    templateUrl: 'tpl/callin_applicant.html',
                    controller: 'CallinApplicantController as vm',
                    resolve: {
                        deps: [
                            '$ocLazyLoad',
                            function ($ocLazyLoad) {
                                return $ocLazyLoad.load('ngGrid').then(function(){
                                    return $ocLazyLoad.load([
                                        'js/controllers/callinapplicant.js'
                                    ]);
                                });
                            }
                        ]
                    }
                })
                /*标签管理*/
                .state('app.tag', {
                    url: '/tag',
                    template: '<div ui-view class="fade-in-up"></div>'
                })
                .state('app.tag.list', {
                    url: '/list',
                    templateUrl: 'tpl/tag_list.html',
                    controller: 'TagListController as vm',
                    resolve: {
                        deps: [
                            '$ocLazyLoad',
                            function ($ocLazyLoad) {
                                return $ocLazyLoad.load('toaster').then(function () {
                                    return $ocLazyLoad.load([
                                        'js/controllers/taglist.js',
                                        'js/controllers/modaltag.js',
                                        'js/factory/CandidateTag.js',
                                        'js/factory/Contact.js',
                                        'js/factory/CandidateTask.js',
                                        'js/controllers/modalinput.js',
                                        'js/controllers/modalcall.js',
                                        'js/controllers/modalcustomer.js',
                                        'js/factory/Customer.js',
                                        'js/filters/genderClass.js',
                                        'js/constants/API.js',
                                        'js/factory/MessageWindow.js'
                                    ]);
                                });
                            }
                        ]
                    }
                })
                /*我的*/
                .state('app.my', {
                    url: '/my',
                    template: '<div ui-view class="fade-in-up"></div>'
                })
                .state('app.my.profile', {
                    url: '/profile',
                    templateUrl: 'tpl/page_profile.html',
                    controller: 'PageProfileController as vm',
                    resolve: {
                        deps: [
                            'uiLoad',
                            function (uiLoad) {
                                return uiLoad.load( ['js/controllers/pageprofile.js'] );
                            }
                        ]
                    }
                })
                .state('app.my.message', {
                    url: '/message',
                    templateUrl: 'tpl/message.html',
                    controller: 'MessageController as vm',
                    resolve: {
                        deps: [
                            '$ocLazyLoad',
                            function ($ocLazyLoad) {
                                return $ocLazyLoad.load('ui.select').then(function(){
                                    return $ocLazyLoad.load(['js/controllers/modal.js','js/controllers/message.js']);
                                });
                            }
                        ]
                    }
                })
                .state('app.my.admin', {
                    url: '/:id/admin',
                    templateUrl: 'tpl/my_admin.html',
                    controller: 'MyAdminController as vm',
                    resolve: {
                        deps: [
                            'uiLoad',
                            function (uiLoad) {
                                return uiLoad.load(['js/controllers/modal.js','js/services/sao/Admin.js','js/controllers/myadmin.js']);
                            }
                        ]
                    }
                })
                .state('app.my.organization', {
                    url: '/organization',
                    templateUrl: 'tpl/my_organization.html',
                    controller: 'MyOrganizationController as vm',
                    resolve: {
                        deps: [
                            'uiLoad',
                            function (uiLoad) {
                                return uiLoad.load( ['js/controllers/myorganization.js'] );
                            }
                        ]
                    }
                })
                .state('app.my.task', {
                    url: '/task',
                    templateUrl: 'tpl/task_list.html',
                    controller: 'TaskListController as vm',
                    resolve: {
                        deps: [
                            '$ocLazyLoad',
                            'uiLoad',
                            function ($ocLazyLoad, uiLoad) {
                                return uiLoad.load([
                                    'vendor/jquery/fullcalendar/fullcalendar.css',
                                    'vendor/jquery/fullcalendar/theme.css',
                                    'vendor/jquery/jquery-ui-1.10.3.custom.min.js',
                                    'vendor/libs/moment.min.js',
                                    'vendor/jquery/fullcalendar/fullcalendar.min.js',
                                    'js/controllers/calendartask.js'
                                ]).then(function(){
                                    return $ocLazyLoad.load('ui.calendar');
                                });
                            }
                        ]
                    }
                })
                .state('app.my.tasklist', {
                    url: '/tasklist',
                    templateUrl: 'tpl/task_table.html',
                    controller: 'TaskTableController as vm',
                    resolve: {
                        deps: [
                            '$ocLazyLoad',
                            'uiLoad',
                            function ($ocLazyLoad, uiLoad) {
                                return $ocLazyLoad.load('toaster').then(function(){
                                    return uiLoad.load([
                                        'js/controllers/modal.js',
                                        'js/controllers/tasktable.js',
                                        'js/controllers/modalcall.js',
                                        'js/factory/Contact.js',
                                        'js/factory/ConfirmWindow.js',
                                        'js/filters/genderClass.js',
                                        'js/controllers/modalsure.js'
                                    ]);
                                });
                            }
                        ]
                    }
                })
                .state('app.my.signup', {
                    url: '/signup/:type',
                    templateUrl: 'tpl/my_signup.html',
                    controller: 'MySignupController as vm',
                    resolve: {
                        deps: [
                            '$ocLazyLoad',
                            function ($ocLazyLoad) {
                                return $ocLazyLoad.load('ngGrid').then(function(){
                                    return $ocLazyLoad.load([
                                        'js/constants/API.js',
                                        'js/controllers/modal.js',
                                        'js/controllers/mysignup.js',
                                        'js/factory/CallinCustomer.js',
                                        'js/factory/MessageWindow.js'
                                    ]);
                                });
                            }
                        ]
                    }
                })
                /*后台用户*/
                .state('app.admin', {
                    url: '/admin',
                    template: '<div ui-view class="fade-in"></div>',
                    resolve: {
                        deps: [
                            'uiLoad',
                            function (uiLoad){
                                return uiLoad.load('js/services/sao/Admin.js');
                            }
                        ]
                    }
                })
                .state('app.admin.list', {
                    url: '/list',
                    templateUrl: 'tpl/admin_list.html',
                    controller: 'AdminListController as vm',
                    resolve: {
                        deps: [
                            'uiLoad',
                            function (uiLoad) {
                                return uiLoad.load('js/controllers/adminlist.js');
                            }
                        ]
                    }
                })
                .state('app.admin.form', {
                    url: '/:id/form',
                    templateUrl: 'tpl/admin_form.html',
                    controller: 'AdminFormController as vm',
                    resolve: {
                        deps: [
                            'uiLoad',
                            function (uiLoad) {
                                return uiLoad.load(['js/controllers/modal.js', 'js/controllers/adminform.js']);
                            }
                        ]
                    }
                })
                .state('app.admin.role', {
                    url: '/:id/role',
                    templateUrl: 'tpl/admin_role.html',
                    controller: 'AdminRoleController as vm',
                    resolve: {
                        deps: [
                            '$ocLazyLoad',
                            function ($ocLazyLoad) {
                                return $ocLazyLoad.load('ui.select').then(function(){
                                    return $ocLazyLoad.load(['js/controllers/adminrole.js','js/services/sao/Role.js']);
                                });
                            }
                        ]
                    }
                })
                .state('app.admin.setting', {
                    url: '/:id/setting',
                    templateUrl: 'tpl/admin_setting.html',
                    controller: 'AdminSettingController as vm',
                    resolve: {
                        deps: [
                            'uiLoad',
                            function (uiLoad) {
                                return uiLoad.load(['js/controllers/adminsetting.js', 'js/factory/MessageWindow.js', 'js/controllers/modal.js']);
                            }
                        ]
                    }
                })
                /*后台权限*/
                .state('app.role', {
                    url: '/role',
                    template: '<div ui-view class="fade-in"></div>',
                    resolve: {
                        deps: [
                            'uiLoad',
                            function (uiLoad) {
                                return uiLoad.load('js/services/sao/Role.js');
                            }
                        ]
                    }
                })
                .state('app.role.list', {
                    url: '/list',
                    templateUrl: 'tpl/role_list.html',
                    controller: 'RoleListController as vm',
                    resolve: {
                        deps: [
                            'uiLoad',
                            function (uiLoad) {
                                return uiLoad.load('js/controllers/rolelist.js');
                            }
                        ]
                    }
                })
                .state('app.role.form', {
                    url: '/:id/form',
                    templateUrl: 'tpl/role_form.html',
                    controller: 'RoleFormController as vm',
                    resolve: {
                        deps: [
                            'uiLoad',
                            function (uiLoad) {
                                return uiLoad.load(['js/controllers/modal.js','js/controllers/roleform.js']);
                            }
                        ]
                    }
                })
                .state('app.role.privileges', {
                   url: '/:id/privileges',
                   templateUrl: 'tpl/role_privileges.html',
                   controller: 'RolePrivilegesController as vm',
                   resolve: {
                       deps: [
                           'uiLoad',
                           function (uiLoad) {
                            return uiLoad.load(['js/controllers/modal.js','js/controllers/roleprivileges.js','js/factory/MessageWindow.js']);
                            }
                       ]
                    }
                })
                /*组织架构*/
                .state('app.organization', {
                    url: '/organization',
                    template: '<div ui-view class="fade-in"></div>'
                })
                .state('app.organization.tree', {
                    url: '/tree',
                    templateUrl: 'tpl/organization_tree.html',
                    controller: 'OrganizationTreeController as vm',
                    resolve: {
                        deps: [
                            '$ocLazyLoad',
                            function ($ocLazyLoad) {
                                return $ocLazyLoad.load('angularBootstrapNavTree').then(function(){
                                    return $ocLazyLoad.load([
                                        'js/services/sao/Organization.js',
                                        'js/controllers/organizationtree.js',
                                        'js/factory/MessageWindow.js',
                                        'js/controllers/modal.js'
                                    ]);
                                });
                            }
                        ]
                    }
                })
                .state('app.organization.position', {
                    url: '/position',
                    templateUrl: 'tpl/position_list.html',
                    controller: 'PositionListController as vm',
                    resolve: {
                        deps: [
                            'uiLoad',
                            function (uiLoad) {
                                return uiLoad.load(['js/services/sao/Position.js','js/controllers/positionlist.js']);
                            }
                        ]
                    }
                })
                .state('app.organization.positionform', {
                    url: '/:id/positionform',
                    templateUrl: 'tpl/position_form.html',
                    controller: 'PositionFormController as vm',
                    resolve: {
                        deps: [
                            'uiLoad',
                            function (uiLoad) {
                                return uiLoad.load(['js/services/sao/Position.js','js/controllers/modal.js','js/controllers/positionform.js']);
                            }
                        ]
                    }
                })
                .state('app.organization.employee', {
                    url: '/employee',
                    templateUrl: 'tpl/employee_list.html',
                    controller: 'EmployeeListController as vm',
                    resolve: {
                        deps: [
                            'uiLoad',
                            function (uiLoad) {
                                return uiLoad.load(['js/services/sao/Employee.js','js/controllers/employeelist.js']);
                            }
                        ]
                    }
                })
                .state('app.organization.employeeform', {
                    url: '/:id/employeeform',
                    templateUrl: 'tpl/employee_form.html',
                    controller: 'EmployeeFormController as vm',
                    resolve: {
                        deps: [
                            'uiLoad',
                            function (uiLoad) {
                                return uiLoad.load(['js/services/sao/Employee.js','js/controllers/modal.js','js/controllers/employeeform.js']);
                            }
                        ]
                    }
                })
                .state('app.organization.employeeuser', {
                    url: '/:id/employeeuser',
                    templateUrl: 'tpl/employee_user.html',
                    controller: 'EmployeeUserController as vm',
                    resolve: {
                        deps: [
                            '$ocLazyLoad',
                            function ($ocLazyLoad) {
                                return $ocLazyLoad.load('ui.select').then(function(){
                                    return $ocLazyLoad.load(['js/services/sao/Employee.js','js/controllers/employeeuser.js','js/services/sao/Admin.js']);
                                });
                            }
                        ]
                    }
                })
                .state('app.organization.employeeorg', {
                    url: '/:id/employeeorg',
                    templateUrl: 'tpl/employee_org.html',
                    controller: 'EmployeeOrgController as vm',
                    resolve: {
                        deps: [
                            '$ocLazyLoad',
                            function ($ocLazyLoad) {
                                return $ocLazyLoad.load('ui.select').then(function(){
                                    return $ocLazyLoad.load(['js/services/sao/Employee.js','js/controllers/employeeorg.js','js/services/sao/Organization.js']);
                                });
                            }
                        ]
                    }
                })
                .state('app.organization.employeepos', {
                    url: '/:id/employeepos',
                    templateUrl: 'tpl/employee_pos.html',
                    controller: 'EmployeePosController as vm',
                    resolve: {
                        deps: [
                            '$ocLazyLoad',
                            function ($ocLazyLoad) {
                                return $ocLazyLoad.load('ui.select').then(function(){
                                    return $ocLazyLoad.load(['js/services/sao/Employee.js','js/controllers/employeepos.js','js/services/sao/Position.js']);
                                });
                            }
                        ]
                    }
                })
                .state('app.organization.employeeavatar', {
                    url: '/:id/employeeavatar',
                    templateUrl: 'tpl/employee_avatar.html',
                    controller: 'EmployeeAvatarController as vm',
                    resolve: {
                        deps: [
                            '$ocLazyLoad',
                            function ($ocLazyLoad) {
                                return $ocLazyLoad.load('ngImgCrop').then(function(){
                                    return $ocLazyLoad.load(['js/services/sao/Employee.js', 'js/controllers/employeeavatar.js']);
                                });
                            }
                        ]
                    }
                })
                .state('app.organization.employeestatistics', {
                    url: '/:id/employeestatistics',
                    templateUrl: 'tpl/employee_statistics.html',
                    controller: 'EmployeeStatisticsController as vm',
                    resolve: {
                        deps: [
                            'uiLoad',
                            function (uiLoad) {
                                return uiLoad.load(['js/services/sao/Employee.js','js/controllers/modal.js','js/controllers/employeestatistics.js']);
                            }
                        ]
                    }
                })
                /*微信*/
                .state('app.wechat', {
                    url: '/wechat',
                    template: '<div ui-view class="fade-in"></div>'
                })
                .state('app.wechat.list', {
                    url: '/list',
                    templateUrl: 'tpl/wechat_user_list.html',
                    controller: 'WechatUserListController as vm',
                    resolve: {
                        deps: [
                            '$ocLazyLoad',
                            function ($ocLazyLoad) {
                                return $ocLazyLoad.load('ngGrid').then(function(){
                                    return $ocLazyLoad.load('js/controllers/wechatuserlist.js');
                                });
                            }
                        ]
                    }
                })
                .state('app.wechat.log', {
                    url: '/log',
                    templateUrl: 'tpl/wechat_log.html',
                    controller: 'WechatLogController as vm',
                    resolve: {
                        deps: [
                            'uiLoad',
                            function (uiLoad) {
                                return uiLoad.load('js/controllers/wechatlog.js');
                            }
                        ]
                    }
                })
                .state('app.recruit', {
                    url: '/recruit',
                    template: '<div ui-view class="fade-in"></div>'
                })
                .state('app.recruit.list', {
                    url: '/list',
                    templateUrl: 'tpl/recruit_list.html',
                    controller: 'RecruitListController as vm',
                    resolve: {
                        deps: [
                            '$ocLazyLoad',
                            function ($ocLazyLoad) {
                               return $ocLazyLoad.load([
                                   'js/controllers/modal.js',
                                   'js/controllers/modalsure.js',
                                   'js/controllers/recruitlist.js',
                                   'js/factory/ConfirmWindow.js',
                                   'js/factory/MessageWindow.js',
                                   'js/services/sao/Recruit.js'
                               ]);
                            }
                        ]
                    }
                })
                .state('app.recruit.form', {
                    url: '/:id/form',
                    templateUrl: 'tpl/recruit_form.html',
                    controller: 'RecruitFormController as vm',
                    resolve: {
                        deps: [
                            '$ocLazyLoad',
                            function ($ocLazyLoad) {
                               return $ocLazyLoad.load('ui.select').then(function(){
                                    return $ocLazyLoad.load(['js/controllers/recruitform.js', 'js/factory/Enterprise.js', 'js/values/Cities.js', 'js/factory/MessageWindow.js', 'js/controllers/modal.js']);
                                })
                            }
                        ]
                    }
                })
                .state('app.labourservice', {
                    url: '/labourservice',
                    template: '<div ui-view class="fade-in"></div>'
                })
                .state('app.labourservice.list', {
                    url: '/list',
                    templateUrl: 'tpl/labourservice_list.html',
                    controller: 'LabourservicelistController as vm',
                    resolve: {
                        deps: [
                            '$ocLazyLoad',
                            function ($ocLazyLoad) {
                               return $ocLazyLoad.load(['js/controllers/labourservicelist.js']);
                            }
                        ]
                    }
                })
                .state('app.labourservice.form', {
                    url:'/:id/form',
                    templateUrl: 'tpl/labourservice_form.html',
                    controller: 'LabourserviceformController as vm',
                    resolve: {
                        deps: [
                            '$ocLazyLoad',
                            function ($ocLazyLoad) {
                               return $ocLazyLoad.load('ui.select').then(function(){
                                    return $ocLazyLoad.load(['js/controllers/labourserviceform.js', 'js/factory/MessageWindow.js', 'js/controllers/modal.js']);
                                })
                            }
                        ]
                    }
                })
                .state('app.poster', {
                    url: '/poster',
                    template: '<div ui-view class="fade-in-down"></div>'
                })
                .state('app.poster.space', {
                    url: '/space',
                    templateUrl: 'tpl/poster_space.html',
                    controller: 'PosterSpaceController as vm',
                    resolve: {
                        deps: [
                            'uiLoad',
                            function (uiLoad) {
                                return uiLoad.load(['js/controllers/posterspace.js']);
                            }
                        ]
                    }
                })
                .state('app.poster.list', {
                    url: '/list/:space',
                    templateUrl: 'tpl/poster_list.html',
                    controller: 'PosterListController as vm',
                    resolve: {
                        deps: [
                            'uiLoad',
                            function (uiLoad) {
                                return uiLoad.load(['js/controllers/posterlist.js']);
                            }
                        ]
                    }
                })
                .state('app.poster.fileupload', {
                    url: '/fileupload/:space',
                    templateUrl: 'tpl/poster_fileupload.html',
                    controller: 'FileUploadController as vm',
                    resolve: {
                        deps: [
                            '$ocLazyLoad',
                            function ($ocLazyLoad) {
                                return $ocLazyLoad.load('ngImgCrop').then(function(){
                                    return $ocLazyLoad.load('js/controllers/file-upload.js');
                                });
                            }
                        ]
                    }
                })
                .state('app.poster.fileuploadupdate', {
                    url: '/:id/fileuploadupdate',
                    templateUrl: 'tpl/poster_fileuploadupdate.html',
                    controller: 'FileUploadupdateController as vm',
                    resolve: {
                        deps: [
                            '$ocLazyLoad',
                            function ($ocLazyLoad) {
                                return $ocLazyLoad.load('ngImgCrop').then(function(){
                                    return $ocLazyLoad.load('js/controllers/file-uploadupdate.js');
                                });
                            }
                        ]
                    }
                })
                /*登录*/
                .state('access', {
                    url: '/access',
                    template: '<div ui-view class="fade-in-right-big smooth"></div>'
                })
                .state('access.signin', {
                    url: '/signin',
                    templateUrl: 'tpl/page_signin.html',
                    controller: 'SigninFormController as vm',
                    resolve: {
                        deps: [
                            'uiLoad',
                            function (uiLoad) {
                                return uiLoad.load( ['js/controllers/signin.js'] );
                            }
                        ]
                    }
                })
                .state('access.signout', {
                    url: '/signout',
                    template: '<div ui-view class="fade-in-up"></div>'
                })
                /*控制面板*/
                .state('app.ctrlpanel', {
                    url: '/ctrlpanel',
                    template: '<div ui-view class="fade-in-up"></div>'
                })
                .state('app.ctrlpanel.wechat', {
                    url: '/wechat',
                    templateUrl: 'tpl/panel_wechat.html',
                    resolve: {
                        deps: [
                            'uiLoad',
                            function (uiLoad) {
                                return uiLoad.load([
                                    'js/controllers/modal.js',
                                    'js/controllers/modalsure.js',
                                    'js/factory/MessageWindow.js',
                                    'js/factory/ConfirmWindow.js',
                                    'js/controllers/panelwechat.js'
                                ]);
                            }
                        ]
                    }
                })
                .state('app.ctrlpanel.admin', {
                    url: '/admin',
                    templateUrl: 'tpl/panel_admin.html',
                    resolve: {
                        deps: [
                            'uiLoad',
                            function (uiLoad) {
                                return uiLoad.load([
                                    'js/controllers/modal.js',
                                    'js/controllers/modalsure.js',
                                    'js/controllers/paneladmin.js',
                                    'js/factory/MessageWindow.js',
                                    'js/factory/ConfirmWindow.js'
                                ]);
                            }
                        ]
                    }
                })
                /*系统补丁*/
                .state('app.patch', {
                    url: '/patch',
                    templateUrl: 'tpl/patch.html',
                    resolve: {
                        deps: [
                            'uiLoad',
                            function (uiLoad) {
                                return uiLoad.load([
                                    'js/controllers/modal.js',
                                    'js/controllers/modalsure.js',
                                    'js/controllers/patch.js',
                                    'js/factory/MessageWindow.js',
                                    'js/factory/ConfirmWindow.js'
                                ]);
                            }
                        ]
                    }
                })
                /*回收站*/
                .state('app.recycle', {
                    url: '/recycle',
                    template: '<div ui-view class="fade-in"></div>'
                })
                .state('app.recycle.bin', {
                    url: '/bin',
                    templateUrl: 'tpl/recycle_bin.html',
                    controller: 'RecycleBinController as vm',
                    resolve: {
                        deps: [
                            '$ocLazyLoad',
                            function ($ocLazyLoad) {
                                return $ocLazyLoad.load('ngGrid').then(function () {
                                    return $ocLazyLoad.load([
                                        'js/controllers/recyclebin.js',
                                        'js/factory/MessageWindow.js',
                                        'js/factory/ConfirmWindow.js',
                                        'js/controllers/modal.js',
                                        'js/controllers/modalsure.js',
                                        'js/factory/RecycleBin.js'
                                    ]);
                                });
                            }
                        ]
                    }
                })
                /*帮助*/
                .state('app.help', {
                    url: '/help',
                    template: '<div ui-view class="fade-in-up"></div>'
                })
                /*系统规则*/
                .state('app.help.docs', {
                    url: '/docs',
                    templateUrl: 'tpl/docs.html'
                })
                /*用户使用手册*/
                .state('app.help.manual', {
                    url: '/manual',
                    templateUrl: 'tpl/manual.html'
                })
                /*经理使用手册*/
                .state('app.help.advancemanual', {
                  url: '/manualadvance',
                  templateUrl: 'tpl/manual_advance.html'
                })
                /*客户冲突制度*/
                .state('app.help.customerrules', {
                  url: '/customerrules',
                  templateUrl: 'tpl/rules_customer.html'
                })
                /*日历*/
                .state('app.calendar', {
                    url: '/calendar',
                    templateUrl: 'tpl/app_calendar.html',
                    controller: 'CalendarTaskController as vm',
                    // use resolve to load other dependences
                    resolve: {
                        deps: [
                            '$ocLazyLoad',
                            'uiLoad',
                            function ($ocLazyLoad, uiLoad) {
                                return uiLoad.load([
                                    'vendor/jquery/fullcalendar/fullcalendar.css',
                                    'vendor/jquery/fullcalendar/theme.css',
                                    'vendor/jquery/jquery-ui-1.10.3.custom.min.js',
                                    'vendor/libs/moment.min.js',
                                    'vendor/jquery/fullcalendar/fullcalendar.min.js',
                                    'js/controllers/calendar.js'
                                ]).then(function () {
                                    return $ocLazyLoad.load('ui.calendar');
                                });
                            }
                        ]
                    }
                })
                /*城市*/
                .state('app.city', {
                    url: '/city',
                    template: '<div ui-view class="fade-in"></div>'
                });
        }
    ]);