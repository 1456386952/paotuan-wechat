/* 封装一个指令用以实现滑动删除的交互效果 */
ActivityModule.directive('runner', [function(){
	// Runs during compile
	return {
		restrict: 'AE',
		template:'<ul class="runner" ng-transclude></ul>',
		replace: true,
		transclude: true,
		controller : function() {
			var runners = [];
			this.gotOpened = function(selectedRunner) {
				angular.forEach(runners, function(runner) {
					if (selectedRunner != runner) {
						runner.showMe = false;
					}
				});
			}
			this.addRunner = function(runner) {
				runners.push(runner);
			}
		}
	};
}]);

/* 依赖于runner指令 */
ActivityModule.directive('runnerList', [function(){
	// Runs during compile
	return {
		restrict: 'AE',
		require : '^?runner',
		template: '<li ng-class="{active: showMe}" ng-swipe-left="show()" ng-swipe-right="hide()" ng-transclude></li>',
		replace: true,
		transclude: true,
		link : function(scope, element, attrs, RunnerController) {
			scope.showMe = false;
			RunnerController.addRunner(scope);
			scope.show = function show() {
				scope.showMe = true;
				RunnerController.gotOpened(scope);
			};
			scope.hide = function hide() {
				scope.showMe = false;
				RunnerController.gotOpened(scope);
			}
		}
	};
}]);

/* 封装一个指令用以实现textarea文本框的提示语 */
ActivityModule.directive('textareaTitle', [function() {
    return {
        require: 'ngModel',
        link: function(scope, ele, attrs, modelController) {

            var text = attrs.textareaTitle + ':';
            var placeholder = attrs.placeholder;
            var alltext = text + placeholder;
            ele.attr('placeholder', alltext);
            ele.on('focus', function() {
                if (!modelController.$modelValue) {
                    setVal(text);
                }
                scope.$apply();
            });
            ele.on('blur', function() {
                if (modelController.$modelValue === text) {
                    setVal('');
                }
            });
            function setVal(v) {
                modelController.$setViewValue(v);
                modelController.$render();
            }
        }
    }
}]);

/* 封装一个指令用以实现倒计时 */
ActivityModule.directive('loadingText',['$rootScope', '$timeout', function($rootScope, $timeout) {
	return {
		restrict: 'A',
		link : function(scope, element, attrs, controller) {
			var text = element.text();
			var num  = attrs.loadingText;

			var countdown = function(num) {
				var time = $timeout(function(){
					num--;
					element.text(num);
					console.log(num)
					if (num==0) {
						$rootScope.isloading = true;
						element.text(text);
						$timeout.cancel();
						return false;
					}
					countdown(num);
				}, 1000);
			};

			element.bind('click', function(){
				if ($rootScope.isloading) {
					$rootScope.isloading = false;
					countdown(num);
				}
			});
			
		}
	}
}]);

/* 封装一个指令tabset */
ActivityModule.directive('tabset', [function(){
	// Runs during compile
	return {
		restrict: 'AE',
		template:'<ul ng-transclude></ul>',
		replace: true,
		transclude: true,
		controller : function() {
			var items = [];
			this.gotOpened = function(selectedItem) {
				angular.forEach(items, function(item) {
					if (selectedItem != item) {
						item.active = false;
					}
				});
				selectedItem.active = true;
			}
			this.addItem = function(item) {
				items.push(item);
			}
		}
	};
}]);

/* 封装一个指令tab */
ActivityModule.directive('tab', [function(){
	// Runs during compile
	return {
		restrict: 'AE',
		require : '^?tabset',
		scope: {
			active: '=?'
		},
		template: '<li ng-class="{active: active}" ng-click="show()" ng-transclude></li>',
		replace: true,
		transclude: true,
		link : function(scope, element, attrs, tabsetController) {
			// scope.active = false;
			tabsetController.addItem(scope);
			scope.$watch('active', function(active) {
				if (active) {
					tabsetController.gotOpened(scope);
				}
			});
			scope.show = function() {
				scope.active = true;
				tabsetController.gotOpened(scope);
			};
		}
	};
}]);

/* 封装一个指令用以上传图片及附件预览 */
ActivityModule.directive('fileModel', ['$parse', function ($parse) {
    return {
        restrict: 'A',
        link: function(scope, element, attrs, ngModel) {
            var model = $parse(attrs.fileModel);
            var modelSetter = model.assign;
            element.bind('change', function(event){
                scope.$apply(function(){
                    modelSetter(scope, element[0].files[0]);
                });
                //附件预览
                scope.file = (event.srcElement || event.target).files[0];
                scope.$apply(attrs.fileFn);
                // scope.getFile();
            });
        }
    };
}]);

/* 封装一个指令用以实现新增、删除报名填写的信息 */
ActivityModule.directive('addInfo', [function () {
    return {
        restrict: 'A',
        link: function(scope, element, attrs, controller) {
            element.on('click', function(event) {
            	var page = element.hasClass('bg-red');

            	if (page) {
            		element.removeClass('bg-red');
            		scope.$apply(attrs.delFn);
            	} else {
            		element.addClass('bg-red');
            		scope.$apply(attrs.addFn);
            	}
            });
        }
    };
}]);

/* 自定义一个指令用以实现图片比例1/3 */
ActivityModule.directive('imgSize', ['$rootScope', function ($rootScope) {
    return {
        restrict: 'A',
        link: function(scope, element, attrs, controller) {
        	var winWidth = $rootScope.winWidth,
        		width    = winWidth - attrs.offset,
        		height   = width/3;

        	element.css({
        		'width': width + 'px', 
        		'height': height + 'px'
        	});
        }
    };
}]);

/* 自定义一个指令用以实现滚动加载 */
ActivityModule.directive('whenScrolled', function() {
	return {
		restrict: 'A',
        link: function(scope, element, attrs, controller) {
			var raw = element[0];
			console.log(element)
			element.bind('scroll', function() {
				if (raw.scrollTop + raw.offsetHeight >= raw.scrollHeight) {
					scope.$apply(attrs.whenScrolled);
				}
			});
		}
	};
});

/* 自定义一个指令用以实现loading */
App.directive('loading', [function(){
	
	return {
		restrict: 'AE',
		template: '<span ng-transclude></span>',
		replace: true,
		transclude: true,
		link: function(scope, element, attrs, controller) {

			console.log(element.text())

			var str 	  = element.text(),
	        	loadClass = ["yoyo-load0", "yoyo-load1", "yoyo-load2", "yoyo-load3", "yoyo-load4", "yoyo-load5"];

	      	if (element != null) element.text('');

			function c_random(num, arrlen) {
				var arr = [];

				function r(i) {
					var t = Math.round(Math.random() * (num - 1));
					if (t == arr[i - 1]) {
						r(i);
						return;
					}
					arr.push(t);
				}

				for (var i = 0; i < arrlen; i++) {
					r(i);
				}

				return arr;
			}

	      	var tarr = c_random(loadClass.length, str.length);

			for (var i = 0; i < str.length; i++) {
				var t = str[i];

				if (t == " ") {
					t = "&nbsp;"
				}

				var _class = "yoyo-x-left";
				if (i > 0 && i < str.length - 1) {
					_class = loadClass[tarr[i]];
				}

				if (i == str.length - 1) {
					_class = 'yoyo-x-right';
				}

				element.append("<p class='" + _class + "'>" + t + "</p>");
			}
		}
	};
}]);