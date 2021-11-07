<?php

namespace Cita\Modular\Form;

use SilverStripe\Dev\Debug;
use SilverStripe\Versioned\VersionedGridFieldItemRequest;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormAction;
use SilverStripe\Versioned\VersionedGridFieldDetailForm;

class BlockEditForm_ItemRequest extends VersionedGridFieldItemRequest
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
