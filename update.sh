#!/bin/bash

# Push repository to every remote configured
for REMOTE in $(git remote | paste -sd   -) ; do git ps $REMOTE master ; done

