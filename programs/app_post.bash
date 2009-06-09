#!/bin/bash

set -e

if [ -z "$WIFF_CONTEXT_ROOT" ]; then
    echo "WIFF_CONTEXT_ROOT is not set!" 1>&2
    exit 1
fi

cd "$WIFF_CONTEXT_ROOT"

export wpub=$WIFF_CONTEXT_ROOT
. "$WIFF_CONTEXT_ROOT"/programs/core_environnement

exec "./${1}/${1}_post" "$2"