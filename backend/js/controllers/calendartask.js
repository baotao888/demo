/**
 * 拨打计划
 */
app.controller('TaskListController', ['$scope', '$http', function($scope, $http) {
    var date = new Date();
    var d = date.getDate();
    var m = date.getMonth();
    var y = date.getFullYear();

    /* event source that contains custom events on the scope */
    $scope.events = [];
    $scope.loadEvents = function() {
        $http.get('/api/index/index/task', {
            params: {
                size: 300
            }
        }).then(function(res){
            if (res.data.length > 0){
                var key = 0;
                angular.forEach(res.data, function(task){
                    var item = {id:task.id, title:task.title, start: task.start_time, end: task.end_time, className: ['b-l b-2x b-info'], location:task.location, info:task.info, key:key};
                    $scope.events.push(item);
                    key++;
                });
            }
        });
    };
    $scope.loadEvents();

    /* alert on dayClick */
    $scope.precision = 400;
    $scope.lastClickTime = 0;

    /* alert on Drop */
    $scope.alertOnDrop = function(event, delta, revertFunc, jsEvent, ui, view){
        $scope.event = event;
        $scope.editItem();
    };
    /* alert on Resize */
    $scope.alertOnResize = function(event, delta, revertFunc, jsEvent, ui, view){
        $scope.event = event;
        $scope.editItem();
    };

    $scope.overlay = $('.fc-overlay');
    $scope.editing = false;
    $scope.alertOnMouseOver = function( event, jsEvent, view ){
        $scope.event = event;
        $scope.overlay.removeClass('left right').find('.arrow').removeClass('left right top pull-up');
        var wrap = $(jsEvent.target).closest('.fc-event');
        var cal = wrap.closest('.calendar');
        var left = wrap.offset().left - cal.offset().left;
        var right = cal.width() - (wrap.offset().left - cal.offset().left + wrap.width());
        if( right > $scope.overlay.width() ) {
            $scope.overlay.addClass('left').find('.arrow').addClass('left pull-up')
        }else if ( left > $scope.overlay.width() ) {
            $scope.overlay.addClass('right').find('.arrow').addClass('right pull-up');
        }else{
            $scope.overlay.find('.arrow').addClass('top');
        }
        (wrap.find('.fc-overlay').length == 0) && wrap.append( $scope.overlay );
    }

    /* config object */
    $scope.uiConfig = {
        calendar:{
            height: 700,
            editable: true,
            header:{
                left: 'prev',
                center: 'title',
                right: 'next'
            },
            eventDrop: $scope.alertOnDrop,
            eventResize: $scope.alertOnResize,
            eventMouseover: $scope.alertOnMouseOver
        }
    };

    $scope.editItem = function() {
        var data = {};
        data.id = $scope.event.id;
        data.title = $scope.event.title;
        data.start_time = $scope.event.start;
        data.end_time = $scope.event.end;
        data.location = $scope.event.location;
        data.info = $scope.event.info;
        $http({
            method:'post',
            url:'/api/index/index/updateTask',
            data:data
        }).success(function(req){
            $scope.editing = false;
        });
    }


    /* remove event */
    $scope.remove = function(index) {
        var id = $scope.events[index].id;
        $http({
            method:'post',
            url:'/api/index/index/finishTask',
            data:{id:id}
        }).success(function(req){
            $scope.editing = false;
        });
        $scope.events.splice(index,1);
    };

    /* Change View */
    $scope.changeView = function(view, calendar) {
        $('.calendar').fullCalendar('changeView', view);
    };

    /* event sources array*/
    $scope.eventSources = [$scope.events];
}]);
/* EOF */