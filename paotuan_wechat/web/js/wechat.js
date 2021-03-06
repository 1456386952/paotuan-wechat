if($.afui){
	$.afui.useOSThemes=false;
	$.afui.isAjaxApp=false;
}

var ug = navigator.userAgent;
var phone = "android";
if(ug.toLowerCase().indexOf("iphone")!=-1||ug.toLowerCase().indexOf('mac')!=-1||ug.toLowerCase().indexOf('ipad')!=-1){
	phone="apple";
}
var forceIframeTransport=false;
if(phone=="android"){
	forceIframeTransport=true;
}
function fileupload(file){
	$("#"+file).fileupload({
		  dataType: 'json',
		  url:"/upload/uploadimg",
		  sequentialUploads:true,
		  disableImageResize:false,
		  imageCrop: false,
		  replaceFileInput:false,
		  formData:{file_id:file,img_type:file},
		  singleFileUploads:false,
		  forceIframeTransport:forceIframeTransport,
		  acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
		  processfail:function(e,data){
			    if(data.files[data.index].error=="File type not allowed"){
                        alert("只能上传图片(gif,jpg,png)!");
				    }
			  },
		  send:function(e,data){
			  showLoading();
			  },
	      done:function(e,data){
		        if(data.result.status==1){
		        	var images = [];
			        $.each(data.result.images,function(i,n){
			        	var temp = {};
			        	$("#"+file).removeAttr("required");
			        	$("#"+file).removeClass("am-field-error");
			        	// $("#"+file+"_pre").show();
			        	// $("#"+file+"_pre").attr("src","http://xiaoi.b0.upaiyun.com"+n+"!80X80");
			        	$("#"+file+"_value").val(n);
			        	if(data.files[i].preview){
			        		temp.pre = data.files[i].preview.toDataURL();
			        	}else{
			        		temp.pre ="http://xiaoi.b0.upaiyun.com"+n+"!mid";
			        	}
			        	temp.image = n;
			        	images.push(temp);
			        });
			        var source   = $("#image-pre-template").html();  
	            	  var template = Handlebars.compile(source);
	            	  $("#"+file).next().remove();
	            	  $("#"+file).after(template({images:images})); 
	            	  $.AMUI.gallery.init();
			      }
		      },
	      fail:function(e,data){
                 // alert("fail");
		      },
		  always:function(e,data){
			  hideLoading();
			}
	  });
}

function fileuploadNg(file,isPre,callBack,hideLoad){
	$("#"+file).fileupload({
		  dataType: 'json',
		  url:"/upload/uploadimg",
		  sequentialUploads:true,
		  disableImageResize:false,
		  imageCrop: false,
		  replaceFileInput:false,
		  formData:{file_id:file,img_type:file},
		  singleFileUploads:false,
		  forceIframeTransport:forceIframeTransport,
		  acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
		  processfail:function(e,data){
			    if(data.files[data.index].error=="File type not allowed"){
                      alert("只能上传图片(gif,jpg,png)!");
				    }
			  },
		  send:function(e,data){
			  showLoading();
			  },
	      done:function(e,data){
		        if(data.result.status==1){
		        	var images = [];
			        $.each(data.result.images,function(i,n){
			        	var temp = {};
			        	$("#"+file).removeAttr("required");
			        	$("#"+file).removeAttr("check-type");
			        	$("#"+file).removeClass("am-field-error");
			        	// $("#"+file+"_pre").show();
			        	// $("#"+file+"_pre").attr("src","http://xiaoi.b0.upaiyun.com"+n+"!80X80");
			        	
			        		$("#"+file+"_value").val(n);
			        	if(data.files[i].preview){
			        		temp.pre = data.files[i].preview.toDataURL();
			        	}else{
			        		temp.pre ="http://xiaoi.b0.upaiyun.com"+n+"!80X80";
			        	}
			        	temp.image = n;
			        	images.push(temp);
			        });
			        if(isPre){
			            var source   = $("#image-pre-template").html();  
		            	  var template = Handlebars.compile(source);
		            	  //$("#"+file).next().remove();
		            	  if($("#"+file).parent().children(".upload-pre").length==0){
		            		  $("#"+file).parent().append(template({images:images}));
		            	  }else{
		            	   $(template({images:images})).replaceAll($("#"+file).parent().children(".upload-pre"));
		            	  }
			        }
			    	if($.isFunction(callBack)){
			    		callBack(images,file);
		        	}
	            	  //$("#"+file).after(template({images:images})); 
			      }
		      },
	      fail:function(e,data){
	    	  hideLoading();
               alert("fail");
		      },
		  always:function(e,data){
			  if(hideLoad||typeof(hideLoad) =='undefined'){
				  hideLoading();
			  }
			}
	  });
}

