<?php
namespace Cums\Validator;

class UpdateContentValidator extends Validator
{
    public function __construct($parameters, $fieldset)
    {
        parent::__construct($parameters, $fieldset);

        $this->validation
            ->set_message('match_collection', ':labelの値が不正です。')
            ->set_message('valid_date', ':labelの値が不正です。')
            ->set_message('max_length', ':labelは256文字以下です。')
            ->add_callable(new UserRules());
        $this->validation
            ->add('userid', '棚卸担当者（userid）')
            ->add_rule('max_length', 256)
            ->add_rule('exist_userid');
        $this->validation
            ->add('inventorystatus', '棚卸ステータス（inventorystatus）')
            ->add_rule('match_collection', ['0', '1']);
        $this->validation
            ->add('inventory', '棚卸対象ステータス（inventory）')
            ->add_rule('match_collection', ['0', '1']);
        $this->validation
            ->add('disableflg', '有効フラグ（disableflg）')
            ->add_rule('match_collection', ['0', '1']);
        $this->validation
            ->add('inventoryduedate', '棚卸期限（inventoryduedate）')
            ->add_rule('valid_date');
    }
}
