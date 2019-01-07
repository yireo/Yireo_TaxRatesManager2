#!/bin/bash
ENV_FILE=$1

EXTENSION_DIR=`pwd`

echo $VENDOR/$MODULE
echo $EXTENSION_DIR
echo $WEB_DIR

exit
cd $WEB_DIR
mkdir -p app/code/${EXTENSION_VENDOR}/
cd app/code/${EXTENSION_VENDOR}/
ln -s

cd $WEB_DIR