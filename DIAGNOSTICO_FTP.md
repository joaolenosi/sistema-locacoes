# Diagnóstico de Problemas FTP

## Erro Atual
```
550 app: Permission denied
550 app: No such file or directory
```

## Possíveis Causas

### 1. Diretório de destino não existe
O diretório `/www/painel/` pode não existir no servidor FTP.

**Solução:**
- Acesse o servidor via FTP (FileZilla, WinSCP, etc.)
- Verifique se o diretório `/www/painel/` existe
- Se não existir, crie manualmente ou verifique o caminho correto

### 2. Permissões insuficientes
O usuário FTP `mobilelocacoes` pode não ter permissão de escrita no diretório.

**Solução:**
- Entre em contato com o suporte da hospedagem
- Solicite permissões de escrita para o usuário FTP no diretório `/www/painel/`
- Ou verifique se há um diretório diferente onde você tem permissões

### 3. Caminho incorreto
O caminho pode estar diferente do esperado.

**Soluções para testar:**

#### Opção A: Caminho relativo
No workflow, tente:
```yaml
server-dir: painel/
```

#### Opção B: Caminho absoluto alternativo
Se o FTP começa em outro diretório, tente:
```yaml
server-dir: /home/mobilelocacoes/www/painel/
```
ou
```yaml
server-dir: /public_html/painel/
```

#### Opção C: Diretório raiz do usuário
Tente apenas:
```yaml
server-dir: ./
```

### 4. Verificar diretório atual do FTP

**Como descobrir o diretório atual:**

1. Conecte via FTP usando FileZilla ou similar
2. Veja qual diretório aparece quando você conecta
3. Navegue até onde você quer fazer o deploy
4. Anote o caminho completo que aparece na barra de endereço

## Teste Manual

### Passo 1: Conectar via FTP
```
Servidor: mobilelocacoes.com
Usuário: mobilelocacoes
Senha: [sua senha FTP]
Porta: 21
Protocolo: FTPS (FTP sobre SSL)
```

### Passo 2: Verificar estrutura
- Qual diretório aparece quando você conecta?
- Existe um diretório `www/` ou `public_html/` ou `htdocs/`?
- Onde você normalmente faz upload de arquivos?

### Passo 3: Criar diretório manualmente
- Tente criar manualmente o diretório `painel/` via FTP
- Se conseguir criar, o problema pode ser no workflow
- Se não conseguir, é problema de permissões

## Soluções Alternativas

### Solução 1: Criar diretórios manualmente primeiro
1. Acesse o servidor via FTP
2. Crie manualmente a estrutura:
   ```
   /www/painel/
   /www/painel/app/
   /www/painel/public/
   /www/painel/writable/
   ```
3. Depois execute o deploy novamente

### Solução 2: Usar caminho diferente
Se o diretório raiz do FTP for diferente, ajuste o `server-dir` no workflow.

### Solução 3: Contatar suporte da hospedagem
- Pergunte qual é o caminho correto para fazer deploy
- Solicite permissões de escrita se necessário
- Peça para criar o diretório `/www/painel/` se não existir

## Informações para o Suporte

Ao contatar o suporte da hospedagem, forneça:

1. **Usuário FTP:** mobilelocacoes
2. **Erro:** `550 app: Permission denied`
3. **O que você precisa:**
   - Confirmar o caminho correto para deploy
   - Permissões de escrita no diretório de destino
   - Criar o diretório `/www/painel/` se necessário

## Próximos Passos

1. ✅ Execute o workflow de teste (`deploy-test.yml`) primeiro
2. ✅ Verifique qual diretório aparece ao conectar via FTP
3. ✅ Tente criar o diretório `painel/` manualmente
4. ✅ Ajuste o `server-dir` no workflow conforme necessário
5. ✅ Entre em contato com o suporte se necessário
