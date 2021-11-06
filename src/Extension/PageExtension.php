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
use SilverStripe\Forms\GridField\GridFieldDataColumns;
use Cita\Modular\Traits\ModularCommonTrait;

class PageExtension extends DataExtension
{
    use ModularCommonTrait;
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

            $this->makeGridField($fields);
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
}
