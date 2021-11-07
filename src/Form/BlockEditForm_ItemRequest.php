<?php

namespace Cita\Modular\Form;

use SilverStripe\Dev\Debug;
use SilverStripe\Forms\GridField\GridFieldDetailForm_ItemRequest;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormAction;

class BlockEditForm_ItemRequest extends GridFieldDetailForm_ItemRequest
{
    private static $allowed_actions = ['ItemEditForm'];

    public function ItemEditForm()
    {
        $form = parent::ItemEditForm();

        $actions = $form->Actions();

        if ($rightGroup = $actions->fieldByName('RightGroup')) {
            $rightGroup->addExtraClass('cita-modular-quick-actions');
        }

        return $form;
    }
}
