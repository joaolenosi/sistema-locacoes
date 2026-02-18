#!/bin/bash

echo "=================================="
echo "📤 Copiando chave SSH para o servidor"
echo "=================================="
echo ""

SSH_USER="mobilelocacoes"
SSH_HOST="mobilelocacoes.com"
SSH_TARGET="${SSH_USER}@${SSH_HOST}"

# Verifica se a chave existe
if [ ! -f ~/.ssh/id_ed25519.pub ]; then
    echo "❌ Chave pública não encontrada!"
    echo "💡 Execute primeiro: ./gerar-chave-ssh.sh"
    exit 1
fi

echo "📋 Sua chave pública:"
cat ~/.ssh/id_ed25519.pub
echo ""
echo ""

# Tenta usar ssh-copy-id primeiro (se disponível)
if command -v ssh-copy-id &> /dev/null; then
    echo "✅ Usando ssh-copy-id (método automático)..."
    ssh-copy-id -i ~/.ssh/id_ed25519.pub "$SSH_TARGET"
    
    if [ $? -eq 0 ]; then
        echo ""
        echo "✅ Chave copiada com sucesso!"
        echo ""
        echo "🧪 Testando conexão..."
        ssh -o BatchMode=yes "$SSH_TARGET" "echo '✅ Conexão SSH funcionando sem senha!'" 2>/dev/null
        
        if [ $? -eq 0 ]; then
            echo ""
            echo "🎉 Tudo pronto! Agora você pode usar ./deploy-diff.sh sem senha!"
        else
            echo ""
            echo "⚠️  A chave foi copiada, mas ainda pode pedir senha na primeira conexão."
            echo "💡 Tente conectar manualmente: ssh $SSH_TARGET"
        fi
        exit 0
    fi
fi

# Se ssh-copy-id não funcionou, usa método manual
echo "📝 ssh-copy-id não disponível. Usando método manual..."
echo ""

PUBLIC_KEY=$(cat ~/.ssh/id_ed25519.pub)

echo "Conectando ao servidor para adicionar a chave..."
echo "💡 Você precisará digitar a senha UMA ÚLTIMA VEZ"
echo ""

# Cria o comando que será executado no servidor
ssh "$SSH_TARGET" "
    mkdir -p ~/.ssh
    chmod 700 ~/.ssh
    echo '$PUBLIC_KEY' >> ~/.ssh/authorized_keys
    chmod 600 ~/.ssh/authorized_keys
    echo '✅ Chave SSH adicionada com sucesso!'
"

if [ $? -eq 0 ]; then
    echo ""
    echo "✅ Chave copiada com sucesso!"
    echo ""
    echo "🧪 Testando conexão (não deve pedir senha)..."
    ssh -o BatchMode=yes "$SSH_TARGET" "echo '✅ Conexão SSH funcionando sem senha!'" 2>/dev/null
    
    if [ $? -eq 0 ]; then
        echo ""
        echo "🎉 Tudo pronto! Agora você pode usar ./deploy-diff.sh sem senha!"
    else
        echo ""
        echo "⚠️  A chave foi copiada. Tente conectar manualmente:"
        echo "   ssh $SSH_TARGET"
        echo ""
        echo "Se ainda pedir senha, verifique as permissões no servidor."
    fi
else
    echo ""
    echo "❌ Erro ao copiar a chave."
    echo ""
    echo "💡 Você pode fazer manualmente:"
    echo "   1. ssh $SSH_TARGET"
    echo "   2. mkdir -p ~/.ssh && chmod 700 ~/.ssh"
    echo "   3. nano ~/.ssh/authorized_keys"
    echo "   4. Cole esta chave:"
    echo ""
    echo "$PUBLIC_KEY"
    echo ""
    echo "   5. chmod 600 ~/.ssh/authorized_keys"
    echo "   6. exit"
fi
