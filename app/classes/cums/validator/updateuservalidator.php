<?php
namespace Cums\Validator;

class UpdateUserValidator extends Validator
{
    public function __construct($parameters, $fieldset)
    {
        parent::__construct($parameters, $fieldset);

        $this->validation
            ->set_message('required', ':labelは必須です。')
            ->set_message('match_collection', ':labelの値が不正です。')
            ->set_message('valid_email', ':labelの値が不正です。')
            ->set_message('min_length', ':labelは8文字以上です。')
            ->set_message('max_length', ':labelは256文字以下です。')
            ->add_callable(new UserRules());
        $this->validation
            ->add('mail', 'メールアドレス（mail）')
            ->add_rule('valid_email')
            ->add_rule('unique_mail', $parameters['userid']);
        $this->validation
            ->add('username', '氏名（username）')
            ->add_rule('max_length', 256);
        $this->validation
            ->add('password', 'パスワード（password）')
            ->add_rule('min_length', 8);
        $this->validation
            ->add('permissiontype', '権限タイプ（permissiontype）')
            ->add_rule('match_collection', ['0', '1']);
        $this->validation
            ->add('enableflg', '有効フラグ（enableflg）')
            ->add_rule('match_collection', ['0', '1']);
    }
}
