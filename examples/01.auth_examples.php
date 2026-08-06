<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Weegosoft\Form\Form;

// Initialisation de la librairie (sans données ni erreurs pour ces exemples vierges)
$form = new Form();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exemples - Authentification & Sécurité</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome (pour les icônes des boutons) -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
        .auth-card { border-radius: 10px; border: none; }
        .auth-card .card-header { border-radius: 10px 10px 0 0; font-weight: bold; }
    </style>
</head>
<body class="py-5">

<div class="container">
    <h2 class="text-center mb-5">Exemples : Authentification & Sécurité</h2>

    <div class="row">
        
        <!-- ==========================================
             1. FORMULAIRE DE CONNEXION (LOGIN)
        =========================================== -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm auth-card h-100">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-sign-in-alt mr-2"></i> Connexion
                </div>
                <div class="card-body">
                    <?= $form->open('/login', 'POST') ?>
                        
                        <?= $form->email('email', 'Adresse Email', [
                            'placeholder' => 'contact@weegosoft.com',
                            'required' => 'required'
                        ]) ?>
                        
                        <?= $form->password('password', 'Mot de passe', [
                            'required' => 'required'
                        ]) ?>
                        
                        <?= $form->checkbox('remember', 'Se souvenir de moi') ?>
                        
                        <div class="mt-4">
                            <?= $form->submit('Se connecter', [
                                'class' => 'btn btn-primary btn-block', 
                                'icon' => 'fas fa-unlock'
                            ]) ?>
                        </div>
                        
                    <?= $form->close() ?>
                </div>
            </div>
        </div>

        <!-- ==========================================
             2. FORMULAIRE D'INSCRIPTION (REGISTER)
        =========================================== -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm auth-card h-100">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-user-plus mr-2"></i> Créer un compte
                </div>
                <div class="card-body">
                    <?= $form->open('/register', 'POST') ?>
                        
                        <?= $form->text('username', 'Nom d\'utilisateur') ?>
                        
                        <?= $form->email('email', 'Adresse Email') ?>
                        
                        <?= $form->password('password', 'Mot de passe') ?>
                        
                        <?= $form->password('password_confirm', 'Confirmer le mot de passe') ?>
                        
                        <?= $form->checkbox('terms', 'J\'accepte les CGU') ?>
                        
                        <div class="mt-4">
                            <?= $form->submit('S\'inscrire', [
                                'class' => 'btn btn-success btn-block', 
                                'icon' => 'fas fa-check-circle'
                            ]) ?>
                        </div>
                        
                    <?= $form->close() ?>
                </div>
            </div>
        </div>

        <!-- ==========================================
             3. MOT DE PASSE OUBLIÉ
        =========================================== -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm auth-card h-100">
                <div class="card-header bg-warning text-dark">
                    <i class="fas fa-question-circle mr-2"></i> Mot de passe oublié
                </div>
                <div class="card-body">
                    <p class="text-muted small">Entrez votre email pour recevoir un lien de réinitialisation.</p>
                    
                    <?= $form->open('/forgot-password', 'POST') ?>
                        
                        <?= $form->email('email', 'Adresse Email', [
                            'help' => 'Vérifiez également vos spams.'
                        ]) ?>
                        
                        <div class="mt-4">
                            <?= $form->submit('Réinitialiser', [
                                'class' => 'btn btn-warning btn-block text-dark', 
                                'icon' => 'fas fa-paper-plane'
                            ]) ?>
                        </div>
                        
                    <?= $form->close() ?>
                </div>
            </div>
        </div>

        <!-- ==========================================
             4. CHANGEMENT DE MOT DE PASSE
        =========================================== -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm auth-card h-100">
                <div class="card-header bg-info text-white">
                    <i class="fas fa-key mr-2"></i> Modifier le mot de passe
                </div>
                <div class="card-body">
                    <?= $form->open('/change-password', 'POST') ?>
                        
                        <?= $form->password('current_password', 'Mot de passe actuel') ?>
                        
                        <hr>
                        
                        <?= $form->password('new_password', 'Nouveau mot de passe', [
                            'help' => 'Doit contenir au moins 8 caractères.'
                        ]) ?>
                        
                        <?= $form->password('new_password_confirm', 'Confirmer le nouveau mot de passe') ?>
                        
                        <div class="mt-4">
                            <?= $form->submit('Mettre à jour', [
                                'class' => 'btn btn-info btn-block', 
                                'icon' => 'fas fa-save'
                            ]) ?>
                        </div>
                        
                    <?= $form->close() ?>
                </div>
            </div>
        </div>

        <!-- ==========================================
             5. DOUBLE AUTHENTIFICATION (2FA)
        =========================================== -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm auth-card h-100">
                <div class="card-header bg-dark text-white">
                    <i class="fas fa-shield-alt mr-2"></i> Double Authentification
                </div>
                <div class="card-body">
                    <p class="text-muted small">Veuillez saisir le code à 6 chiffres envoyé sur votre appareil.</p>
                    
                    <?= $form->open('/verify-2fa', 'POST') ?>
                        
                        <!-- Utilisation de classes custom pour un affichage de code -->
                        <?= $form->text('otp_code', 'Code de sécurité', [
                            'placeholder' => '• • • • • •',
                            'maxlength' => '6',
                            'class' => 'text-center font-weight-bold text-tracking-wide',
                            'style' => 'letter-spacing: 5px; font-size: 1.2rem;'
                        ]) ?>
                        
                        <div class="mt-4">
                            <?= $form->submit('Vérifier', [
                                'class' => 'btn btn-dark btn-block', 
                                'icon' => 'fas fa-check'
                            ]) ?>
                        </div>
                        
                    <?= $form->close() ?>
                </div>
            </div>
        </div>

    </div>
</div>

</body>
</html>