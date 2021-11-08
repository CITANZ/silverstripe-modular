<?php

namespace Cita\Modular\Traits;

use SilverStripe\Forms\TextField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\HeaderField;
use Symbiote\GridFieldExtensions\GridFieldAddNewMultiClass;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Forms\GridField\GridFieldDataColumns;
use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;
use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripe\Forms\GridField\GridField;
use Symbiote\GridFieldExtensions\GridFieldEditableColumns;
use SilverStripe\Core\ClassInfo;
use Cita\Modular\Model\Block;
use Cita\Modular\Model\FlexBlock;
use SilverStripe\View\ViewableData;
use Cita\Modular\Form\BlockEditForm_ItemRequest;

trait ModularCommonTrait
{
    private function makeGridField(&$fields)
    {
        if (!$this->owner->exists()) {
            return;
        }

        $fields->removeByName(['ModularBlocks']);

        $classes = $this->filterBlockClasses(ClassInfo::subclassesFor(Block::class));
        $classes = array_values($classes);

        if (empty($classes)) {
            return $this->handleNoClassCase(&$fields);
        }

        $this->buildGridFieldConfig($fields, $classes);
    }

    private function buildGridFieldConfig(&$fields, $classes)
    {
        $fields->addFieldToTab(
            'Root.Main',
            GridField::create(
                'ModularBlocks',
                'Modular blocks',
                $this->owner->ModularBlocks(),
                $config = GridFieldConfig_RelationEditor::create()
            )->setDescription('The blocks you add on this page will be listed under the page\'s main content')
        );

        $this->buildGridFieldConfigTable($config, $classes);

        if ($this->owner instanceof FlexBlock) {
            $this->createInlineEditors($config);
        }

        $config
            ->getComponentByType(GridFieldDetailForm::class)
            ->setItemRequestClass(BlockEditForm_ItemRequest::class)
        ;
    }

    private function createInlineEditors(&$config)
    {
        $config->addComponent($this->makeEditableField('ColOffset', 'Offset - default'));
        $config->addComponent($this->makeEditableField('ColSize', 'Grid size - default'));
        $config->addComponent($this->makeEditableField('ColOffsetLg', 'Offset - lg'));
        $config->addComponent($this->makeEditableField('ColSizeLg', 'Grid size - lg'));
        $config->addComponent($this->makeEditableField('ColOffsetMd', 'Offset - md'));
        $config->addComponent($this->makeEditableField('ColSizeMd', 'Grid size - md'));
        $config->addComponent($this->makeEditableField('ColSizeSm', 'Grid size - sm'));
        $config->addComponent($this->makeEditableField('ColOffsetSm', 'Offset - sm'));
    }

    private function buildGridFieldConfigTable(&$config, $classes)
    {
        $multi = new GridFieldAddNewMultiClass();
        $multi = $multi->setClasses($classes);

        $config
            ->removeComponentsByType(GridFieldAddNewButton::class)
            ->addComponent($multi)
            ->addComponent(new GridFieldOrderableRows('SortOrder'));

        $dataColumns = $config->getComponentByType(GridFieldDataColumns::class);

        $dataFields = $this->owner instanceof FlexBlock ?
            [
                'Type' => 'Type',
                'Title' => 'Title',
            ] : [
                'showID' => 'Anchor',
                'Type' => 'Type',
                'Title' => 'Title',
                'BlockSummary' => 'Summary',
            ]
        ;

        $dataColumns->setDisplayFields($dataFields)->setFieldCasting([
            'Type' => 'HTMLText->RAW',
        ]);
    }

    private function handleNoClassCase(&$fields)
    {
        $fields->addFieldsToTab(
            'Root.Main',
            [
                HeaderField::create(
                    'ModularBlocksNotice',
                    'Modular blocks'
                ),
                LiteralField::create(
                    'ModularBlocks',
                    '<p class="alert alert-warning">No block type available for this page type</p>'
                ),
            ]
        );
    }

    private function makeEditableField($fieldName, $fieldTitle)
    {
        $field = new GridFieldEditableColumns();
        $field->setDisplayFields(array(
            $fieldName => [
                'title' => $fieldTitle,
                'field' => TextField::class
            ]
        ));

        return $field;
    }

    private function filterBlockClasses($classes)
    {
        if (!empty($this->owner->config()->allowed_modulars)) {
            $classes = [];

            foreach ($this->owner->config()->allowed_modulars as $modular) {
                $classes[strtolower($modular)] = $modular;
            }
        } elseif (!empty($this->owner->config()->disallowed_modulars)) {
            foreach ($this->owner->config()->disallowed_modulars as $modular) {
                unset($classes[strtolower($modular)]);
            }
        }

        unset($classes[strtolower(Block::class)]);

        if ($this->owner instanceof FlexBlock) {
            unset($classes[strtolower(FlexBlock::class)]);
        }

        return $classes;
    }

    public function getModulars()
    {
        return ViewableData::create()
            ->customise(['ModularBlocks' => $this->owner->ModularBlocks()])
            ->renderWith(
                $this->owner instanceof FlexBlock ?
                'Cita\\Modular\\FlexModularList' : 'Cita\\Modular\\ModularList'
            )
        ;
    }
}
