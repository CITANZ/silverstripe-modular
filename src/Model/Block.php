<?php

namespace Cita\Modular\Model;

use SilverStripe\Dev\Debug;
use SilverStripe\Forms\CompositeField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordViewer;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\ORM\DataObject;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Versioned\Versioned;
use SilverStripe\Forms\LiteralField;
use Page;

/**
 * Description
 *
 * @package silverstripe
 * @subpackage mysite
 */
class Block extends DataObject
{
    /**
     * Defines the database table name
     * @var string
     */
    private static $table_name  =   'Cita_Modular_Block';

    /**
     * Singular name for CMS
     * @var string
     */
    private static $singular_name = 'Block';

    private static $icon_class = 'font-icon-help-circled';

    /**
     * Database fields
     * @var array
     */
    private static $db = [
        'Anchor' => 'Varchar(16)',
        'Title' => 'Varchar(128)',
        'TitleDisplayRule' => 'Enum("Show,Sr-only,No output")',
    ];

    /**
     * Defines summary fields commonly used in table columns
     * as a quick overview of the data for this dataobject
     * @var array
     */
    private static $summary_fields = [
        'showID' => 'Anchor',
        'singular_name' => 'Type',
        'Title' => 'Title',
        'BlockSummary' => 'Summary',
    ];

    /**
     * Belongs_many_many relationship
     * @var array
     */
    private static $belongs_many_many = [
        'Pages' => Page::class,
        'FlexBlocks' => FlexBlock::class . '.ModularBlocks'
    ];

    /**
     * Defines a default list of filters for the search context
     * @var array
     */
    private static $searchable_fields = [
        'Title'
    ];

    /**
     * Defines extension names and parameters to be applied
     *  to this object upon construction.
     *  @var array
     */
    private static $extensions = [
        Versioned::class,
    ];

    public function showID()
    {
        if (!empty($this->Anchor)) {
            return $this->Anchor;
        }

        return strtolower('modular-block-' . $this->ID);
    }

    public function getType()
    {
        return '<span style="white-space: nowrap; display: flex; align-items: center;">
            <i
                style="font-size: 24px; display: inline-block; margin-right: 0.25em;"
                class="' . $this->config()->icon_class . '"
            ></i>' .
            $this->singular_name() .
        '</span>';
    }

    /**
     * CMS Fields
     * @return FieldList
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        // this is to hold the place for Title field, which we are going to replace with a composite field later
        $fields->insertBefore('Title', LiteralField::create('PosHolder', ''));

        $anchorField = $fields->fieldByName('Root.Main.Anchor');
        $titleField = $fields->dataFieldByName('Title');
        $ruleField = $fields->dataFieldByName('TitleDisplayRule');

        $fields->removeByName(['Title', 'Plain', 'Pages', 'FlexBlocks', 'TitleDisplayRule']);

        $fields->addFieldToTab(
            'Root.Main',
            CompositeField::create(
                [
                    $titleField->setTitle(null),
                    $ruleField->setTitle(null),
                ]
            )->setName('ModularTitleHolder')->setTitle('Title'),
            'PosHolder'
        );

        $fields->removeByName(['PosHolder']);

        if (!empty($anchorField)) {
            $anchorField
                ->setDescription(
                    'This will be used as the HTML\'s "id" attribute.
                    If left blank, it will fall back to use the block\'s default id' .
                    ($this->exists() ? (': modular-block-' . $this->ID) : '')
                )
            ;

            $fields->addFieldToTab(
                'Root.Configurations',
                $anchorField
            );
        }

        if ($this->exists()) {
            $fields->addFieldToTab(
                'Root.Used on Pages',
                GridField::create(
                    'Pages',
                    'Pages',
                    $this->Pages(),
                    GridFieldConfig_RecordViewer::create()
                )
            );
        }

        return $fields;
    }

    public function getPlain()
    {
        return '';
    }

    public function getBlockSummary()
    {
        return '';
    }

    public function OutputTitle()
    {
        return $this->TitleDisplayRule !== 'No output';
    }

    public function Renderer($heading = 2)
    {
        return $this->customise(['Heading' => $heading])->renderWith([$this->ClassName, Block::class]);
    }

    public function getTitleFieldClasses()
    {
        if (empty($this->TitleDisplayRule) || $this->TitleDisplayRule == 'Show') {
            return '';
        }

        return strtolower($this->TitleDisplayRule);
    }

    public function forTemplate()
    {
        return $this->Renderer();
    }
}
