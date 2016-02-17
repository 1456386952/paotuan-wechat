function showMsg(text){
	$("#alert-content").text(text);
	$("#alert-msg").modal();
}

function showFormError(form,alert,text){
	$(alert).text(text);
	$(alert).show();
}

function hideFormError(alert){
	$(alert).hide();
}

function getOrderList(channelId){
	var url="/channel/"+channelId+"/orders";
	var $oprate_loading = $("#oprate_modal-loading");
	$oprate_loading.modal();
	$.post(url,function(data){
		$oprate_loading.modal("close");
		$("#detail-content").html($(data).contents().find("#orders").html());
	});
}


(function($) {
    'use strict';

    $(function() {
    	 var $oprate_loading = $("#oprate_modal-loading");
    	$.ajaxSetup({
    		error:function(){
    			showMsg("服务器异常请稍候重试.");
    		},
    		statusCode:{500: function() {
    			showMsg("服务器异常请稍候重试.");
    		  }
    		 }
    		});
    	
        var $fullText = $('.admin-fullText');
        $('#admin-fullscreen').on('click', function() {
          $.AMUI.fullscreen.toggle();
        });
        $(document).on($.AMUI.fullscreen.raw.fullscreenchange, function() {
          $.AMUI.fullscreen.isFullscreen ? $fullText.text('关闭全屏') : $fullText.text('开启全屏');
        });
        $('.create-channel').on('click', function(e) {
            var $modal = $('#create-channel');
            var $target = $(this);
            $("#channel_title").text($target.attr('data-title'));
            $("#actid").val($target.attr('data-id'));
            $modal.modal();
        });
        $('.edit-channel').on('click', function(e) {
            var $modal = $('#edit-channel');
            var $target = $(this);
            $modal.modal();
        });
       
        $('.create-quota').on('click', function(e) {
            var $target = $(this);
            var actid = $target.attr('data-id');
            var channelid = $target.attr('data-channelid');
            var href = '/channel/item/quota';
            $oprate_loading.modal();
            $.post(href,{actid:actid,channelid:channelid},function(data){
                $oprate_loading.modal('close');
                data=$.parseJSON(data);
                if(data.status==1){
                    showMsg(data.message);
                }else{
                    var H = '';
                    var D = data.result;
                    $(D).each(function(index){
                        H += '<option data-courseid="'+ D[index].courseid+'" value="'+ D[index].course_name+'">'+ D[index].course_name+'</option>';
                    });
                    $(".quota_name").empty().append(H);
                    var $modal = $('#quota-modal');
                    $("#quota_title").text("添加");
                    var A = '/channel/item/create?channelid='+channelid;
                    $("#quota_form").attr('action',A);
                    $modal.modal();
                }
            });
            return false;
        });
        $('.edit-quota').on('click', function(e) {
            var $target =$(this);
            var itemid = $target.attr('data-itemid');
            var R = '/channel/item/info';
            $oprate_loading.modal();
            $.post(R,{itemid:itemid},function(data){
                $oprate_loading.modal('close');
                data=$.parseJSON(data);
                if(data.status===1){
                	showMsg(data.message);
                }else{
                    var D = data.result;
                    $("#courseid").val(D.courseid);
                    var H = '<option data-courseid="'+ D.courseid+'" value="'+ D.item_name+'">'+ D.item_name+'</option>';
                    $("#quota_name").empty().append(H);
                    var A = '/channel/item/edit?itemid='+itemid;
                    $("#quota_form").attr('action',A);
                    $("#item_num_limit").val(D.item_num_limit);
                    $("#item_price").val(D.item_price);
                    $("#item_desc").val(D.item_desc);
                    var $modal = $('#quota-modal');
                    /*获取信息进行改造*/
                    $("#quota_title").text("编辑");
                    $modal.modal();
                }
            });
            return false;
        });
        $('.create-other').on('click', function(e) {
            var $modal = $('#other-modal');
            var $target =$(this);
            var channelid = $target.attr('data-channelid');
            var A = '/channel/item/create?channelid='+channelid;
            $("#other_form").attr('action',A);
            $("#other_title").text("添加");
            $modal.modal();
            return false;
        });
        $('.edit-other').on('click', function(e) {
            var $target = $(this);
            var itemid = $target.attr('data-itemid');
            var R = '/channel/item/info';
            $oprate_loading.modal();
            $.post(R,{itemid:itemid},function(data){
                $oprate_loading.modal('close');
                data=$.parseJSON(data);
                if(data.status===1){
                	showMsg(data.message);
                }else{
                    var D = data.result
                    $("#other_type").find("option[value='"+ D.item_type+"']").attr("selected",true);
                    $("#other_name").val(D.item_name);
                    var A = '/channel/item/edit?itemid='+itemid;
                    $("#other_form").attr('action',A);
                    $("#other_num_limit").val(D.item_num_limit);
                    $("#other_price").val(D.item_price);
                    $("#other_desc").val(D.item_desc);
                    var $modal = $('#other-modal');
                    /*获取信息进行改造*/
                    $("#other_title").text("编辑");
                    $modal.modal();
                }
            });
        });
        /**/
        $("#channel-btn").on('click',function(e){
            var $F = $("#channel_form");
            if(!$F.data('amui.validator').isFormValid()){
             	return false;
             }
            /*
            var channelDesc = $.trim($("#channel_desc").val());
            var channelEnd = $.trim($("#channel_end").val());
            if(channelEnd ===''){
            	showFormError("#channel_form","#channel_form-error","报名通道截止时间不能为空");
                //alert('报名通道截止时间不能为空');
                return false;
            }
            if(channelDesc === ''){
                alert('报名通道描述不能为空');
                return false;
            }
            */
            $oprate_loading.modal();
            $.post($F.attr('action'),$F.serialize(),function(data){
            	data=$.parseJSON(data);
                if(data.status===0){
                    location.href=data.href;
                }else{
                    alert(data.message)
                }
            });
            return false;
        });
        $("#item-quota-btn").on('click',function(e){
        	 var $F = $("#quota_form");
        	 if(!$F.data('amui.validator').isFormValid()){
             	return false;
             }
            var item_num_limit = Number($("#item_num_limit").val());
            if(item_num_limit===0){
                alert('报名名额不能为0');
                return false;
            }
           
            $("#courseid").val($("#quota_name").find("option:selected").attr("data-courseid"));
            $oprate_loading.modal();
            $.post($F.attr('action'),$F.serialize(),function(data){
            	data=$.parseJSON(data);
                if(data.status===0){
                    location.reload();
                }
            });
            return false;
        });
        
        $("#item-other-btn").on('click',function(e){
        	   var $F = $("#other_form");
            var other_num_limit = Number($("#other_num_limit").val());
            if(!$F.data('amui.validator').isFormValid()){
            	return false;
            }
            if(other_num_limit===0){
                alert('服务名额不能为0');
                return false;
            }
         
            $oprate_loading.modal();
            $.post($F.attr('action'),$F.serialize(),function(data){
            	data=$.parseJSON(data);
                if(data.status===0){
                    location.reload();
                }
            });
            return false;
        });
        

        $(".oprate_confirm").on('click',function(e){
            var $target =$(this);
            $('#oprate-confirm').modal({
                relatedTarget: this,
                onConfirm: function(options) {
                    $.post($target.attr('href'),function(){
                        location.reload();
                    });
                },
                onCancel: function() {
                }
            });
            return false;
        });

        $(".logo-loading").on('click',function(e){
            var file = $('#club_logo').val();
            if(!/.(gif|jpg|jpeg|png)$/.test(file)){
                alert("图片类型必须是gif,jpeg,jpg,png中的一种");
                return false;
            }
            var $modal = $('#logo-loading');
            $modal.modal();
        });
        
        $('.am-modal').on('closed.modal.amui', function() {
 		   $(this).removeData('amui.modal');
 		 });
    });
})(jQuery);
