<?php

namespace app\models;

use Yii;
use yii\base\Model;
use app\models\User;

/**
 * LoginForm is the model behind the login form
 * @property User|null $user This property is read-only
 */
class LoginForm extends Model
{
    /** @var string */
    public $username;
    /** @var string */
    public $password;

    /** @var User|false */
    private $_user = false;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['username', 'password'], 'required'],
            ['password', 'validatePassword'],
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();

            if (!$user || !$user->validatePassword($this->password))
                $this->addError($attribute, 'Incorrect username or password.');
        }
    }

    /**
     * Logs in a user using the provided username and password.
     * @return boolean whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            $user = $this->getUser();
            $user->last_login_at = time();
            $user->generateTokens();
            $user->save(false, ['last_login_at', 'access_token', 'refresh_token']);
            
            return Yii::$app->user->login($user, 3600*24*30);
        }

        return false;
    }

    /**
     * Finds user by [[username]]
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === false)
            $this->_user = User::findByUsername($this->username);

        return $this->_user;
    }
}
