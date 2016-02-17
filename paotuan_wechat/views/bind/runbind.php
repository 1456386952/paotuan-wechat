<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
<title>身份确认</title>

<link href="/css/paotuanzhuce/app.css?<?=time()?>" type="text/css" rel="stylesheet"/>
<link rel="stylesheet" href="/css/amazeui.min.css"/>
<link rel="stylesheet" href="/css/bindmenu.css">
</head>
<body>
    <div>
        <div style="background-color:#009ada;color:white;height:50px;line-height:50px;">
        <img src="/image/bind/application.png" style="margin-left:20px;width:20px;height:20px;">
        应用同步
        </div>
        <div style="width:92%;margin-top: 10px; margin-left: auto;margin-right:auto;">
            <?php  if ($codoonbind):?>
    			<div class="bind_item" style="font-size: 13px; color: black; ">
    				<div style="float:left;margin-top:10px;">
    				    <img src="/image/bind/codoon.png" style="width: 20px; height: 20px;" />
    				</div>
    				<div style="margin-left:10px;float:left;">
                        <div><?=$codoonbind->nick_name?></div>
                        <div style="font-size: 12px; color: gray;">绑定时间：<?=date('Y.m.d',strtotime($codoonbind->create_time))?></div>
    			    </div>
    			    <div style="float:right;margin-top:11px;">
    			        <a href="/bind/unbind?bind_type=1" class="unbind">解绑</a>
    			    </div>
    			</div>
            <?php else :?>
    			<div class="bind_item">
                    <div style="float:left;margin-top:10px;font-size: 15px; color: black;">
        				<img src="/image/bind/codoon.png" style="width: 20px; height: 20px;" />
        				<span style="margin-left:8px;">咕咚</span> 
        			</div>
        			<div style="float:right;margin-top:11px;">
        			    <a href="/codoon/user" class="bind_write">绑定</a>
        			</div>
    			</div>
            <?php endif;?>
            <?php  if ($hupubind):?>
    			<div class="bind_item" style="font-size: 13px; color: black; ">
    				<div style="float:left;">
    				    <img src="/image/bind/hupu.png" style="width: 20px; height: 20px;" />
                    </div>
                    <div style="margin-left:10px;float:left;">
                        <div><?=$hupubind->nick_name?></div>
                        <div style="font-size: 12px; color: gray;">绑定时间：<?=date('Y.m.d',strtotime($hupubind->create_time))?></div>
    			    </div>
    			    <div style="float:right;margin-top:11px;">
    			        <a href="/bind/unbind?bind_type=2" class="unbind">解绑</a>
    			    </div>
    			</div>
            <?php else :?>
                <div class="bind_item">
                    <div style="float:left;margin-top:10px;font-size: 15px; color: black;">
        				<img src="/image/bind/hupu.png" style="width: 20px; height: 20px;" />
        				<span style="margin-left:8px;">虎扑</span> 
        			</div>
        			<div style="float:right;margin-top:11px;">
        			    <a href="/hupu/user" class="bind_write">绑定</a>
        			</div>
    			</div>
            <?php endif;?>
        </div>
    </div>
<script type="text/javascript" src="/js/jquery.min.js"></script>
<script src="/js/amazeui.min.js"></script>
<script type="text/javascript" src="/js/paotuanzhuce/app.js"></script>
</body>
</html>