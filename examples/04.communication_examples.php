<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Utilisation de l'espace de nom d'origine de votre classe
use Weegosoft\Form\Form;

$form = new Form([]);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exemples - Communication & Support</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
        }

        .comm-card {
            border-radius: 10px;
            border: none;
        }

        .comm-card .card-header {
            border-radius: 10px 10px 0 0;
            font-weight: bold;
        }
    </style>
</head>

<body class="py-5">

    <div class="container">
        <h2 class="text-center mb-5">Exemples : Communication & Support</h2>

        <div class="row">

            <!-- ==========================================
             1. FORMULAIRE DE CONTACT CLASSIQUE
        =========================================== -->
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm comm-card h-100">
                    <div class="card-header bg-primary text-white">
                        <i class="fas fa-envelope mr-2"></i> Nous Contacter
                    </div>
                    <div class="card-body">
                        <?= $form->open('/contact/send', 'POST') ?>

                        <?= $form->text('full_name', 'Nom complet', [
                            'placeholder' => 'Ex: Jean Dupont'
                        ]) ?>

                        <?= $form->email('email', 'Adresse Email') ?>

                        <?= $form->text('subject', 'Sujet de votre message') ?>

                        <?= $form->textarea('message', 'Votre message', [
                            'rows' => 4,
                            'placeholder' => 'Comment pouvons-nous vous aider ?'
                        ]) ?>

                        <div class="mt-4">
                            <?= $form->submit('Envoyer le message', [
                                'class' => 'btn btn-primary btn-block',
                                'icon' => 'fas fa-paper-plane'
                            ]) ?>
                        </div>

                        <?= $form->close() ?>
                    </div>
                </div>
            </div>

            <!-- ==========================================
             2. TICKET DE SUPPORT TECHNIQUE
        =========================================== -->
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm comm-card h-100">
                    <div class="card-header bg-danger text-white">
                        <i class="fas fa-life-ring mr-2"></i> Ouvrir un Ticket d'Incident
                    </div>
                    <div class="card-body">
                        <?= $form->open('/support/ticket', 'POST', true) ?>

                        <?= $form->select('issue_category', 'Catégorie du problème', [
                            'bug' => 'Bug / Erreur technique',
                            'billing' => 'Problème de facturation',
                            'account' => 'Accès au compte',
                            'other' => 'Autre'
                        ]) ?>

                        <?= $form->radioList('priority', 'Niveau de priorité', [
                            'low' => 'Basse',
                            'normal' => 'Normale',
                            'high' => 'Urgente'
                        ]) ?>

                        <?= $form->textarea('description', 'Description détaillée', [
                            'rows' => 3,
                            'help' => 'Décrivez précisément les étapes pour reproduire le problème.'
                        ]) ?>

                        <!-- Utilisation de la méthode dropzone pour les pièces jointes -->
                        <div class="mt-3">
                            <?= $form->dropzone('attachments', 'Glissez vos captures d\'écran ici') ?>
                        </div>

                        <div class="mt-4">
                            <?= $form->submit('Soumettre le ticket', [
                                'class' => 'btn btn-danger btn-block',
                                'icon' => 'fas fa-ticket-alt'
                            ]) ?>
                        </div>

                        <?= $form->close() ?>
                    </div>
                </div>
            </div>

            <!-- ==========================================
             3. PRISE DE RENDEZ-VOUS / RÉSERVATION
        =========================================== -->
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm comm-card h-100">
                    <div class="card-header bg-info text-white">
                        <i class="fas fa-calendar-check mr-2"></i> Prise de Rendez-vous
                    </div>
                    <div class="card-body">
                        <?= $form->open('/booking/reserve', 'POST') ?>

                        <p class="text-muted small mb-4">Sélectionnez le service et le créneau qui vous conviennent.</p>

                        <?= $form->select('service_type', 'Type de consultation', [
                            'demo' => 'Démonstration logicielle (30 min)',
                            'audit' => 'Audit technique (1h)',
                            'consulting' => 'Consulting Stratégique (1h30)'
                        ]) ?>

                        <div class="row mt-3">
                            <div class="col-6">
                                <!-- Champ HTML5 date natif -->
                                <?= $form->date('appointment_date', 'Date souhaitée') ?>
                            </div>
                            <div class="col-6">
                                <!-- Champ HTML5 time natif -->
                                <?= $form->time('appointment_time', 'Heure') ?>
                            </div>
                        </div>

                        <?= $form->tel('phone_number', 'Numéro de téléphone', [
                            'placeholder' => '+242 ...',
                            'help' => 'Pour vous joindre en cas de besoin.'
                        ]) ?>

                        <div class="mt-4">
                            <?= $form->submit('Confirmer la réservation', [
                                'class' => 'btn btn-info btn-block text-white',
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