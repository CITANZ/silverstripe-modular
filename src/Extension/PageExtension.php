<?php

namespace Cita\Modular\Extension;

use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Dev\Debug;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataExtension;
use Cita\Modular\Model\Block;
use Symbiote\GridFieldExtensions\GridFieldAddNewMultiClass;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Forms\LiteralField;
use SilverStripe\View\ViewableData;
use SilverStripe\Forms\GridField\GridFieldDataColumns;


class PageExtension extends DataExtension
{
    /**
     * Many_many relationship
     * @var array
     */
    private static $many_many = [
        'ModularBlocks' => Block::class,
    ];

    /**
     * Defines Database fields for the Many_many bridging table
     *  @var array
     */
    private static $many_many_extraFields = [
        'ModularBlocks' => [
            'SortOrder' => 'Int',
        ]
    ];

    /**
     * Update Fields
     * @return FieldList
     */
    public function updateCMSFields(FieldList $fields)
    {
        if ($this->owner->config()->modulated) {
            $fields->removeByName([
                'Content',
            ]);

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
                $dataColumns->setDisplayFields([
                    'showID' => 'Anchor',
                    'Type' => 'Type',
                    'Title' => 'Title',
                    'BlockSummary' => 'Summary',
                ])->setFieldCasting([
                    'Type' => 'HTMLText->RAW',
                ]);
            }
        }

        return $fields;
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

        return $classes;
    }

    /**
     * Event handler called before writing to the database.
     *
     * @uses DataExtension->onAfterWrite()
     */
    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        $list = $this->owner->ModularBlocks()->filterByCallback(function($item) {
            return $item->isPublished();
        });

        $this->owner->Content = '';

        foreach ($list as $module) {
            $this->owner->Content .= $module->Plain . "\n";
        }
    }

    public function getModulars()
    {
        return ViewableData::create()
            ->customise(['ModularBlocks' => $this->owner->ModularBlocks()])
            ->renderWith('Cita\\Modular\\ModularList');
    }
}
