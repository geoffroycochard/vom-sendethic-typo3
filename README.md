# Sendethic Newsletter Unsubscribe Plugin TYPO3

Plugin TYPO3 pour la gestion des désabonnements newsletter via l'API Sendethic.

## Fonctionnalités

- **Désinscription par segment** : Permet aux utilisateurs de se désinscrire d'une newsletter spécifique
- **Désinscription complète (RGPD)** : Permet la suppression complète des données personnelles
- **Génération de liens sécurisés** : Création de tokens sécurisés avec expiration
- **Middleware d'interception** : Redirection automatique des liens Sendethic
- **Interface utilisateur moderne** : Templates Bootstrap responsifs
- **Commandes CLI** : Outils de génération de liens en ligne de commande

## Installation

1. Copiez le plugin dans le dossier `libraries/orleans-metropole/sendethic-typo3/`
2. Ajoutez l'extension à votre `composer.json` :

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "libraries/orleans-metropole/sendethic-typo3"
        }
    ],
    "require": {
        "orleans-metropole/sendethic-typo3": "*"
    }
}
```

3. Exécutez `composer install`
4. Activez l'extension dans l'Extension Manager de TYPO3

## Configuration

### Configuration du site

Ajoutez les attributs suivants à votre configuration de site :

```yaml
# config/sites/[site]/config.yaml
attributes:
  sendethicApiUsername: 'your_api_username'
  sendethicApiPassword: 'your_api_password'
  newsletterSegments:
    '1': 'Newsletter générale'
    '2': 'Newsletter événements'
    '3': 'Newsletter culture'
  newsletterUnsubscribePid: 123  # ID de la page contenant le plugin

routeEnhancers:
  NewsletterUnsubscribe:
    type: Plugin
    routePath: '/newsletter/unsubscribe/{action}/{token}'
    namespace: 'tx_sendethictypo3_newsletterunsubscribe'
    defaults:
      controller: 'NewsletterUnsubscribe'
      action: 'unsubscribeSegment'
    requirements:
      action: 'unsubscribeSegment|confirmSegmentUnsubscribe|unsubscribeAll|confirmDeleteAll|success|error'
```

### Configuration du plugin

1. Créez une page pour le plugin de désabonnement
2. Ajoutez le plugin "Newsletter - Désabonnement" à cette page
3. Configurez les pages de succès et d'erreur dans le FlexForm

## Utilisation

### Génération de liens

Utilisez la commande CLI pour générer des liens de désabonnement :

```bash
# Générer des liens pour tous les sites
vendor/bin/typo3 sendethic:generate-links user@example.com

# Générer des liens pour un site spécifique
vendor/bin/typo3 sendethic:generate-links user@example.com main
```

### Utilisation programmatique

```php
use OrleansMetropole\SendethicTypo3\Service\NewsletterUnsubscribeLinkService;

// Injection de dépendance
$linkService = GeneralUtility::makeInstance(NewsletterUnsubscribeLinkService::class);

// Générer un lien de désinscription pour un segment
$link = $linkService->generateSegmentUnsubscribeLink($site, $email, $segmentId);

// Générer un lien de désinscription complète
$link = $linkService->generateAllUnsubscribeLink($site, $email);

// Générer tous les liens
$links = $linkService->generateAllLinks($site, $email);
```

## Sécurité

- Les tokens sont signés avec HMAC-SHA256
- Expiration automatique des tokens (7 jours par défaut)
- Validation stricte des paramètres d'entrée
- Protection contre les attaques CSRF
- Vérification du referer pour les liens Sendethic

## Personnalisation

### Templates

Les templates sont situés dans `Resources/Private/Templates/NewsletterUnsubscribe/` :

- `UnsubscribeSegment.html` : Confirmation de désinscription par segment
- `UnsubscribeAll.html` : Confirmation de suppression complète
- `Success.html` : Page de succès
- `Error.html` : Page d'erreur

### Styles

Le plugin utilise Bootstrap 5 par défaut. Vous pouvez personnaliser les styles en surchargeant les templates ou en ajoutant vos propres CSS.

## API Sendethic

Le plugin utilise l'API Sendethic pour :

- Récupérer les informations des contacts
- Mettre à jour les préférences de newsletter
- Supprimer complètement les contacts (RGPD)

### Configuration API

Assurez-vous que les identifiants API sont correctement configurés dans les attributs du site :

- `sendethicApiUsername` : Nom d'utilisateur de l'API
- `sendethicApiPassword` : Mot de passe de l'API

## Support

Pour toute question ou problème, contactez l'équipe de développement d'Orléans Métropole.

## Licence

GPL-2.0+ 