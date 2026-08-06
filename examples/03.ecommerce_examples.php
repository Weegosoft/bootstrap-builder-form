<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Weegosoft\Form\Form;

// Simulation des données d'un produit en cours d'édition
$dummyProduct = [
    'product_name' => 'Licence Pro - Système de Gestion',
    'category_id' => 'software',
    'price' => 150000,
    'stock' => 99,
    'status' => 'published',
    'delivery_method' => 'express',
    'payment_method' => 'momo'
];

$form = new Form($dummyProduct);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exemples - E-commerce & Vente</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
        .ecommerce-card { border-radius: 10px; border: none; }
        .ecommerce-card .card-header { border-radius: 10px 10px 0 0; font-weight: bold; }
    </style>
</head>
<body class="py-5">

<div class="container">
    <h2 class="text-center mb-5">Exemples : E-commerce & Vente</h2>

    <div class="row">
        
        <!-- ==========================================
             1. GESTION DE PRODUIT (Création / Édition)
        =========================================== -->
        <div class="col-md-7 mb-4">
            <div class="card shadow-sm ecommerce-card h-100">
                <div class="card-header bg-dark text-white">
                    <i class="fas fa-box-open mr-2"></i> Ajouter / Modifier un Produit
                </div>
                <div class="card-body">
                    <?= $form->open('/admin/products/save', 'POST', true) ?>
                        
                        <div class="row">
                            <div class="col-md-8">
                                <?= $form->text('product_name', 'Nom du produit') ?>
                            </div>
                            <div class="col-md-4">
                                <?= $form->select('category_id', 'Catégorie', [
                                    'hardware' => 'Matériel & Équipement',
                                    'software' => 'Logiciel & SaaS',
                                    'services' => 'Services'
                                ]) ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <!-- La méthode currency par défaut utilise FCFA -->
                                <?= $form->currency('price', 'Prix de vente', 'FCFA', ['min' => 0]) ?>
                            </div>
                            <div class="col-md-6">
                                <?= $form->number('stock', 'Quantité en stock', ['min' => 0]) ?>
                            </div>
                        </div>

                        <!-- Upload d'images multiples -->
                        <?= $form->images('gallery', 'Galerie photos du produit', [
                            'help' => 'Vous pouvez sélectionner plusieurs images.'
                        ]) ?>
                        
                        <div class="mt-3 mb-4">
                            <!-- Toggle group pour le statut du produit -->
                            <?= $form->buttonGroup('status', 'Statut de publication', [
                                'draft' => 'Brouillon',
                                'published' => 'Publié',
                                'archived' => 'Archivé'
                            ]) ?>
                        </div>
                        
                        <div class="mt-2">
                            <?= $form->submit('Enregistrer le produit', [
                                'class' => 'btn btn-dark', 
                                'icon' => 'fas fa-save'
                            ]) ?>
                        </div>
                        
                    <?= $form->close() ?>
                </div>
            </div>
        </div>

        <!-- ==========================================
             2. TUNNEL DE COMMANDE & PAIEMENT (Checkout)
        =========================================== -->
        <div class="col-md-5 mb-4">
            <div class="card shadow-sm ecommerce-card h-100">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-shopping-cart mr-2"></i> Validation de Commande
                </div>
                <div class="card-body">
                    <?= $form->open('/checkout/process', 'POST') ?>
                        
                        <h6 class="font-weight-bold text-muted border-bottom pb-2 mb-3">Informations de livraison</h6>
                        
                        <?= $form->text('customer_name', 'Nom complet', ['placeholder' => 'Ex: Jean Dupont']) ?>
                        
                        <!-- Utilisation de l'alias address() pour le textarea -->
                        <?= $form->address('shipping_address', 'Adresse de livraison', ['rows' => 2]) ?>
                        
                        <?= $form->radioList('delivery_method', 'Mode de livraison', [
                            'standard' => 'Standard (3-5 jours)',
                            'express' => 'Express (24h)'
                        ]) ?>
                        
                        <h6 class="font-weight-bold text-muted border-bottom pb-2 mb-3 mt-4">Paiement sécurisé</h6>
                        
                        <?= $form->radioList('payment_method', 'Moyen de paiement', [
                            'momo' => 'Mobile Money (MTN / Airtel)',
                            'card' => 'Carte Bancaire',
                            'cash' => 'Paiement à la livraison'
                        ]) ?>
                        
                        <!-- Code Promo avec input_group personnalisé -->
                        <?= $form->text('promo_code', 'Code Promo', [
                            'input_group' => [
                                'append' => '<button type="button" class="btn btn-outline-secondary">Appliquer</button>'
                            ]
                        ]) ?>
                        
                        <div class="mt-4">
                            <?= $form->submit('Confirmer et Payer', [
                                'class' => 'btn btn-success btn-block btn-lg', 
                                'icon' => 'fas fa-check-circle'
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