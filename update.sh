#!/bin/bash

ISO_DATE=$(date -u +"%Y-%m-%dT%H:%M:%SZ")

# Ensure registry repository is up-to-date
git -C ../registry/ pull upstream master:master --quiet 2>&1

# Checkout master branch in dn42/repository
git -C ../registry/ checkout master --quiet

# Update with data from registry
php roagen.php
php rfc8416.php

# Ensure sub-repo is created to track roa file udpates 
if [ ! -d roa/ ] ; then mkdir roa ; fi
if [ ! -d roa/.git/ ] ; then
  git -C roa/ init              
  if [ ! -f roa/README.md ; then
    touch roa/README.md
    echo '## roas' | tee roa/README.md ; fi                
  git -C roa/ commit --allow-empty -m "Initial commit"
  git -C roa/ commit README.md -m "Add README.md" ; fi

# Write out last commit to file
echo "## Notes

- These files are Bird 1.x compatible:
  - bird_roa_dn42.conf
  - bird4_roa_dn42.conf
  - bird6_roa_dn42.conf
- These files are Bird 2.x compatible:
  - bird_route_dn42.conf
  - bird4_route_dn42.conf
  - bird6_route_dn42.conf

## Last commit

\`\`\`
$(git -C ../registry/ log -n 1)
\`\`\`" > roa/README.md

# Commit latest version of ROA files
git -C roa/ add README.md *.conf *.json
git -C roa/ commit -m "Updated ROA files - $ISO_DATE" --quiet

# Push ROA repository to every remote configured
for REMOTE in $(git -C roa/ remote | egrep -v upstream | paste -sd " " -) ; do git -C roa/ push $REMOTE master:master --quiet ; done

# Push local roagen repository to every remote configured 
for REMOTE in $(git remote | egrep -v upstream | paste -sd " " -) ; do git push $REMOTE master:master --quiet ; done
