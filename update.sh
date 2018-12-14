#!/bin/bash

ISO_DATE=$(date -u +"%Y-%m-%dT%H:%M:%SZ")

# Commit latest version of dn42-rpki-export.json
git add dn42-rpki-export.json
git commit dn42-rpki-export.json -m "Updated dn42-rpki-export.json - $ISO_DATE"

# Commit latest version of dn42-rfc8416-export.json
git add dn42-rfc8416-export.json
git commit dn42-rfc8416-export.json -m "Updated dn42-rfc8416-export.json - $ISO_DATE"

# Push repository to every remote configured
for REMOTE in $(git remote | paste -sd " " -) ; do git ps $REMOTE master ; done
