<?php

namespace Cita\Modular\Model;

use SilverStripe\View\Parsers\ShortcodeParser;
use SilverStripe\ORM\FieldType\DBHTMLText;

class ContentBlock extends Block
{
    /**
     * Defines the database table name
     *  @var string
     */
    private static $table_name = 'Cita_Modular_ContentBlock';

    private static $icon_class = 'font-icon-block-content';

    /**
     * Singular name for CMS
     *  @var string
     */
    private static $singular_name = 'Content block';

    /**
     * Database fields
     * @var array
     */
    private static $db = [
        'Content' => 'HTMLText',
    ];

    public function getPlain()
    {
        return "<h2>" . $this->Title . "</h2>" . $this->Content;
    }

    public function getBlockSummary()
    {
        return DBHTMLText::create()->setValue(strip_tags(ShortcodeParser::get_active()->parse($this->Content)))->Summary(15);
    }
}
