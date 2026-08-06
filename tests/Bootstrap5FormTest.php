<?php

namespace Weegosoft\Form\Tests;

use PHPUnit\Framework\TestCase;
use Weegosoft\Form\Bootstrap5\Form as FormBS5;

class Bootstrap5FormTest extends TestCase
{
    public function testFormInstantiationWithoutArgs(): void
    {
        $form = new FormBS5();
        $this->assertInstanceOf(FormBS5::class, $form);
        $this->assertEquals("", (string)$form);
    }

    public function testOpenAndCloseForm(): void
    {
        $form = new FormBS5();
        $htmlOpen = $form->open('/submit-bs5', 'POST', true, ['id' => 'myFormBS5'], 'tokenBS5');
        $this->assertStringContainsString('<form action="/submit-bs5" method="POST" enctype="multipart/form-data" id="myFormBS5">', $htmlOpen);
        $this->assertStringContainsString('name="_token" value="tokenBS5"', $htmlOpen);

        $htmlClose = $form->close();
        $this->assertEquals('</form>', $htmlClose);
    }

    public function testTextInputWithBS5Classes(): void
    {
        $data = ['name' => 'Jean Dupont'];
        $form = new FormBS5($data);

        $html = $form->text('name', 'Nom complet', ['placeholder' => 'Entrez votre nom']);
        $this->assertStringContainsString('class="form-label">Nom complet</label>', $html);
        $this->assertStringContainsString('class="form-control"', $html);
        $this->assertStringContainsString('value="Jean Dupont"', $html);
    }

    public function testErrorFeedbackBS5(): void
    {
        $errors = ['name' => ['Le nom est requis.', 'Trop court.']];
        $form = new FormBS5([], $errors);

        $html = $form->text('name', 'Nom');
        $this->assertStringContainsString('is-invalid', $html);
        $this->assertStringContainsString('<div class="invalid-feedback d-block">Le nom est requis.<br>Trop court.</div>', $html);
    }

    public function testBS5Switch(): void
    {
        $data = ['is_active' => '1'];
        $form = new FormBS5($data);

        $html = $form->switch('is_active', 'Statut actif', '1');
        $this->assertStringContainsString('form-check form-switch', $html);
        $this->assertStringContainsString('type="checkbox"', $html);
        $this->assertStringContainsString('checked', $html);
    }

    public function testBS5Select(): void
    {
        $form = new FormBS5(['country' => 'FR']);
        $html = $form->select('country', 'Pays', ['FR' => 'France', 'CA' => 'Canada']);

        $this->assertStringContainsString('<select', $html);
        $this->assertStringContainsString('name="country"', $html);
        $this->assertStringContainsString('class="form-select"', $html);
        $this->assertStringContainsString('<option value="FR" selected>France</option>', $html);
    }

    public function testBS5Dropzone(): void
    {
        $form = new FormBS5();
        $html = $form->dropzone('avatar', 'Déposez votre photo');

        $this->assertStringContainsString('dropzone', $html);
        $this->assertStringContainsString('Déposez votre photo', $html);
    }
}
