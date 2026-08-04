<?php
require_once __DIR__ . '/vendor/autoload.php';

useWeegosoft\Form;

// Simulation des données d'un utilisateur provenant de la base de données
$dummyUser = [
    'first_name' => 'Keyam',
    'last_name' => 'Dev',
    'bio' => 'Créateur de solutions logicielles et passionné par l\'innovation tech.',
    'website' => 'https://weegosoft.com',
    'email_notif' => 1,          // 1 = activé
    'push_notif' => 0,           // 0 = désactivé
    'language' => 'fr',
    'theme' => 'dark'
];

// Initialisation du formulaire avec les données pour le pré-remplissage
$form = new Form($dummyUser);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exemples - Profil & Gestion d'utilisateur</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
        }

        .profile-card {
            border-radius: 10px;
            border: none;
        }

        .profile-card .card-header {
            border-radius: 10px 10px 0 0;
            font-weight: bold;
        }
    </style>
</head>

<body class="py-5">

    <div class="container">
        <h2 class="text-center mb-5">Exemples : Profil & Gestion d'utilisateur</h2>

        <div class="row">

            <!-- ==========================================
             1. ÉDITION DE PROFIL (Informations publiques)
        =========================================== -->
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm profile-card h-100">
                    <div class="card-header bg-primary text-white">
                        <i class="fas fa-user-edit mr-2"></i> Informations Publiques
                    </div>
                    <div class="card-body">
                        <?= $form->open('/profile/update', 'POST', true) ?> <!-- true pour autoriser l'upload d'image -->

                        <div class="row">
                            <div class="col-md-6">
                                <?= $form->text('first_name', 'Prénom') ?>
                            </div>
                            <div class="col-md-6">
                                <?= $form->text('last_name', 'Nom') ?>
                            </div>
                        </div>

                        <?= $form->image('avatar', 'Photo de profil', [
                            'help' => 'Formats acceptés : JPG, PNG. Max 2Mo.'
                        ]) ?>

                        <?= $form->textarea('bio', 'Biographie', ['rows' => 3]) ?>

                        <?= $form->url('website', 'Site Web ou Portfolio') ?>

                        <div class="mt-4">
                            <?= $form->submit('Enregistrer le profil', [
                                'class' => 'btn btn-primary',
                                'icon' => 'fas fa-save'
                            ]) ?>
                        </div>

                        <?= $form->close() ?>
                    </div>
                </div>
            </div>

            <!-- ==========================================
             2. PRÉFÉRENCES & PARAMÈTRES (UI / UX)
        =========================================== -->
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm profile-card h-100">
                    <div class="card-header bg-info text-white">
                        <i class="fas fa-cogs mr-2"></i> Préférences de l'application
                    </div>
                    <div class="card-body">
                        <?= $form->open('/profile/preferences', 'POST') ?>

                        <h6 class="font-weight-bold text-muted mb-3">Notifications</h6>

                        <!-- Utilisation de la méthode switch pour des réglages modernes -->
                        <?= $form->switch('email_notif', 'Recevoir les alertes par Email') ?>
                        <?= $form->switch('push_notif', 'Activer les notifications Push') ?>

                        <hr>

                        <h6 class="font-weight-bold text-muted mb-3">Affichage</h6>

                        <?= $form->select('language', 'Langue de l\'interface', [
                            'fr' => 'Français',
                            'en' => 'English',
                            'es' => 'Español'
                        ]) ?>

                        <!-- Utilisation de radioList pour un choix rapide -->
                        <?= $form->radioList('theme', 'Thème d\'affichage', [
                            'light' => 'Clair',
                            'dark' => 'Sombre',
                            'auto' => 'Système'
                        ]) ?>

                        <div class="mt-4">
                            <?= $form->submit('Mettre à jour les préférences', [
                                'class' => 'btn btn-info',
                                'icon' => 'fas fa-check-double'
                            ]) ?>
                        </div>

                        <?= $form->close() ?>
                    </div>
                </div>
            </div>

            <!-- ==========================================
             3. CONFIDENTIALITÉ & COMPTE (RGPD)
        =========================================== -->
            <div class="col-md-12 mb-4">
                <div class="card shadow-sm profile-card">
                    <div class="card-header bg-dark text-white">
                        <i class="fas fa-shield-alt mr-2"></i> Sécurité & Gestion des données (RGPD)
                    </div>
                    <div class="card-body">
                        <?= $form->open('/profile/security', 'POST') ?>

                        <div class="row">
                            <div class="col-md-6 border-right">
                                <h6 class="font-weight-bold text-muted mb-3">Modification de l'accès</h6>
                                <?= $form->email('new_email', 'Nouvelle adresse email') ?>
                                <?= $form->password('confirm_password', 'Mot de passe actuel (pour confirmer)') ?>
                                <?= $form->submit('Changer l\'email', ['class' => 'btn btn-outline-dark btn-sm mt-2']) ?>
                            </div>

                            <div class="col-md-6 pl-md-4">
                                <h6 class="font-weight-bold text-muted mb-3">Export & Suppression</h6>

                                <?= $form->checkbox('export_data', 'Demander une archive complète de mes données (Règlement RGPD)') ?>

                                <div class="mt-3">
                                    <!-- Utilisation du buttonGroup pour une sélection critique -->
                                    <?= $form->buttonGroup('account_action', 'Action sur le compte', [
                                        'keep' => 'Conserver mon compte',
                                        'deactivate' => 'Désactiver temporairement',
                                        'delete' => 'Supprimer définitivement'
                                    ]) ?>
                                </div>

                                <div class="mt-4">
                                    <?= $form->submit('Appliquer la décision', [
                                        'class' => 'btn btn-danger',
                                        'icon' => 'fas fa-exclamation-triangle'
                                    ]) ?>
                                </div>
                            </div>
                        </div>

                        <?= $form->close() ?>
                    </div>
                </div>
            </div>

        </div>
    </div>

</body>

</html>