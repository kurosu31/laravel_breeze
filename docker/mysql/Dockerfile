# M1Macのプラットフォームはarm64
# MySQLのDockerイメージのサポートプラットフォームはamd64
# Dockerイメージをプルする際に実行環境のプラットフォームに合わせて自動的にamd64のイメージを取得するのでビルドするとエラーになる
FROM --platform=linux/x86_64 mysql:8.0 

# 環境変数（TZ）を定義
ENV TZ=UTC
# MySQLの設定ファイルをコンテナ内にコピーして対応づける
COPY ./docker/mysql/my.cnf /etc/my.cnf