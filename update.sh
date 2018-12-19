#!/bin/bash

ISO_DATE=$(date -u +"%Y-%m-%dT%H:%M:%SZ")

# Commit latest version of ROA files
git add roa/* --quiet
git commit roa/* -m "Updated ROA files - $ISO_DATE" --quiet

# Push repository to every remote configured
for REMOTE in $(git remote | egrep -v upstream | paste -sd " " -) ; do git push $REMOTE master:master --quiet ; done
