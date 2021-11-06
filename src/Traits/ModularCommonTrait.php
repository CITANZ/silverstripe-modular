<?php

namespace Cita\Modular\Traits;

use SilverStripe\Forms\TextField;
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

trait ModularCommonTrait
{
    private function makeGridField(&$fields)
    {
        if (!$this->owner->exists()) {
            return;
        }

        $fields->removeByName(['ModularBlocks']);

        $multi = new GridFieldAddNewMultiClass();

        $classes = $this->filterBlockClasses(ClassInfo::subclassesFor(Block::class));
        $classes = array_values($classes);

        if (empty($classes)) {
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
        } else {
            $fields->addFieldToTab(
                'Root.Main',
                GridField::create(
                    'ModularBlocks',
                    'Modular blocks',
                    $this->owner->ModularBlocks(),
                    $config = GridFieldConfig_RelationEditor::create()
                )->setDescription('The blocks you add on this page will be listed under the page\'s main content')
            );

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
                ] :
                [
                    'showID' => 'Anchor',
                    'Type' => 'Type',
                    'Title' => 'Title',
                    'BlockSummary' => 'Summary',
                ]
            ;

            $dataColumns->setDisplayFields($dataFields)->setFieldCasting([
                'Type' => 'HTMLText->RAW',
            ]);

            if ($this->owner instanceof FlexBlock) {
                $config->addComponent($this->makeEditableField('ColSizeSm', 'Grid size - sm'), GridFieldDataColumns::class);
                $config->addComponent($this->makeEditableField('ColOffsetSm', 'Offset - sm'), GridFieldDataColumns::class);
                $config->addComponent($this->makeEditableField('ColSizeMd', 'Grid size - md'), GridFieldDataColumns::class);
                $config->addComponent($this->makeEditableField('ColOffsetMd', 'Offset - md'), GridFieldDataColumns::class);
                $config->addComponent($this->makeEditableField('ColSizeLg', 'Grid size - lg'), GridFieldDataColumns::class);
                $config->addComponent($this->makeEditableField('ColOffsetLg', 'Offset - lg'), GridFieldDataColumns::class);
                $config->addComponent($this->makeEditableField('ColSize', 'Grid size - default'), GridFieldDataColumns::class);
                $config->addComponent($this->makeEditableField('ColOffset', 'Offset - default'), GridFieldDataColumns::class);
            }
        }
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
            'Cita\\Modular\\FlexModularList' :
            'Cita\\Modular\\ModularList'
        );
    }
}
