<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <title>Login Page</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="format-detection" content="telephone=no">
    <meta name="renderer" content="webkit">
    <meta http-equiv="Cache-Control" content="no-siteapp" />
    <link rel="stylesheet" href="/css/amazeui.min.css"/>
    <link rel="stylesheet" href="/css/channel.css"/>
    <script type="text/javascript" src="/js/jquery.min.js"></script>
    <script type="text/javascript" src="/js/amazeui.min.js"></script>
    <style>
        .header {
            text-align: center;
        }
        .header h1 {
            font-size: 200%;
            color: #333;
            margin-top: 30px;
        }
        .header p {
            font-size: 14px;
        }
    </style>
        <script type="text/javascript">
        var baseAPIUrl="<?=Yii::$app->params["site_url"]?>"+"v2/";
        var backType="";
        function showReg(){
        	  backType="r";
              $("#login").hide();
              $("#reg").show();
              $("#reg-username").val("");
              $("#reg-password").val("");
              $("#backLogin").show();
              $("#loginTitle").hide();
              $("#title").text("注册");
            }

        function forgetPassword(){
            if($("#forgetForm").data('amui.validator').isFormValid()){
            	 var  phone = $.trim($("#f-username").val());
                 $("#forget-confirm").modal({
                	 onConfirm:function(){
                		 $("#f-btn").button("loading");
                		 $.post(baseAPIUrl+"site/verifyphone",{user_name:phone},function(data){
                                if(data.status==0){
                                      if(data.result.bind_status==1){
                                    	  backType="f";
                                    	  $("#f-btn").hide();
                                    	  showWait("#f-wait","找回密码","#f-btn");
                                    	  $("#f-btn").button("reset");
                                          }else if(data.result.bind_status==2){
                                        	  $("#fError").text("该手机号不存在！");
                                        	  $("#f-btn").button("reset");
                                           }
                                    }
                             });
                    	 }
                     });
                }
                         
             }
        	
        function getVeriCode(){
        	$("#regError").text("");
             var  phone = $.trim($("#reg-username").val());
             if(phone==""){
            	 $("#reg-username").focus();
                 return;
             }
             disableBtn("#veriCode",$("#veriCode").text());
             $.post(baseAPIUrl+"site/verifyphone",{user_name:phone},function(data){
            	     if(data.status==0&&data.result.bind_status==1){
                	       $("#regError").text("该手机号已注册！您的密码已被重置，我们将已短信的方式把您的密码发送到您的手机上，请注意查收！");
                	     }
                 });
            }

        function disableBtn(btn,text){
        	  var sec=30;
              $(btn).addClass("am-disabled");
              var timer = window.setInterval(function(){
            	  $(btn).addClass("am-disabled");
                    $(btn).text(text+"("+--sec+")");
                  
                    if(sec==0){
                 	   $(btn).text(text);
                 	   $(btn).removeClass("am-disabled");
                 	   window.clearInterval(timer);
                        }
                  }, 1000);
            }

         function showWait(id,text,btn){
        	 var sec=30;
        	 $(id).text(text+"("+--sec+")");
             $(id).show();
             var timer = window.setInterval(function(){
                   $(id).text(text+"("+--sec+")");
                   if(sec==0){
                	   $(id).text("");
                	   window.clearInterval(timer);
                	    $(btn).show();
                	    $(id).hide();
                       }
                 }, 1000);
             }
       
        
       function register(){
             if($("#regForm").data('amui.validator').isFormValid()){
            	   $.post(baseAPIUrl+"site/userregister",$("#regForm").serialize(),function(data){
                	   if(data.status==1){
                		   $("#regError").text(data.message);
                    	   }else if(data.status==0){
                                   $("#username").val($.trim($("#reg-username").val()));
                                   $("#password").val($.trim($("#reg-password").val()));
                                   $("#loginForm").submit();
                        	   }
                     });
                 }
           }

       function backLogin(){
              $("#backLogin").hide();
               $("#loginTitle").show();
               $("#login").show();
               $("#reg").hide();
               $("#forget").hide();
               if(backType=="f"){
                   $("#userName").val($("f-username").val());
                   }else if(backType=="r"){
                   $("#userName").val($("reg-username").val());
                   }
               $("#title").text("登录");
           }

       function showForget(){
    	   $("#login").hide();
           $("#forget").show();
           $("#backLogin").show();
           $("#loginTitle").hide();
            $("#title").text("找回密码");
           }

       $(function(){
    	   $('.am-modal').on('closed.modal.amui', function() {
    		   $(this).removeData('amui.modal');
    		 });
           });

    </script>
</head>
<body>
<header class="am-topbar admin-header">
    <div class="am-topbar-brand">
       <img alt="" src="/image/share/irunner.png" style="height:90%;padding-top:2px;padding-bottom:2px">&nbsp;&nbsp;<small id="title">登录</small>
    </div>
</header>

