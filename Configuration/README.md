# Configuration de l'extension Sendethic TYPO3

## Inclusion automatique

L'extension est configurée pour s'inclure automatiquement via le fichier `ext_localconf.php`. 
Aucune action supplémentaire n'est nécessaire pour les fonctionnalités de base.

## Inclusion manuelle dans un site-package

Si vous souhaitez personnaliser la configuration, vous pouvez inclure l'extension manuellement dans votre site-package :

### Méthode 1 : Via le fichier de configuration de site

Ajoutez dans votre fichier `config/sites/[site]/config.yaml` :

```yaml
imports:
  - resource: 'EXT:sendethic_typo3/Configuration/SiteConfiguration/sendethic_typo3.yaml'
```

### Méthode 2 : Via ext_localconf.php du site-package

Ajoutez dans le fichier `ext_localconf.php` de votre site-package :

```php
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
    '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:sendethic_typo3/Configuration/TsConfig/Page/All.tsconfig">'
);
```

### Méthode 3 : Via TypoScript

Ajoutez dans votre fichier `setup.typoscript` :

```typoscript
@import 'EXT:sendethic_typo3/Configuration/TypoScript/setup.typoscript'
```

## Inclusion via fichier statique TypoScript

L'extension est configurée pour être incluse via un fichier statique TypoScript, comme les extensions standards TYPO3.

### Comment inclure l'extension

1. **Dans le backend TYPO3** :
   - Allez dans **Template** > **Template Analyzer**
   - Sélectionnez votre template de page racine
   - Cliquez sur **Modifier**
   - Dans l'onglet **Includes**, ajoutez le fichier statique :
     - **Include static (from extensions)** : `Sendethic Newsletter Unsubscribe`

2. **Via TypoScript** :
   ```typoscript
   @import 'EXT:sendethic_typo3/Configuration/TypoScript/setup.txt'
   ```

3. **Via configuration de site** :
   ```yaml
   # Dans config/sites/[site]/config.yaml
   imports:
     - resource: 'EXT:sendethic_typo3/Configuration/TypoScript/setup.txt'
   ```

## Fichiers de configuration

### Fichier statique principal
- `Configuration/TypoScript/setup.txt` - Configuration principale à inclure

### Fichiers de support
- `Configuration/TypoScript/constants.typoscript` - Constantes TypoScript
- `Configuration/TypoScript/setup.typoscript` - Setup TypoScript (ancienne méthode)

### Configuration TCA
- `Configuration/TCA/Overrides/sys_template.php` - Enregistrement du fichier statique

## Personnalisation

### CSS personnalisé

Les styles CSS sont automatiquement inclus via le fichier statique. Pour les personnaliser :

1. **Surcharger dans votre site-package** :
   ```typoscript
   page.includeCSS.newsletterUnsubscribe = fileadmin/css/newsletter-unsubscribe-custom.css
   ```

2. **Désactiver et inclure votre propre CSS** :
   ```typoscript
   page.includeCSS.newsletterUnsubscribe >
   page.includeCSS.newsletterCustom = fileadmin/css/newsletter-custom.css
   ```

### Templates personnalisés

Pour personnaliser les templates, créez vos propres versions dans votre site-package :

```
Resources/Private/Templates/NewsletterUnsubscribe/
├── Error.html
├── Success.html
├── UnsubscribeAll.html
└── UnsubscribeSegment.html
```

## Configuration des plugins

L'extension configure automatiquement le plugin `NewsletterUnsubscribe` avec les actions suivantes :

- `unsubscribeSegment` - Désinscription d'un segment
- `confirmSegmentUnsubscribe` - Confirmation de désinscription
- `unsubscribeAll` - Suppression complète des données
- `confirmDeleteAll` - Confirmation de suppression
- `success` - Page de succès
- `error` - Page d'erreur

## Templates disponibles

- **Error.html** - Page d'erreur avec design générique
- **Success.html** - Page de confirmation de succès
- **UnsubscribeAll.html** - Page de suppression complète RGPD
- **UnsubscribeSegment.html** - Page de désinscription d'un segment

Tous les templates sont conçus sans dépendance Bootstrap et utilisent des classes CSS personnalisées.

## Avantages de cette méthode

- ✅ **Standard TYPO3** : Utilise la méthode officielle des fichiers statiques
- ✅ **Flexibilité** : Chaque site peut choisir d'inclure ou non l'extension
- ✅ **Gestion backend** : Peut être configuré via l'interface d'administration
- ✅ **Séparation claire** : Configuration isolée et réutilisable 