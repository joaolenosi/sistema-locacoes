# 🔐 Configurar Autenticação SSH por Chave

Este guia explica como configurar autenticação SSH por chave para que o script `deploy-diff.sh` não solicite senha.

---

## 📋 Pré-requisitos

- Git Bash instalado (Windows)
- Acesso SSH ao servidor com senha (pelo menos uma vez)

---

## 🔑 Passo 1: Verificar se já existe uma chave SSH

Abra o **Git Bash** e execute:

```bash
ls -la ~/.ssh
```

Se você vir arquivos como `id_rsa` e `id_rsa.pub` (ou `id_ed25519` e `id_ed25519.pub`), você já tem uma chave SSH. Pule para o **Passo 3**.

Se não existir nenhuma chave, continue para o **Passo 2**.

---

## 🔑 Passo 2: Gerar uma nova chave SSH

No **Git Bash**, execute:

```bash
ssh-keygen -t ed25519 -C "seu-email@exemplo.com"
```

**OU** se seu sistema não suportar ed25519:

```bash
ssh-keygen -t rsa -b 4096 -C "seu-email@exemplo.com"
```

Quando solicitado:
- **Localização do arquivo**: Pressione `Enter` para usar o local padrão (`~/.ssh/id_ed25519` ou `~/.ssh/id_rsa`)
- **Senha**: Você pode pressionar `Enter` para não usar senha, ou criar uma senha para maior segurança

---

## 📤 Passo 3: Copiar a chave pública para o servidor

### Opção A: Usando ssh-copy-id (Recomendado - mais fácil)

```bash
ssh-copy-id mobilelocacoes@mobilelocacoes.com
```

Se `ssh-copy-id` não estiver disponível no Windows, use a **Opção B**.

### Opção B: Copiar manualmente

1. **Exibir sua chave pública:**

```bash
cat ~/.ssh/id_ed25519.pub
```

**OU** se você usou RSA:

```bash
cat ~/.ssh/id_rsa.pub
```

2. **Copie todo o conteúdo** que aparecer (começa com `ssh-ed25519` ou `ssh-rsa`)

3. **Conecte ao servidor:**

```bash
ssh mobilelocacoes@mobilelocacoes.com
```

4. **No servidor, execute:**

```bash
mkdir -p ~/.ssh
chmod 700 ~/.ssh
nano ~/.ssh/authorized_keys
```

5. **Cole sua chave pública** no arquivo `authorized_keys` (uma chave por linha)

6. **Salve e saia** do editor (Ctrl+X, depois Y, depois Enter)

7. **Defina as permissões corretas:**

```bash
chmod 600 ~/.ssh/authorized_keys
```

8. **Saia do servidor:**

```bash
exit
```

---

## ✅ Passo 4: Testar a conexão

No **Git Bash**, execute:

```bash
ssh mobilelocacoes@mobilelocacoes.com
```

Se tudo estiver configurado corretamente, você **não será solicitado a digitar a senha** e entrará diretamente no servidor.

---

## 🚀 Passo 5: Usar o deploy-diff.sh sem senha

Agora você pode executar o script normalmente:

```bash
./deploy-diff.sh
```

O script não solicitará mais senha! 🎉

---

## 🔧 Configuração Avançada (Opcional)

Se você quiser usar uma chave SSH específica ou configurar um alias, crie/edite o arquivo `~/.ssh/config`:

```bash
nano ~/.ssh/config
```

Adicione:

```
Host mobilelocacoes
    HostName mobilelocacoes.com
    User mobilelocacoes
    IdentityFile ~/.ssh/id_ed25519
    IdentitiesOnly yes
```

Agora você pode usar `ssh mobilelocacoes` em vez de `ssh mobilelocacoes@mobilelocacoes.com`.

---

## ❓ Solução de Problemas

### Erro: "Permission denied (publickey)"

1. Verifique se a chave pública está no servidor:
   ```bash
   ssh mobilelocacoes@mobilelocacoes.com "cat ~/.ssh/authorized_keys"
   ```

2. Verifique as permissões no servidor:
   ```bash
   ssh mobilelocacoes@mobilelocacoes.com "ls -la ~/.ssh"
   ```
   - `~/.ssh` deve ter permissão `700` (drwx------)
   - `~/.ssh/authorized_keys` deve ter permissão `600` (-rw-------)

### Erro: "Host key verification failed"

Execute:
```bash
ssh-keygen -R mobilelocacoes.com
```

Depois tente conectar novamente.

### Ainda pede senha mesmo após configurar

1. Verifique se está usando a chave correta:
   ```bash
   ssh -v mobilelocacoes@mobilelocacoes.com
   ```
   (O `-v` mostra informações detalhadas de debug)

2. Verifique se o servidor aceita autenticação por chave:
   ```bash
   ssh mobilelocacoes@mobilelocacoes.com "grep PubkeyAuthentication /etc/ssh/sshd_config"
   ```
   Deve retornar `PubkeyAuthentication yes`

---

## 🔒 Segurança

- **Nunca compartilhe sua chave privada** (`id_rsa` ou `id_ed25519`)
- A chave privada deve ter permissão `600` (`-rw-------`)
- Se você usou uma senha na chave, precisará digitá-la na primeira conexão de cada sessão
- Considere usar uma senha na chave SSH para maior segurança

---

## 📚 Referências

- [Documentação oficial do GitHub sobre chaves SSH](https://docs.github.com/en/authentication/connecting-to-github-with-ssh)
- [Guia SSH do GitLab](https://docs.gitlab.com/ee/ssh/)
