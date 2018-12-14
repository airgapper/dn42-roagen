## dn42-rpki-export.json-test

This repository is for testing purpose before deploying code into [master repository](https://git.dn42.us/netravnen/dn42-rpki-export.json)

### Requirements for running

1. Verify curl, git, bash, and php is installed.
2. `mkdir -p ~/dn42/`.
3. `cd ~/dn42/`.
4. `git clone https://git.dn42.us/netravnen/dn42-rpki-export.json-test`.
5. `git clone https://git.dn42.us/dn42/registry`.
6. Verify everything work by running `cd ~/dn42/dn42-rpki-export.json-test/ && php roagen.php`.
7. In $USER crontab file put `44 */3 * * * cd ~/dn42/dn42-rpki-export.json-test/ && php roagen.php`. Finetune time between runs to your liking.

NB: The roagen.php script is written with the paths to the dn42 registry folder being both git repositories reside in the same parent folder.
