<?php

namespace Cita\Modular\Model;

use SilverStripe\View\Parsers\ShortcodeParser;
use SilverStripe\ORM\FieldType\DBHTMLText;
use Cita\Modular\Traits\ModularCommonTrait;

class FlexBlock extends Block
{
    use ModularCommonTrait;
    /**
     * Defines the database table name
     *  @var string
     */
    private static $table_name = 'Cita_Modular_FlexBlock';

    private static $icon_class = 'font-icon-columns';

    /**
     * Singular name for CMS
     *  @var string
     */
    private static $singular_name = 'Flex block';

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
            'ColOffset' => 'Varchar(8)',
            'ColSize' => 'Varchar(8)',
            'ColOffsetSm' => 'Varchar(8)',
            'ColSizeSm' => 'Varchar(8)',
            'ColOffsetMd' => 'Varchar(8)',
            'ColSizeMd' => 'Varchar(8)',
            'ColOffsetLg' => 'Varchar(8)',
            'ColSizeLg' => 'Varchar(8)',
        ],
    ];

    /**
     * CMS Fields
     * @return FieldList
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $this->makeGridField($fields);

        return $fields;
    }

    public function getPlain()
    {
        return "<h2>" . $this->Title . "</h2>" . $this->Content;
    }

    public function getBlockSummary()
    {
        return DBHTMLText::create()->setValue(strip_tags(ShortcodeParser::get_active()->parse($this->Content)))->Summary(15);
    }
}
