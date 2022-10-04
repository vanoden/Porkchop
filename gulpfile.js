const gulp = require('gulp');
const template = require('gulp-template');
const data = require('gulp-data');
const debug = require('gulp-debug');
const fs = require('fs');

const ver = "0.9.1";
const preProcessPath = 'html.src/pre';
const postProcessPath = 'tmp';
const today = new Date();
const month = today.getMonth() + 1;

const configJSON = fs.readFileSync('html.src/gulp_config.json');
const configDict = JSON.parse(configJSON);

const videoPath = configDict.videoPath;
const docsPath = configDict.docsPath;
const siteTitle = configDict.siteTitle;
const staticVersion = today.getFullYear()+"."+month+"."+today.getDate()+"."+today.getHours()+"."+today.getMinutes();
const htmlBlocks = configDict.htmlBlocks;

html2process = {
	"static_version": staticVersion,
	"video_path": videoPath,
	"docs_path": docsPath,
	"company_name": configDict.companyName,
	"company": configDict.companyCode
};


gulp.task('evaluate', function() {
	console.log('Starting gulp process');
	console.log('preProcessPath: '+preProcessPath);
	for (const key in htmlBlocks) {
		if (fs.existsSync(preProcessPath+'/'+htmlBlocks[key])) {
			console.log('Loading '+preProcessPath+'/'+htmlBlocks[key]);
			html2process[key] = fs.readFileSync(preProcessPath+'/'+htmlBlocks[key], 'utf8');
			html2process[key] = template(html2process);
		}
		else {
			console.log(key + ' NOT found');
		} 
	}
	console.log(html2process);
});

gulp.task('process', ['evaluate','js','css','jpegs','pngs','svg','gif','ico', 'dashboards'], () =>
	gulp.src('html.src/**/*.html')
		.pipe(data(() => (html2process)))
		.pipe(template())
		.pipe(debug())
		.pipe(gulp.dest('html'))
);

gulp.task('pre', () =>
	gulp.src(preProcessPath+'/*.html')
		.pipe(data(() => (htmlBlocks)))
		.pipe(template().on('error',function(e){
		console.log(e);
	}))
	.pipe(debug())
	.pipe(gulp.dest(postProcessPath))
);

gulp.task('js', () =>
	gulp.src('html.src/**/*.js')
		.pipe(data(() => (
			{
				"field": "content",
				"static_version": staticVersion,
				"num": "${num}"
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

gulp.task('gif', () =>
	gulp.src('html.src/**/*.gif')
		.pipe(debug())
		.pipe(gulp.dest('html'))
);

gulp.task('ico', () =>
	gulp.src('html.src/**/*.ico')
		.pipe(debug())
		.pipe(gulp.dest('html'))
);

gulp.task('dashboards', () =>
	gulp.src('html.src/dashboards/**/*.html')
		.pipe(debug())
		.pipe(gulp.dest('html'))
);
