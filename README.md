# Retroway — Chat de Áudio

Rede social de áudio: grave e publique áudios, stories de 24h, comentários em áudio,
estrelas, ecos, amigos, Paradas do dia, bloqueio, denúncias e moderação.

---

## Rodar no computador (XAMPP)

1. Instale o [XAMPP](https://www.apachefriends.org/) e ligue o **Apache** e o **MySQL**.
2. Coloque esta pasta dentro de `C:\xampp\htdocs\` (ex.: `C:\xampp\htdocs\chataudio2`).
3. **Copie `config.exemplo.php` para `config.php`** e coloque a senha do seu MySQL em `db_senha`
   (se o seu MySQL não tem senha, deixe vazio).
4. Abra `http://localhost/chataudio2/` (ou a porta que o seu Apache usa, ex.: `http://127.0.0.1:8080/chataudio2/`).

Em modo `desenvolvimento`, o banco e as tabelas são criados e atualizados sozinhos.
Crie sua conta em **Cadastre-se**. No XAMPP não sai e-mail, então o link de "Esqueci minha senha"
aparece na própria tela.

---

## Colocar no ar (produção)

### 1. Hospedagem
- PHP **7.4 ou mais novo** (recomendado 8.x) com `mysqli`.
- MySQL 5.7+ ou MariaDB 10.3+.
- Apache com `.htaccess` liberado (`AllowOverride All`) e `mod_rewrite`.
- **HTTPS obrigatório**: sem HTTPS o navegador bloqueia o microfone. Quase toda hospedagem
  oferece certificado grátis (Let's Encrypt). O `.htaccess` já redireciona tudo para `https://`.

### 2. Banco de dados
Crie no painel da hospedagem um banco e **um usuário só para o site** (não use `root`), com
permissão de `SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX, REFERENCES` nesse banco.

### 3. Configuração
Envie os arquivos e crie o `config.php` no servidor (copie de `config.exemplo.php`):

```php
'db_servidor' => 'localhost',
'db_usuario'  => 'usuario_do_site',
'db_senha'    => 'uma senha forte',
'db_nome'     => 'nome_do_banco',
'ambiente'    => 'producao',
'url_site'    => 'https://seusite.com.br',
'email_remetente' => 'nao-responda@seusite.com.br',
'moderadores' => array('seu_usuario'),
```

### 4. Criar as tabelas
No terminal (SSH) da hospedagem, dentro da pasta do site:

```
php instalar.php
```

Rode de novo sempre que atualizar o site (só cria o que falta). Se a hospedagem não tem SSH,
importe o `bd.sql` pelo phpMyAdmin.

### 5. E-mail
A recuperação de senha usa a função `mail()` do PHP. Confira na hospedagem se ela está liberada
e se o `email_remetente` é um e-mail do seu domínio.

### 6. Textos legais
Preencha os campos entre [colchetes] em `termos.php` e `privacidade.php` (nome, contato,
idade mínima, data). O ideal é pedir a revisão de um profissional de direito.

### 7. Antes de divulgar — checklist
- [ ] Site abre só em `https://`
- [ ] `config.php` com `'ambiente' => 'producao'` e usuário do banco que **não** é o root
- [ ] `php instalar.php` rodou sem erro
- [ ] Você criou sua conta e colocou seu usuário em `moderadores`
- [ ] Testou: cadastro, login, gravar áudio, story, comentário, denúncia, "Esqueci minha senha"
- [ ] Termos de uso e Política de privacidade preenchidos
- [ ] O repositório no GitHub não tem senhas nem fotos de usuários

---

## Segurança (o que o projeto já faz)
- Senhas com `password_hash` (contas antigas em MD5 são convertidas no próximo login)
- Proteção contra SQL Injection (consultas preparadas) e XSS (tudo escapado na tela)
- Proteção CSRF em todos os formulários e ações
- Sessão segura (cookie `HttpOnly`, `SameSite`, `Secure` com HTTPS, nova sessão a cada login)
- Limite de tentativas de login e de publicações/comentários/pedidos por minuto
- Áudios e fotos validados pelo conteúdo do arquivo, com limite de tamanho, em pastas que não executam código
- Cabeçalhos de segurança (CSP, proteção contra clickjacking, etc.)
- Bloquear usuários, denunciar conteúdo, página de moderação
- Excluir conta com todos os dados (LGPD)
