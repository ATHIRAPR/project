<?php
namespace Cums\Validator;

class UpdateDirectoryValidator extends Validator
{
    public function __construct($parameters, $fieldset)
    {
        parent::__construct($parameters, $fieldset);

        $this->validation
            ->set_message('required', ':labelは必須です。')
            ->set_message('match_collection', ':labelの値が不正です。')
            ->set_message('valid_string', ':labelの値が不正です。')
            ->set_message('max_length', ':labelは256文字以下です。')
            ->add_callable(new DirectoryRules());
        $this->validation
            ->add('domainid', 'ドメインID（domainid）')
            ->add_rule('valid_string', ['numeric'])
            ->add_rule('exist_domainid');
        $this->validation
            ->add('path', 'パス（path）')
            ->add_rule('max_length', 256)
            ->add_rule('unique_path', $parameters['domainid'], $parameters['directoryid']);
        $this->validation
            ->add('enableflg', '有効フラグ（enableflg）')
            ->add_rule('match_collection', ['0', '1']);
    }
}
