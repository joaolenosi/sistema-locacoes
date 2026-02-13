# Solução para Erro 404 - Página não encontrada

## Problema

Ao acessar `https://mobilelocacoes.com/sistema/login`, está retornando erro 404.

## Possíveis Causas

### 1. URL Rewrite Module não está instalado ou funcionando

O IIS precisa do **URL Rewrite Module** para redirecionar requisições para `index.php`.

**Solução:**
1. Verifique se o módulo está instalado no IIS
2. Se não estiver, baixe e instale: https://www.iis.net/downloads/microsoft/url-rewrite
3. Reinicie o IIS após instalar

### 2. IIS não está apontando para o diretório correto

O IIS pode estar apontando para um diretório diferente de `public/`.

**Verificar:**
- O IIS deve apontar para `/www/sistema/public/` ou `/home/mobilelocacoes/www/sistema/public/`
- Não deve apontar para `/www/sistema/` (raiz do projeto)

### 3. web.config não está sendo aplicado

O arquivo `web.config` pode não estar sendo lido pelo IIS.

**Solução:**
1. Verifique se `public/web.config` existe no servidor
2. Verifique se tem permissões de leitura
3. Tente recarregar o site no IIS Manager

### 4. Problema com baseURL

Se o `baseURL` estiver incorreto, as rotas não funcionarão.

**Verificar:** `app/Config/App.php` linha 19
```php
public string $baseURL = 'https://mobilelocacoes.com/sistema/';
```

## Testes para Diagnóstico

### Teste 1: Acessar arquivo PHP diretamente

Acesse: `https://mobilelocacoes.com/sistema/test-route.php`

Se funcionar, o PHP está OK. Se não funcionar, problema é com o servidor.

### Teste 2: Acessar index.php diretamente

Acesse: `https://mobilelocacoes.com/sistema/index.php`

Se funcionar, o problema é com o URL Rewrite. Se não funcionar, problema é com o CodeIgniter.

### Teste 3: Acessar index.php/login

Acesse: `https://mobilelocacoes.com/sistema/index.php/login`

Se funcionar, o problema é apenas com o URL Rewrite. Se não funcionar, problema é com as rotas.

### Teste 4: Arquivo de debug

Acesse: `https://mobilelocacoes.com/sistema/debug.php`

Este arquivo mostra informações detalhadas sobre a configuração.

## Soluções Implementadas

### 1. Melhorado web.config

O `web.config` foi melhorado para:
- Ignorar arquivos físicos corretamente
- Ignorar diretórios corretamente
- Aplicar rewrite apenas quando necessário

### 2. Arquivos de teste criados

- `public/test-route.php` - Teste simples de PHP
- `public/debug.php` - Diagnóstico completo do CodeIgniter

## Checklist de Verificação

- [ ] URL Rewrite Module está instalado no IIS?
- [ ] IIS está apontando para o diretório `public/`?
- [ ] Arquivo `public/web.config` existe no servidor?
- [ ] Arquivo `public/index.php` existe no servidor?
- [ ] `baseURL` está correto em `app/Config/App.php`?
- [ ] Teste `test-route.php` funciona?
- [ ] Teste `index.php` diretamente funciona?
- [ ] Teste `index.php/login` funciona?

## Próximos Passos

1. ✅ Execute os testes acima
2. ✅ Verifique se o URL Rewrite Module está instalado
3. ✅ Verifique onde o IIS está apontando
4. ✅ Verifique se os arquivos foram enviados corretamente
5. ✅ Se necessário, entre em contato com o suporte da hospedagem

## Informações para Suporte

Se precisar de ajuda, forneça:
- Resultado do teste `test-route.php`
- Resultado do teste `index.php`
- Resultado do teste `index.php/login`
- Se o URL Rewrite Module está instalado
- Onde o IIS está apontando (qual diretório físico)
