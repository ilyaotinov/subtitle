<?php

namespace Unit\models;

use app\models\User;
use Codeception\Specify;
use Codeception\Test\Unit;
use \UnitTester;

class UserTest extends Unit
{
    use Specify;

    protected UnitTester $tester;

    protected function _before() {}

    public function testValidation()
    {
        $user = User::create();
        $user->login = 'simple login';
        $this->assertTrue($user->validate(['login']));
        $user->login = '1343949493994993493493949394943943934943939
            34939493949393494399493949394fsdkfskdjfskdfjskdjfklsdjfalksjlsdja
            sadfkljskladjfklsadjfklsajdflasjdfkldsajlfjasdlfkjsldfjakldfjlask
            sadflkjsalkdjfalskdjfaklsdjflkasdjflkasjdflkasjdflkajsdflkjsadlkf
            asdlkfjskldjfaklsjdfklasjdflkasjfdklajsdflkjasldkfjaslkdjflaksdfj
            askdljfaskljfklsajdfkljsadfkljsadlfkjasdlfjasdklfjaslkdfjaskldfjs
            klsdjfklsajdflkjsdklfajsdklfjaslkdfjalsjfdlkasjdflkasjdflkasjdfkl
            asldkflskdjafkljasdklfjasldkfjslkdxcmvnzxm,cvnmz,xcvnxm,cvnm,nksd
            dofiguidofsugpdfugiposdufgpoisudfgoidusfopgiusdoifgusdiofguiopdss
            weqrjkwqehrqwkjehrqwophjosqdhfsiqjdbfniqwefbqiuwfberiubfuieqrbfiu';
        $this->assertFalse($user->validate(['login']));

        $user->email = 'example@example.com';
        $this->assertTrue($user->validate('email'));
        $user->email = 'invalid-email';
        $this->assertFalse($user->validate('email'));
    }
}
