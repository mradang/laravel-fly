FROM mysql:8.3

ARG MYSQL_ROOT_PASSWORD

RUN mycnf=/root/.my.cnf && \
    echo '[client]' >$mycnf && \
    echo 'user=root' >>$mycnf && \
    echo "password=$MYSQL_ROOT_PASSWORD" >>$mycnf && \
    echo '[mysqldump]' >>$mycnf && \
    echo 'user=root' >>$mycnf && \
    echo "password=$MYSQL_ROOT_PASSWORD" >>$mycnf && \
    chmod 600 $mycnf

COPY init.sql /docker-entrypoint-initdb.d/init.sql
