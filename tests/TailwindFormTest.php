<?php

namespace Weegosoft\Form\Tests;

use PHPUnit\Framework\TestCase;
use Weegosoft\Form\Tailwind\Form as FormTailwind;

class TailwindFormTest extends TestCase
{
    public function testFormInstantiationWithoutArgs(): void
    {
        $form = new FormTailwind();
        $this->assertInstanceOf(FormTailwind::class, $form);
        $this->assertEquals("", (string)$form);
    }

    public function testOpenAndCloseForm(): void
    {
        $form = new FormTailwind();
        $htmlOpen = $form->open('/submit-tailwind', 'POST', false, ['class' => 'space-y-4'], 'tokenTW');
        $this->assertStringContainsString('<form action="/submit-tailwind" method="POST" class="space-y-4">', $htmlOpen);
        $this->assertStringContainsString('name="_token" value="tokenTW"', $htmlOpen);

        $htmlClose = $form->close();
        $this->assertEquals('</form>', $htmlClose);
    }

    public function testTextInputWithTailwindClasses(): void
    {
        $data = ['title' => 'Tailwind Form'];
        $form = new FormTailwind($data);

        $html = $form->text('title', 'Titre de l\'article');
        $this->assertStringContainsString('Titre de l&#039;article', $html);
        $this->assertStringContainsString('name="title"', $html);
        $this->assertStringContainsString('value="Tailwind Form"', $html);
    }

    public function testErrorHandlingTailwind(): void
    {
        $errors = ['title' => 'Le titre est obligatoire.'];
        $form = new FormTailwind([], $errors);

        $html = $form->text('title', 'Titre');
        $this->assertStringContainsString('Le titre est obligatoire.', $html);
    }

    public function testSelectFieldTailwind(): void
    {
        $form = new FormTailwind(['theme' => 'dark']);
        $html = $form->select('theme', 'Thème', ['light' => 'Clair', 'dark' => 'Sombre']);

        $this->assertStringContainsString('<select', $html);
        $this->assertStringContainsString('name="theme"', $html);
        $this->assertStringContainsString('<option value="dark" selected>Sombre</option>', $html);
    }

    public function testButtonTailwind(): void
    {
        $form = new FormTailwind();
        $html = $form->submit('Enregistrer');

        $this->assertStringContainsString('type="submit"', $html);
        $this->assertStringContainsString('Enregistrer', $html);
    }
}
