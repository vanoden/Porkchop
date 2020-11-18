#!/bin/bash

DIR=$1

if [ -z "$DIR" ]
then
	echo "Working path required as only parameter.  Should be the folder containing gulpfile.js";
	exit 1
fi

if [ ! -e "$DIR/gulpfile.js" ]
then
	echo "gulpfile.js not found in working path"
	exit 1
fi

cd $DIR
npm install gulp gulp-debug gulp-template
