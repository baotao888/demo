$(function(){
  ajaxBind();//微信绑定验证
  ajaxMarket();//加载市场推广码
  switchShowCity();//加载城市
  //切换城市
  $("#switchCity a").on('click', function(){
	switchCity($(this).html());
	$(".top-city-list").toggle();
  });
  $("#topCityList").on('click', function(){
	 $(".top-city-list").toggle();  
  });
});