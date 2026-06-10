# MC Starterkit

## Sommaire
- [Prérequis](#1---prérequis)
- [Introduction](#2---introduction)
- [Installation](#3---installation)
- [Lancer un projet déjà installé](#4---lancer-un-projet-déjà-installé)
- [Différents environnements en local : prod et staging](#5---avoir-différents-environnements-en-local--prod-et-staging)
- [Mise à jour du starterkit](#6---mise-à-jour-du-starterkit-lui-même)

## 1 - Prérequis

**Avoir DDEV installé en version supérieure ou égale à v1.24.10 (au moment du projet, pour Drupal 11), et par conséquent Docker.
Vous trouverez les différents liens depuis la page DDEV**

- [Docker Installation](https://docs.ddev.com/en/stable/users/install/docker-installation/)
- [DDEV](https://docs.ddev.com/en/stable/users/install/ddev-installation/)
- Ou via Dockerdocs [Docker Desktop](https://docs.docker.com/desktop/)
- Ou [Docker Engine](https://docs.docker.com/engine/) sur certains linux

Avoir créé un dossier /tmp et /private à la racine du projet, au même niveau que /web

## 2 - Introduction

Basé sur https://www.drupal.org/docs/develop/using-composer/starting-a-site-using-drupal-composer-project-templates
et drupal/recommended-project

Installation basée sur des recettes "Recipes", on trouve "MC Core" qui est la recette de base, avec les modules essentiels
et communs à tous sites, DSFR ou non. On trouve aussi "MC DSFR" qui lui est basé sur le MC Core, mais ajoute UI Suite DSFR.
"MC Native" quant à lui permet d'installer un environnement non DSFR.

Avec ddev addon : adminer with dracula theme, varnish, browsersync, cron, nvm, redis, redis-commander
https://project.pages.drupalcode.org/distributions_recipes/getting_started.html

## 3 - Installation

1. Cloner votre projet VIDE, et incluez les fichiers du starterkit, sans le .git de ce dernier.

    ```bash
    git clone https://code.culture.fr/global/ligne-produit/grand-public/franceterme/dev/drupal.git
    ```

2. Personnalisez le nom de votre projet dans .ddev/config.yaml
    Editez la ligne :
    ```yaml
    name: starterkit-culture
    ```
    Et renseignez le nom de votre projet, par exemple :
    ```yaml
    name: france-terme
    ```

    La partie additonal_hostnames en conséquence :
    ```yaml
    additional_hostnames:
    - starterkit-culture-staging
    - starterkit-culture-localprod
    ```
    Par :
    ```yaml
    additional_hostnames:
    - france-terme-localstaging
    - france-terme-localprod
    ```
   Vous pouvez également personnaliser les versions dont vous avez besoin, comme par exemple node :
    ```yaml
    nodejs_version: "24"
    ```
    La version 24 est la dernière LTS sortie au moment de la création de ce README.


3. Personnalisez maintenant votre mapping de sites : web/sites/sites.php, éditez la ligne
    ```php
    $sites['starterkit-culture-staging.ddev.site'] = 'localstaging';
    $sites['starterkit-culture-prod.ddev.site'] = 'localprod';
    ```
    Par :
    ```php
    $sites['starterkit-culture-staging.ddev.site'] = 'localstaging';
    $sites['starterkit-culture-prod.ddev.site'] = 'localprod';
    ```

@todo : voir si j'explique le settings.local ici

4. Installez les dépendances, éventuellement avec l'option --no-dev pour un environnement sans dépendances de dév.
    ```bash
    ddev composer install
    ```
    Vous pouvez ensuite aller sur l'adresse de votre site, par exemple :
    https://france-terme.ddev.site
    Pour toutes les différentes adresses et services :
    ```bash
    ddev describe
    ```

5. Installer Drupal.
  On va créer une instance drupal, c'est nécessaire pour appliquer un "recipe". Pour ça, allez sur votre site,
par exemple : https://france-terme.ddev.site, et suivez le processus d'installation standard. Créez un admin, etc. On peut alors passer à l'installation personnalisée.


6. Installer la recipe en accord avec votre projet. PENSER à supprimer composer.lock s'il est présent.

    6.1 Votre projet est basé sur DSFR ? Installez mc_dsfr :
    ```bash
    ddev composer require mc/mc_dsfr
    ddev drush cr
    ddev drush recipe ../recipes/mc_dsfr -v
    ```
   Ajoutez maintenant la déclaration de répertoire de la librairie DSFR dans la partie "repertories" du composer.json à la racine du projet. Pour cela, ouvrez le fichier web/themes/contrib/ui_suite_dsfr/composer.json
   et copiez cette partie dans "repertories" :
    ```json
    {
      "type": "package",
      "package": {
          "name": "gouvernementfr/dsfr",
          "type": "drupal-library",
          "version": "1.14.2",
          "dist": {
              "type": "zip",
              "url": "https://github.com/GouvernementFR/dsfr/releases/download/v1.14.2/dsfr-v1.14.2.zip"
          }
      }
    }
    ```
   puis

    ```bash
    ddev composer require "gouvernementfr/dsfr"
    ```
    Installez alors le thème MC Theme via l'administration, les recettes ne permerttant pas l'installation de thème custom.

   6.2 Votre projet est natif, détaché de DSFR ? Installez mc_native :

    ```bash
    ddev composer require mc/mc_native
    ddev drush cr
    ddev drush recipe ../recipes/mc_native -v
    ```

    Pour ce cas de figure, il faut aller modifier le thème de base, dans :
   web/themes/custom/mc_theme/mc_theme.info.yml, remplacez
    ```yaml
    base theme: ui_suite_dsfr
    ```
   par
    ```yaml
    base theme: stable9
    ```
   Installez alors le thème MC Theme via l'administration, les recettes ne permerttant pas l'installation de thème custom.

   6.3 Optionnel, vous pouvez choisir une interface administrateur basée sur le thème Gin :

    ```bash
    ddev composer require kanopi/gin-admin-experience
    ddev drush cr
    ddev drush recipe ../recipes/gin-admin-experience -v
    ```

7. Une fois votre projet installé :

   7.1 Dans .gitignore, décommentez la ligne suivante. Vous pouvez supprimer le dossier 'recipes' de votre projet, à condition de supprimer les "repositories" de mc_dsfr et mc_native de votre package.json.
    ```gitignore
    # Uncomment it for your project, you don't need it anymore.
    /recipes
    ```
   7.2 Dans .gitignore, vous devez décommenter "composer.lock".
    ```gitignore
    # Comment this line, for yout project. You will now need a lock file.
    composer.lock
    ```


8. Fichier .env @todo : finish.
    Dupliquez le fichier .env.dist en .env, puis indiquez vos valeurs :

    8.1 ENV_ID : est utilisé pour pointer vers la configuration (config_split) correcte du projet. C'est primordial.

    8.2 HASH_SALT : unique au site, une fois votre site installé, pensez à reporter ce hash sur tous les environnements.

    8.3 DB : pas nécessaire sur ddev, pensez à les renseigner pour les autres environnements.

    8.4 THEME_NAME mc_dsfr ou mc_bootstrap, sert à la compilation du thème. @todo !!!

    8.5 BACKEND_CACHE=null pour aucun cache, utilisé en environnement de dev.

    8.6 REVERSE_PROXY=FALSE, TRUE en prod, @todo !.

    8.7 REDIS : You don't need to set host and so for the ddev environment, juste let REDIS_ENABLE=TRUE

    8.8 S3 : Set S3_ENABLE=TRUE to use S3, not usable on ddev for the moment.

    8.9 OIDC : Openid connect, OIDC_ENABLE=FALSE and so. Not for ddev environment.

    8.10 SMTP : Openid connect, OIDC_ENABLE=FALSE and so. Not for ddev environment.

## 4 - Lancer un projet déjà installé
@todo Make upgrade ?

## 5 - Avoir différents environnements en local : prod et staging.
@todo : expliquer sites.php
expliquer  settings.php + prendre development.services.yml
ddev -l prod ou -l staging
voir le make upgrade

## 6 - Mise à jour du starterkit lui même
Si vous voulez ajouter des modules pour vos prochains sites par exemple.

Ne jamais envoyer le composer.lock ni le composer.json du projet dans les sources du starterkit. Préférez le composer.json du
recipe mc_core en ajoutant manuellement la ligne. Le projet étant conçu à partir de recettes, il est préférable d'étoffer
les recettes mc_core, mc_dsfr et mc_base.

Lors de l'installation de votre projet avec "composer install", le composer.json
du projet récupère toutes les dépendances des différentes recettes que vous aurez choisies pour générer VOTRE fichier de dépendances.

Un conseil : pensez bien de manière générale à ne jamais faire "composer install" dans le starterkit, mais uniquement dans un projet
de test, et à envoyer uniquement les améliorations / mises à jours des recettes "recipes".

Et bien sûr, pensez bien à mettre à jour ce document, notamment les "requirements".
node : ddev config --nodejs-version=xx

## 7 - Mise à jour du projet lui même
Le projet étant alors décorellé du starterkit, vous pouvez mettre à jour de manière standard grâce à composer, et via
le .ddev/config.yaml

## 8 - Subtilité
Il a fallu faire : composer require drupal/varnish_purge

mais pour l'installation c'est varnish_purger avec un r à la fin : install varnish_purger
