#!/usr/bin/env bash
set -e

if [ -n "$MYSQL_PORT_3306_TCP" ]; then
	if [ -z "$SIMPLE_OA_DB_HOST" ]; then
		SIMPLE_OA_DB_HOST='mysql'
	else
		echo 2>&1 'warning: both SIMPLE_OA_DB_HOST and MYSQL_PORT_3306_TCP found'
		echo 2>&1 "  Connecting to SIMPLE_OA_DB_HOST ($SIMPLE_OA_DB_HOST)"
		echo 2>&1 '  instead of the linked mysql container'
	fi
fi

if [ -z "$SIMPLE_OA_DB_HOST" ]; then
	echo 2>&1 'error: missing SIMPLE_OA_DB_HOST and MYSQL_PORT_3306_TCP environment variables'
	echo 2>&1 '  Did you forget to --link some_mysql_container:mysql or set an external db'
	echo 2>&1 '  with -e SIMPLE_OA_DB_HOST=hostname:port?'
	exit 1
fi

# if we're linked to MySQL, and we're using the root user, and our linked
# container has a default "root" password set up and passed through... :)
: ${SIMPLE_OA_DB_USER:=root}
if [ "$SIMPLE_OA_DB_USER" = 'root' ]; then
	: ${SIMPLE_OA_DB_PASS:=$MYSQL_ENV_MYSQL_ROOT_PASSWORD}
fi
: ${SIMPLE_OA_DB_NAME:=simple_oa}

if [ -z "$SIMPLE_OA_DB_PASS" ]; then
	echo 2>&1 'error: missing required SIMPLE_OA_DB_PASS environment variable'
	echo 2>&1 '  Did you forget to -e SIMPLE_OA_DB_PASS=... ?'
	echo 2>&1
	echo 2>&1 '  (Also of interest might be SIMPLE_OA_DB_USER and SIMPLE_OA_DB_NAME.)'
	exit 1
fi

if ! [ -e index.php ]; then
	echo 2>&1 'error: missing SimpleOA files.'
	exit 1
fi

# Modify db config
(cat <<'EOPHP'
<?php
return array(
	'default' => 0,
	'pool' => array(
		array(
			'uri' => 'mysql://__mysqlurl__'
		),
	)
);
EOPHP
) > system/config/db.php
value="$SIMPLE_OA_DB_USER:$SIMPLE_OA_DB_PASS@$SIMPLE_OA_DB_HOST/$SIMPLE_OA_DB_NAME"
sed_escaped_value="$(echo "$value" | sed 's/[\/&]/\\&/g')"
sed -ri "s/__mysqlurl__/$sed_escaped_value/" system/config/db.php


TERM=dumb php -- "$SIMPLE_OA_DB_HOST" "$SIMPLE_OA_DB_USER" "$SIMPLE_OA_DB_PASS" "$SIMPLE_OA_DB_NAME" <<'EOPHP'
<?php
// database might not exist, so let's try creating it (just to be safe)
$stderr = fopen('php://stdout', 'w');
list($host, $port) = explode(':', $argv[1], 2);
$maxTries = 10;
do {
	$mysql = new mysqli($host, $argv[2], $argv[3], '', (int)$port);
	if ($mysql->connect_error) {
		fwrite($stderr, "\n" . 'MySQL Connection Error: (' . $mysql->connect_errno . ') ' . $mysql->connect_error . "\n");
		--$maxTries;
		if ($maxTries <= 0) {
			exit(1);
		}
		sleep(3);
	}
} while ($mysql->connect_error);
if (!$mysql->query('CREATE DATABASE IF NOT EXISTS `' . $mysql->real_escape_string($argv[4]) . '`')) {
	fwrite($stderr, "\n" . 'MySQL "CREATE DATABASE" Error: ' . $mysql->error . "\n");
	$mysql->close();
	exit(1);
}
// Import init data
if (file_exists('system/data/oa.sql')) {
    if (!$mysql->select_db($argv[4])) {
        fwrite($stderr, "\n" . 'Can not select database: '.$argv[4]."\n");
        $mysql->close();
        exit(1);
    }
    $mysql->query('BEGIN');
    $lines = explode(";\n", file_get_contents('system/data/oa.sql'));
    foreach ($lines as $line) {
        if (trim($line) == '') {
            continue;
        }
        if (!$mysql->query(trim($line))) {
            break;
        }
    }
    if ($mysql->errno && $mysqlError = $mysql->error) {
        $mysql->query('ROLLBACK');
        fwrite($stderr, "\n" . 'MySQL Import SQL Error: ' . $mysqlError . "\n");
        $mysql->close();
        exit(1);
    } else {
        $mysql->query('COMMIT');
    }
    if (!rename('system/data/oa.sql', 'system/data/oa.sql.bak')) {
        fwrite($stderr, "\n" . 'Can not remove SQL Script! '."\n");
        $mysql->close();
        exit(1);
    }
}
$mysql->close();
EOPHP

exec "$@"