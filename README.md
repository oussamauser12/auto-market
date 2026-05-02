# Auto-Market - Guide d'installation

## 🚀 Prérequis
- XAMPP (Apache + MySQL + PHP 8.0+)
- Navigateur moderne

## ⚙️ Installation

### 1. Placer le projet
```
C:\xampp\htdocs\auto-market\
```

### 2. Créer la base de données
1. Ouvrir **phpMyAdmin** → http://localhost/phpmyadmin
2. Aller dans **SQL**
3. Coller et exécuter le contenu de `db.sql`

### 3. Configuration DB (si nécessaire)
Modifier `config/db.php` si vos identifiants MySQL sont différents :
```php
$host = 'localhost';
$db   = 'auto_market_db';
$user = 'root';
$pass = '';  // vide par défaut sur XAMPP
```

### 4. Accéder au projet
```
http://localhost/auto-market/
```

---

## 🔐 Comptes de test

| Rôle   | Email                     | Mot de passe |
|--------|---------------------------|--------------|
| Client | client@auto-market.ma     | 123456       |
| Admin  | admin@auto-market.ma      | admin123     |

---

## 📁 Structure du projet

```
auto-market/
├── config/
│   └── db.php              # Configuration base de données
├── espace-client/
│   ├── index.php           # Accueil - liste des annonces
│   ├── annonce.php         # Détail d'une annonce
│   ├── deposer.php         # Formulaire de dépôt d'annonce
│   ├── traitement_depot.php # Traitement du formulaire
│   ├── profil.php          # Mon garage / mes annonces
│   ├── favoris.php         # Mes favoris
│   ├── ajouter_favoris.php # Toggle favori
│   └── supprimer_annonce.php
├── espace-admin/
│   ├── dashboard.php       # Tableau de bord admin
│   ├── moderer.php         # Modération des annonces
│   └── gestion-users.php   # Gestion utilisateurs
├── admin/
│   ├── index.php           # Panneau de modération (legacy)
│   └── valider.php         # Action valider/refuser
├── assets/
│   └── css/
├── uploads/                # Photos des annonces (créé auto.)
├── db.sql                  # Script SQL complet
├── login.php
├── register.php
├── logout.php
└── index.php               # Redirection intelligente
```

---

## ✅ Corrections apportées

1. **login.php** — Fonctionnel, redirection admin/client correcte
2. **register.php** — Refait avec design cohérent, validation complète (confirmation MDP, longueur min, email valide), sélecteur de ville
3. **db.sql** — Mots de passe hashés (bcrypt), ajout de `'refuse'` dans l'ENUM statut, compte admin ajouté, plus de marques/modèles
4. **index.php** — Redirection intelligente selon rôle (non connecté → login, admin → dashboard, client → accueil)
5. **admin/index.php** — Ajout vérification de rôle admin (sécurité)
6. **admin/valider.php** — Ajout vérification de rôle + sécurisation des IDs (cast int) + action `refuser` correcte
7. **traitement_depot.php** — Ajout `puissance_fiscale`, `enctype="multipart/form-data"`, validation complète, gestion des photos, messages d'erreur propres
8. **deposer.php** — Ajout champ puissance fiscale, champ photos, `enctype` multipart, affichage messages d'erreur
9. **annonce.php** — Sécurisation de l'ID (cast int, validation > 0)
10. **logout.php** — Nettoyé
