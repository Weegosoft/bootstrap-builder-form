<?php
require_once __DIR__ . '/vendor/autoload.php';

useWeegosoft\Form;

// Simulation de données pour un article en cours de rédaction
$dummyArticle = [
    'title' => 'Les avantages de l\'intelligence artificielle dans l\'ERP',
    'slug' => 'avantages-ia-erp',
    'category' => 'tech',
    'status' => 'draft',
    'allow_comments' => 1
];

$form = new Form($dummyArticle);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exemples - Contenu & Interaction</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
        }

        .blog-card {
            border-radius: 10px;
            border: none;
        }

        .blog-card .card-header {
            border-radius: 10px 10px 0 0;
            font-weight: bold;
        }
    </style>
</head>

<body class="py-5">

    <div class="container">
        <h2 class="text-center mb-5">Exemples : Contenu & Interaction (Blog / CMS)</h2>

        <div class="row">

            <!-- ==========================================
             1. RÉDACTION D'ARTICLE (Éditeur CMS)
        =========================================== -->
            <div class="col-md-5 mb-4">
                <div class="card shadow-sm blog-card h-100">
                    <div class="card-header bg-primary text-white">
                        <i class="fas fa-edit mr-2"></i> Rédaction d'un Article
                    </div>
                    <div class="card-body">
                        <?= $form->open('/admin/blog/save', 'POST', true) ?>

                        <?= $form->text('title', 'Titre de l\'article', ['placeholder' => 'Saisissez un titre accrocheur...']) ?>

                        <?= $form->text('slug', 'Permalien (URL)', [
                            'input_group' => ['prepend' => '<span class="input-group-text">weegosoft.com/blog/</span>']
                        ]) ?>

                        <?= $form->select('category', 'Catégorie', [
                            'tech' => 'Technologie & IA',
                            'business' => 'Business & Stratégie',
                            'tutorials' => 'Tutoriels'
                        ]) ?>

                        <?= $form->image('thumbnail', 'Image à la une', ['help' => 'Taille recommandée : 1200x630px.']) ?>

                        <?= $form->textarea('content', 'Contenu principal', [
                            'rows' => 6,
                            'placeholder' => 'Écrivez votre article ici (le support d\'un éditeur WYSIWYG comme TinyMCE peut être ajouté via une classe custom)...',
                            'class' => 'wysiwyg-editor'
                        ]) ?>

                        <hr>

                        <div class="row">
                            <div class="col-md-6">
                                <?= $form->buttonGroup('status', 'Statut', [
                                    'draft' => 'Brouillon',
                                    'published' => 'Publier'
                                ]) ?>
                            </div>
                            <div class="col-md-6 mt-4 pt-1">
                                <?= $form->switch('allow_comments', 'Activer les commentaires') ?>
                            </div>
                        </div>

                        <div class="mt-4">
                            <?= $form->submit('Sauvegarder l\'article', [
                                'class' => 'btn btn-primary btn-block',
                                'icon' => 'fas fa-save'
                            ]) ?>
                        </div>

                        <?= $form->close() ?>
                    </div>
                </div>
            </div>

            <!-- ==========================================
             2. ESPACE COMMENTAIRES (Côté visiteur)
        =========================================== -->
            <div class="col-md-3 mb-4">
                <div class="card shadow-sm blog-card h-100">
                    <div class="card-header bg-dark text-white">
                        <i class="fas fa-comments mr-2"></i> Laisser un commentaire
                    </div>
                    <div class="card-body">
                        <p class="text-muted small">Participez à la discussion ! Votre adresse email ne sera pas publiée.</p>

                        <!-- Instance vierge pour le formulaire de commentaire -->
                        <?php $commentForm = new Form([]); ?>

                        <?= $commentForm->open('/blog/comment/add', 'POST') ?>

                        <?= $commentForm->text('author_name', 'Votre nom', ['required' => 'required']) ?>

                        <?= $commentForm->email('author_email', 'Adresse Email', ['required' => 'required']) ?>

                        <?= $commentForm->textarea('comment_body', 'Votre message', ['rows' => 4, 'required' => 'required']) ?>

                        <?= $commentForm->checkbox('save_info', 'Enregistrer mon nom et mon e-mail dans le navigateur pour mon prochain commentaire.') ?>

                        <div class="mt-4">
                            <?= $commentForm->submit('Publier', [
                                'class' => 'btn btn-dark btn-block',
                                'icon' => 'fas fa-paper-plane'
                            ]) ?>
                        </div>

                        <?= $commentForm->close() ?>
                    </div>
                </div>
            </div>

            <!-- ==========================================
             3. SONDAGES & ENQUÊTES DE SATISFACTION
        =========================================== -->
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm blog-card h-100">
                    <div class="card-header bg-success text-white">
                        <i class="fas fa-poll mr-2"></i> Enquête de satisfaction
                    </div>
                    <div class="card-body">
                        <!-- Instance vierge pour l'enquête -->
                        <?php $surveyForm = new Form([]); ?>

                        <?= $surveyForm->open('/survey/submit', 'POST') ?>

                        <h6 class="font-weight-bold text-muted mb-3">1. Comment évaluez-vous notre service ?</h6>

                        <!-- Utilisation de la méthode range pour un curseur de notation -->
                        <?= $surveyForm->range('rating', 'Note (de 1 à 10)', [
                            'min' => '1',
                            'max' => '10',
                            'step' => '1',
                            'value' => '8'
                        ]) ?>
                        <div class="d-flex justify-content-between small text-muted mt-n3 mb-3">
                            <span>Médiocre</span>
                            <span>Excellent</span>
                        </div>

                        <hr>

                        <h6 class="font-weight-bold text-muted mb-3">2. Recommanderiez-vous notre plateforme ?</h6>
                        <?= $surveyForm->radioList('recommend', '', [
                            'yes' => 'Oui, tout à fait',
                            'maybe' => 'Peut-être',
                            'no' => 'Non, pas du tout'
                        ]) ?>

                        <hr>

                        <h6 class="font-weight-bold text-muted mb-3">3. Avez-vous des suggestions ?</h6>
                        <?= $surveyForm->textarea('feedback', '', [
                            'rows' => 3,
                            'placeholder' => 'Ce que nous pourrions améliorer...'
                        ]) ?>

                        <div class="mt-4">
                            <?= $surveyForm->submit('Envoyer mes réponses', [
                                'class' => 'btn btn-success btn-block',
                                'icon' => 'fas fa-check-circle'
                            ]) ?>
                        </div>

                        <?= $surveyForm->close() ?>
                    </div>
                </div>
            </div>

        </div>
    </div>

</body>

</html>