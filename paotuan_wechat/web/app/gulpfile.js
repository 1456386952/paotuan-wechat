/*!
 * gulp
 * $ npm install gulp gulp-ruby-sass gulp-autoprefixer gulp-minify-css gulp-jshint gulp-concat gulp-uglify gulp-imagemin gulp-notify gulp-rename gulp-cache gulp-clean gulp-usemin gulp-rev imagemin-pngquant gulp-zip browser-sync moment --save-dev
 */

// Load plugins
var gulp         = require('gulp'),                   //gulp  
    sass         = require('gulp-ruby-sass'),         //sass
    autoprefixer = require('gulp-autoprefixer'),      //css前缀 
    minifycss    = require('gulp-minify-css'),        //css压缩
    jshint       = require('gulp-jshint'),            //js代码校验
    uglify       = require('gulp-uglify'),            //js压缩
    imagemin     = require('gulp-imagemin'),          //img压缩
    pngquant     = require('imagemin-pngquant'),      //img压缩
    rename       = require('gulp-rename'),            //重命名
    concat       = require('gulp-concat'),            //合并文件
    notify       = require('gulp-notify'),            //更改提醒
    cache        = require('gulp-cache'),             //图片缓存
    Browsersync  = require('browser-sync').create(),  //同步多浏览器
    reload       = Browsersync.reload,                //自动刷新页面
    clean        = require('gulp-clean'),             //清空文件夹
    usemin       = require('gulp-usemin'),            //替换css、js路径
    rev          = require('gulp-rev'),               //追加哈希值为版本号
    zip          = require('gulp-zip'),               //自动打包文件
    moment       = require('moment');                 //日期格式化


//环境目录
var app = {
    src: 'mobile',
    dist: 'dist'
    // src: 'src',
    // dist: 'dist'
};

//当前日期
var nowDate = moment().format('YYYY-MM-DD');

//替换css、js路径
gulp.task('usemin', function() {
  return gulp.src('../'+app.src+'/*.html')
    .pipe(usemin({
      css: [  ],
      js: [  ],
    }))
    .pipe(gulp.dest('../'+app.dist+'/'));
});

// Styles
gulp.task('styles', function() {
  return sass('../'+app.src+'/styles/main.scss', { style: 'expanded' })
    .pipe(autoprefixer())
    .pipe(gulp.dest('../'+app.dist+'/styles'))
    .pipe(rename({ suffix: '.min' }))
    .pipe(minifycss())
    .pipe(gulp.dest('../'+app.dist+'/styles'))
    .pipe(notify({ message: 'Styles task complete' }));
});

// Styles
gulp.task('styles-src', function() {
  return sass('../'+app.src+'/styles/main.scss', { style: 'expanded' })
    .pipe(autoprefixer())
    .pipe(gulp.dest('../'+app.src+'/styles'))
    .pipe(rename({ suffix: '.min' }))
    .pipe(minifycss())
    .pipe(gulp.dest('../'+app.src+'/styles'))
    .pipe(notify({ message: 'Styles task complete' }));
});

// Scripts
gulp.task('scripts', function() {
  return gulp.src([
        '../'+app.src+'/scripts/angular/*.js',
        '../'+app.src+'/scripts/angular-ui/*.js',
        '../'+app.src+'/scripts/custom/*.js'
    ])
    .pipe(jshint())
    .pipe(jshint.reporter('default'))
    .pipe(concat('main.js'))
    .pipe(gulp.dest('../'+app.dist+'/scripts'))
    .pipe(rename({ suffix: '.min' }))
    .pipe(uglify())
    .pipe(gulp.dest('../'+app.dist+'/scripts'))
    .pipe(notify({ message: 'Scripts task complete' }));
});

// Images
gulp.task('images', function() {
    return gulp.src('../'+app.src+'/images/**/*')
    .pipe(imagemin({
        progressive: true,
        svgoPlugins: [{removeViewBox: false}],
        use: [pngquant()]
    }))
    .pipe(gulp.dest('../'+app.dist+'/images'))
    .pipe(notify({ message: 'Images task complete' }));
});

//Html copy
gulp.task('html', function () {
    return gulp.src('../'+app.src+'/*.html')
    .pipe(gulp.dest('../'+app.dist+'/'))
    .pipe(notify({ message: 'Html task complete' }));
});

//Fonts copy
gulp.task('fonts', function () {
    return gulp.src('../'+app.src+'/fonts/**/*')
    .pipe(gulp.dest('../'+app.dist+'/fonts'))
    .pipe(notify({ message: 'Fonts task complete' }));
});

//Tpl copy
gulp.task('tpl', function () {
    return gulp.src('../'+app.src+'/tpl/**/*')
    .pipe(gulp.dest('../'+app.dist+'/tpl'))
    .pipe(notify({ message: 'Tpl task complete' }));
});

//Api copy
gulp.task('api', function () {
    return gulp.src('../'+app.src+'/api/**/*')
    .pipe(gulp.dest('../'+app.dist+'/api'))
    .pipe(notify({ message: 'Api task complete' }));
});

//Clean
gulp.task('clean', function() {
  return gulp.src(
    [
        '../'+app.dist+'/styles', 
        '../'+app.dist+'/scripts', 
        '../'+app.dist+'/images', 
        '../'+app.dist+'/fonts', 
        '../'+app.dist+'/tpl', 
        '../'+app.dist+'/api',
        '../'+app.dist+'/**/*',
    ], {read: false})
    .pipe(clean({force: true}));
});

//自动打包文件
gulp.task('zip', function () {
    return gulp.src('../'+app.dist+'/**/*')
        .pipe(zip('app.zip'))
        .pipe(rename({ suffix: '-' + nowDate }))
        .pipe(gulp.dest('../build'));
});

// 生产环境 
gulp.task('browser-sync', ['styles', 'scripts', 'images', 'fonts', 'tpl', 'api'], function() {

    //替换css、js路径为生产环境
    gulp.start('usemin');

    //静态服务器
    Browsersync.init({
        port: '8888',
        server: {
            baseDir: "../"+app.dist
        }
    });

    // Watch .scss files
    gulp.watch('../'+app.src+'/styles/**/*.scss', ['styles']);

    // Watch .js files
    gulp.watch('../'+app.src+'/scripts/**/*.js', ['scripts']);

    // Watch image files
    gulp.watch('../'+app.src+'/images/**/*', ['images']);

    // Watch fonts files
    gulp.watch('../'+app.src+'/fonts/**/*', ['fonts']);

    // Watch tpl files
    gulp.watch('../'+app.src+'/tpl/**/*', ['tpl']);

    // Watch api files
    gulp.watch('../'+app.src+'/api/**/*', ['api']);

    // Watch any files in dist/, reload on change
    gulp.watch(['../'+app.dist+'/**']).on('change', reload);
});

// Default task
gulp.task('default', ['browser-sync'], function() {
    // gulp.start('styles', 'scripts', 'images');
});



// 开发环境
gulp.task('app-src', ['styles-src'], function() {

    //静态服务器
    Browsersync.init({
        port: '8888',
        server: {
            baseDir: "../"+app.src
        }
    });

    // Watch .scss files
    gulp.watch('../'+app.src+'/styles/**/*.scss', ['styles-src']);

    // Watch any files in dist/, reload on change
    gulp.watch(['../'+app.src+'/**']).on('change', reload);
});

// Src task
gulp.task('src', ['app-src'], function() {
    // gulp.start('styles', 'scripts', 'images');
});