## dn42-roagen

### Requirements for running

1. Verify curl, git, bash, and php is installed.
2. `mkdir -p ~/dn42/`.
3. `cd ~/dn42/`.
4. `git clone https://git.dn42.us/netravnen/dn42-roagen.git roagen`.
5. `git clone https://git.dn42.us/netravnen/dn42-rpki-export.json.git roagen/roa`.
6. `git clone https://git.dn42.us/dn42/registry.git`.
6. `git -C registry/ remote rename origin upstream && git -C registry/ fetch --all`
7. Verify everything work by running `cd ~/dn42/roagen/ && ./update.sh`.
8. In $USER crontab file put `44 */3 * * * cd ~/dn42/roagen/ && ./update.sh`. Finetune time between runs to your liking.

NB: The roagen.php script is written with the paths to the dn42 registry folder being both git repositories reside in the same parent folder.

```
$ tree -L 3 ~/dn42/
/home/$USER/dn42/
|-- registry
|   |-- README.md
|   |-- check-my-stuff
|   |-- check-pol
|   |-- check-remote
|   |-- data
|   |   |-- as-block
|   |   |-- as-set
|   |   |-- aut-num
|   |   |-- dns
|   |   |-- filter.txt
|   |   |-- filter6.txt
|   |   |-- inet6num
|   |   |-- inetnum
|   |   |-- key-cert
|   |   |-- mntner
|   |   |-- organisation
|   |   |-- person
|   |   |-- registry
|   |   |-- role
|   |   |-- route
|   |   |-- route-set
|   |   |-- route6
|   |   |-- schema
|   |   |-- tinc-key
|   |   `-- tinc-keyset
|   |-- fmt-my-stuff
|   |-- install-commit-hook
|   `-- utils
|       `-- schema-check
`-- roagen
    |-- README.md
    |-- lib
    |   |-- define.php
    |   `-- functions.php
    |-- rfc8416.php
    |-- roa
    |   |-- README.md
    |   |-- bird4_roa_dn42.conf
    |   |-- bird4_route_dn42.conf
    |   |-- bird6_roa_dn42.conf
    |   |-- bird6_route_dn42.conf
    |   |-- bird_roa_dn42.conf
    |   |-- bird_route_dn42.conf
    |   |-- export_dn42.json
    |   `-- export_rfc8416_dn42.json
    |-- roagen.php
    `-- update.sh
```
