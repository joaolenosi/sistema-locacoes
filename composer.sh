#!/bin/bash

echo "🔐 Conectando ao servidor..."

ssh mobilelocacoes@mobilelocacoes.com << 'EOF'

echo "📂 Indo para home..."
cd ~

# Verifica se composer existe
if [ ! -f composer.phar ]; then
    echo "⬇ Composer não encontrado. Instalando..."
    curl -sS https://getcomposer.org/installer | php
else
    echo "✅ Composer já instalado."
fi

echo "📂 Entrando na pasta do sistema..."
cd /home/mobilelocacoes/www/sistema

echo "📦 Rodando composer install..."
php ~/composer.phar install --no-dev

echo "🧹 Limpando cache..."
php ~/composer.phar clear-cache

echo "✅ Processo finalizado!"

EOF

echo "🚀 Deploy composer concluído!"
