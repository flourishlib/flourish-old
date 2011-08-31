#!/bin/bash

SCRIPT="$0"
if [[ $(readlink $SCRIPT) != "" ]]; then
	SCRIPT=$(dirname $SCRIPT)/$(readlink $SCRIPT)
fi
if [[ $0 = ${0%/*} ]]; then
		SCRIPT=$(pwd)/$0
fi
DIR=$(cd ${SCRIPT%/*} && pwd -P)

svn up

REV_FILE="$DIR/classes/flourish.rev"
REV=$(svn info | grep Revision: | awk '{print $2}')
NEW_REV=$(( $REV + 1 ))

# Make sure the flourish.rev file is up to date
echo "This folder contains Flourish r$NEW_REV
Flourish is an open source PHP 5 library - see http://flourishlib.com for more information" > $REV_FILE

if [[ $# = 2 ]]; then
	svn ci "$@"
else
	svn ci classes/flourish.rev "$@"
fi
