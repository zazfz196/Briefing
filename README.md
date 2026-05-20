# 🌊 Cabo Frio Excursões — Site Institucional

Site de excursões para Cabo Frio, saindo de Belo Horizonte.  
Feito com **HTML + CSS + JavaScript** no front, e **PHP + SQLite** no back-end.

---

## 📁 Estrutura do projeto

```
cabo-frio-viagens/
├── index.html      → Página principal
├── style.css       → Estilos
├── script.js       → Interações e envio do formulário
├── salvar.php      → Recebe e salva os leads no banco
├── admin.php       → Painel de clientes (protegido por senha)
└── banco/
    ├── .htaccess   → Bloqueia acesso direto ao banco
    └── clientes.db → Criado automaticamente na primeira visita
```

---

## 🚀 Como subir no servidor (hospedagem PHP)

### Opção 1 — Hospedagem compartilhada (Hostgator, Locaweb, etc.)
1. Faça upload de **todos os arquivos** via FTP para a pasta `public_html/`
2. Acesse `seudominio.com.br` — o site já está no ar!
3. O banco `banco/clientes.db` é criado automaticamente na primeira submissão

### Opção 2 — VPS com Apache/Nginx + PHP
```bash
sudo apt install php php-sqlite3 libsqlite3-dev
# Copie os arquivos para /var/www/html/
```

---

## 🔐 Painel administrativo

Acesse: `seudominio.com.br/admin.php`

**Senha padrão:** `cabofrio2025`

> ⚠️ **IMPORTANTE:** Troque a senha antes de publicar!  
> Edite a linha `$SENHA = 'cabofrio2025';` no arquivo `admin.php`

No painel você verá:
- Todos os clientes que preencheram o formulário
- Nome, telefone (com link direto para WhatsApp), e-mail
- Pacote escolhido, número de pessoas e data desejada

---

## ☁️ GitHub Pages (apenas front-end estático)

O GitHub Pages **não suporta PHP**. Se quiser usar GitHub Pages:

1. Suba o repositório normalmente
2. O formulário vai mostrar erro (sem back-end)
3. **Solução:** Use um serviço externo para o back-end:
   - [InfinityFree](https://infinityfree.net) — hospedagem PHP gratuita
   - [000webhost](https://www.000webhost.com) — gratuito com PHP
   - Coloque apenas o `salvar.php` e `admin.php` lá, atualize a URL no `script.js`

---

## ✏️ Como personalizar

| O que mudar | Onde |
|---|---|
| Nome da empresa | `index.html` → `.logo` e `<title>` |
| WhatsApp de contato | `index.html` → seção footer e admin.php |
| Preços dos pacotes | `index.html` → `.card-preco` |
| Datas de saída | `index.html` → descrição dos cards |
| Senha do admin | `admin.php` → linha `$SENHA` |
| Imagens de fundo | `style.css` → `.hero`, `.card-img-*`, `.sobre-img` |

---

## 📞 Suporte

Em caso de dúvidas, consulte a documentação do PHP em https://php.net  
Para SQLite: https://www.sqlite.org/docs.html
