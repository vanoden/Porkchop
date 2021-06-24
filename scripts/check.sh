#!/bin/bash
#############################################
### check.sh				###
### Sync latest updates for each repo.	###
### A. CAravello 12/22/2017		###
### Copyright 2017, Boston Metrics, Inc	###
###########################################

# Identify Parent Path from Command
PARENT_DIR=`/bin/pwd`
#PARENT_DIR=${PARENT_DIR%%script/pull.sh}

# Loop Through Repositories and pull
for repo in `find . -type d|grep -v ".old$"`
do
	DIR=${PARENT_DIR}/$repo/
	if [ -d "$DIR/.git" ]
	then
		#echo "Updating $DIR"
		if ! cd $DIR
		then
			echo "Cannot check ${repo}: Cannot cd into $DIR"
			continue
		fi
		if output=$(git status --porcelain --untracked-files=no) && [ ! -z "$output" ]
		then
			echo -n "Directory "
			echo $DIR
			REPO=`/bin/cat .git/config|/bin/grep url|/bin/sed -rn 's/.*(\/(SpectrosInstruments|vanoden)\/[a-z\_\-]+).*/\1/p'`
			echo "Repo '${REPO}' modifications: "
			echo $output
		fi
		cd $PARENT_DIR
	fi
done
