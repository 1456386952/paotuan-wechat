<?php use common\component\CustomHelper;?>
<!doctype html>
<html lang="en" class="m js cssanimations">
<head>
  <meta charset="UTF-8">
  <meta name="viewport"
        content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
  <title>跑步去</title>
  <link rel="stylesheet" href="/css/amazeui.min.css"/>
  <style>
    html,
    body,
    .page {
      height: 100%;
    }
    
    .am-list-item-hd{
overflow: hidden; 
text-overflow: ellipsis; 
white-space: nowrap;
}

.am-list-thumb{
  width:84px
}

.am-list-thumb a img{
  height:60px;
  min-height:60px;
}

    #wrapper {
      position: absolute;
      top: 70px;
      bottom: 0;
      overflow: hidden;
      margin: 0;
      width: 100%;
      padding: 0 8px;
    }


    .pull-action {
      text-align: center;
      height: 45px;
      line-height: 45px;
      color: #999;
    }

    .pull-action .am-icon-spin {
      display: none;
    }

    .pull-action.loading .am-icon-spin {
      display: block;
    }

    .pull-action.loading .pull-label {
      display: none;
    }
  </style>
</head>
<body>
<?php 
//     if(is_null($user)||!$user||$user->status==1){
//     	echo "登录失败,请稍候重试.";
//     	exit;
//     }else{
//     	if(empty($operation)){
//     		$operation="bm";
//     	}
//     }

?>
<div >
<form class="am-form" style="margin-top:10px" id="searchForm">
  <div class="am-form-group am-form-warning  am-form-icon am-form-feedback am-center" style="width: 80%;">
    <input type="search" class="am-form-field am-round" placeholder="输入赛事名、地点" id="search" name="search">
    <span class="am-icon-search"></span>
  </div>
</form>

  <div id="wrapper" data-am-widget="list_news"
       class="am-list-news am-list-news-default">
    <div class="am-list-news-bd">
      <div class="pull-action" id="pull-down">
        <span class="am-icon-arrow-down pull-label"
              id="pull-down-label"> 下拉刷新</span>
        <span class="am-icon-spinner am-icon-spin"></span>
      </div>
     <ul class="am-list m-widget-list" id="data-list" style="transition-timing-function: cubic-bezier(0.1, 0.57, 0.1, 1); -webkit-transition-timing-function: cubic-bezier(0.1, 0.57, 0.1, 1); transition-duration: 0ms; -webkit-transition-duration: 0ms; transform: translate(0px, 0px) translateZ(0px);">
      <?php if(count($data)==0):?>
            <li class="am-g am-g-fixed"">
              对不起，没有找到任何赛事信息.
      </li>
        <?php else:?>
        <?php foreach ($data as $act):?>
      <!--缩略图在标题左边-->
      <li class="am-g am-list-item-desced am-list-item-thumbed am-list-item-thumb-left am-g-fixed">
        <div class="am-u-sm-4 am-list-thumb">
        <a>
            <img src="<?=CustomHelper::CreateImageUrl($act->act_logo, 'small80')?>">
            </a>
        </div>
        <div class=" am-u-sm-8 am-list-main">
          <h3 class="am-list-item-hd" style="">
            <a href="" class=""><?=$act->act_name?></a>
          </h3>
          <div class="am-list-item-text">
           <span class="am-icon-map-marker"></span> 
           <?php if(isset($act->country)&&!empty($act->country->country_logo)):?>
           <img src="<?=CustomHelper::CreateImageUrl($act->country->country_logo, 'img_logo')?>" style="width:30px"/>
          <?php endif;?>
          <?php if(isset($act->city)):?>
           <?=$act->city->chn_name?>
          <?php endif;?>
          <span class="am-icon-calendar" style="margin-left: 4px"></span>  <?=date('Y.m.d', strtotime($act->act_day))?>
          </div>
          </div>
      </li>
     <?php endforeach;?>
     <?php endif;?>
      </ul>
      <div class="pull-action" id="pull-up">
        <span class="am-icon-arrow-down pull-label"
              id="pull-up-label"> 上拉加载更多</span>
        <span class="am-icon-spinner am-icon-spin"></span>
      </div>
    </div>
  </div>
