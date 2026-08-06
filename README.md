# WEEGOSOFT Form Builder

Une librairie PHP fluide, sécurisée et robuste pour générer des formulaires HTML élégants et réactifs, compatibles nativement avec **Bootstrap 4**, **Bootstrap 5** et **Tailwind CSS**. Conçue pour les architectures MVC et micro-frameworks modernes, elle simplifie le binding de données, l'échappement XSS et l'intégration de composants UI avancés.

---

## 🚀 Fonctionnalités principales

* **Support Multi-Framework CSS :** Classes dédiées pour **Bootstrap 4**, **Bootstrap 5** et **Tailwind CSS**.
* **Binding automatique des données :** Pré-remplissage intelligent à partir de tableaux (`$_POST`, tableaux associatifs) ou d'objets (DTOs, modèles ORM avec propriétés ou getters camelCase).
* **Gestion automatique des erreurs :** Injection dynamique des classes d'erreur (`is-invalid` / styles d'erreur) et affichage propre des messages de validation.
* **Sécurité XSS & CSRF intégrée :** Échappement HTML automatique de toutes les valeurs/attributs et génération simple de tokens CSRF.
* **Composants UI avancés :** *Switches*, *Dropzones* (upload glisser-déposer), *Button Groups*, *Input Groups*, *Champs devises* et *Autocomplete*.

---

## 📦 Installation

Installez le package via [Composer](https://getcomposer.org/) :

```bash
composer require weegosoft/bootstrap-form-builder
```

---

## 🛠️ Configuration requise

* **PHP :** >= 8.0
* **Framework CSS au choix :** Bootstrap 4.x, Bootstrap 5.x ou Tailwind CSS
* *(Optionnel)* **FontAwesome :** Pour l'affichage d'icônes dans les boutons

---

## 💡 Espaces de noms (Namespaces)

La librairie propose un espace de nom dédié pour chaque framework CSS :

| Framework CSS | Classe PHP |
| :--- | :--- |
| **Bootstrap 4 (Défaut)** | `Weegosoft\Form\Form` ou `Weegosoft\Form\Bootstrap4\Form` |
| **Bootstrap 5** | `Weegosoft\Form\Bootstrap5\Form` |
| **Tailwind CSS** | `Weegosoft\Form\Tailwind\Form` |

---

## 🎨 Exemples d'utilisation

### 1. Bootstrap 5

```php
use Weegosoft\Form\Bootstrap5\Form;

// Données d'exemple (tableau ou objet)
$data = ['email' => 'john@weegosoft.com'];
$errors = ['email' => 'Adresse email déjà utilisée.'];

$form = new Form($data, $errors);

echo $form->open('/register', 'POST', true, [], 'csrf_token_here');

echo $form->text('username', 'Nom d\'utilisateur', ['placeholder' => 'johndoe']);
echo $form->email('email', 'Adresse Email');
echo $form->switch('notifications', 'Activer les notifications', 1);

echo $form->submit('Inscrire', ['class' => 'btn btn-primary', 'icon' => 'fas fa-user-plus']);
echo $form->close();
```

### 2. Bootstrap 4

```php
use Weegosoft\Form\Form; // Ou Weegosoft\Form\Bootstrap4\Form;

$form = new Form(['role' => 'admin']);

echo $form->open('/profile/update', 'POST');
echo $form->text('username', 'Nom d\'utilisateur');
echo $form->select('role', 'Rôle', [
    'admin' => 'Administrateur',
    'editor' => 'Éditeur',
    'user' => 'Utilisateur'
]);
echo $form->currency('price', 'Prix de vente', 'FCFA');
echo $form->submit('Mettre à jour');
echo $form->close();
```

### 3. Tailwind CSS

```php
use Weegosoft\Form\Tailwind\Form;

$form = new Form();

echo $form->open('/settings', 'POST', false, ['class' => 'space-y-4']);
echo $form->text('title', 'Titre de l\'application');
echo $form->submit('Enregistrer');
echo $form->close();
```

---

## 🎨 Composants UI avancés

* **Interrupteur (Switch) :** `$form->switch('is_active', 'Statut actif', 1)`
* **Zone d'upload (Dropzone) :** `$form->dropzone('documents', 'Glissez vos fichiers ici')`
* **Champ Monétaire (Input Group) :** `$form->currency('price', 'Prix', 'EUR')`
* **Groupe de boutons (Button Group) :** `$form->buttonGroup('status', 'Statut', ['draft' => 'Brouillon', 'published' => 'Publié'])`

---

## 🧪 Tests unitaires

Pour exécuter la suite complète de tests PHPUnit :

```bash
composer test
# ou
vendor/bin/phpunit
```

---

## 📂 Dossier d'exemples

Pour consulter des cas réels (Authentification, Profil, E-commerce, CRM, etc.), explorez le dossier `examples/` inclus dans le dépôt.

---

## 📄 Licence

Ce projet est sous licence **MIT**.