var file_validate_error=false;
function fileuploadMulti(file,callback){
	$("#"+file).fileupload({
		  dataType: 'json',
		  url:"/upload/uploadimg",
		  sequentialUploads:true,
		  disableImageResize:false,
		  imageCrop: false,
		  replaceFileInput:false,
		  formData:{file_id:file,img_type:file},
		  singleFileUploads:false,
		  forceIframeTransport:forceIframeTransport,
		  maxNumberOfFiles:9,
		  acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
		  processfail:function(e,data){
			    if(data.files[data.index].error=="File type not allowed"&&!file_validate_error){
			    	file_validate_error=true;   
			    	alert("只能上传图片(gif,jpg,png)!");
				    }else if(data.files[data.index].error=="Maximum number of files exceeded"&&!file_validate_error){
				    	file_validate_error=true;   
				    	alert("最多选择9个图片！");
				    }
			  },
			  change:function(){
				  file_validate_error=false;
			  },
		  send:function(e,data){
			  showLoading();
			  },
	      done:function(e,data){
		        if(data.result.status==1){
		        	var images = [];
		        	var values="";
			         $.each(data.result.images,function(i,n){
			        	var temp = {};
			        	$("#"+file).removeAttr("required");
			        	$("#"+file).removeClass("am-field-error");
			        	// $("#"+file+"_pre").show();
			        	// $("#"+file+"_pre").attr("src","http://xiaoi.b0.upaiyun.com"+n+"!80X80");
			        	//$("#"+file+"_value").val(n);
			        	values = values+","+n;
			        	if(data.files[i].preview){
			        		temp.pre = data.files[i].preview.toDataURL();
			        	}else{
			        		temp.pre ="http://xiaoi.b0.upaiyun.com"+n+"!mid";
			        	}
			        	temp.image = n;
			        	images.push(temp);
			          });
			          
		        	   var source   = $("#image-pre-template").html();  
		            	  var template = Handlebars.compile(source);
		            	  $("#"+file).next().remove();
		            	  $("#"+file).after(template({images:images})); 
		            	  $.AMUI.gallery.init();
		            	  if(values!=""){
			        		  values = values.substring(1);
			        		  $("#"+file+"_value").val(values);
			        	  }
		            	if($.isFunction(callback)){
				        		callback(data,file);
				        	}
			      }
		      },
	      fail:function(e,data){
                alert("上传超时!");
		      },
		  always:function(e,data){
			  hideLoading();
			}
	  });
}


var file_validate_error=false;
function fileuploadMultiNg(file,callback,isPre,replaceFileInput,hideLoad){
	$("#"+file).fileupload({
		  dataType: 'json',
		  url:"/upload/uploadimg",
		  sequentialUploads:true,
		  disableImageResize:false,
		  imageCrop: false,
		  formData:{file_id:file,img_type:file},
		  singleFileUploads:false,
		  replaceFileInput:replaceFileInput,
		  forceIframeTransport:forceIframeTransport,
		  maxNumberOfFiles:9,
		  acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
		  processfail:function(e,data){
			    if(data.files[data.index].error=="File type not allowed"&&!file_validate_error){
			    	file_validate_error=true;   
			    	alert("只能上传图片(gif,jpg,png)!");
				    }else if(data.files[data.index].error=="Maximum number of files exceeded"&&!file_validate_error){
				    	file_validate_error=true;   
				    	alert("最多选择9个图片！");
				    }
			  },
			  change:function(){
				  file_validate_error=false;
			  },
		  send:function(e,data){
			  showLoading();
			  },
	      done:function(e,data){
		        if(data.result.status==1){
		        	var images = [];
		        	var values="";
			         $.each(data.result.images,function(i,n){
			        	var temp = {};
			        	$("#"+file).removeAttr("required");
			        	$("#"+file).removeClass("ng-valid");
			        	// $("#"+file+"_pre").show();
			        	// $("#"+file+"_pre").attr("src","http://xiaoi.b0.upaiyun.com"+n+"!80X80");
			        	//$("#"+file+"_value").val(n);
			        	values = values+","+n;
			        	if(data.files[i].preview){
			        		temp.pre = data.files[i].preview.toDataURL();
			        	}else{
			        		temp.pre ="http://xiaoi.b0.upaiyun.com"+n+"!mid";
			        	}
			        	temp.image = n;
			        	images.push(temp);
			          });
			          if(isPre){
		        	   var source   = $("#image-pre-template").html();  
		            	  var template = Handlebars.compile(source);
		            	  $("#"+file).next().remove();
		            	  $("#"+file).after(template({images:images})); 
			          }
		            	  if(values!=""){
			        		  values = values.substring(1);
			        		  $("#"+file+"_value").val(values);
			        	  }
		            	if($.isFunction(callback)){
				        		callback(images,file);
				        	}
			      }
		      },
	      fail:function(e,data){
                  alert("图片上传超时");
                  hideLoading();
		      },
		  always:function(e,data){
			  if(hideLoad||typeof(hideLoad) =='undefined'){
				  hideLoading();
			  }
			}
	  });
}

