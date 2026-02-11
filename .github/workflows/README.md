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

#### 2. Adicione o secret FTP_PASSWORD
- Clique em **New repository secret**
- **Name**: `FTP_PASSWORD`
- **Secret**: `Lun@0712`
- Clique em **Add secret**

### Como Funciona

1. **Trigger**: O workflow é executado automaticamente quando você faz push para a branch `main`

2. **Etapas do Deploy**:
   - Checkout do código do repositório
   - Configuração do ambiente PHP 8.0
   - Instalação das dependências do Composer na pasta `sistema/` (apenas produção)
   - Upload dos arquivos da pasta `sistema/` para o servidor FTP em `/www/sistema/`

3. **Arquivos Excluídos**: O workflow exclui automaticamente:
   - Arquivos do Git (`.git/`, `.github/`)
   - Arquivos de teste (`tests/`)
   - Arquivos de ambiente (`.env`)
   - Arquivos de IDE (`.vscode/`, `.idea/`)
   - Arquivos de documentação (`README.md`)
   - Arquivos de configuração de testes (`phpunit.xml.dist`)

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