</div>
<script src="/js/jquery.min.js"></script>
<script src="/js/amazeui.min.js"></script>
<script>
  (function($) {
    var EventsList = function(element, options) {
      var $main = $('#wrapper');
      var $list = $main.find('#data-list');
      var $pullDown = $main.find('#pull-down');
      var $pullDownLabel = $main.find('#pull-down-label');
      var $pullUp = $main.find('#pull-up');
      var topOffset = -$pullDown.outerHeight();
      var pages=0;
      var total = options.total;
      var pageSize = options.pageSize;
      //this.compiler = Handlebars.compile($('#tpi-list-item').html());
      var pageIndex=1;
      this.renderList = function(start, type) {
        var _this = this;
        var $el = $pullDown;

        if (type === 'load') {
        	 if(pageIndex>pages){
         	    $pullUp.hide();
                 return;
              }else{
             	 this.setLoading($pullUp);
                  if(pageIndex=pages){
             	    $pullUp.hide();
                  }
               }
          $el = $pullUp;
        }
        $.get(options.api,{offset:(start-1)*<?=$limit?>,operation:"<?=$operation?>",search:$.trim($("#search").val())}).then(function(data){
        	if (type === 'refresh') {
            	$list.empty();
                $list.html(data);
                _this.total = $("#total").val();
                _this.calPages();
              } else if (type === 'load') {
                  $list.append(data);
              } else {
                $list.html(data);
              } 
        	  setTimeout(function() {
                  _this.iScroll.refresh();
                }, 100);
             },function(){
            	 console.log('error');
                 }).always(function() {
                 _this.resetLoading($el);
                 if (type !== 'load') {
                   _this.iScroll.scrollTo(0, topOffset, 800, $.AMUI.iScroll.utils.circular);
                 }
               });
        if (type !== 'load') {
          _this.iScroll.scrollTo(0, topOffset, 800, $.AMUI.iScroll.utils.circular);
        }
      };

      this.setLoading = function($el) {
        $el.addClass('loading');
      };

      this.resetLoading = function($el) {
        $el.removeClass('loading');
      };

      this.init = function() {
        var myScroll = this.iScroll = new $.AMUI.iScroll('#wrapper',{momentum:true,shrinkScrollbars:"clip"});
        // myScroll.scrollTo(0, topOffset);
        var _this = this;
        var pullFormTop = false;
        var pullStart;
        this.calPages();

        myScroll.on('scrollStart', function() {
          if (this.y >= topOffset) {
            pullFormTop = true;
         }
          pullStart = this.y;
          // console.log(this);
        });

        myScroll.on('scrollEnd', function() {
       if (pullFormTop && this.directionY === -1) {
            _this.handlePullDown();
         }else{
        	 if($list.css("padding-bottom")=="1000px"){
             	 _this.iScroll.scrollToElement("#data-list");
             	 return;
               }
             }
          pullFormTop = false;

          // pull up to load more
          if (pullStart === this.y && (this.directionY === 1)) {
            _this.handlePullUp();
          }
        });
      };

      this.calPages=function(){
    	  pageIndex=1;
    	  if(total==0){
         	 $pullUp.hide();
             }else{
             	$pullUp.show();
             	if(total%pageSize==0){
                     pages = total/pageSize;
                 }else{
                 	pages = Math.ceil(total/pageSize);
                     }
                 if(pages==1){
                	 $pullUp.hide();
                     }
              }
          if($list.height()<=$main.height()){
        	  $list.css("padding-bottom","1000px");
        	  this.iScroll.options.momentum=false;
              }else{
            	  $list.css("padding-bottom","0px");
            	  this.iScroll.options.momentum=true;
               }

          this.iScroll.refresh();
          }

      this.handlePullDown = function() {
        console.log('handle pull down');
          this.setLoading($pullDown);
          this.renderList(0, 'refresh');
      };

      this.handlePullUp = function() {
        console.log('handle pull up');
        pageIndex++;
        this.renderList(pageIndex, 'load');
      };

    };

    $(function() {
      var app = new EventsList(null, {
        api: "/wechat/refresh",
        total:<?=$total?>,
        pageSize:<?=$limit?>
      });
      app.init();
      <?php if($operation=="bm"):?>
      $("title").text("赛事报名");
      <?php elseif ($operation=="zr"):?>
      $("title").text("名额转让");
      <?php elseif ($operation=="dl"):?>
      $("title").text("赛包代领");
      <?php endif;?>
     $("#searchForm").submit(function(){
    	 app.handlePullDown();
            return false;
         });
    });

    document.addEventListener('touchmove', function(e) {
      e.preventDefault();
    }, false);
  })(window.jQuery);
</script>
</body>
</html>
