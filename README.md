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
3. [Générer les clés d'accés privées JWT](https://symfony.com/bundles/LexikJWTAuthenticationBundle/current/index.html#generate-the-ssl-keys)

## API

L'API de NextEvent est accessible via l'URL suivante : `http://next-event/api/nextevent/`.

### Endpoints

#### Evenements

- [GET] `/api/:type/events` : Récupérer la liste des événements par type
- [GET] `/api/:type/events/:date` : Récupérer la liste des événements par type & date
- [GET] `/api/events/:id` : Récupérer les informations sur un événement spécifique
- [PUT] `/api/events/:id` : Modifier les informations sur un événement spécifique 🔐
- [DELETE] `/api/events/:id` : Supprimer un événement spécifique 🔐

#### Auth 
- [POST] `api/auth/sign-up` : S'enregistrer
- [POST] `api/auth/token` : Se connecter et récupérer le token
- [POST] `api/auth/token_renew` : Mettre à jour le token expiré
#### Utilisateur

- [PUT] `/api/user/profil` : Modifier les informations de profil d'un utilisateur 
- [GET] `/api/user/profil` : Récupérer les informations de profil d'un utilisateur
- [DELETE] `/api/user` : Supprimer un utilisateur 🔐

#### Favoris

- [GET] `/api/favorites` : Récupérer la liste des événements favoris d'un utilisateur
- [POST] `/api/favorites/:id` : Ajouter un événement aux favoris d'un utilisateur 
- [DELETE] `/api/favorites/:id` : Supprimer un événement des favoris d'un utilisateur

### Authentification

L'authentification est requise pour accéder aux endpoints `/profil`, `/favorites`, `/favorites/add/:id` et `/favorites/remove/:id`.
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
