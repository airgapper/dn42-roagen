#!/bin/bash

ISO_DATE=$(date -u +"%Y-%m-%dT%H:%M:%SZ")

# Ensure registry repository is up-to-date
git -C ../registry/ pull upstream master:master --quiet 2>&1

# Checkout master branch in dn42/repository
git -C ../registry/ checkout master --quiet

# Update with data from registry
php roagen.php
php rfc8416.php

# Write out last commit to file
echo "## Last commit

\`\`\`
$(git -C ../registry/ show)
\`\`\`" > roa/README.md

# Commit latest version of ROA files
git add roa/*
git commit roa/* -m "Updated ROA files - $ISO_DATE" --quiet

# Push repository to every remote configured
for REMOTE in $(git remote | egrep -v upstream | paste -sd " " -) ; do git push $REMOTE master:master --quiet ; done
