<?php
/**
 * Sistema de Previsão do Tempo — IPIL Projecto #03
 * Entry point do backend PHP puro
 *
 * Toda requisição HTTP chega aqui.
 * Responsabilidade: aplicar CORS, carregar autoloader e despachar para routes/api.php
 */

declare(strict_types=1);

// ── Autoloader simples baseado em namespaces ─────────────────────────────────
spl_autoload_register(function (string $class): void {
    $base = dirname(__DIR__) . DIRECTORY_SEPARATOR;
    $file = $base . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// ── Carregar variáveis de ambiente ────────────────────────────────────────────
require_once dirname(__DIR__) . '/config/env.php';

// ── CORS ──────────────────────────────────────────────────────────────────────
require_once dirname(__DIR__) . '/config/cors.php';

// ── Resposta padrão JSON ──────────────────────────────────────────────────────
header('Content-Type: application/json; charset=UTF-8');

// ── Roteamento ────────────────────────────────────────────────────────────────
require_once dirname(__DIR__) . '/routes/api.php';