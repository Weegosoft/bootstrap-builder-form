# WEEGOSOFT Bootstrap Form Builder

Une librairie PHP fluide et robuste pour générer des formulaires HTML élégants, nativement stylisés avec Bootstrap 4 et 5. Conçue pour les architectures MVC modernes, elle simplifie le binding de données, la gestion des erreurs et l'intégration de composants UI complexes.

## 🚀 Fonctionnalités principales

* **Binding automatique des données :** Pré-remplissage intelligent des formulaires à partir de tableaux (ex: `$_POST`) ou d'objets (DTO, Modèles avec getters).

* **Gestion intégrée des erreurs :** Détection automatique des erreurs de validation et injection dynamique des classes `is-invalid` et des messages de feedback Bootstrap.

* **Composants UI avancés :** Génération simplifiée de *Switches*, *Dropzones* (glisser-déposer), *Boutons radio stylisés (Toggle Groups)* et champs *Autocomplete*.

* **Sécurité CSRF :** Intégration facile de jetons CSRF pour sécuriser vos soumissions.

* **Flexibilité maximale :** Accepte des attributs HTML personnalisés (`class`, `id`, `data-*`) et gère les préfixes/suffixes de champs (Input Groups).

## 📦 Installation

Vous pouvez installer ce package via [Composer](https://getcomposer.org/). Exécutez la commande suivante dans votre terminal :

```bash
composer require weegosoft/bootstrap-form-builder

```

## 🛠️ Configuration requise

* PHP >= 8.0
* Bootstrap 4.x ou 5.x (pour le rendu visuel)
* *Optionnel :* FontAwesome (pour les icônes des boutons)

## 💡 Utilisation de base

### 1. Initialisation

La classe `Form` prend en paramètre les données de votre modèle ou tableau pour pré-remplir les champs, et récupère automatiquement les erreurs stockées en session via `WSM\Http\Session`.

```php
use WSM\HTML\Form;

// Exemple avec un tableau de données (peut aussi être un Objet)
$data = [
    'username' => 'johndoe',
    'email' => 'john@weegosoft.com'
];

$form = new Form($data);

```

### 2. Création d'un formulaire

Générez vos balises avec une syntaxe fluide et lisible. La librairie s'occupe de générer la structure complète (labels, wrappers de groupes, inputs, et messages d'erreurs).

```php
<?= $form->open('/profile/update', 'POST') ?>

    <!-- Champ texte classique avec label -->
    <?= $form->text('username', 'Nom d\'utilisateur') ?>

    <!-- Champ email avec attributs HTML supplémentaires (ex: placeholder) -->
    <?= $form->email('email', 'Adresse Email', ['placeholder' => 'Saisissez votre email']) ?>
    
    <!-- Liste déroulante -->
    <?= $form->select('role', 'Rôle utilisateur', [
        'admin' => 'Administrateur',
        'editor' => 'Éditeur',
        'user' => 'Utilisateur standard'
    ]) ?>

    <!-- Bouton de soumission avec icône (nécessite FontAwesome) -->
    <?= $form->submit('Mettre à jour', ['class' => 'btn btn-primary', 'icon' => 'fas fa-save']) ?>

<?= $form->close() ?>

```

## 🎨 Éléments d'interface avancés

La librairie WEEGOSOFT brille par sa capacité à générer des composants Bootstrap complexes en une seule ligne de code PHP :

### Interrupteur (Switch)

Génère un composant `custom-switch` Bootstrap.

```php
<?= $form->switch('notifications', 'Activer les alertes par email', 1) ?>

```

### Champ Monétaire avec Input Group

Ajoute automatiquement la devise en suffixe (append) via les *Input Groups*.

```php
<?= $form->currency('price', 'Prix de vente', 'FCFA') ?>

```

### Groupe de Boutons (Button Group)

Remplace les boutons radios classiques par des boutons cliquables stylisés.

```php
<?= $form->buttonGroup('status', 'Statut de publication', [
    'draft' => 'Brouillon',
    'published' => 'Publié'
]) ?>

```

### Zone de Glisser-Déposer (Dropzone)

Génère une zone d'upload de fichiers stylisée en pointillés avec une icône.

```php
<?= $form->dropzone('attachments', 'Glissez vos fichiers ici ou cliquez pour parcourir') ?>

```

## 📚 Liste des méthodes disponibles

Voici un aperçu des méthodes de génération prises en charge :

* **Inputs standards :** `text()`, `email()`, `password()`, `number()`, `tel()`, `url()`, `color()`, `search()`, `hidden()`
* **Dates & Heures :** `date()`, `time()`, `datetimeLocal()`, `week()`, `month()`
* **Fichiers :** `file()`, `image()`, `images()` (uploads multiples), `dropzone()`
* **Choix & Sélections :** `select()`, `checkbox()`, `radio()`, `checkboxList()`, `radioList()`, `switch()`, `toggle()`, `buttonGroup()`
* **Textes longs :** `textarea()`, `paragraph()`, `address()`
* **Spécifiques :** `currency()`, `range()`, `autocomplete()`
* **Boutons :** `submit()`, `reset()`, `button()`

## 📂 Exemples complets

Pour voir la librairie en action avec des cas d'utilisation réels (E-commerce, CRM, Authentification, etc.), consultez le dossier `examples/` inclus dans ce dépôt. Vous y trouverez des pages complètes prêtes à être testées.

## 📄 Licence

Ce projet est sous licence MIT. Vous êtes libre de l'utiliser, de le modifier et de le distribuer dans vos projets personnels comme commerciaux.
