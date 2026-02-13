# Diagnóstico Erro 403 - Acesso Negado

## Teste Rápido

### 1. Teste se PHP está funcionando

Acesse: `https://mobilelocacoes.com/painel/test.php`

Este arquivo foi criado para diagnóstico e mostra:
- Se PHP está funcionando
- Se os arquivos existem
- Se há permissões corretas
- Se os diretórios estão configurados

**⚠️ IMPORTANTE:** Remova o arquivo `test.php` após o diagnóstico!

### 2. Teste rota de login diretamente

Acesse: `https://mobilelocacoes.com/painel/login`

Se funcionar, o problema é com o filtro de autenticação na rota raiz.

### 3. Verifique logs

Verifique os logs em:
- `writable/logs/` - Logs da aplicação CodeIgniter
- Logs do IIS (se tiver acesso)

## Possíveis Causas e Soluções

### Causa 1: Filtro de Autenticação Bloqueando

**Sintoma:** Erro 403 ao acessar `/` mas `/login` funciona

**Solução:** O filtro foi ajustado para redirecionar corretamente. Verifique se o deploy foi feito.

### Causa 2: baseURL Incorreto

**Sintoma:** Erro 403 em todas as rotas

**Verificar:** `app/Config/App.php` linha 19
```php
public string $baseURL = 'https://mobilelocacoes.com/painel/';
```

**Teste:** Tente acessar diretamente:
- `https://mobilelocacoes.com/painel/public/index.php`
- `https://mobilelocacoes.com/painel/index.php`

### Causa 3: Permissões no Servidor

**Sintoma:** Erro 403 mesmo em arquivos estáticos

**Solução:**
1. Verifique permissões de `writable/` e subdiretórios
2. Verifique se `IIS_IUSRS` tem permissões de leitura
3. Verifique se o diretório `public/` tem permissões corretas

### Causa 4: Problema com web.config

**Sintoma:** Erro 403 e nenhum log aparece

**Solução:** Verifique se `public/web.config` existe e está correto

### Causa 5: Problema com Rotas

**Sintoma:** Erro 403 mas `test.php` funciona

**Solução:** Verifique `app/Config/Routes.php` e `app/Config/Filters.php`

## Checklist de Verificação

- [ ] PHP está funcionando? (teste com `test.php`)
- [ ] Arquivo `public/index.php` existe?
- [ ] Arquivo `app/Config/App.php` existe?
- [ ] Arquivo `app/Config/Routes.php` existe?
- [ ] Arquivo `app/Config/Filters.php` existe?
- [ ] Diretório `writable/` tem permissões de escrita?
- [ ] Diretório `writable/session/` tem permissões de escrita?
- [ ] `baseURL` está correto em `app/Config/App.php`?
- [ ] Rota `/login` funciona?
- [ ] Arquivo `.env` existe?

## Próximos Passos

1. Execute o teste com `test.php`
2. Tente acessar `/login` diretamente
3. Verifique os logs
4. Verifique permissões
5. Se necessário, entre em contato com o suporte da hospedagem

## Informações para Suporte

Se precisar de ajuda, forneça:
- Resultado do acesso a `test.php`
- Resultado do acesso a `/login`
- Conteúdo dos logs em `writable/logs/`
- Mensagem de erro completa (se houver)
