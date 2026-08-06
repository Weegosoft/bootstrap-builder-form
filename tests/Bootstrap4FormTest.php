<?php

namespace Weegosoft\Form\Tests;

use PHPUnit\Framework\TestCase;
use Weegosoft\Form\Form;
use Weegosoft\Form\Bootstrap4\Form as FormBS4;

class DummyUser
{
    public string $username = 'johndoe';
    private string $email = 'john@weegosoft.com';

    public function getEmail(): string
    {
        return $this->email;
    }
}

class DummyArrayAccess implements \ArrayAccess
{
    private array $container = ['role' => 'admin'];

    public function offsetExists($offset): bool
    {
        return isset($this->container[$offset]);
    }

    public function offsetGet($offset): mixed
    {
        return $this->container[$offset] ?? null;
    }

    public function offsetSet($offset, $value): void
    {
        $this->container[$offset] = $value;
    }

    public function offsetUnset($offset): void
    {
        unset($this->container[$offset]);
    }
}

class Bootstrap4FormTest extends TestCase
{
    public function testFormInstantiationWithoutArgs(): void
    {
        $form = new Form();
        $this->assertInstanceOf(Form::class, $form);
        $this->assertEquals("", (string)$form);

        $bs4Form = new FormBS4();
        $this->assertInstanceOf(FormBS4::class, $bs4Form);
    }

    public function testOpenAndCloseForm(): void
    {
        $form = new Form();
        $htmlOpen = $form->open('/submit', 'POST', true, ['id' => 'myForm'], 'token123');
        $this->assertStringContainsString('<form action="/submit" method="POST" enctype="multipart/form-data" id="myForm">', $htmlOpen);
        $this->assertStringContainsString('name="_token" value="token123"', $htmlOpen);

        $htmlClose = $form->close();
        $this->assertEquals('</form>', $htmlClose);
    }

    public function testCsrf(): void
    {
        $form = new Form();
        $csrfHtml = $form->csrf('secret-token');
        $this->assertEquals('<input type="hidden" name="_token" value="secret-token" />', $csrfHtml);
    }

    public function testTextInputWithArrayData(): void
    {
        $data = ['username' => 'alice'];
        $form = new Form($data);

        $html = $form->text('username', 'Nom d\'utilisateur', ['placeholder' => 'Entrez votre nom']);
        $this->assertStringContainsString('name="username"', $html);
        $this->assertStringContainsString('id="inputusername"', $html);
        $this->assertStringContainsString('value="alice"', $html);
        $this->assertStringContainsString('class="form-control"', $html);
        $this->assertStringContainsString('placeholder="Entrez votre nom"', $html);
        $this->assertStringContainsString('class="control-label">Nom d&#039;utilisateur</label>', $html);
    }

    public function testInputWithObjectAndGetterData(): void
    {
        $user = new DummyUser();
        $form = new Form($user);

        $htmlUsername = $form->text('username', 'Username');
        $this->assertStringContainsString('value="johndoe"', $htmlUsername);

        $htmlEmail = $form->email('email', 'Email');
        $this->assertStringContainsString('value="john@weegosoft.com"', $htmlEmail);
    }

    public function testInputWithArrayAccessData(): void
    {
        $data = new DummyArrayAccess();
        $form = new Form($data);

        $htmlRole = $form->text('role', 'Role');
        $this->assertStringContainsString('value="admin"', $htmlRole);
    }

    public function testErrorHandlingAndInvalidFeedback(): void
    {
        $errors = ['email' => 'Adresse email invalide'];
        $form = new Form([], $errors);

        $html = $form->email('email', 'Email');
        $this->assertStringContainsString('is-invalid', $html);
        $this->assertStringContainsString('<div class="invalid-feedback d-block">Adresse email invalide</div>', $html);
    }

    public function testSelectField(): void
    {
        $data = ['category' => 'php'];
        $form = new Form($data);

        $options = ['php' => 'PHP', 'js' => 'JavaScript'];
        $html = $form->select('category', 'Catégorie', $options);

        $this->assertStringContainsString('<select', $html);
        $this->assertStringContainsString('name="category"', $html);
        $this->assertStringContainsString('<option value="php" selected>PHP</option>', $html);
        $this->assertStringContainsString('<option value="js">JavaScript</option>', $html);
    }

    public function testCheckboxField(): void
    {
        $data = ['remember' => '1'];
        $form = new Form($data);

        $html = $form->checkbox('remember', 'Se souvenir de moi');
        $this->assertStringContainsString('type="checkbox"', $html);
        $this->assertStringContainsString('checked', $html);
        $this->assertStringContainsString('Se souvenir de moi', $html);
    }

    public function testTextareaField(): void
    {
        $data = ['bio' => 'Développeur PHP'];
        $form = new Form($data);

        $html = $form->textarea('bio', 'Biographie');
        $this->assertStringContainsString('<textarea name="bio"', $html);
        $this->assertStringContainsString('>Développeur PHP</textarea>', $html);
    }

    public function testButtons(): void
    {
        $form = new Form();
        $submit = $form->submit('Valider', ['class' => 'btn btn-primary', 'icon' => 'fas fa-check']);
        $this->assertStringContainsString('type="submit"', $submit);
        $this->assertStringContainsString('<i class="fas fa-check', $submit);
        $this->assertStringContainsString('Valider', $submit);

        $reset = $form->reset('Réinitialiser');
        $this->assertStringContainsString('type="reset"', $reset);

        $btn = $form->button('Action', ['class' => 'btn btn-secondary']);
        $this->assertStringContainsString('type="button"', $btn);
    }

    public function testSpecialInputs(): void
    {
        $form = new Form();

        $switch = $form->switch('status', 'Actif', '1');
        $this->assertStringContainsString('custom-switch', $switch);

        $dropzone = $form->dropzone('file_upload', 'Glissez vos fichiers');
        $this->assertStringContainsString('dropzone', $dropzone);

        $currency = $form->currency('price', 'Prix', 'EUR');
        $this->assertStringContainsString('EUR', $currency);
    }
}
