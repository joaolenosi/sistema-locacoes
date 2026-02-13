# 🚀 Deploy do Projeto - Sistema Locações (CodeIgniter 4)

Este documento descreve como conectar via SSH usando Git Bash
e como atualizar o projeto em produção.

---

# 🔐 1️⃣ Conectar via SSH (Git Bash)

Abra o **Git Bash** e execute:

```bash
ssh mobilelocacoes@mobilelocacoes.com


📂 2️⃣ Ir para a pasta do sistema
cd /home/mobilelocacoes/www/sistema

📦 3️⃣ Rodar Composer (Servidor)

Caso o Composer esteja instalado como composer.phar:

php ~/composer.phar install --no-dev --optimize-autoloader

✅ Dar permissão para o script (apenas na primeira vez)

No Git Bash, dentro da pasta do projeto:

chmod +x deploy-full.sh

🚀 Executar o deploy completo
./deploy-full.sh


Se existir alias configurado:

composer install --no-dev --optimize-autoloader

🔄 Atualização Completa do Projeto (Automática)

Se estiver usando o script de deploy completo:

No Git Bash, dentro da pasta do projeto local:

./deploy-full.sh


📌 Observação Importante

Sempre que alterar dependências no composer.json, execute:

composer install --no-dev


antes de rodar o deploy.