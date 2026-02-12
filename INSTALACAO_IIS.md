# Instruções de Instalação no IIS

## Problema Resolvido

O erro HTTP 403.14 ocorre quando o IIS não encontra um arquivo padrão ou não está configurado corretamente.

## Arquivos Criados

1. **`public/web.config`** - Configuração do IIS para o diretório público
2. **`web.config`** (raiz) - Redirecionamento para o diretório public (caso necessário)

## Configurações Realizadas

1. ✅ Criado arquivo `web.config` no diretório `public/`
2. ✅ Criado arquivo `web.config` na raiz do projeto
3. ✅ Configurado `indexPage` como vazio em `app/Config/App.php`
4. ✅ Alterado ambiente para `production` no `.env`

## Passos no Servidor IIS

### 1. Verificar URL Rewrite Module

O IIS precisa ter o **URL Rewrite Module** instalado:
- Baixe em: https://www.iis.net/downloads/microsoft/url-rewrite
- Instale se ainda não estiver instalado

### 2. Configurar o Site no IIS

**Opção A: Se o site aponta para a raiz do projeto**
- O IIS deve apontar para o diretório raiz do projeto
- O arquivo `web.config` na raiz redirecionará para `public/`

**Opção B: Se o site aponta diretamente para `public/`** (RECOMENDADO)
- Configure o IIS para apontar diretamente para o diretório `public/`
- O arquivo `web.config` em `public/` cuidará de tudo

### 3. Configurar Documento Padrão

No IIS Manager:
1. Selecione seu site/aplicação
2. Abra "Default Document"
3. Certifique-se de que `index.php` está na lista
4. Se não estiver, adicione `index.php` e mova para o topo

### 4. Configurar Permissões

Certifique-se de que:
- O diretório `writable/` tem permissões de escrita
- O IIS_IUSRS tem permissões de leitura no diretório do projeto

### 5. Verificar PHP

Certifique-se de que:
- PHP está instalado e configurado no IIS
- A extensão PHP está habilitada no IIS

## Estrutura de Diretórios Esperada

```
sistema-locacoes/
├── app/
├── public/
│   ├── index.php
│   ├── web.config      ← NOVO
│   └── .htaccess
├── writable/
├── web.config          ← NOVO (raiz)
└── .env
```

## Teste

Após fazer upload dos arquivos:
1. Acesse: `https://mobilelocacoes.com/painel/`
2. Deve carregar normalmente sem erro 403.14

## Troubleshooting

Se ainda houver problemas:

1. **Verifique os logs do IIS**: `C:\inetpub\logs\LogFiles\`
2. **Verifique os logs do PHP**: Configure em `php.ini`
3. **Verifique os logs da aplicação**: `writable/logs/`
4. **Teste se o PHP está funcionando**: Crie um arquivo `test.php` com `<?php phpinfo(); ?>` em `public/`

## Nota Importante

Se você estiver usando um provedor de hospedagem compartilhada:
- Entre em contato com o suporte para verificar se o URL Rewrite Module está instalado
- Verifique se há alguma configuração específica necessária
- Alguns provedores podem precisar de configurações adicionais no painel de controle
