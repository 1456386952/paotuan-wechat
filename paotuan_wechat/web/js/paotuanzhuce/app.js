var app={
	init:function(){//初始化
		this.tab('.tabList a','cur','.tabContent .tabContentEle');
	},
	tab:function(ele,className,showEle){ //tab切换
		$(ele).on('click',function(){
			var _index = $(this).index();
			$(this).addClass(className).siblings().removeClass(className);
			$(showEle).eq(_index).show().siblings().hide();
		})
		$(ele).eq(2).trigger('click');
	},
	
	//register 折叠
	
	showAndHide:function(){
		$('.col-header').on('click',function(){
			$(this).siblings('.col-content').slideDown();
			$(this).parent().siblings('.col-list-ele').find('.col-content').slideUp();
		})
		$('.col-header').eq(0).trigger('click');
	}
}

