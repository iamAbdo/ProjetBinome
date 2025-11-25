## Template e-commerce pour XAMP (Projet version Monome) 

Exemple de site e-commerce pour les groupes monome avec deux portails séparés :

- `client/` : vitrine publique (accueil, catalogue, à propos) avec formulaire de commande.
- `admin/` : back-office (login, produits, catégories, commandes).
- `public/` : assets partagés (CSS, JS, images).
- `private/` : assets partagés priver (configuration PHP, script SQL).

### Installation rapide

1. Importez `public/database.sql` dans phpMyAdmin ou via `mysql`.
2. Adaptez vos identifiants MySQL dans `public/config.php`.
3. Placez le dossier dans `C:\xampp\htdocs\` puis lancez Apache & MySQL depuis WAMP/XAMPP.
4. Accédez à `http://localhost/ProjetMonome/` (redirection vers `client/`).

Identifiants admin par défaut :

- Email : `test@test.dz`
- Mot de passe : `test1234` (correspond au hash déjà inséré)

Si compte admin marche pas 
supprimer require_admin(); dans ligne 4 dans admin/includes/header.php

### Structure

```
├─ README.md
├─ apropos.html
├─ index.php
├─ produits.php
├─ admin
   ├─ admins.php
   ├─ categories.php
   ├─ includes
   │  ├─ bootstrap.php
   │  ├─ footer.php
   │  └─ header.php
   ├─ index.php
   ├─ login.php
   ├─ logout.php
   ├─ orders.php
   └─ products.php
├─ client
   ├─ apropos.html
   ├─ includes
   │  ├─ footer.php
   │  └─ header.php
   ├─ index.php
   ├─ order-handler.php
   └─ products.php
├─ private
   ├─ config.php
   └─ database.sql
└─ public
   ├─ css
      └─ style.css
   └─ js
      └─ app.js
   └─ images
      └─ products
```

Les fichiers racine (`index.php`, `produits.php`, `apropos.html`) redirigent automatiquement vers le portail client. Ajustez/étendez selon vos besoins.