<div class="am-g" style="background-image: url('/image/site_back.png');padding-top:14px">
    <div class="am-cf am-u-sm-8" style="">
      <img alt="" src="/image/site_zi.png" class="am-center">
      <img alt="" src="/image/site_shouji.png" class="am-center">
    </div>
    
    <div class="am-u-sm-4">
        <div class="am-panel am-panel-primary">
            <div class="am-panel-hd" id="loginTitle">
                <h4 class="am-panel-title">登录</h4>
            </div>
            
             <div class="am-panel-hd" id="backLogin" style="display: none">
                          <a href="javascript:;" onclick="backLogin();" style="color:white;"> <h4 class="am-panel-title"><i class="am-icon-arrow-left"></i> 返回登录</h4></a>
            </div>
            
            
            <div class="am-panel-bd" id="login">
                <form method="post" action="<?=\Yii::$app->urlManager->createUrl(['site/login',]);?>" class="am-form" data-am-validator id="loginForm">
                    <label for="username">手机:</label>
                      <div class="am-form-group am-form-icon">
    <i class="am-icon-mobile"></i>
                    <input type="text" class="am-form-field" name="LoginForm[username]" id="username" placeholder="请输入您的手机号码" value="" pattern="^1((3|5|8){1}\d{1}|70|47)\d{8}$" required>
</div>
                    <label for="password">密码:</label>
                      <div class="am-form-group am-form-icon">
    <i class="am-icon-lock"></i>
                    <input type="password" class="am-form-field" name="LoginForm[password]" id="password" placeholder="请输入您的密码" value="" required>
                   </div>
                    <span class="am-help">
                        <?php
                        foreach($model->getErrors() as $err){
                            echo $err[0];break;
                        }
                        ?>
                    </span>
                    <div class="am-cf" style="text-align: right">
                    <a href="javascript:;" onclick="showReg();">注册 </a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="javascript:;" onclick="showForget();">忘记密码？</a>
                    </div>
                 <br>
                    <div class="am-cf">
                        <input type="submit" name="" value="登 录" class="am-btn am-btn-danger am-btn-sm am-btn-block">
                    </div>
                </form>
            </div>
            
             <div class="am-panel-bd" id="reg" style="display: none">
                <span class="am-help" id="regError">
                    </span>
                <form method="post" class="am-form" data-am-validator id="regForm">
                    <label for="username">手机:</label>
  <div class="am-form-group am-form-icon">
    <i class="am-icon-mobile"></i>
  <input type="text" class="am-form-field"  id="reg-username" name="user_name" placeholder="请输入您的手机号码" value="" pattern="^1((3|5|8){1}\d{1}|70|47)\d{8}$" required></div>
                    
                    <label for="reg-code">验证码:</label>
                   <div class="am-g">
  <div class="am-u-sm-4">
  <div class="am-form-group am-form-icon">
    <i class="am-icon-key"></i>
  <input type="text" class="am-form-field"  id="reg-code" name="code" placeholder="请输入验证码" value="" required maxlength="6"></div>
  </div>
  <div class="am-u-sm-3"><a class="am-btn am-btn-danger" onclick="getVeriCode();" id="veriCode" >获取验证码</a></div>
  <div class="am-u-sm-5"> <span class="am-help am-vertical-align-middle" id="codeError" >
                    </span></div>
</div>
                  
                    <label for="password">密码:</label>
                    <div class="am-form-group am-form-icon">
    <i class="am-icon-lock"></i>
                    <input type="password" class="am-form-field"  id="reg-password" name="password" placeholder="请输入您的密码" value="" maxlength="12" required>
                    </div>
                     <label for="password">请再次输入密码:</label>
                       <div class="am-form-group am-form-icon">
    <i class="am-icon-lock"></i>
                    <input type="password"  class="am-form-field"  id="reg-password-2" placeholder="请再次输入您的密码" value="" data-equal-to="#reg-password" maxlength="12" required>
                   </div>
                 
                    <br>
                    <div class="am-cf">
                        <input  name="" value="注册" class="am-btn am-btn-danger am-btn-sm am-btn-block" type="button" onclick="register();">
                    </div>
                </form>
            </div>
            
            <div class="am-panel-bd" id="forget" style="display: none;">
             <span class="am-help" id="fError">
                    </span>
                <form method="post"  class="am-form" data-am-validator id="forgetForm">
                    <label for="username">手机:</label>
                      <div class="am-form-group am-form-icon">
    <i class="am-icon-mobile"></i>
                    <input type="text" class="am-form-field" id="f-username" placeholder="请输入您的手机号码" value="" pattern="^1((3|5|8){1}\d{1}|70|47)\d{8}$" required>
</div>
                    <br>
                    <div class="am-cf">
                         <a  name="" class="am-btn am-btn-danger am-btn-sm  am-btn-block am-disabled"  id="f-wait" style="display:none"></a>
                        <input type="button" name="" value="找回密码" class="am-btn am-btn-danger am-btn-sm am-btn-block" onclick="forgetPassword();" data-am-loading="{spinner: 'circle-o-notch', loadingText: '正在发送...'}" id="f-btn">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<footer data-am-widget="footer" class="am-footer am-footer-default" data-am-footer="{  }">
  <div class="am-footer-miscs ">
    <p>CopyRight©2015 iRUNNER</p>
  </div>
</footer>
<div class="am-modal am-modal-confirm" tabindex="-1" id="forget-confirm">
  <div class="am-modal-dialog">
    <div class="am-modal-hd"><h3>忘记密码</h3></div>
    <div class="am-modal-bd">
         您的密码将会被重置，我们将以短信的方式把您的密码发送到您的手机上，请注意查收！
    </div>
    <div class="am-modal-footer">
      <span class="am-modal-btn" data-am-modal-cancel>取消</span>
      <span class="am-modal-btn" data-am-modal-confirm>确定</span>
    </div>
  </div>
</div>

</body>
</html>