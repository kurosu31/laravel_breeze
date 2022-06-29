# ○事前準備
# Github
# 作業ディレクトリにGitを使う宣言をする
  git init
# ターミナルにてgithubで登録したユーザー名、emailを登録
  git config --global user.name "登録している名前"
  git config --global user.email "登録しているemail"
# gitでファストフォアードを防ぐ
  git config --global merge.ff false
# pullの時にリベースをする
  git config --global pull.rebase merges
# 設定を確認
  git config --list
