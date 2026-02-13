#!/bin/bash

echo "=============================="
echo "🚀 DEPLOY SOMENTE ALTERAÇÕES"
echo "=============================="

# Lista arquivos modificados (não commitados)
FILES=$(git diff --name-only)

if [ -z "$FILES" ]; then
    echo "⚠ Nenhum arquivo modificado para enviar."
    exit 0
fi

echo ""
echo "📂 Arquivos que serão enviados:"
echo "$FILES"

echo ""
echo "📤 Enviando arquivos..."

for FILE in $FILES
do
    echo "Enviando $FILE"
    scp "$FILE" mobilelocacoes@mobilelocacoes.com:/home/mobilelocacoes/www/sistema/"$FILE"
done

echo ""
echo "✅ Deploy de alterações concluído!"
