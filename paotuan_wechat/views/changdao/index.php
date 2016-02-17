<!DOCTYPE html>
<html ng-app="wechatApp">
<head>
<meta charset="UTF-8">
<meta name="viewport"
	content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
<title>长岛马报名查询</title>
<link href="http://libs.baidu.com/bootstrap/3.3.4/css/bootstrap.min.css"  rel="stylesheet">
</head>
<body >
<div class="container-fluid">
   <form  method="post" action="/changdao/index" class="form-inline" style="margin-top:14px">
     <div class="form-group">
     <label>证件号码:</label>
      <input type="search" class="form-control" id="id_no" name="id_no" placeholder="请输入证件号" value="<?=trim(Yii::$app->request->post("id_no",""))?>"> 
     </div>
     <button type="submit" class="btn btn-default">查询</button>
   </form>
 
   <?php if($r):?>
     <hr>
   <div class="list-group col-xs-12 col-md-5">
   <div class="list-group-item container-fluid">
    <div class="col-xs-4 col-lg-3" style="padding:0">订单号 </div>
    <div class="col-xs-8 col-lg-6" style="padding:0"><?=$r->orderid?></div>
    </div>
       <div class="list-group-item container-fluid">
    <div class="col-xs-4 col-lg-3" style="padding:0">报名确认码 </div>
    <div class="col-xs-8 col-lg-6" style="padding:0"><?=$r->bm_code?></div>
    </div>
       <div class="list-group-item container-fluid">
    <div class="col-xs-4 col-lg-3" style="padding:0">参赛号码 </div>
    <div class="col-xs-8 col-lg-6" style="padding:0"><?=$r->cs_code?></div>
    </div>
       <div class="list-group-item container-fluid">
    <div class="col-xs-4 col-lg-3" style="padding:0">报名类别 </div>
    <div class="col-xs-8 col-lg-6" style="padding:0"><?=$r->bm_type?></div>
    </div>
       <div class="list-group-item container-fluid">
    <div class="col-xs-4 col-lg-3" style="padding:0">参赛类型 </div>
    <div class="col-xs-8 col-lg-6" style="padding:0"><?=$r->cs_type?></div>
    </div>
       <div class="list-group-item container-fluid">
    <div class="col-xs-4 col-lg-3" style="padding:0">姓名 </div>
    <div class="col-xs-8 col-lg-6" style="padding:0"><?=mb_substr(trim($r->name), 0,1,"UTF-8").str_repeat("*",mb_strlen(trim($r->name),"UTF-8")-1 )?></div>
    </div>
       <div class="list-group-item container-fluid">
    <div class="col-xs-4 col-lg-3" style="padding:0">性别 </div>
    <div class="col-xs-8 col-lg-6" style="padding:0"><?=$r->xb?></div>
    </div>
       <div class="list-group-item container-fluid">
    <div class="col-xs-4 col-lg-3" style="padding:0">生日</div>
    <div class="col-xs-8 col-lg-6" style="padding:0"><?=$r->sr?></div>
    </div>
       <div class="list-group-item container-fluid">
    <div class="col-xs-4 col-lg-3" style="padding:0">手机号码</div>
    <div class="col-xs-8 col-lg-6" style="padding:0"><?=substr_replace(trim($r->cell), "****", 3,4)?></div>
    </div>
       <div class="list-group-item container-fluid">
    <div class="col-xs-4 col-lg-3" style="padding:0">电子邮箱 </div>
    <div class="col-xs-8 col-lg-6" style="padding:0"><?=$r->email?></div>
    </div>
       <div class="list-group-item container-fluid">
    <div class="col-xs-4 col-lg-3" style="padding:0">证件类型 </div>
    <div class="col-xs-8 col-lg-6" style="padding:0"><?=$r->id_type?></div>
    </div>
       <div class="list-group-item container-fluid">
    <div class="col-xs-4 col-lg-3" style="padding:0">证件号码 </div>
    <div class="col-xs-8 col-lg-6" style="padding:0"><?=$r->id_no?></div>
    </div>
       <div class="list-group-item container-fluid">
    <div class="col-xs-4 col-lg-3" style="padding:0">现居国家</div>
    <div class="col-xs-8 col-lg-6" style="padding:0"><?=$r->guojia?></div>
    </div>
       <div class="list-group-item container-fluid">
    <div class="col-xs-4 col-lg-3" style="padding:0">紧急联系人电话</div>
    <div class="col-xs-8 col-lg-6" style="padding:0"><?=substr_replace(trim($r->jinji_cell), "****", 3,4)?></div>
    </div>
       <div class="list-group-item container-fluid">
    <div class="col-xs-4 col-lg-3" style="padding:0">个性号码</div>
    <div class="col-xs-8 col-lg-6" style="padding:0"><?=$r->gexing_code?></div>
    </div>
       <div class="list-group-item container-fluid">
    <div class="col-xs-4 col-lg-3" style="padding:0">订单金额</div>
    <div class="col-xs-8 col-lg-6" style="padding:0"><?=$r->amount?></div>
    </div>
       <div class="list-group-item container-fluid">
    <div class="col-xs-4 col-lg-3" style="padding:0">支付方式</div>
    <div class="col-xs-8 col-lg-6" style="padding:0"><?=$r->pay_type?></div>
    </div>
       <div class="list-group-item container-fluid">
    <div class="col-xs-4 col-lg-3" style="padding:0">报名时间</div>
    <div class="col-xs-8 col-lg-6" style="padding:0"><?=$r->reg_time?></div>
    </div>
   </div>
   <?php elseif ($query):?>
      <hr>
      <p>您所查找的信息不存在</p>
   <?php endif;?>
 </div>
</body>
</html>