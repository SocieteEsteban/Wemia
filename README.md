# WEMIA - Plateforme d'Automatisation n8n avec IA

WEMIA est une plateforme web moderne permettant aux particuliers, freelances, PME et grandes entreprises de demander des automatisations n8n avec IA. Elle offre une interface utilisateur intuitive et professionnelle pour la gestion des demandes d'automatisation.

## 🚀 Fonctionnalités

- Interface utilisateur moderne et responsive
- Système d'authentification sécurisé
- Formulaire de demande d'automatisation
- Espace administrateur pour la gestion des demandes
- Intégration avec n8n
- Design inspiré d'Uber et Stripe

## 🛠 Prérequis

- PHP 8.0 ou supérieur
- MySQL 5.7 ou supérieur
- Node.js 16+ et npm
- Serveur web (Apache/Nginx)

## 📦 Installation

1. **Cloner le repository**
   ```bash
   git clone https://github.com/votre-username/wemia.git
   cd wemia
   ```

2. **Installer les dépendances Node.js**
   ```bash
   npm install
   ```

3. **Configurer la base de données**
   - Créer une base de données MySQL
   - Importer le schéma depuis `sql/init.sql`
   - Configurer les accès dans `includes/config.php`

4. **Configurer le serveur web**
   - Pointer le document root vers le dossier du projet
   - Activer le module PHP
   - Configurer les permissions des dossiers

5. **Compiler les assets**
   ```bash
   npm run build
   ```

## 🚀 Démarrage

1. **Développement**
   ```bash
   npm run dev
   ```
   Le site sera accessible sur `http://localhost:3000`

2. **Production**
   ```bash
   npm run build
   ```
   Déployer le contenu du dossier `dist` sur votre serveur

## 👤 Compte administrateur par défaut

- Email : admin@wemia.com
- Mot de passe : admin123

⚠️ Pensez à changer ces identifiants en production !

## 🔒 Sécurité

- Protection CSRF
- Mots de passe hashés avec Bcrypt
- Validation des entrées
- Sessions sécurisées
- Préparation des requêtes SQL

## 📁 Structure du projet

```
wemia/
├── index.html           # Page d'accueil
├── src/
│   ├── css/            # Styles Tailwind
│   └── js/             # Scripts Alpine.js
├── php/                # Scripts backend
├── includes/           # Configuration PHP
├── sql/               # Scripts SQL
└── assets/            # Images et autres ressources
```

## 🤝 Contribution

1. Fork le projet
2. Créer une branche (`git checkout -b feature/amazing-feature`)
3. Commit les changements (`git commit -m 'Add amazing feature'`)
4. Push sur la branche (`git push origin feature/amazing-feature`)
5. Ouvrir une Pull Request

## 📝 License

Ce projet est sous licence MIT. Voir le fichier `LICENSE` pour plus de détails. 