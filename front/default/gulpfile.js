const gulp             = require('gulp');
const concat           = require('gulp-concat');
const sourcemaps       = require('gulp-sourcemaps');
const uglify           = require('gulp-uglify');
const htmlToJs         = require('gulp-html-to-js');
const wrapFile         = require('gulp-wrap-file');
const sass             = require('gulp-sass')(require('sass'));
const rollup           = require('@rollup/stream');
const rollupSourcemaps = require('rollup-plugin-sourcemaps');
const rollupCommonjs   = require('@rollup/plugin-commonjs');
const rollupBabel      = require('@rollup/plugin-babel');
const nodeResolve      = require('@rollup/plugin-node-resolve');
const source           = require('vinyl-source-stream');
const buffer           = require("vinyl-buffer");

const through2 = require('through2');
const crypto   = require('crypto');
const fs       = require('fs');

const conf = {
    dist: "./dist",
    css: {
        fileMin: 'core.min.css',
        main: 'src/main.scss',
        src: [
            'src/css/**/*.scss',
        ]
    },
    js: {
        core: {
            fileMin: 'core.min.js',
            main: 'src/main.js',
            name: 'Core',
            src: [
                'src/**/*.js',
            ]
        }
    },
    tpl: {
        file: 'tpl.js',
        dist: './src/js/core',
        src: [
            'src/html/**/*.html',
            'src/html/*.html'
        ]
    },
    html: {
        file: './index.html'
    }
};


gulp.task('build_css', function(){
    return gulp.src(conf.css.main)
        .pipe(sourcemaps.init())
        .pipe(sass({outputStyle: 'compressed', includePaths: ['node_modules']}).on('error', sass.logError))
        .pipe(concat(conf.css.fileMin))
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest(conf.dist));
});

gulp.task('build_css_fast', function(){
    return gulp.src(conf.css.main)
        .pipe(sourcemaps.init())
        .pipe(sass({includePaths: ['node_modules']}).on('error', sass.logError))
        .pipe(concat(conf.css.fileMin))
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest(conf.dist));
});


gulp.task('build_js', function() {
    return rollup({
        input: conf.js.core.main,
        output: {
            sourcemap: true,
            format: 'umd',
            name: conf.js.core.name
        },
        onwarn: function (log, handler) {
            if (log.code === 'CIRCULAR_DEPENDENCY') {
                return; // Ignore circular dependency warnings
            }
            handler(log.message);
        },
        context: "window",
        plugins: [
            nodeResolve(),
            rollupSourcemaps(),
            rollupCommonjs(),
            rollupBabel({babelHelpers: 'bundled'}),
        ]
    })
        .pipe(source(conf.js.core.fileMin))
        .pipe(buffer())
        .pipe(sourcemaps.init())
        .pipe(uglify())
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest(conf.dist));
});

gulp.task('build_js_fast', function() {
    return rollup({
        input: conf.js.core.main,
        output: {
            sourcemap: false,
            format: 'umd',
            name: conf.js.core.name
        },
        onwarn: function (log, handler) {
            if (log.code === 'CIRCULAR_DEPENDENCY') {
                return; // Ignore circular dependency warnings
            }
            handler(log.message);
        },
        context: "window",
        plugins: [
            nodeResolve(),
            rollupSourcemaps(),
            rollupCommonjs(),
            rollupBabel({babelHelpers: 'bundled'}),
        ]
    })
        .pipe(source(conf.js.core.fileMin))
        .pipe(buffer())
        .pipe(gulp.dest(conf.dist));
});


gulp.task('build_tpl', function() {
    return gulp.src(conf.tpl.src)
        .pipe(htmlToJs({global: 'tpl', concat: conf.tpl.file}))
        .pipe(wrapFile({
            wrapper: function(content, file) {
                content = content.replace(/\\n/g, ' ');
                content = content.replace(/[ ]{2,}/g, ' ');
                return 'let ' + content + ";\nexport default tpl;"
            }
        }))
        .pipe(gulp.dest(conf.tpl.dist));
});


gulp.task('update_index', function() {

    /**
     * Получение хэша из файла
     * @param filePath
     * @return {string}
     */
    function getFileHash(filePath) {
        if ( ! fs.existsSync(filePath)) {
            return 'no_hash';
        }

        const fileBuffer = fs.readFileSync(filePath);
        const hashSum    = crypto.createHash('sha256');

        hashSum.update(fileBuffer);
        return hashSum.digest('hex').slice(0, 8);
    }

    let dirIndex = conf.html.file.split('/');
    dirIndex.splice(-1, 1)
    dirIndex = dirIndex.join('/') + '/';

    return gulp.src(conf.html.file)
        .pipe(through2.obj((file, enc, cb) => {
            let content = file.contents.toString();

            const jsRegex  = /(src="[^"]+\.js(\?.*|)")/g;
            const cssRegex = /(href="[^"]+\.css(\?.*|)")/g;

            // Функция для замены путей с добавлением хэша
            const addHashToAssets = (match, attr) => {
                const filePath = match.match(/src="([^"]+)"/)?.[1] || match.match(/href="([^"]+)"/)?.[1];

                if ( ! filePath) {
                    return match;
                }

                const filePathPure = filePath.split('?')[0];
                const pathSplit    = filePathPure.split('/');
                const fullPath     = conf.dist + '/' + pathSplit[pathSplit.length - 1];
                const hash         = getFileHash(fullPath);

                return match.replace(filePath, `${filePathPure}?_=${hash}`);
            };

            // Замена для JS и CSS
            content = content.replace(jsRegex, (match) => addHashToAssets(match, 'src'));
            content = content.replace(cssRegex, (match) => addHashToAssets(match, 'href'));

            file.contents = Buffer.from(content);
            cb(null, file);
        }))
        .pipe(gulp.dest(dirIndex));
});


gulp.task('build_watch', function() {
    gulp.watch(conf.css.src, gulp.series(['build_css_fast', 'update_index']));
    gulp.watch(conf.tpl.src, gulp.series(['build_tpl', 'build_js_fast', 'update_index']));
    gulp.watch([conf.js.core.src, '!' + conf.tpl.dist + '/' + conf.tpl.file], gulp.series(['build_js_fast', 'update_index']));
});

gulp.task("default", gulp.series([ 'build_tpl', 'build_js', 'build_css', 'update_index']));