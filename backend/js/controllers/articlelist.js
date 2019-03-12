'use strict';

/* Controllers */
// job list controller
app.controller('ArticleListController', function(Article) {
    var vm = this;
    vm.articles = {};
    Article.get(function(response){
        vm.articles = response;
    });
})
;