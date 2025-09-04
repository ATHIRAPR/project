#!/bin/bash
docker compose exec db bash -c 'mysql -u root -pmysql $MYSQL_DATABASE < /tmp/mysql-backup.sql'