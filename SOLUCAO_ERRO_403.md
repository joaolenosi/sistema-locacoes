# Solução para Erro 403 - Acesso Negado

## Problema

Ao acessar o painel em produção, está retornando:
```
Erro 403
Acesso negado
```

## Possíveis Causas

### 1. Filtro de Autenticação Bloqueando Acesso

O filtro `auth` pode estar bloqueando o acesso antes de redirecionar para login.

**Solução:** Verifique se o filtro está configurado corretamente em `app/Config/Filters.php`

### 2. Problema com baseURL

Se o `baseURL` estiver incorreto, pode causar problemas de roteamento.

**Verificar:** `app/Config/App.php` - linha 19
```php
public string $baseURL = 'https://mobilelocacoes.com/painel/';
```

### 3. Permissões no Servidor IIS

O IIS pode estar bloqueando acesso por falta de permissões.

**Solução:**
1. Verifique permissões do diretório `writable/`
2. Verifique se `IIS_IUSRS` tem permissões de leitura no diretório do projeto
3. Verifique se o diretório `public/` tem permissões corretas

### 4. Problema com web.config

O `web.config` pode estar bloqueando acesso.

**Verificar:** `public/web.config` - Certifique-se de que está configurado corretamente

### 5. Problema com Rotas

As rotas podem não estar sendo reconhecidas corretamente.

## Soluções Implementadas

### 1. Ajuste no Filtro de Autenticação

Modificado `app/Config/Filters.php` para:
- Aplicar filtro apenas em rotas que precisam de autenticação
- Excluir rotas de login do filtro
- Garantir redirecionamento correto

### 2. Verificação de Configuração

Certifique-se de que:
- `baseURL` está correto em `app/Config/App.php`
- Rotas estão configuradas corretamente em `app/Config/Routes.php`
- Filtros estão configurados corretamente

## Troubleshooting

### Passo 1: Verificar Logs

Verifique os logs em:
- `writable/logs/` - Logs da aplicação
- Logs do IIS em `C:\inetpub\logs\LogFiles\`

### Passo 2: Testar Rota de Login Diretamente

Tente acessar diretamente:
```
https://mobilelocacoes.com/painel/login
```

Se funcionar, o problema é com o filtro de autenticação na rota raiz.

### Passo 3: Verificar Sessões

O problema pode ser com sessões não funcionando. Verifique:
- Permissões de escrita em `writable/session/`
- Configuração de sessão em `app/Config/Session.php`

### Passo 4: Desabilitar Filtro Temporariamente

Para testar, você pode temporariamente comentar o filtro em `app/Config/Filters.php`:

```php
public array $filters = [
    // 'auth' => [
    //     'before' => [
    //         '/',
    //         'admin',
    //         'admin/*',
    //     ],
    // ],
];
```

**⚠️ ATENÇÃO:** Reative o filtro após o teste!

### Passo 5: Verificar Permissões no Servidor

1. Acesse o servidor via FTP ou RDP
2. Navegue até o diretório do projeto
3. Verifique permissões:
   - `writable/` deve ter permissões de escrita
   - `public/` deve ter permissões de leitura
   - `app/` deve ter permissões de leitura

## Teste Rápido

Crie um arquivo `test.php` em `public/`:

```php
<?php
echo "PHP está funcionando!<br>";
echo "Diretório atual: " . __DIR__ . "<br>";
echo "Base URL: " . (defined('base_url') ? base_url() : 'Não definido') . "<br>";
phpinfo();
?>
```

Acesse: `https://mobilelocacoes.com/painel/test.php`

Se funcionar, o problema é com o CodeIgniter. Se não funcionar, é problema de configuração do servidor.

## Próximos Passos

1. ✅ Verifique os logs do servidor
2. ✅ Teste a rota `/login` diretamente
3. ✅ Verifique permissões de arquivos
4. ✅ Verifique configuração do `baseURL`
5. ✅ Teste com o arquivo `test.php`

## Informações para Diagnóstico

- **URL acessada:** `https://mobilelocacoes.com/painel/`
- **Caminho físico:** `/home/mobilelocacoes/www/sistema/` (ou `/www/painel/`)
- **Servidor:** IIS
- **PHP:** Versão 7.4+

Se o problema persistir, forneça:
- Conteúdo dos logs em `writable/logs/`
- Resultado do acesso a `/login`
- Resultado do teste com `test.php`
