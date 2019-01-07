#!/bin/bash
# This script relies on the following global variables (set through .gitlab-ci.yml)
# - WEB_DIR, where Magento is installed

# The source file .gitlab-ci.env should define at least the following variables
# - VENDOR, for example Yireo
# - MODULE, for example FooBar
#
# Optionally, the following variables are also supported
# - RUN_PHPCS_EQP2=1
# - RUN_PHPCS_EXTDN=1
# - RUN_YIREO_EXTENSIONCHECKER=1

ENV_FILE=`pwd`/.gitlab-ci.env
source $ENV_FILE
EXTENSION_DIR=`pwd`

# Create a symlink to this repo
cd $WEB_DIR
mkdir -p app/code/${VENDOR}/
cd app/code/${VENDOR}/
ln -s $EXTENSION_DIR ${MODULE}

echo "Enabling this extension"
cd $WEB_DIR
./bin/magento module:enable ${VENDOR}_${MODULE}
./bin/magento module:status --enabled | grep ${VENDOR}_${MODULE}

echo "Run PHPUnit unit tests"
if [[ -f $WEB_DIR/app/code/${VENDOR}/${MODULE}/phpunit.unit.xml ]] ; then
    cd $WEB_DIR
    cp app/code/${VENDOR}/${MODULE}/phpunit.unit.xml dev/tests/unit/phpunit.xml
    vendor/bin/phpunit -c ./dev/tests/unit/phpunit.xml
fi

if [[ ! -z "$RUN_YIREO_EXTENSIONCHECKER" && "$RUN_YIREO_EXTENSIONCHECKER" -eq 1 ]] ; then
    echo "Run Yireo ExtensionChecker"
    composer require yireo/magento2-extensionchecker:dev-master
    ./bin/magento module:enable Yireo_ExtensionChecker
    echo "Checking extension with Yireo_ExtensionChecker"
    ./bin/magento yireo_extensionchecker:scan ${VENDOR}_${MODULE} || exit 1
    echo "Done"
fi

if [[ ! -z "$RUN_PHPCS_EXTDN" && "$RUN_PHPCS_EXTDN" -eq 1 ]] ; then
    echo "Running ExtDN codesniffer"
    cd $WEB_DIR
    composer require magento/marketplace-eqp:dev-master
    composer require extdn/phpcs:dev-master
    vendor/bin/phpcs --standard=./vendor/extdn/phpcs/Extdn app/code/${VENDOR}/${MODULE}
fi

if [[ ! -z "$RUN_PHPCS_EQP2" && "$RUN_PHPCS_EQP2" -eq 1 ]] ; then
    echo "Running MEQP2 codesniffer"
    MEQP2_DIR=$WEB_DIR/meqp2
    mkdir $MEQP2_DIR
    cd $MEQP2_DIR
    composer create-project --repository=https://repo.magento.com magento/marketplace-eqp .
    vendor/bin/phpcs --config-set m2-path $WEB_DIR
    vendor/bin/phpcs $WEB_DIR/app/code/${VENDOR}/${MODULE} --standard=MEQP2 --severity=10 --extensions=php,phtml
fi

if [[ -f $WEB_DIR/app/code/${VENDOR}/${MODULE}/phpunit.integration.xml ]] ; then
    echo "Running PHPUnit Integration Tests"
    cd $WEB_DIR
    cp /shared/integration-tests/install-config-mysql.php $WEB_DIR/dev/tests/integration/etc
    cp $WEB_DIR/app/code/${VENDOR}/${MODULE}/phpunit.integration.xml $WEB_DIR/dev/tests/integration/phpunit.xml
    bin/magento dev:tests:run integration
fi