#!/bin/bash

echo "=================================="
echo "🔑 Gerando chave SSH"
echo "=================================="
echo ""

# Verifica se já existe chave
if [ -f ~/.ssh/id_ed25519 ]; then
    echo "⚠️  Já existe uma chave SSH (id_ed25519)"
    read -p "Deseja sobrescrever? (s/N): " resposta
    if [[ ! "$resposta" =~ ^[Ss]$ ]]; then
        echo "❌ Operação cancelada."
        exit 0
    fi
fi

echo "📝 Gerando chave SSH ed25519..."
echo "💡 Pressione Enter quando pedir localização (usa padrão)"
echo "💡 Pressione Enter quando pedir senha (deixa sem senha para deploy automático)"
echo ""

ssh-keygen -t ed25519 -C "deploy-sistema-locacoes"

echo ""
echo "✅ Chave SSH gerada com sucesso!"
echo ""
echo "📋 Sua chave pública:"
echo "=================================="
cat ~/.ssh/id_ed25519.pub
echo "=================================="
echo ""
echo "📤 Próximo passo: Copie a chave para o servidor"
echo ""
echo "🚀 Opção mais fácil (recomendado):"
echo "  ./copiar-chave-ssh.sh"
echo ""
echo "📝 Ou manualmente:"
echo "  1. ssh mobilelocacoes@mobilelocacoes.com"
echo "  2. mkdir -p ~/.ssh && chmod 700 ~/.ssh"
echo "  3. nano ~/.ssh/authorized_keys"
echo "  4. Cole a chave pública acima"
echo "  5. chmod 600 ~/.ssh/authorized_keys"
echo ""
