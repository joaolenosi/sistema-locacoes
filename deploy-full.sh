#!/bin/bash

echo "=============================="
echo "🚀 INICIANDO DEPLOY COMPLETO"
echo "=============================="

# echo ""
# echo "📦 Rodando composer local..."
#composer install --no-dev

echo ""
echo "🗜 Compactando projeto..."

tar --exclude='.git' \
    --exclude='.github' \
    --exclude='.cursor' \
    --exclude='node_modules' \
    --exclude='tests' \
    --exclude='writable/cache/*' \
    --exclude='writable/logs/*' \
    -czf deploy.tar.gz .

echo ""
echo "📤 Enviando para servidor..."

scp deploy.tar.gz mobilelocacoes@mobilelocacoes.com:/home/mobilelocacoes/

echo ""
echo "🔐 Executando comandos no servidor..."

ssh mobilelocacoes@mobilelocacoes.com << 'EOF'

echo "📂 Indo para home..."
cd ~

# Instala composer se não existir
if [ ! -f composer.phar ]; then
    echo "⬇ Instalando Composer..."
    curl -sS https://getcomposer.org/installer | php
else
    echo "✅ Composer já existe."
fi

echo "📂 Preparando pasta do sistema..."
rm -rf /home/mobilelocacoes/www/sistema/*
tar -xzf ~/deploy.tar.gz -C /home/mobilelocacoes/www/sistema

echo "📦 Rodando composer no servidor..."
cd /home/mobilelocacoes/www/sistema
php ~/composer.phar install --no-dev --optimize-autoloader

echo "🧹 Limpando arquivo temporário..."
rm ~/deploy.tar.gz

echo "✅ DEPLOY FINALIZADO COM SUCESSO!"

EOF

echo ""
echo "🎉 PROCESSO COMPLETO!"
