<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Verificar se está tentando acessar login ou logout - permitir sempre
        $uri = $request->getUri();
        $path = $uri->getPath();
        
        // Remover barra inicial e baseURL se presente
        $path = ltrim($path, '/');
        $baseURL = config('App')->baseURL;
        $basePath = parse_url($baseURL, PHP_URL_PATH);
        if ($basePath && $basePath !== '/') {
            $basePath = trim($basePath, '/');
            if (strpos($path, $basePath) === 0) {
                $path = substr($path, strlen($basePath));
                $path = ltrim($path, '/');
            }
        }
        
        // Permitir acesso a login e logout sem autenticação
        if (in_array($path, ['login', 'logout']) || strpos($path, 'login/') === 0) {
            return null; // Permitir acesso
        }
        
        // Verificar se está logado
        if (! session()->get('empresa_logado')) {
            // Redirecionar para login
            $loginURL = base_url('login');
            return redirect()->to($loginURL);
        }
        
        return null; // Permitir acesso
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Não fazer nada após a requisição
    }
}
