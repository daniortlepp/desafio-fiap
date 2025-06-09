# Desafio FIAP – Sistema de Secretaria
Sistema administrativo para gestão de alunos, turmas e matrículas, desenvolvido em PHP puro.

### Instalação e como rodar o projeto localmente

1) Clone o repositório:

```zsh
git clone https://github.com/daniortlepp/desafio-fiap.git
cd seu-repositorio
```

2) Crie o arquivo .env na raiz do projeto:
```zsh
DB_HOST=localhost
DB_USER=seu_usuario
DB_PASS=sua_senha
DB_NAME=
```

3) Preencha os dados de acesso do banco de dados no .env:
- Substitua seu_usuario e sua_senha pelos dados corretos do seu MySQL.
- Deixe DB_NAME vazio neste momento.

4)Execute o script de instalação
- No terminal ou navegador, rode:
```zsh
php installation.php
```
- Isso irá criar o banco de dados, as tabelas e os registros iniciais.

Configure o arquivo .env:
- Inserir o usuário e senha do MySQL juntamente com o nome do banco de dados que ira usar:
```zsh
APP_ENV=dev
APP_SECRET=2d430eb59387ef97df7617f144526da2
DATABASE_URL="mysql://username:password@127.0.0.1:3306/database_name?serverVersion=5.7"
CORS_ALLOW_ORIGIN="*" (Aqui pode colocar a url do frontend)
```

5) Atualize o nome do banco no .env
- Após a instalação, edite o .env e defina:
```zsh
DB_NAME=desafio_fiap
```

6) Suba o servidor local:
```zsh
php -S 127.0.0.1:8000:
```

7) Acesse o sistema:
- Abra o navegador e vá para: http://127.0.0.1:8000

Usuário administrador padrão:
E-mail: admin@fiap.com.br
Senha: UY3N*nqe8QnD18P

