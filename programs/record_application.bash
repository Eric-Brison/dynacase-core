#!/bin/bash

export wpub=$WIFF_CONTEXT_ROOT # same as `wiff.php --getValue=rootdirectory`
. `dirname $0`/core_environment
$wpub/wsh.php  --api=appadmin --method=update --appname=$1
