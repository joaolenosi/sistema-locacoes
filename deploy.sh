#!/bin/bash

echo "📦 Compactando projeto..."

tar --exclude='vendor' \
    --exclude='.git' \
    --exclude='.github' \
    --exclude='.cursor' \
    --exclude='node_modules' \
    --exclude='tests' \
    --exclude='writable/cache/*' \
    --exclude='writable/logs/*' \
    -czf deploy.tar.gz .

echo "🚀 Enviando para servidor..."

scp deploy.tar.gz mobilelocacoes@mobilelocacoes.com:/home/mobilelocacoes/www/

echo "📂 Extraindo no servidor..."

ssh mobilelocacoes@mobilelocacoes.com << EOF
cd /home/mobilelocacoes/www
rm -rf sistema/*
tar -xzf deploy.tar.gz -C sistema
rm deploy.tar.gz
EOF

echo "✅ Deploy finalizado!"
