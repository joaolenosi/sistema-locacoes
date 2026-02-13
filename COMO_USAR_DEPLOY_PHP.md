# Como Usar o Script de Deploy PHP

## ✅ Solução Criada

Criei um script PHP customizado que faz o deploy via FTP com mais controle e flexibilidade que o FTP-Deploy-Action.

## 📁 Arquivos Criados

1. **`scripts/deploy-ftp.php`** - Script principal de deploy
2. **`.github/workflows/deploy-php.yml`** - Workflow do GitHub Actions
3. **`scripts/README.md`** - Documentação do script

## 🚀 Como Usar

### Opção 1: Via GitHub Actions (Automático)

O workflow `deploy-php.yml` executa automaticamente quando você faz push para `main`.

**Para ativar:**
1. Faça commit dos arquivos:
   ```bash
   git add scripts/ .github/workflows/deploy-php.yml
   git commit -m "Add: Script PHP para deploy via FTP"
   git push origin main
   ```

2. O deploy será executado automaticamente!

### Opção 2: Executar Localmente

1. **Instalar extensão FTP do PHP** (se necessário):
   ```bash
   # Windows (via XAMPP/WAMP)
   # Edite php.ini e descomente: extension=ftp
   
   # Linux
   sudo apt-get install php-ftp
   ```

2. **Definir senha FTP**:
   ```bash
   # Windows PowerShell
   $env:FTP_PASSWORD="sua_senha"
   
   # Linux/Mac
   export FTP_PASSWORD='sua_senha'
   ```

3. **Executar o script**:
   ```bash
   php scripts/deploy-ftp.php
   ```

## ⚙️ Configuração

### Ajustar Caminho do Servidor

Edite o arquivo `scripts/deploy-ftp.php` e altere a linha:

```php
'serverDir' => './painel/', // Ajuste conforme necessário
```

**Opções para testar:**

1. **Caminho relativo** (padrão):
   ```php
   'serverDir' => './painel/',
   ```

2. **Caminho absoluto**:
   ```php
   'serverDir' => '/www/painel/',
   ```

3. **Para cPanel**:
   ```php
   'serverDir' => 'public_html/painel/',
   ```

4. **Para outros servidores**:
   ```php
   'serverDir' => 'htdocs/painel/',
   ```

### Outras Configurações

No início do arquivo `deploy-ftp.php`:

```php
$ftpConfig = [
    'server' => 'mobilelocacoes.com',
    'username' => 'mobilelocacoes',
    'password' => getenv('FTP_PASSWORD') ?: '',
    'port' => 21,
    'useSSL' => true,      // Tente false se houver problemas
    'passive' => true,     // Mantenha true para a maioria dos servidores
    'serverDir' => './painel/',
];
```

## 🔍 Troubleshooting

### Erro: "Senha FTP não fornecida"

**Solução:**
- Defina a variável de ambiente `FTP_PASSWORD`
- Ou edite o script e coloque a senha diretamente (não recomendado para produção)

### Erro: "Não foi possível conectar ao servidor FTP"

**Soluções:**
1. Verifique se a extensão FTP está habilitada no PHP
2. Verifique se o servidor e porta estão corretos
3. Tente desabilitar SSL: `'useSSL' => false`

### Erro: "Não foi possível acessar o diretório remoto"

**Soluções:**
1. Tente diferentes caminhos (veja seção "Ajustar Caminho do Servidor")
2. Conecte via FTP manualmente e veja qual diretório aparece
3. Tente criar o diretório `painel/` manualmente primeiro

### Erro: "Falha no upload"

**Soluções:**
1. Verifique permissões do usuário FTP
2. Verifique se há espaço em disco no servidor
3. Verifique se o arquivo não está sendo bloqueado por antivírus

## 📊 Vantagens do Script PHP

✅ **Mais controle** - Você pode ver exatamente o que está acontecendo  
✅ **Melhor tratamento de erros** - Logs detalhados de cada operação  
✅ **Execução local** - Pode testar antes de fazer commit  
✅ **Criação automática de diretórios** - Cria pastas conforme necessário  
✅ **Fácil customização** - Pode ajustar conforme suas necessidades  
✅ **Progresso em tempo real** - Vê o progresso do upload  

## 🎯 Próximos Passos

1. ✅ Faça commit dos arquivos criados
2. ✅ Execute o workflow ou teste localmente
3. ✅ Se houver erro de caminho, ajuste o `serverDir` no script
4. ✅ Se funcionar, você pode desabilitar o workflow antigo (`deploy.yml`)

## 📝 Notas Importantes

- O script exclui automaticamente: `.git`, `.github`, `.cursor`, `vendor`, `node_modules`, `tests`, etc.
- A senha FTP deve ser configurada como secret no GitHub (`FTP_PASSWORD`)
- O script cria diretórios automaticamente se não existirem
- Logs coloridos facilitam identificar problemas
