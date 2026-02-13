<?php
/**
 * Script de Deploy via FTP
 * 
 * Este script faz o deploy dos arquivos para o servidor FTP
 * Execute: php scripts/deploy-ftp.php
 */

// Configurações FTP
$ftpConfig = [
    'server' => 'mobilelocacoes.com',
    'username' => 'mobilelocacoes',
    'password' => getenv('FTP_PASSWORD') ?: '', // Pode ser passado via variável de ambiente
    'port' => 21,
    'useSSL' => true,
    'passive' => true,
    // Tentará estes caminhos em ordem até encontrar um que funcione
    'serverDirOptions' => [
        'painel/',
        '/www/painel/',
        'public_html/painel/',
        'htdocs/painel/',
        'www/painel/',
        './painel/',
        '/home/mobilelocacoes/www/sistema/'
    ],
];

// Diretórios e arquivos a excluir
$exclude = [
    '.git',
    '.github',
    '.cursor',
    '.env',
    'vendor',
    'node_modules',
    '.vscode',
    '.idea',
    'tests',
    'README.md',
    'phpunit.xml.dist',
    'INSTALACAO_IIS.md',
    'SOLUCAO_403_14.md',
    'DIAGNOSTICO_FTP.md',
    '.DS_Store',
    'Thumbs.db',
    '.ftpignore',
    'scripts', // Excluir a pasta scripts também
];

// Cores para output
$colors = [
    'reset' => "\033[0m",
    'green' => "\033[32m",
    'red' => "\033[31m",
    'yellow' => "\033[33m",
    'blue' => "\033[34m",
];

function logMessage($message, $color = 'reset') {
    global $colors;
    echo $colors[$color] . $message . $colors['reset'] . "\n";
}

function shouldExclude($path, $exclude) {
    foreach ($exclude as $pattern) {
        if (strpos($path, $pattern) !== false) {
            return true;
        }
    }
    return false;
}

function getFiles($dir, $baseDir = '', $exclude = []) {
    $files = [];
    $items = scandir($dir);
    
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        
        $fullPath = $dir . DIRECTORY_SEPARATOR . $item;
        $relativePath = $baseDir ? $baseDir . '/' . $item : $item;
        
        if (shouldExclude($relativePath, $exclude)) {
            continue;
        }
        
        if (is_dir($fullPath)) {
            $files = array_merge($files, getFiles($fullPath, $relativePath, $exclude));
        } else {
            $files[] = [
                'local' => $fullPath,
                'remote' => $relativePath,
            ];
        }
    }
    
    return $files;
}

function createRemoteDir($ftp, $dir) {
    if (empty($dir) || $dir === '/' || $dir === '.') {
        return true;
    }
    
    // Remove barras duplicadas e normaliza
    $dir = preg_replace('#/+#', '/', trim($dir, '/'));
    
    if (empty($dir)) {
        return true;
    }
    
    // Se começar com /, é caminho absoluto
    $isAbsolute = (substr($dir, 0, 1) === '/');
    
    $parts = explode('/', $dir);
    $currentDir = $isAbsolute ? '' : '';
    
    foreach ($parts as $part) {
        if (empty($part)) continue;
        
        $currentDir .= ($isAbsolute && empty($currentDir) ? '/' : ($currentDir ? '/' : '')) . $part;
        
        // Verifica se o diretório já existe tentando entrar nele
        $pwd = @ftp_pwd($ftp);
        $exists = @ftp_chdir($ftp, $currentDir);
        
        if (!$exists) {
            // Tenta criar o diretório
            if (@ftp_mkdir($ftp, $currentDir)) {
                // Tenta entrar no diretório criado
                @ftp_chdir($ftp, $currentDir);
            } else {
                // Se não conseguiu criar, volta para onde estava
                if ($pwd) {
                    @ftp_chdir($ftp, $pwd);
                }
                return false;
            }
        }
    }
    
    return true;
}

// Verificar se a senha foi fornecida
if (empty($ftpConfig['password'])) {
    logMessage("ERRO: Senha FTP não fornecida!", 'red');
    logMessage("Defina a variável de ambiente FTP_PASSWORD ou edite o script.", 'yellow');
    logMessage("Exemplo: export FTP_PASSWORD='sua_senha' && php scripts/deploy-ftp.php", 'yellow');
    exit(1);
}

logMessage("=== Iniciando Deploy FTP ===", 'blue');
logMessage("Servidor: {$ftpConfig['server']}", 'blue');
logMessage("Diretório remoto: {$ftpConfig['serverDir']}", 'blue');

// Conectar ao FTP
logMessage("\nConectando ao servidor FTP...", 'yellow');

if ($ftpConfig['useSSL']) {
    $ftp = ftp_ssl_connect($ftpConfig['server'], $ftpConfig['port'], 30);
} else {
    $ftp = ftp_connect($ftpConfig['server'], $ftpConfig['port'], 30);
}

if (!$ftp) {
    logMessage("ERRO: Não foi possível conectar ao servidor FTP!", 'red');
    exit(1);
}

logMessage("Conectado! Autenticando...", 'green');

// Login
if (!@ftp_login($ftp, $ftpConfig['username'], $ftpConfig['password'])) {
    logMessage("ERRO: Falha na autenticação FTP!", 'red');
    ftp_close($ftp);
    exit(1);
}

logMessage("Autenticado com sucesso!", 'green');

// Modo passivo
if ($ftpConfig['passive']) {
    ftp_pasv($ftp, true);
}

