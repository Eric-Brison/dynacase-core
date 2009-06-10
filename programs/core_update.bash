#!/bin/bash

corepost=$WIFF_CONTEXT_ROOT"/CORE/CORE_post"

if [ ! -x $corepost ]; then
    echo "file $corepost not found or not executable" >&2
    exit 1
fi

export wpub=$WIFF_CONTEXT_ROOT # same as `wiff.php --getValue=rootdirectory`
. `dirname $0`/core_environment

$corepost U