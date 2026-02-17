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
echo "📤 Enviando pacote e extraindo no servidor..."

# Usa uma única conexão SSH para enviar arquivo e executar comandos
# Isso evita solicitar senha múltiplas vezes
# Se SSH_PASS estiver definida, usa sshpass para não solicitar senha
if [ -n "$SSH_PASS" ]; then
    # Usa sshpass se disponível e SSH_PASS estiver definida
    if command -v sshpass &> /dev/null; then
        cat deploy-diff.tar.gz | sshpass -p "$SSH_PASS" ssh mobilelocacoes@mobilelocacoes.com "
            cat > ~/deploy-diff.tar.gz &&
            tar -xzf ~/deploy-diff.tar.gz -C /home/mobilelocacoes/www/sistema &&
            rm ~/deploy-diff.tar.gz &&
            echo '✅ Arquivos atualizados!'
        "
    else
        echo "⚠ sshpass não encontrado. Instale com: sudo apt-get install sshpass (Linux) ou brew install hudochenkov/sshpass/sshpass (Mac)"
        echo "📝 Continuando sem sshpass (senha será solicitada uma vez)..."
        cat deploy-diff.tar.gz | ssh mobilelocacoes@mobilelocacoes.com "
            cat > ~/deploy-diff.tar.gz &&
            tar -xzf ~/deploy-diff.tar.gz -C /home/mobilelocacoes/www/sistema &&
            rm ~/deploy-diff.tar.gz &&
            echo '✅ Arquivos atualizados!'
        "
    fi
else
    # Conexão normal (senha será solicitada apenas uma vez)
    cat deploy-diff.tar.gz | ssh mobilelocacoes@mobilelocacoes.com "
        cat > ~/deploy-diff.tar.gz &&
        tar -xzf ~/deploy-diff.tar.gz -C /home/mobilelocacoes/www/sistema &&
        rm ~/deploy-diff.tar.gz &&
        echo '✅ Arquivos atualizados!'
    "
fi

rm deploy-diff.tar.gz

echo ""
echo "🎉 Deploy concluído!"
