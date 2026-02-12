# Solução para Erro HTTP 403.14

## Problema
O IIS está retornando erro 403.14 ao acessar `https://mobilelocacoes.com/painel/`

**Caminho físico no servidor:** `d:\web\localuser\mobilelocacoes\www\painel\`

## Solução Implementada

Foram criados/modificados os seguintes arquivos:

### 1. `index.php` (raiz do projeto) - NOVO
Este arquivo funciona como ponto de entrada quando o IIS aponta para a raiz do projeto. Ele inclui diretamente o `public/index.php`.

### 2. `web.config` (raiz do projeto) - ATUALIZADO
Configura o IIS para:
- Definir `index.php` como documento padrão
- Desabilitar listagem de diretórios
- Mostrar erros detalhados (para debug)

### 3. `public/web.config` - JÁ EXISTENTE
Configura o URL Rewrite para o CodeIgniter funcionar corretamente.

## Verificações Necessárias no Servidor

### ✅ Passo 1: Verificar se os arquivos foram enviados

Após fazer o deploy, verifique se os seguintes arquivos existem no servidor:

```
/www/painel/
├── index.php          ← DEVE EXISTIR (novo)
├── web.config         ← DEVE EXISTIR
├── public/
│   ├── index.php      ← DEVE EXISTIR
│   └── web.config     ← DEVE EXISTIR
```

### ✅ Passo 2: Verificar URL Rewrite Module

O IIS precisa ter o **URL Rewrite Module** instalado:

1. Acesse o servidor via RDP ou painel de controle
2. Abra o **IIS Manager**
3. No servidor, verifique se há o módulo "URL Rewrite" instalado
4. Se não estiver instalado:
   - Baixe em: https://www.iis.net/downloads/microsoft/url-rewrite
   - Instale o módulo
   - Reinicie o IIS

### ✅ Passo 3: Configurar Documento Padrão no IIS

1. Abra o **IIS Manager**
2. Navegue até seu site/aplicação (`mobilelocacoes.com`)
3. Clique duas vezes em **"Default Document"**
4. Verifique se `index.php` está na lista
5. Se não estiver, clique em **"Add..."** e adicione `index.php`
6. Mova `index.php` para o topo da lista (usando as setas)
7. Clique em **"Apply"** no painel direito

### ✅ Passo 4: Verificar Permissões

Certifique-se de que o IIS tem permissões de leitura:

1. No Windows Explorer, navegue até `d:\web\localuser\mobilelocacoes\www\painel\`
2. Clique com botão direito na pasta `painel`
3. Selecione **"Properties"** > **"Security"**
4. Verifique se **"IIS_IUSRS"** ou **"IUSR"** tem permissões de leitura
5. Se não tiver, adicione e conceda permissões de leitura

### ✅ Passo 5: Verificar PHP

1. Crie um arquivo `test.php` em `/www/painel/` com o conteúdo:
   ```php
   <?php phpinfo(); ?>
   ```
2. Acesse `https://mobilelocacoes.com/painel/test.php`
3. Se não funcionar, o PHP não está configurado corretamente no IIS
4. **IMPORTANTE:** Remova o arquivo `test.php` após o teste por segurança

## Teste Final

Após todas as verificações:

1. Acesse: `https://mobilelocacoes.com/painel/`
2. Deve carregar a aplicação normalmente
3. Se ainda houver erro, verifique os logs do IIS em:
   - `C:\inetpub\logs\LogFiles\`

## Estrutura Esperada no Servidor

```
/www/painel/                    (IIS aponta aqui)
├── index.php                   ← NOVO - Entry point
├── web.config                  ← Configuração IIS raiz
├── app/                        ← Código da aplicação
├── public/
│   ├── index.php              ← Front controller CodeIgniter
│   ├── web.config             ← URL Rewrite para CodeIgniter
│   └── assets/                ← Arquivos estáticos
├── writable/                   ← Precisa de permissão de escrita
└── vendor/                     ← Dependências Composer
```

## Troubleshooting

### Erro persiste após todas as verificações?

1. **Verifique os logs do IIS:**
   - Localização: `C:\inetpub\logs\LogFiles\`
   - Procure por erros relacionados ao seu site

2. **Verifique os logs da aplicação:**
   - Localização: `/www/painel/writable/logs/`
   - Veja se há erros do CodeIgniter

3. **Teste o PHP diretamente:**
   - Crie `test.php` com `<?php echo "PHP funciona!"; ?>`
   - Acesse via navegador
   - Se não funcionar, problema é na configuração do PHP no IIS

4. **Verifique se o URL Rewrite está funcionando:**
   - Acesse `https://mobilelocacoes.com/painel/index.php`
   - Se funcionar mas `/painel/` não funcionar, o problema é no URL Rewrite

5. **Contate o suporte da hospedagem:**
   - Alguns provedores têm configurações específicas
   - Pode ser necessário configurar via painel de controle

## Notas Importantes

- O arquivo `index.php` na raiz é necessário porque o IIS está apontando para `/www/painel/` e não para `/www/painel/public/`
- Se você tiver acesso para mudar o diretório físico do IIS, seria melhor apontar diretamente para `/www/painel/public/`
- O `web.config` na raiz garante que o IIS encontre o `index.php` como documento padrão