function initDateTimePicker(opt,select,type){
	var currYear = (new Date()).getFullYear();	
	if(opt==null){
		opt={};
	}
	opt.defaultCfg = {
		theme: 'android-ics light', // 皮肤样式
        display: 'modal', // 显示方式
        mode: 'scroller', // 日期选择模式
		dateFormat: 'yyyy-mm-dd',
		lang: 'zh',
		showNow: true,
		nowText: "今天",
	};
	   switch(type){
	      case 'date':opt.preset='date';$(select).mobiscroll($.extend(opt.defaultCfg, opt));break;
	      case 'datetime':opt.preset='datetime';opt.width=50;$(select).mobiscroll($.extend(opt.defaultCfg, opt));break;
	      case 'time':opt.preset='time';$(select).mobiscroll($.extend(opt.defaultCfg, opt));break;
	   }
}

function showLoading(){
	$("#file_progress_percent").hide();
	$("#xiaoi-loading").show();
}
function hideLoading(){
	$("#xiaoi-loading").hide();
}

function showFileLoading(){
	$("#file_progress_percent").show();
	$("#xiaoi-loading").show();
}

function setProgressPercent(percent){
	$("#file_progress_percent").text(percent+"%");
}
var progress_percent=0;
$(function(){
	$("body").append('<div id="xiaoi-loading" style="width: 100%;background-color: rgba(0,0,0,0);z-index:1040;height:100%;position: fixed;top: 0;left: 0;text-align: center;display:none">'+
		     '<div style="background-color:black;opacity:0.7;width: 150px;padding:14px;border-radius:8px;display: inline-block;margin-top: 55%"><span id="file_progress_percent" style="display:none;color:#fff">'+progress_percent+'%</span>&nbsp;&nbsp;<img alt="" src="/image/loading.gif" style="width: 50px"></div>'+
		'</div>');
	$("#xiaoi-loading").height($(document).height());
	var loadingEl = $("#xiaoi-loading").children()[0];
	  $(loadingEl).css("margin-top", ($(window).height()-$(loadingEl).outerHeight())/2-20+"px");
	
	$(document).on("click",".carousel-control",function(event){
	    $(this).parent().carousel($(this).attr("data-slide"));
	});
	
	$(document).on("click","img[data-pre]",function(event){
		 var urls = new Array();
		 $.each($("img"),function(i,n){
			   urls.push($(this).attr("data-pre"));
			 });
    	  wx.previewImage({
    	    current:$(this).attr("data-pre"), // 当前显示的图片链接
    	    urls: urls // 需要预览的图片链接列表
    	   });
	});
	
	$(document).on("click","a[data-toggle='tab']",function(event){
		   var tab = event.target;
		   tab=$(tab).attr("data-target");
		   $.each($(".tab-content").children(),function(i,n){
			   if(n.id!=tab){
				   $("#"+n.id).removeClass("active");
				   $("#"+n.id).hide();
			   }else{
				   $("#"+n.id).addClass("active");
			   }
		   });
		   $("#"+tab).show();
			event.preventDefault();
	});
});


