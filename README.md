# 🎵 Suno Music Player Direct - Plugin WordPress

[![WordPress](https://img.shields.io/badge/WordPress-5.0+-blue.svg)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-7.4+-purple.svg)](https://php.net/)
[![License](https://img.shields.io/badge/License-GPL_v2-red.svg)](LICENSE)
[![Version](https://img.shields.io/badge/Version-2.0-green.svg)](https://github.com/WeAreReForm/suno-music-player-direct)

## 📖 Description

**Suno Music Player Direct** est un plugin WordPress complet pour héberger et afficher vos créations musicales Suno directement sur votre site. Plus besoin de dépendre de services tiers !

### ✨ Fonctionnalités principales

- 🎵 **Player moderne** avec interface style Spotify
- 📤 **Upload direct** depuis WordPress (admin & front-end)
- 🎨 **Design responsive** optimisé mobile
- 📊 **Statistiques** de lectures et téléchargements  
- ❤️ **Système de likes** pour l'engagement
- 🎯 **Shortcodes flexibles** pour personnalisation
- 🎮 **Contrôles avancés** (volume, progression, playlist)
- 🔒 **Gestion des permissions** pour upload public

## 🚀 Installation

### Méthode 1 : Installation directe

1. Téléchargez le plugin depuis GitHub
```bash
git clone https://github.com/WeAreReForm/suno-music-player-direct.git
```

2. Uploadez le dossier dans `/wp-content/plugins/`
3. Activez le plugin depuis WordPress Admin
4. Configurez dans **Réglages > Suno Music**

### Méthode 2 : ZIP Upload

1. [Téléchargez le ZIP](https://github.com/WeAreReForm/suno-music-player-direct/archive/refs/heads/main.zip)
2. Dans WordPress : **Extensions > Ajouter**
3. Cliquez sur **Téléverser une extension**
4. Sélectionnez le fichier ZIP
5. Activez après installation

## 📝 Configuration

### Paramètres disponibles

Dans **Réglages > Suno Music** :

| Paramètre | Description | Défaut |
|-----------|-------------|---------|
| Couleur principale | Couleur du thème du player | #6366f1 |
| Autoplay | Lecture automatique | Désactivé |
| Upload public | Autoriser les visiteurs à uploader | Désactivé |
| Taille max | Limite de taille des fichiers | 50MB |

## 🎯 Utilisation

### Shortcodes disponibles

#### Playlist complète
```
[suno_playlist]
```

#### Playlist personnalisée
```
[suno_playlist title="Ma musique Suno" limit="10" show_upload="true"]
```

#### Formulaire d'upload
```
[suno_upload_form]
```

#### Player unique
```
[suno_player id="123"]
```

### Paramètres des shortcodes

| Paramètre | Description | Valeurs |
|-----------|-------------|---------|
| `title` | Titre de la playlist | Texte |
| `limit` | Nombre de chansons | Nombre |
| `show_upload` | Afficher formulaire upload | true/false |
| `autoplay` | Lecture automatique | true/false |
| `genre` | Filtrer par genre | pop, rock, etc. |

## 🎨 Personnalisation CSS

Le plugin utilise des variables CSS pour faciliter la personnalisation :

```css
:root {
    --suno-primary: #6366f1;
    --suno-primary-dark: #4f46e5;
    --suno-secondary: #10b981;
    --suno-border: #e5e7eb;
    --suno-radius: 8px;
}
```

Ajoutez votre CSS personnalisé dans **Apparence > Personnaliser > CSS additionnel**.

## 📊 Base de données

Le plugin crée une table `wp_suno_tracks` avec les colonnes :

- `id` - Identifiant unique
- `title` - Titre de la chanson
- `artist` - Nom de l'artiste
- `description` - Description
- `genre` - Genre musical
- `file_url` - URL du fichier
- `duration` - Durée en secondes
- `play_count` - Nombre de lectures
- `likes` - Nombre de likes
- `download_count` - Nombre de téléchargements
- `created_at` - Date de création

## 🔧 API & Hooks

### Actions disponibles

```php
// Après upload d'une chanson
do_action('suno_track_uploaded', $track_id);

// Après lecture d'une chanson
do_action('suno_track_played', $track_id);

// Après like d'une chanson
do_action('suno_track_liked', $track_id, $user_id);
```

### Filtres disponibles

```php
// Modifier les types de fichiers autorisés
add_filter('suno_allowed_file_types', function($types) {
    $types[] = 'audio/ogg';
    return $types;
});

// Modifier la taille max
add_filter('suno_max_file_size', function($size) {
    return 100 * 1024 * 1024; // 100MB
});
```

## 🐛 Dépannage

### Problèmes courants

**Upload échoue**
- Vérifiez les permissions du dossier `uploads`
- Augmentez `upload_max_filesize` dans PHP
- Vérifiez l'espace disque disponible

**Player ne fonctionne pas**
- Assurez-vous que jQuery est chargé
- Vérifiez la console pour les erreurs JS
- Testez avec un thème par défaut

**Shortcode n'affiche rien**
- Vérifiez que le plugin est activé
- Assurez-vous d'avoir des chansons dans la base
- Vérifiez les paramètres du shortcode

## 🚀 Optimisation VPS/Docker

Pour votre installation sur VPS OVH avec Docker :

### Configuration PHP recommandée

```ini
upload_max_filesize = 100M
post_max_size = 100M
max_execution_time = 300
memory_limit = 256M
```

### Docker Compose

```yaml
version: '3'
services:
  wordpress:
    image: wordpress:latest
    environment:
      WORDPRESS_DB_HOST: db
      WORDPRESS_DB_NAME: wordpress
      WORDPRESS_DB_USER: root
      WORDPRESS_DB_PASSWORD: password
      # PHP settings
      PHP_UPLOAD_MAX_FILESIZE: 100M
      PHP_POST_MAX_SIZE: 100M
    volumes:
      - ./wp-content:/var/www/html/wp-content
    ports:
      - "80:80"
```

## 📈 Roadmap

- [ ] Intégration API Suno officielle
- [ ] Import depuis compte Suno
- [ ] Waveform visualisation 
- [ ] Playlists multiples
- [ ] Système de commentaires
- [ ] Partage social intégré
- [ ] Mode sombre
- [ ] Export/Import de playlists

## 🤝 Contribution

Les contributions sont les bienvenues !

1. Fork le projet
2. Créez votre branche (`git checkout -b feature/AmazingFeature`)
3. Commit (`git commit -m 'Add AmazingFeature'`)
4. Push (`git push origin feature/AmazingFeature`)
5. Ouvrez une Pull Request

## 📄 Licence

Distribué sous licence GPL-2.0. Voir [LICENSE](LICENSE) pour plus d'informations.

## 🙏 Remerciements

- [Suno AI](https://suno.ai) pour l'inspiration
- [WordPress](https://wordpress.org) pour la plateforme
- [WeAreReForm](https://github.com/WeAreReForm) pour le développement

## 📞 Support

- 🐛 [Issues](https://github.com/WeAreReForm/suno-music-player-direct/issues)
- 💬 [Discussions](https://github.com/WeAreReForm/suno-music-player-direct/discussions)
- 📧 hello@wearereform.fr

---

⭐ **Si ce plugin vous aide, n'hésitez pas à mettre une étoile sur GitHub !**

🔗 **Liens utiles**
- [Documentation complète](https://github.com/WeAreReForm/suno-music-player-direct/wiki)
- [Changelog](CHANGELOG.md)
- [Site de démo](https://parcoursmetiersbtp.fr)
