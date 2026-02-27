#!/bin/bash

echo "=============================="
echo "🚀 DEPLOY SOMENTE ALTERAÇÕES"
echo "=============================="

# Se DEPLOY_FILES foi definido (ex.: pelo hook pre-push), usa essa lista
if [ -n "$DEPLOY_FILES" ]; then
    FILES=$(echo "$DEPLOY_FILES" | grep -v '^$' | sort -u)
else
    # Pega arquivos modificados e criados (não rastreados)
    MODIFIED=$(git diff --name-only)
    UNTRACKED=$(git ls-files --others --exclude-standard)
    # Combina ambos os tipos de arquivos
    FILES=$(echo -e "$MODIFIED\n$UNTRACKED" | grep -v '^$' | sort -u)
fi

if [ -z "$FILES" ]; then
    echo "⚠ Nenhum arquivo modificado ou criado."
    exit 0
fi

echo ""
echo "📂 Arquivos alterados/criados:"
echo "$FILES"

echo ""
echo "🔍 Verificando arquivos existentes no disco..."

EXISTING_FILES_ARRAY=()
while IFS= read -r f; do
  [ -z "$f" ] && continue
  if [ -e "$f" ]; then
    EXISTING_FILES_ARRAY+=("$f")
  else
    echo "⚠ Arquivo removido ou inexistente, ignorando: $f"
  fi
done <<< "$FILES"

if [ ${#EXISTING_FILES_ARRAY[@]} -eq 0 ]; then
  echo "⚠ Nenhum arquivo existente para compactar (todos foram removidos)."
  exit 0
fi

echo ""
echo "🗜 Compactando arquivos alterados..."

tar -czf deploy-diff.tar.gz "${EXISTING_FILES_ARRAY[@]}"

echo ""
echo "📤 Enviando pacote e extraindo no servidor..."

# Configuração SSH
SSH_USER="mobilelocacoes"
SSH_HOST="mobilelocacoes.com"
SSH_TARGET="${SSH_USER}@${SSH_HOST}"

# Detecta chave SSH disponível (prioridade: ed25519 > rsa)
SSH_KEY=""
if [ -f ~/.ssh/id_ed25519 ]; then
    SSH_KEY="~/.ssh/id_ed25519"
elif [ -f ~/.ssh/id_rsa ]; then
    SSH_KEY="~/.ssh/id_rsa"
fi

# Monta comando SSH com chave se disponível
SSH_CMD="ssh"
if [ -n "$SSH_KEY" ]; then
    SSH_CMD="ssh -i $SSH_KEY"
fi

# Usa uma única conexão SSH para enviar arquivo e executar comandos
# Isso evita solicitar senha múltiplas vezes
# Prioridade: 1) Chave SSH (se configurada), 2) sshpass (se SSH_PASS definida), 3) Senha interativa
if [ -n "$SSH_KEY" ]; then
    # Usa chave SSH (não solicita senha)
    cat deploy-diff.tar.gz | $SSH_CMD -o StrictHostKeyChecking=no "$SSH_TARGET" "
        cat > ~/deploy-diff.tar.gz &&
        tar -xzf ~/deploy-diff.tar.gz -C /home/mobilelocacoes/www/sistema &&
        rm ~/deploy-diff.tar.gz &&
        echo '✅ Arquivos atualizados!'
    "
elif [ -n "$SSH_PASS" ]; then
    # Usa sshpass se disponível e SSH_PASS estiver definida
    if command -v sshpass &> /dev/null; then
        cat deploy-diff.tar.gz | sshpass -p "$SSH_PASS" ssh "$SSH_TARGET" "
            cat > ~/deploy-diff.tar.gz &&
            tar -xzf ~/deploy-diff.tar.gz -C /home/mobilelocacoes/www/sistema &&
            rm ~/deploy-diff.tar.gz &&
            echo '✅ Arquivos atualizados!'
        "
    else
        echo "⚠ sshpass não encontrado. Instale com: sudo apt-get install sshpass (Linux) ou brew install hudochenkov/sshpass/sshpass (Mac)"
        echo "💡 Dica: Configure chave SSH para não precisar de senha. Veja CONFIGURAR_SSH.md"
        echo "📝 Continuando sem sshpass (senha será solicitada uma vez)..."
        cat deploy-diff.tar.gz | ssh "$SSH_TARGET" "
            cat > ~/deploy-diff.tar.gz &&
            tar -xzf ~/deploy-diff.tar.gz -C /home/mobilelocacoes/www/sistema &&
            rm ~/deploy-diff.tar.gz &&
            echo '✅ Arquivos atualizados!'
        "
    fi
else
    # Conexão normal (senha será solicitada apenas uma vez)
    echo "💡 Dica: Configure chave SSH para não precisar de senha. Veja CONFIGURAR_SSH.md"
    cat deploy-diff.tar.gz | ssh "$SSH_TARGET" "
        cat > ~/deploy-diff.tar.gz &&
        tar -xzf ~/deploy-diff.tar.gz -C /home/mobilelocacoes/www/sistema &&
        rm ~/deploy-diff.tar.gz &&
        echo '✅ Arquivos atualizados!'
    "
fi

rm deploy-diff.tar.gz

echo ""
echo "🎉 Deploy concluído!"
