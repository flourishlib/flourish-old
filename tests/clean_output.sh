#!/bin/sh

if [ "$1" != "" ]; then
	rm -f output/*$1*
else
	rm -f output/*
fi
