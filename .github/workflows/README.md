# Configuração do Deploy Automático

Este diretório contém os workflows do GitHub Actions para deploy automático da aplicação.

## Workflow de Deploy FTP

O arquivo `deploy.yml` configura o deploy automático para o servidor FTP sempre que houver um commit na branch `main`.

### Configuração Necessária

Antes de usar o workflow, você precisa configurar os secrets do GitHub:

#### 1. Acesse as configurações do repositório
- Vá para o seu repositório no GitHub
- Clique em **Settings** (Configurações)
- No menu lateral, clique em **Secrets and variables** > **Actions**

#### 2. Adicione os secrets necessários

**FTP_PASSWORD** (obrigatório):
- Clique em **New repository secret**
- **Name**: `FTP_PASSWORD`
- **Secret**: `Lun@0712`
- Clique em **Add secret**

**SSH_HOST** (obrigatório):
- Clique em **New repository secret**
- **Name**: `SSH_HOST`
- **Secret**: O hostname ou IP do servidor SSH (ex: `ftp.guinesoftware.com.br` ou `guinesoftware.com.br`)
- Clique em **Add secret**

**SSH_USER** (obrigatório):
- Clique em **New repository secret**
- **Name**: `SSH_USER`
- **Secret**: O usuário SSH (geralmente o mesmo do FTP: `guinesoftware`)
- Clique em **Add secret**

**SSH_PASSWORD** (obrigatório):
- Clique em **New repository secret**
- **Name**: `SSH_PASSWORD`
- **Secret**: A senha SSH (pode ser a mesma do FTP ou diferente)
- Clique em **Add secret**

**SSH_PORT** (opcional):
- Clique em **New repository secret**
- **Name**: `SSH_PORT`
- **Secret**: A porta SSH (padrão: `22`)
- Clique em **Add secret**
- Se não configurar, será usada a porta padrão 22

### Como Funciona

1. **Trigger**: O workflow é executado automaticamente quando você faz push para a branch `main`

2. **Etapas do Deploy**
   - Checkout do código do repositório
   - Upload dos arquivos da pasta `sistema/` para o servidor FTP em `/www/sistema/` (sem a pasta `vendor/`)
   - Conexão SSH ao servidor
   - Execução de `composer install --no-dev --optimize-autoloader` diretamente no servidor

3. **Arquivos Excluídos**: O workflow exclui automaticamente do upload FTP:
   - Arquivos do Git (`.git/`, `.github/`)
   - Arquivos de teste (`tests/`)
   - Arquivos de ambiente (`.env`)
   - Arquivos de IDE (`.vscode/`, `.idea/`)
   - Arquivos de documentação (`README.md`)
   - Arquivos de configuração de testes (`phpunit.xml.dist`)
   - Pasta `vendor/` (será gerada no servidor via SSH)

### Verificação do Deploy

Após fazer um commit na branch `main`:
1. Vá para a aba **Actions** no GitHub
2. Clique no workflow em execução para ver os logs
3. Verifique se todas as etapas foram concluídas com sucesso
4. Confirme no servidor FTP que os arquivos foram enviados corretamente

### Credenciais FTP

- **Host**: ftp.guinesoftware.com.br
- **Port**: 21
- **Username**: guinesoftware
- **Password**: Configurado como secret `FTP_PASSWORD`
- **Diretório remoto**: `/www/sistema/`

### Credenciais SSH

- **Host**: Configurado como secret `SSH_HOST`
- **Port**: Configurado como secret `SSH_PORT` (padrão: 22)
- **Username**: Configurado como secret `SSH_USER`
- **Password**: Configurado como secret `SSH_PASSWORD`
- **Diretório de execução**: `/www/sistema/`

**Nota**: O `composer install` é executado diretamente no servidor via SSH após o upload dos arquivos via FTP. Isso garante que as dependências sejam instaladas usando o PHP já configurado na hospedagem.
