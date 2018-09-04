const gulp = require('gulp');
const template = require('gulp-template');
const data = require('gulp-data');
const debug = require('gulp-debug');
const fs = require('fs');

var today = new Date();
var month = today.getMonth() + 1;

const staticVersion = today.getFullYear()+"."+month+"."+today.getDate()+"."+today.getHours()+"."+today.getMinutes();
const videoPath = 'http://assets.spectrosinstruments.com/video';
const docsPath = 'http://assets.spectrosinstruments.com/docs';

gulp.task('hello', function() {
	console.log('Hello, Tony');
});

gulp.task('process', ['tmp','js','css','jpegs','pngs','svg'], () =>
	gulp.src('html.src/**/*.html')
		.pipe(data(() => (
			{
				"static_version": staticVersion,
				"video_path": videoPath,
				"docs_path": docsPath,
				"header": fs.readFileSync('html.src/tmp/header.html', 'utf8'),
				"footer": fs.readFileSync('html.src/tmp/footer.html', 'utf8')
			}
		)))
		.pipe(template())
		.pipe(debug())
		.pipe(gulp.dest('html'))
);

gulp.task('tmp', () =>
    gulp.src('html.src/*.html')
        .pipe(data(() => (
            {
                "static_version": staticVersion,
				"video_path": videoPath,
				"docs_path": docsPath,
                "header": fs.readFileSync('html.src/header.html', 'utf8'),
                "footer": fs.readFileSync('html.src/footer.html', 'utf8')
            }
        )))
        .pipe(template())
        .pipe(debug())
		.pipe(gulp.dest('html.src/tmp'))
);
    

gulp.task('js', () =>
	gulp.src('html.src/**/*.js')
		.pipe(data(() => (
			{
				"field": "content",
				"static_version": staticVersion
			}
		)))
		.pipe(template())
		.pipe(debug())
		.pipe(gulp.dest('html'))
);

gulp.task('css', () =>
	gulp.src('html.src/**/*.css')
		.pipe(data(() => (
			{ "field": "content" }
		)))
		.pipe(template())
		.pipe(debug())
		.pipe(gulp.dest('html'))
);

gulp.task('pngs', () =>
	gulp.src('html.src/**/*.png')
		.pipe(debug())
		.pipe(gulp.dest('html'))
);

gulp.task('svg', () =>
	gulp.src('html.src/**/*.svg')
		.pipe(debug())
		.pipe(gulp.dest('html'))
);

gulp.task('jpegs', () =>
	gulp.src('html.src/**/*.jpg')
		.pipe(debug())
		.pipe(gulp.dest('html'))
);
