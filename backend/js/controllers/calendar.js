/**
 * 我的任务日历 - 0.1.3
 */
app.controller('CalendarTaskController', ['$scope', '$http', function($scope, $http) {
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
			  angular.forEach(res.data, function(task){
				var item = {id:task.id, title:task.title, start: task.start_time, end: task.end_time, className: ['b-l b-2x b-info'], location:task.location, info:task.info};    
				$scope.events.push(item);
			  });
		  }			  
		});
    };
	$scope.loadEvents();
    /*$scope.events = [
      {title:'All Day Event', start: new Date(y, m, 1), className: ['b-l b-2x b-info'], location:'New York', info:'This a all day event that will start from 9:00 am to 9:00 pm, have fun!'},
      {title:'Dance class', start: new Date(y, m, 3), end: new Date(y, m, 4, 9, 30), allDay: false, className: ['b-l b-2x b-danger'], location:'London', info:'Two days dance training class.'},
      {title:'Game racing', start: new Date(y, m, 6, 16, 0), className: ['b-l b-2x b-info'], location:'Hongkong', info:'The most big racing of this year.'},
      {title:'Soccer', start: new Date(y, m, 8, 15, 0), className: ['b-l b-2x b-info'], location:'Rio', info:'Do not forget to watch.'},
      {title:'Family', start: new Date(y, m, 9, 19, 30), end: new Date(y, m, 9, 20, 30), className: ['b-l b-2x b-success'], info:'Family party'},
      {title:'Long Event', start: new Date(y, m, d - 5), end: new Date(y, m, d - 2), className: ['bg-success bg'], location:'HD City', info:'It is a long long event'},
      {title:'Play game', start: new Date(y, m, d - 1, 16, 0), className: ['b-l b-2x b-info'], location:'Tokyo', info:'Tokyo Game Racing'},
      {title:'Birthday Party', start: new Date(y, m, d + 1, 19, 0), end: new Date(y, m, d + 1, 22, 30), allDay: false, className: ['b-l b-2x b-primary'], location:'New York', info:'Party all day'},
      {title:'Repeating Event', start: new Date(y, m, d + 4, 16, 0), alDay: false, className: ['b-l b-2x b-warning'], location:'Home Town', info:'Repeat every day'},      
      {title:'Click for Google', start: new Date(y, m, 28), end: new Date(y, m, 29), url: 'http://google.com/', className: ['b-l b-2x b-primary']},
      {title:'Feed cat', start: new Date(y, m+1, 6, 18, 0), className: ['b-l b-2x b-info']}
    ];*/

    /* alert on dayClick */
    $scope.precision = 400;
    $scope.lastClickTime = 0;
	$scope.newEvent = {'title':'New Task'};
    $scope.alertOnEventClick = function( date, jsEvent, view ){
      var time = new Date().getTime();
      if(time - $scope.lastClickTime <= $scope.precision){
          /*添加任务*/
		  $scope.addEvent(date);
      }
      $scope.lastClickTime = time;
    };
    /* alert on Drop */
    $scope.alertOnDrop = function(event, delta, revertFunc, jsEvent, ui, view){
       //$scope.alertMessage = ('Event Droped to make dayDelta ' + delta);
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
        dayClick: $scope.alertOnEventClick,
        eventDrop: $scope.alertOnDrop,
        eventResize: $scope.alertOnResize,
        eventMouseover: $scope.alertOnMouseOver
      }
    };
    
    /* add custom event*/
    $scope.addEvent = function(start) {
	  if (start==undefined)	start = date;
	  $http({
		  method:'post',
		  url:'/api/index/index/addTask',
		  data:{title:$scope.newEvent.title, start_time:start}
	  }).success(function(req){
		  $scope.events.push({
			id: req.id,
			title: $scope.newEvent.title,
			start: start,
			className: ['b-l b-2x b-info']
		  });
	  });
    };
	
	/*edit event*/
	$scope.editEvent = function() {
		$scope.editing = true;
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
      $scope.events.splice(index,1);
    };

    /* Change View */
    $scope.changeView = function(view, calendar) {
      $('.calendar').fullCalendar('changeView', view);
    };

    $scope.today = function(calendar) {
      $('.calendar').fullCalendar('today');
    };

    /* event sources array*/
    $scope.eventSources = [$scope.events];
}]);
/* EOF */