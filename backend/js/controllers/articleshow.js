app.controller('ArticleShowController', function($scope, $stateParams, Article, $state) {
    var vm = this;
    Article.get({id: $stateParams.id},function(response){
        vm.article = response;
        console.log(angular.fromJson(response))
        console.log(response);
    });
});