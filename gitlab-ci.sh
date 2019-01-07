#!/bin/bash
ENV_FILE=`pwd`/.gitlab-ci.env
source $ENV_FILE

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

# @todo: Install MEQP2 and test
# @todo: Run PHPUnit tests
# @todo: Install Yireo_ExtensionChecker
