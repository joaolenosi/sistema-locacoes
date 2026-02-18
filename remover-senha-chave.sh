#!/bin/bash

echo "=================================="
echo "🔓 Removendo senha da chave SSH"
echo "=================================="
echo ""

KEY_FILE="$HOME/.ssh/id_ed25519"

if [ ! -f "$KEY_FILE" ]; then
    echo "❌ Chave SSH não encontrada: $KEY_FILE"
    exit 1
fi

echo "📝 Removendo senha da chave SSH..."
echo "💡 Quando pedir a senha atual, digite a senha que você definiu"
echo "💡 Quando pedir a nova senha, pressione Enter duas vezes (deixa vazio)"
echo ""

# Remove a senha da chave (passphrase vazia)
ssh-keygen -p -f "$KEY_FILE"

if [ $? -eq 0 ]; then
    echo ""
    echo "✅ Senha removida com sucesso!"
    echo ""
    echo "🧪 Testando conexão..."
    ssh -o BatchMode=yes mobilelocacoes@mobilelocacoes.com "echo '✅ Conexão funcionando sem senha!'" 2>/dev/null
    
    if [ $? -eq 0 ]; then
        echo ""
        echo "🎉 Perfeito! Agora o deploy-diff.sh funcionará sem pedir senha!"
    else
        echo ""
        echo "⚠️  Teste manualmente: ssh mobilelocacoes@mobilelocacoes.com"
    fi
else
    echo ""
    echo "❌ Erro ao remover senha. Verifique se digitou a senha atual corretamente."
fi
