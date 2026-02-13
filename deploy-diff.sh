#!/bin/bash

echo "=============================="
echo "🚀 DEPLOY SOMENTE ALTERAÇÕES"
echo "=============================="

FILES=$(git diff --name-only)

if [ -z "$FILES" ]; then
    echo "⚠ Nenhum arquivo modificado."
    exit 0
fi

echo ""
echo "📂 Arquivos alterados:"
echo "$FILES"

echo ""
echo "🗜 Compactando arquivos alterados..."

tar -czf deploy-diff.tar.gz $FILES

echo ""
echo "📤 Enviando pacote..."

scp deploy-diff.tar.gz mobilelocacoes@mobilelocacoes.com:/home/mobilelocacoes/

echo ""
echo "🔐 Extraindo no servidor..."

ssh mobilelocacoes@mobilelocacoes.com << 'EOF'

tar -xzf ~/deploy-diff.tar.gz -C /home/mobilelocacoes/www/sistema
rm ~/deploy-diff.tar.gz

echo "✅ Arquivos atualizados!"

EOF

rm deploy-diff.tar.gz

echo ""
echo "🎉 Deploy concluído!"
