#!/bin/bash

ISO_DATE=$(date -u +"%Y-%m-%dT%H:%M:%SZ")

# Commit latest version of ROA files
git add roa/*
git commit roa/* -m "Updated ROA files - $ISO_DATE"

# Commit latest version of dn42-rfc8416-export.json
git add dn42-rfc8416-export.json
git commit dn42-rfc8416-export.json -m "Updated dn42-rfc8416-export.json - $ISO_DATE"

# Push repository to every remote configured
for REMOTE in $(git remote | paste -sd " " -) ; do git ps $REMOTE master ; done
