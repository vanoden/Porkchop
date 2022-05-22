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
sudo npm install --global gulp gulp-cli gulp-debug gulp-template gulp-data
