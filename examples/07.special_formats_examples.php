<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Weegosoft\Form;

// Simulation de filtres actifs dans l'URL (ex: $_GET)
$activeFilters = [
    'search_query' => 'Ordinateur portable',
    'price_max' => 500000,
    'brand' => ['hp', 'dell']
];

$filterForm = new Form($activeFilters);
$wizardForm = new Form([]);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exemples - Composants & Formats Spéciaux</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
        }

        .special-card {
            border-radius: 10px;
            border: none;
        }

        .special-card .card-header {
            border-radius: 10px 10px 0 0;
            font-weight: bold;
        }

        .step-indicator {
            width: 30px;
            height: 30px;
            line-height: 30px;
            text-align: center;
            border-radius: 50%;
            display: inline-block;
        }
    </style>
</head>

<body class="py-5">

    <div class="container">
        <h2 class="text-center mb-5">Exemples : Composants & Formats Spéciaux</h2>

        <div class="row">

            <!-- ==========================================
             1. MOTEUR DE RECHERCHE & FILTRES AVANCÉS
        =========================================== -->
            <div class="col-md-5 mb-4">
                <div class="card shadow-sm special-card h-100">
                    <div class="card-header bg-dark text-white">
                        <i class="fas fa-filter mr-2"></i> Filtres de Recherche Avancée
                    </div>
                    <div class="card-body">
                        <!-- Souvent en GET pour que l'URL soit partageable -->
                        <?= $filterForm->open('/search', 'GET') ?>

                        <?= $filterForm->search('search_query', 'Mots-clés', [
                            'placeholder' => 'Que recherchez-vous ?',
                            'input_group' => [
                                'prepend' => '<span class="input-group-text"><i class="fas fa-search"></i></span>'
                            ]
                        ]) ?>

                        <hr>

                        <h6 class="font-weight-bold text-muted mb-3">Prix maximum</h6>
                        <?= $filterForm->range('price_max', '', [
                            'min' => '0',
                            'max' => '1000000',
                            'step' => '10000'
                        ]) ?>
                        <div class="text-right small font-weight-bold text-primary mb-3">
                            <span id="price-val"><?= $activeFilters['price_max'] ?? '0' ?></span> FCFA
                        </div>

                        <h6 class="font-weight-bold text-muted mb-3">Marques (Choix multiple)</h6>
                        <!-- Utilisation du checkboxList pour les filtres multiples -->
                        <div class="max-height-200 overflow-auto border p-2 rounded mb-3">
                            <?= $filterForm->checkboxList('brand', null, [
                                'apple' => 'Apple',
                                'samsung' => 'Samsung',
                                'hp' => 'HP',
                                'dell' => 'Dell',
                                'lenovo' => 'Lenovo'
                            ]) ?>
                        </div>

                        <div class="d-flex mt-4">
                            <?= $filterForm->reset('Réinitialiser', ['class' => 'btn btn-outline-secondary flex-fill mr-2']) ?>
                            <?= $filterForm->submit('Filtrer', ['class' => 'btn btn-dark flex-fill', 'icon' => 'fas fa-sort-amount-down']) ?>
                        </div>

                        <?= $filterForm->close() ?>
                    </div>
                </div>
            </div>

            <!-- ==========================================
             2. FORMULAIRE MULTI-ÉTAPES (WIZARD - Étape 1)
        =========================================== -->
            <div class="col-md-7 mb-4">
                <div class="card shadow-sm special-card h-100">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-magic mr-2"></i> Inscription Multi-étapes (Wizard)</span>
                        <span class="badge badge-light text-primary">Étape 1 sur 3</span>
                    </div>
                    <div class="card-body">

                        <!-- Barre de progression visuelle -->
                        <div class="d-flex justify-content-between mb-4 position-relative">
                            <div class="progress position-absolute" style="top: 15px; left: 10%; right: 10%; height: 3px; z-index: 1;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%;"></div>
                            </div>
                            <div class="text-center position-relative" style="z-index: 2;">
                                <span class="step-indicator bg-primary text-white font-weight-bold shadow">1</span>
                                <div class="small mt-1 font-weight-bold text-primary">Identité</div>
                            </div>
                            <div class="text-center position-relative" style="z-index: 2;">
                                <span class="step-indicator bg-light text-muted border">2</span>
                                <div class="small mt-1 text-muted">Coordonnées</div>
                            </div>
                            <div class="text-center position-relative" style="z-index: 2;">
                                <span class="step-indicator bg-light text-muted border">3</span>
                                <div class="small mt-1 text-muted">Validation</div>
                            </div>
                        </div>

                        <?= $wizardForm->open('/wizard/step-2', 'POST') ?>

                        <!-- Champ caché pour suivre la progression du formulaire -->
                        <?= $wizardForm->hidden('current_step', '1') ?>

                        <div class="row">
                            <div class="col-md-6">
                                <?= $wizardForm->text('first_name', 'Prénom') ?>
                            </div>
                            <div class="col-md-6">
                                <?= $wizardForm->text('last_name', 'Nom') ?>
                            </div>
                        </div>

                        <?= $wizardForm->date('birth_date', 'Date de naissance') ?>

                        <?= $wizardForm->radioList('gender', 'Genre', [
                            'm' => 'Homme',
                            'f' => 'Femme',
                            'o' => 'Autre / Préfère ne pas préciser'
                        ]) ?>

                        <div class="d-flex justify-content-end border-top pt-3 mt-4">
                            <?= $wizardForm->submit('Suivant', [
                                'class' => 'btn btn-primary px-4',
                                'icon' => 'fas fa-arrow-right',
                                'style' => 'flex-direction: row-reverse;' // Pour mettre l'icône à droite si besoin (via CSS custom)
                            ]) ?>
                        </div>

                        <?= $wizardForm->close() ?>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        // Petit script pour la mise à jour dynamique du curseur de prix (Filtre)
        document.addEventListener('DOMContentLoaded', function() {
            const priceInput = document.querySelector('input[name="price_max"]');
            const priceDisplay = document.getElementById('price-val');

            if (priceInput && priceDisplay) {
                priceInput.addEventListener('input', function(e) {
                    // Formater le nombre avec des espaces pour les milliers
                    priceDisplay.textContent = Number(e.target.value).toLocaleString('fr-FR');
                });
            }
        });
    </script>
</body>

</html>