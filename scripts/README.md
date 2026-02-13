# Scripts de Deploy

## deploy-ftp.php

Script PHP para fazer deploy dos arquivos para o servidor FTP.

### Uso Local

1. **Instalar extensão FTP do PHP** (se necessário):
   ```bash
   # Ubuntu/Debian
   sudo apt-get install php-ftp
   
   # Ou habilitar no php.ini
   extension=ftp
   ```

2. **Definir senha FTP**:
   ```bash
   export FTP_PASSWORD='sua_senha_ftp'
   ```

3. **Executar o script**:
   ```bash
   php scripts/deploy-ftp.php
   ```

### Uso via GitHub Actions

O workflow `deploy-php.yml` executa automaticamente este script quando há push para a branch `main`.

### Configuração

Edite as configurações no início do arquivo `deploy-ftp.php`:

```php
$ftpConfig = [
    'server' => 'mobilelocacoes.com',
    'username' => 'mobilelocacoes',
    'password' => getenv('FTP_PASSWORD') ?: '',
    'port' => 21,
    'useSSL' => true,
    'passive' => true,
    'serverDir' => '/www/painel/', // Ajuste conforme necessário
];
```

### Ajustar Caminho do Servidor

Se o caminho `/www/painel/` não funcionar, tente:

- `./painel/` - Caminho relativo
- `public_html/painel/` - Para cPanel
- `htdocs/painel/` - Para alguns servidores
- `www/painel/` - Sem barra inicial

### Exclusões

Os seguintes arquivos/pastas são automaticamente excluídos:
- `.git`, `.github`, `.cursor`
- `.env`, `vendor`, `node_modules`
- `.vscode`, `.idea`, `tests`
- Arquivos de documentação e configuração

Para adicionar mais exclusões, edite o array `$exclude` no script.

### Vantagens sobre FTP-Deploy-Action

1. ✅ Mais controle sobre o processo
2. ✅ Melhor tratamento de erros
3. ✅ Pode ser executado localmente
4. ✅ Logs mais detalhados
5. ✅ Criação automática de diretórios
6. ✅ Pode ser facilmente customizado

### Troubleshooting

**Erro de conexão:**
- Verifique se a extensão FTP está habilitada no PHP
- Verifique se o servidor e porta estão corretos
- Tente desabilitar SSL temporariamente (`'useSSL' => false`)

**Erro de permissão:**
- Verifique se o caminho do servidor está correto
- Tente criar o diretório manualmente via FTP primeiro
- Verifique permissões do usuário FTP

**Arquivos não são enviados:**
- Verifique se não estão na lista de exclusões
- Verifique os logs do script para mais detalhes