// Descobrir diretório atual
$currentDir = @ftp_pwd($ftp);
logMessage("Diretório atual do FTP: " . ($currentDir ?: '/'), 'blue');

// Listar conteúdo do diretório atual para diagnóstico
logMessage("\nConteúdo do diretório atual:", 'yellow');
$files = @ftp_nlist($ftp, '.');
if ($files) {
    foreach (array_slice($files, 0, 10) as $file) {
        logMessage("  - " . basename($file), 'blue');
    }
    if (count($files) > 10) {
        logMessage("  ... e mais " . (count($files) - 10) . " itens", 'blue');
    }
} else {
    logMessage("  (Não foi possível listar)", 'yellow');
}

// Tentar encontrar o caminho correto
logMessage("\nTentando encontrar/criar o diretório 'painel'...", 'yellow');
$serverDir = null;

// Primeiro, tentar criar "painel" diretamente no diretório atual
if (@ftp_mkdir($ftp, 'painel')) {
    if (@ftp_chdir($ftp, 'painel')) {
        $serverDir = 'painel/';
        logMessage("✓ Diretório 'painel' criado e acessado no diretório atual!", 'green');
    }
} else {
    // Tentar entrar se já existe
    if (@ftp_chdir($ftp, 'painel')) {
        $serverDir = 'painel/';
        logMessage("✓ Diretório 'painel' já existe e foi acessado!", 'green');
    }
}

// Se não funcionou, tentar outros caminhos
if (!$serverDir) {
    foreach ($ftpConfig['serverDirOptions'] as $dirOption) {
        logMessage("Tentando: {$dirOption}", 'blue');
        
        // Tentar mudar para o diretório
        if (@ftp_chdir($ftp, $dirOption)) {
            $serverDir = $dirOption;
            logMessage("✓ Caminho encontrado: {$dirOption}", 'green');
            break;
        }
        
        // Tentar criar e depois entrar
        if (createRemoteDir($ftp, $dirOption)) {
            if (@ftp_chdir($ftp, $dirOption)) {
                $serverDir = $dirOption;
                logMessage("✓ Caminho criado e acessado: {$dirOption}", 'green');
                break;
            }
        }
        
        // Voltar para o diretório atual
        @ftp_chdir($ftp, $currentDir ?: '/');
    }
}

if (!$serverDir) {
    logMessage("\nERRO: Não foi possível encontrar ou criar o diretório!", 'red');
    logMessage("Tentados os seguintes caminhos:", 'yellow');
    foreach ($ftpConfig['serverDirOptions'] as $dirOption) {
        logMessage("  - {$dirOption}", 'yellow');
    }
    logMessage("\nSugestões:", 'yellow');
    logMessage("1. Conecte via FTP manualmente e veja qual diretório aparece", 'yellow');
    logMessage("2. Crie o diretório 'painel' manualmente via FTP", 'yellow');
    logMessage("3. Edite o script e adicione o caminho correto em 'serverDirOptions'", 'yellow');
    ftp_close($ftp);
    exit(1);
}

$ftpConfig['serverDir'] = $serverDir;
logMessage("\nUsando diretório: {$serverDir}", 'green');

// Obter lista de arquivos
logMessage("\nEscaneando arquivos locais...", 'yellow');
$files = getFiles(__DIR__ . '/..', '', $exclude);
$totalFiles = count($files);

logMessage("Encontrados {$totalFiles} arquivos para upload", 'blue');

// Upload dos arquivos
$uploaded = 0;
$failed = 0;
$skipped = 0;

logMessage("\nIniciando upload...\n", 'yellow');

foreach ($files as $index => $file) {
    $progress = sprintf("[%d/%d]", $index + 1, $totalFiles);
    
    // Criar diretório remoto se necessário
    $remoteDir = dirname($file['remote']);
    if ($remoteDir !== '.' && $remoteDir !== '') {
        // Voltar para o diretório base primeiro
        @ftp_chdir($ftp, $ftpConfig['serverDir']);
        createRemoteDir($ftp, $remoteDir);
    }
    
    // Voltar para o diretório base
    @ftp_chdir($ftp, $ftpConfig['serverDir']);
    
    // Upload do arquivo
    $remotePath = $file['remote'];
    
    logMessage("{$progress} Enviando: {$file['remote']}", 'blue');
    
    if (@ftp_put($ftp, $remotePath, $file['local'], FTP_BINARY)) {
        $uploaded++;
        logMessage("  ✓ Upload concluído", 'green');
    } else {
        $failed++;
        $error = error_get_last();
        logMessage("  ✗ Falha no upload: " . ($error['message'] ?? 'Erro desconhecido'), 'red');
    }
    
    // Mostrar progresso a cada 10 arquivos
    if (($index + 1) % 10 === 0) {
        logMessage("Progresso: {$uploaded} enviados, {$failed} falhas", 'yellow');
    }
}

// Estatísticas
logMessage("\n=== Estatísticas do Deploy ===", 'blue');
logMessage("Total de arquivos: {$totalFiles}", 'blue');
logMessage("Uploads bem-sucedidos: {$uploaded}", 'green');
logMessage("Falhas: {$failed}", $failed > 0 ? 'red' : 'green');
logMessage("Ignorados: {$skipped}", 'yellow');

// Fechar conexão
ftp_close($ftp);

if ($failed === 0) {
    logMessage("\n✓ Deploy concluído com sucesso!", 'green');
    exit(0);
} else {
    logMessage("\n⚠ Deploy concluído com alguns erros.", 'yellow');
    exit(1);
}
