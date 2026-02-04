<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Habilita a confiança em Proxies
        // Isso ativa automaticamente os headers: FOR, HOST, PORT, PROTO e AWS_ELB (o antigo ALL)
        
        $middleware->trustProxies(at: [
            '*', // ATENÇÃO: Use '*' se não souber o IP do proxy, ou coloque o IP fixo aqui
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Throwable $e, $request) {
            
            // Só interceptamos se for o erro "Unwilling to perform"
            if (str_contains($e->getMessage(), 'Server is unwilling to perform')) {
                
                // Tenta identificar se é erro de senha pelo código hexadecimal do AD (0000052D)
                // OU se a requisição atual está enviando um campo 'password'
                // OU se é a rota de criação de usuários (onde a senha é gerada no backend)
                $ehProblemaDeSenha = str_contains($e->getMessage(), '0000052D') 
                                     || $request->has('password')
                                     || $request->routeIs('ldapusers.store'); // Ajuste o nome da rota de create se necessário

                if ($ehProblemaDeSenha) {
                    return response(
                        'A senha gerada ou fornecida não atende os requisitos de complexidade do servidor LDAP. (Erro 53)',
                        422
                    );
                }

                // Se não for senha, deixamos o Laravel mostrar o erro real (como aconteceu agora)
                // ou retornamos o erro técnico para facilitar o debug
                return response(
                    'Erro no servidor LDAP: ' . $e->getMessage(),
                    400 // Bad Request
                );
            }
        });
    })->create();
