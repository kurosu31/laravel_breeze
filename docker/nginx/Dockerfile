FROM nginx:1.18-alpine

# 環境変数（TZ）を定義
ENV TZ=UTC

# nginx config file  Nginxの設定ファイルをコンテナ内にコピーして対応づける
COPY ./docker/nginx/*.conf /etc/nginx/conf.d/

WORKDIR /var/www/html