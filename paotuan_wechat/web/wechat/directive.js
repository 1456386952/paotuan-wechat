wechatApp.directive('onFinishRenderFilters', function($timeout) {
	return {
		restrict: 'A',
		link: function(scope, element, attr) {
			$timeout(function() {
				scope.$emit('ngRenderFinished');
			});
		}
	};
});

wechatApp.directive('onRepeatFinishRenderFilters', function($timeout) {
	return {
		restrict: 'A',
		link: function(scope, element, attr) {
			if (scope.$last === true) {
				$timeout(function() {
					scope.$emit('ngRepeatRenderFinished');
				});
			}
		}
	};
});

wechatApp.directive('onFormRepeatFinishRender', function($timeout) {
	return {
		restrict: 'A',
		link: function(scope, element, attr) {
			if (scope.$last === true) {
				$timeout(function() {
					scope.$emit('ngFormRepeatRenderFinished');
				});
			}
		}
	};
});

wechatApp.directive('dateComponent', function($timeout) {
	return {
		restrict: 'A',
		link: function(scope, element, attr) {
			if ($("#data_time_css").length == 0) {
				$("body").append('<link href="/datetime/datetime.min.css" id="data_time_css" rel="stylesheet" type="text/css">');
			}
			$script("/datetime/datetime.js", function() {
				if (attr.dateMax == "now") {
					$(element).attr("max", new Date());
				}
				initDateTimePicker(null, "#" + attr.id, attr.dateType);
			});
		}
	};
});

wechatApp.directive('ngSelected', function($timeout) {
	return {
		restrict: 'A',
		link: function(scope, element, attr) {
			attr.$observe("ngSelected", function(v) {
				$.each($(element).children(), function(i, n) {
					if (n.value == eval("scope." + v)) {
						n.selected = true;
					} else {
						n.selected = false;
					}
				});
			});
		}
	}
});

wechatApp.directive('ngSlide', function($timeout) {
	return {
		restrict: 'A',
		link: function(scope, element, attr) {
			if ($("#bootstrap_slider").length == 0) {
				$("body").append('<link href="/bootstrap-slider/bootstrap-slider.min.css" id="bootstrap_slider" rel="stylesheet"">');
			}
			$script('/bootstrap-slider/bootstrap-slider.min.js', function() {
				attr.$observe("ngSlide", function(v) {
					if ($('#' + attr.id).length == 0)
						return;
					var slide = $('#' + attr.id).slider({});
					slide.on("change", function(slideEvt) {
						$('#' + attr.id).next().text(slideEvt.value.newValue);
						//$('#'+attr.id).after("<span style='margin-left:10px'>"+slideEvt.value.newValue+"</span>");
						scope.$eval(v + "=" + slideEvt.value.newValue);
					});
					$('#' + attr.id).next().remove();
					$('#' + attr.id).after("<span style='margin-left:10px'>1</span>");
					if (scope.sliders) {
						scope.sliders[attr.id] = slide;
					}
				});

			});
		}
	};
});

wechatApp.directive('ngFileupload', function($timeout) {
	return {
		restrict: 'A',
		link: function(scope, element, attr) {
			if (attr.type != "file") {
				return;
			}
			var params = {};
			attr.$observe("ngFileupload", function(v) {
				if (v) {
					params = eval("(" + v + ")");
					if (typeof (params.isPre) == 'undefined') {
						params.isPre = true;
					}
				} else {
					params.isPre = true;
				}
				if (scope.$root.weixin) {
					element.on("click", function() {
						if (attr.multiple) {
							scope.chooseImage(9, params.isPre, eval("scope." + params.callback));
						} else {
							scope.chooseImage(1, params.isPre, eval("scope." + params.callback));
						}
						event.preventDefault();
					});

					return;
				}
				$script("/jq_fileupload/jquery.fileupload-all.js", function() {
					$script("/jq_fileupload/jquery.fileupload-image-all.js", function() {
						if (attr.multiple) {
							fileuploadMultiNg(attr.id, eval("scope." + params.callback), params.isPre, params.replaceFileInput);
						} else {
							fileuploadNg(attr.id, params.isPre, eval("scope." + params.callback));
						}
					});
				});
			});
		}
	};
});

wechatApp.directive('ngVbt', function() {
	return {
		restrict: 'A',
		link: function(scope, element, attr) {
			$script("/js/vbt.min.js", function() {
				$("#" + attr.id).validation({reqmark: false});
			});
		}
	};
});

wechatApp.directive('scrollLoad', function() {
	return {
		restrict: 'A',
		link: function(scope, element, attr) {
//			  $(element).on("scroll",function(){
//				  if($(document).scrollTop()>0&&($(document).scrollTop()+$(window).height())>=$(document).height()-10){
//					  eval("scope."+attr.scrollLoad);
//				  }
//			  });
//			   eval("scope."+attr.scrollLoad);
		}
	};
});


wechatApp.directive('divSelect', function() {
	return {
		restrict: 'A',
		link: function(scope, element, attr) {
			$(element).on("click", function() {
				eval("scope." + attr.divSelect);
			});
		}
	};
});