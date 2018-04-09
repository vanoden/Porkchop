const gulp = require('gulp');
const template = require('gulp-template');
const data = require('gulp-data');
const debug = require('gulp-debug');

gulp.task('hello', function() {
	console.log('Hello, Tony');
});

gulp.task('process', ['js','css','jpegs','pngs','svg'], () =>
	gulp.src('html.src/**/*.html')
		.pipe(data(() => (
			{
				"video_path": 'http://assets.spectrosinstruments.com/video',
				"docs_path": 'http://assets.spectrosinstruments.com/docs'
			}
		)))
		.pipe(template())
		.pipe(debug())
		.pipe(gulp.dest('html'))
);

gulp.task('js', () =>
	gulp.src('html.src/**/*.js')
		.pipe(data(() => (
			{ "field": "content" }
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
