## To Run,

```bash
cd docker
docker compose pull
docker compose up -d
# prepare data.sql file
./import-db.sh
# make necessary db connection changes in the app/ by referencing docker-compose.yml web container env variables (MYSQL_**)
# Check Ports tab next to terminal panel to access site and db from browser/localmachine.
```

## To Stop
```bash
docker compose down
```



- https://github.com/edhaase/vscode-devcontainer-php7-apache/tree/master
- https://qiita.com/Bana7/items/beca2d5a4cbef5b52368
- https://tbpgr.hatenablog.com/entry/2015/10/15/232937
- https://github.com/horatjp/devcontainer-php/tree/b5eafc225948bb65b6ac7cc62e30eb8ae76d0cb3
- https://github.com/shinsenter/php/tree/main/src/webapps/zz-fuelphp