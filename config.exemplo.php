<?php
/*
 * CONFIGURAÇÃO DO RETROWAY
 *
 * 1. Copie este arquivo para "config.php" (na mesma pasta).
 * 2. Preencha os dados do seu banco e do seu site.
 *
 * O "config.php" NÃO vai para o GitHub (está no .gitignore),
 * então as suas senhas ficam só no seu computador / servidor.
 */
return array(
    // ---------- Banco de dados ----------
    'db_servidor' => 'localhost',
    'db_usuario'  => 'root',          // no servidor, use um usuário próprio do site (não o root)
    'db_senha'    => '',
    'db_nome'     => 'chataudiobd',

    // ---------- Ambiente ----------
    // 'desenvolvimento': mostra erros e cria/atualiza o banco sozinho (XAMPP)
    // 'producao': esconde erros e não mexe na estrutura do banco (use "php instalar.php")
    'ambiente' => 'desenvolvimento',

    // ---------- Site ----------
    'url_site'        => 'http://localhost/chataudio2',   // endereço do site, sem barra no final (usado nos e-mails)
    'email_remetente' => 'nao-responda@seusite.com.br',   // remetente dos e-mails de recuperação de senha

    // ---------- Moderação ----------
    // Usuários que podem ver e resolver denúncias (página moderacao.php)
    'moderadores' => array('admin'),
);
