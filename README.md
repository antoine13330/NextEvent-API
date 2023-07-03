# NextEvent-API

NextEvent est une application pour référencer des événements de jeux vidéo et des festivals. L'application offre une gestion de favoris pour aider les utilisateurs à suivre leurs événements préférés.

## Fonctionnalités

- Recherche d'événements par mot-clé, date, genre, etc.
- Ajout d'événements aux favoris
- Voir les details des événements
- Gestion des favoris d'événements
- Gestion de compte utilisateur

## Installation

1. Clonez ce référentiel sur votre machine locale.
2. Installez les dépendances avec la commande `composer install`
3. `php bin/console d:m:m`
4. `php bin/console doctrine:fixtures:load`

## API

L'API de NextEvent est accessible via l'URL suivante : `http://next-event/api/nextevent/`.

### Endpoints

#### Evenements

- [GET] `/api/evenements` : Récupérer la liste des événements
- [GET] `/api/evenementsByType` + "type": "festival" ou "esport" dans le corps de la requête : Récupérer la liste des événements par type
- [GET] `/api/:type/events/:date` : Récupérer la liste des événements par type & date
- [GET] `/api/evenement/:id` : Récupérer les informations sur un événement spécifique
- [PATCH] `/api/evenement/:id` : Modifier les informations sur un événement spécifique 🔐
- [DELETE] `/api/evenement/:id` : Supprimer un événement spécifique 🔐

#### Auth 
- [POST] `/api/login` : S'enregistrer
- [POST] `/api/logout` : Se déconnecter
  
#### Utilisateur

- [GET] `/api/users` : Récuper tous les utilisateurs
- [GET] `/api/user/` : Récupérer les informations de profil d'un utilisateur
- [PATCH] `/api/user/` : Modifier les informations de profil d'un utilisateur
- [DELETE] `/api/user/` : Supprimer un utilisateur 🔐

### Authentification

L'authentification est requise pour accéder à certains endpoint.
De plus tous les endpoints avec un 🔐 sont accessibles seulement aux utilisateurs avec `ROLE_ADMIN`.

## Technologies utilisées

- Symfony
- Doctrine
- MySQL

## Auteurs

- Antoine Despres
- Denis Chevannae
- Noémie Dupuis
- Clément Etienne
