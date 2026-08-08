<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Weegosoft\Form\Form;

// Données pré-remplies simulant la modification d'une facture existante
$dummyInvoice = [
    'client_id' => '1042',
    'due_date' => date('Y-m-d', strtotime('+30 days')),
    'amount_ht' => 450000,
    'tax_rate' => '18',
    'status' => 'draft'
];

$form = new Form($dummyInvoice);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exemples - ERP / CRM & Saisie de Données</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
        }

        .erp-card {
            border-radius: 10px;
            border: none;
        }

        .erp-card .card-header {
            border-radius: 10px 10px 0 0;
            font-weight: bold;
        }
    </style>
</head>

<body class="py-5">

    <div class="container">
        <h2 class="text-center mb-5">Exemples : Saisie de Données & Métier (ERP / CRM)</h2>

        <div class="row">

            <!-- ==========================================
             1. CRÉATION / ÉDITION DE FACTURE (Facturation)
        =========================================== -->
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm erp-card h-100">
                    <div class="card-header bg-primary text-white">
                        <i class="fas fa-file-invoice-dollar mr-2"></i> Nouvelle Facture
                    </div>
                    <div class="card-body">
                        <?= $form->open('/erp/invoices/save', 'POST') ?>

                        <?= $form->select('client_id', 'Client facturé', [
                            '' => '--- Sélectionner un client ---',
                            '1041' => 'Entreprise Alpha',
                            '1042' => 'Acme Corp',
                            '1043' => 'Société Générale'
                        ], ['required' => 'required']) ?>

                        <?= $form->date('due_date', 'Date d\'échéance') ?>

                        <?= $form->currency('amount_ht', 'Montant Total HT', 'FCFA') ?>

                        <?= $form->radioList('tax_rate', 'Taux de TVA', [
                            '0' => 'Exonéré (0%)',
                            '18' => 'Standard (18%)'
                        ]) ?>

                        <div class="mt-3 mb-4">
                            <?= $form->buttonGroup('status', 'Statut de la facture', [
                                'draft' => 'Brouillon',
                                'validated' => 'Validée',
                                'paid' => 'Payée'
                            ]) ?>
                        </div>

                        <?= $form->textarea('notes', 'Notes pour le client', ['rows' => 2]) ?>

                        <div class="mt-4">
                            <?= $form->submit('Enregistrer la facture', [
                                'class' => 'btn btn-primary btn-block',
                                'icon' => 'fas fa-save'
                            ]) ?>
                        </div>

                        <?= $form->close() ?>
                    </div>
                </div>
            </div>

            <!-- ==========================================
             2. GESTION DES STOCKS (Mouvement d'inventaire)
        =========================================== -->
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm erp-card h-100">
                    <div class="card-header bg-dark text-white">
                        <i class="fas fa-boxes mr-2"></i> Mouvement de Stock
                    </div>
                    <div class="card-body">
                        <?= $form->open('/erp/inventory/move', 'POST', true) ?>

                        <!-- Utilisation de la méthode autocomplete pour un gros catalogue -->
                        <?= $form->autocomplete('product_search', 'Rechercher un article', '/api/products/search', [
                            'placeholder' => 'Saisissez le nom ou le code barre...',
                            'help' => 'La recherche se fait dynamiquement.'
                        ]) ?>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <?= $form->number('quantity', 'Quantité', ['min' => 1, 'value' => 1]) ?>
                            </div>
                            <div class="col-md-6">
                                <?= $form->text('location', 'Emplacement', ['placeholder' => 'Ex: Allée B']) ?>
                            </div>
                        </div>

                        <div class="mt-3 mb-4">
                            <?= $form->buttonGroup('movement_type', 'Type de mouvement', [
                                'in' => '<i class="fas fa-arrow-down text-success"></i> Entrée',
                                'out' => '<i class="fas fa-arrow-up text-danger"></i> Sortie'
                            ]) ?>
                        </div>

                        <?= $form->file('proof_document', 'Bon de livraison / Justificatif') ?>

                        <div class="mt-4">
                            <?= $form->submit('Valider le mouvement', [
                                'class' => 'btn btn-dark btn-block',
                                'icon' => 'fas fa-check'
                            ]) ?>
                        </div>

                        <?= $form->close() ?>
                    </div>
                </div>
            </div>

            <!-- ==========================================
             3. RESSOURCES HUMAINES (Fiche Employé)
        =========================================== -->
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm erp-card h-100">
                    <div class="card-header bg-success text-white">
                        <i class="fas fa-id-badge mr-2"></i> Fiche Employé (RH)
                    </div>
                    <div class="card-body">
                        <?= $form->open('/erp/hr/employee/save', 'POST') ?>

                        <div class="row">
                            <div class="col-md-6">
                                <?= $form->text('first_name', 'Prénom') ?>
                            </div>
                            <div class="col-md-6">
                                <?= $form->text('last_name', 'Nom') ?>
                            </div>
                        </div>

                        <?= $form->date('hire_date', 'Date d\'embauche') ?>

                        <?= $form->select('department_id', 'Département', [
                            'it' => 'Développement & IT',
                            'marketing' => 'Marketing & Ventes',
                            'hr' => 'Ressources Humaines',
                            'direction' => 'Direction'
                        ]) ?>

                        <?= $form->radioList('contract_type', 'Type de contrat', [
                            'cdi' => 'CDI',
                            'cdd' => 'CDD',
                            'intern' => 'Stage'
                        ]) ?>

                        <hr class="my-4">

                        <!-- Toggle switch pour activer ou désactiver l'accès au système -->
                        <?= $form->switch('system_access', 'Accès au système ERP', 1) ?>

                        <div class="mt-4">
                            <?= $form->submit('Créer le dossier', [
                                'class' => 'btn btn-success btn-block',
                                'icon' => 'fas fa-user-check'
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