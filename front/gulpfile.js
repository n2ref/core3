const gulp             = require('gulp');
const concat           = require('gulp-concat');
const sourcemaps       = require('gulp-sourcemaps');
const uglify           = require('gulp-uglify');
const htmlToJs         = require('gulp-html-to-js');
const sass             = require('gulp-sass')(require('sass'));
const rollup           = require('rollup-stream');
const rollupSourcemaps = require('rollup-plugin-sourcemaps');
const rollupBabel      = require('rollup-plugin-babel');
const source           = require('vinyl-source-stream');
const buffer           = require("vinyl-buffer");
const wrapFile         = require('gulp-wrap-file');

const conf = {
    dist: "./dist",
    css: {
        file: 'core-all.css',
        fileMin: 'core-all.min.css',
        main: 'src/css/main.scss',
        src: [
            'src/css/**/*.scss',
        ]
    },
    js: {
        file: 'core-all.js',
        src: [
            'node_modules/jquery/dist/jquery.min.js',
            'node_modules/bootstrap/dist/js/bootstrap.bundle.min.js',

            'node_modules/coreui-panel/dist/coreui-panel.js',
            'node_modules/coreui-tabs/dist/coreui-tabs.min.js',
            'node_modules/coreui-alert/dist/coreui-alert.min.js',
            'node_modules/coreui-modal/dist/coreui-modal.js',
            'node_modules/coreui-info/dist/coreui-info.min.js',
            'node_modules/coreui-notice/dist/coreui-notice.min.js',
            'node_modules/coreui-layout/dist/coreui-layout.js',
            'node_modules/coreui-table/dist/coreui-table.js',
            'node_modules/coreui-form/dist/coreui-form.js',
            'node_modules/coreui-chart/dist/coreui-chart.min.js',
            'node_modules/coreui-breadcrumb/dist/coreui-breadcrumb.min.js',

            'dist/core.js',
        ]
    },
    js_min: {
        file: 'core-all.min.js',
        src: [
            'node_modules/jquery/dist/jquery.min.js',
            'node_modules/bootstrap/dist/js/bootstrap.bundle.min.js',

            'node_modules/coreui-panel/dist/coreui-panel.min.js',
            'node_modules/coreui-tabs/dist/coreui-tabs.min.js',
            'node_modules/coreui-alert/dist/coreui-alert.min.js',
            'node_modules/coreui-modal/dist/coreui-modal.min.js',
            'node_modules/coreui-info/dist/coreui-info.min.js',
            'node_modules/coreui-notice/dist/coreui-notice.min.js',
            'node_modules/coreui-layout/dist/coreui-layout.min.js',
            'node_modules/coreui-table/dist/coreui-table.min.js',
            'node_modules/coreui-form/dist/coreui-form.min.js',
            'node_modules/coreui-chart/dist/coreui-chart.min.js',
            'node_modules/coreui-breadcrumb/dist/coreui-breadcrumb.min.js',

            'dist/core.min.js',
        ]
    },
    js_core: {
        file: 'core.js',
        fileMin: 'core.min.js',
        index: 'src/js/index.js',
        src: [
            'src/js/*.js',
            'src/js/**/*.js',
        ]
    },
    tpl: {
        file: 'core.templates.js',
        dist: './src/js/core',
        src: [
            'src/html/**/*.html',
            'src/html/*.html'
        ]
    }
};

gulp.task('build_css', function(){
    return gulp.src(conf.css.main)
        .pipe(sass().on('error', sass.logError))
        .pipe(concat(conf.css.file))
        .pipe(gulp.dest(conf.dist));
});

gulp.task('build_css_min', function(){
    return gulp.src(conf.css.main)
        .pipe(sourcemaps.init())
        .pipe(sass({outputStyle: 'compressed'}).on('error', sass.logError))
        .pipe(concat(conf.css.fileMin))
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest(conf.dist));
});

gulp.task('build_js', function() {
    return gulp.src(conf.js.src)
        .pipe(concat(conf.js.file, {newLine: ";\n"}))
        .pipe(gulp.dest('./dist'));
});

gulp.task('build_js_min', function() {
    return gulp.src(conf.js_min.src)
        .pipe(sourcemaps.init())
        .pipe(uglify())
        .pipe(concat(conf.js_min.file, {newLine: ";\n"}))
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest('./dist'));
});

gulp.task('build_js_core', function() {
    return rollup({
        input: conf.js_core.index,
        sourcemap: false,
        format: 'umd',
        name: "Core",
        plugins: [rollupBabel()],
        context: "window"
    })
        .pipe(source(conf.js_core.file))
        .pipe(buffer())
        .pipe(gulp.dest(conf.dist));
});

gulp.task('build_js_core_min', function() {
    return rollup({
        input: conf.js_core.index,
        sourcemap: false,
        format: 'umd',
        name: "Core",
        plugins: [
            rollupSourcemaps(),
            rollupBabel()
        ],
        context: "window",
    })
        .pipe(source(conf.js_core.fileMin))
        .pipe(buffer())
        .pipe(uglify())
        .pipe(gulp.dest(conf.dist));
});

gulp.task('build_tpl', function() {

    return gulp.src(conf.tpl.src)
        .pipe(htmlToJs({global: 'coreTpl', concat: conf.tpl.file}))
        .pipe(wrapFile({
            wrapper: function(content, file) {
                content = content.replace(/\\n/g, ' ');
                content = content.replace(/[ ]{2,}/g, ' ');
                return 'let ' + content + ";\nexport default coreTpl;"
            }
        }))
        .pipe(gulp.dest(conf.tpl.dist));
});


gulp.task('build_watch', function() {
    gulp.watch(conf.tpl.src, gulp.series(['build_tpl', 'build_js_core_min', 'build_js_min']));
    gulp.watch(conf.js_core.src, gulp.series(['build_js_core_min', 'build_js_min']));
    gulp.watch(conf.css.src, gulp.series(['build_css_min']));
});


gulp.task("default", gulp.series([ 'build_tpl', 'build_js_core', 'build_js_core_min', 'build_js', 'build_js_min', 'build_css_min', 'build_css']));