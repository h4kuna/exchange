#!/bin/bash

DIR=`dirname $0`;

cd $DIR/..
composer install --no-interaction --prefer-source
cd -

TEMP_DIR=$DIR/temp

rm -rf $TEMP_DIR/*
mkdir -p $TEMP_DIR/cache/latte

$DIR/../vendor/bin/tester -p php $DIR -s -j 5 --colors 1 -c $DIR/data/php_unix.ini
