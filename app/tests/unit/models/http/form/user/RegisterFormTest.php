<?php


namespace Unit\models\http\form\user;

use app\models\http\form\user\RegisterForm;
use Codeception\Test\Unit;
use UnitTester;

class RegisterFormTest extends Unit
{
    protected UnitTester $tester;

    protected function _before() {}

    // tests
    public function testValidationIsOk()
    {
        // Arrange
        $model = new RegisterForm();
        $model->login = 'admin';
        $model->password = '12345678';
        $model->email = 'admin@example.com';

        // Act
        $valid = $model->validate();

        // Assert
        $this->assertTrue($valid);
    }

    public function testValidationPasswordToShort()
    {
        // Arrange
        $model = new RegisterForm();
        $model->login = 'admin';
        $model->password = '123';
        $model->email = 'admin@example.com';

        // Act
        $valid = $model->validate();

        // Assert
        $this->assertFalse($valid);
    }

    public function testValidationMissingLogin()
    {
        // Arrange
        $model = new RegisterForm();
        $model->password = '12345678';
        $model->email = 'admin@example.com';

        // Act
        $valid = $model->validate();

        // Assert
        $this->assertFalse($valid);
    }

    public function testValidationMissingPassword()
    {
        // Arrange
        $model = new RegisterForm();
        $model->login = 'admin';
        $model->email = 'admin@example.com';

        // Act
        $valid = $model->validate();

        // Assert
        $this->assertFalse($valid);
    }

    public function testValidationLoginTooLong()
    {
        // Arrange
        $model = new RegisterForm();
        $model->login = 'adminadminadminadminadminadminadminadminadminadminadminadminadminadminadminadmin
        adminadminadminadminadminadminadminadminadminadminadminadminadminadminadminadmin
        adminadminadminadminadminadminadminadminadminadminadminadminadminadminadminadmin
        adminadminadminadminadminadminadminadminadminadminadminadminadminadminadminadmin
        adminadminadminadminadminadminadminadminadminadminadminadminadminadminadminadmin
        adminadminadminadminadminadminadminadminadminadminadminadminadminadminadminadmin';
        $model->password = '12345678';
        $model->email = 'admin@example.com';

        // Act
        $valid = $model->validate();

        // Assert
        $this->assertFalse($valid);
    }

    public function testValidationEmailMissing()
    {
        // Arrange
        $model = new RegisterForm();
        $model->login = 'admin';
        $model->password = '12345678';

        // Act
        $valid = $model->validate();

        // Assert
        $this->assertFalse($valid);
    }

    public function testValidationEmailInvalid()
    {
        // Arrange
        $model = new RegisterForm();
        $model->login = 'admin';
        $model->password = '12345678';
        $model->email = 'adminexamplecom';

        // Act
        $valid = $model->validate();

        // Assert
        $this->assertFalse($valid);
    }
}
