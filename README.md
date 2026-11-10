# Chat de Audio
 Nesse projeto irei criar um chat de voz nos moldes de uma rede social.

## Como rodar (Windows + XAMPP)

1. Instale o [XAMPP](https://www.apachefriends.org/) e, no XAMPP Control Panel, clique em **Start** no **Apache** e no **MySQL**.
2. Copie esta pasta para `C:\xampp\htdocs\chataudio`.
3. Abra **http://localhost/chataudio/** no navegador.

Pronto. Na primeira vez que a página abre, o banco `chataudiobd` e todas as tabelas
são criados automaticamente (a partir do arquivo `bd.sql`). Não precisa importar nada no phpMyAdmin.

### Usuários de teste

| Usuário | Senha  |
|---------|--------|
| admin   | 123456 |
| admin2  | 123456 |

Para criar outra conta: **http://localhost/chataudio/criarConta.php**

### Senha do MySQL

O projeto usa o padrão do XAMPP: usuário `root` **sem senha**.
Se o seu MySQL tiver senha, altere `$bd_senha` em `audioConexao.php`.

### Microfone

O navegador só libera o microfone em `localhost` ou em sites com HTTPS.
Quando ele pedir permissão, clique em **Permitir**.
