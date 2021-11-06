# CitaNZ's SilverStripe modular module
Requires: `SilverStripe 4.0+`

CitaNZ Modular is a lightweight block module which uses `ManymanyList` to link the pages and the content blocks, and builds the site with the "modulated" concept. 

It not only allows the users to reuse the same blocks on different pages across site, but also give the developer a safe fall protection when a block class becomes unavailable (e.g. a deleted block subclass will fall back to `Block::cass`, instead of crashing the block edit list in the CMS).

## Installation
```
composer require cita/silverstripe-modular
sake dev/build flush=all
```
Make sure you also `/dev/build?flush=all` on the web too

## Enable Modular on the class
Please note: if the page's `Content` field is already in use, then DO NOT enable this module!!!

Add `$modulated` static variable to the class
```
...
class SomePageType extends Page
{
    ...
    private static $modulated = true;
    ...
}
```

and then flush the site cache (`/?flush=all`)

## Build your on Modular blocks
Create a new class, and extend it from `Cita\Modular\Model\Block`

```
...
use Cita\Modular\Model\Block;

class MyShmartBlock extends Block
{
    ...
    private static $icon_class = 'font-icon-block-content'; // choose an icon
    private static $singular_name = 'Content block'; // singular name is also the block type

    public function getPlain()
    {
        return 'Extract the text content on this block. It will be used for search purpose. If you are using your own search index implementation, then you don\'t need to worry about this';
    }

    public function getBlockSummary()
    {
        return 'Return a summary to explain what this block is about - this will be used on the block\'s gridfield';
    }
}
```

## Allowing/Disallowing block types
below line will only allow `BlockA::class`, `BlockB::class` and `BlockC::class` on the page
```
private static $allowed_modulars = [BlockA::class, BlockB::class, BlockC::class]
```

below line will exclude `BlockA::class`, `BlockB::class` and `BlockC::class` from the available block types;
```
private static $disallowed_modulars = [BlockA::class, BlockB::class, BlockC::class]
```

## Frontend
To print the modular list, just place `$Modulars` in the `.ss` files where it's needed
```
<h1>The shmart page</h1>
$Modulars
```

## Templating
When a new block subclass is created, the default Block class template (`Cita\Modular\Model\Block`) will be applied to keep the frontend page stick together. The next step is to create the new subclass's template (make sure it matches the correct namespace), and then flush the cache on the browser -- now all you need to do is to build your block's HTML in the template file and style it with your frontend skills.
### Overriding CitaNZ's modular default templates
If you need to tweak the default block's HTML and/or change how the modular blocks get listed on the page, please follow the steps:

1. cp -rf `vendor/cita/silverstripe-modular/templates/Cita` `your_theme/templates/.` (replace `your_theme` accordingly...)
2. flush (on the browser)
3. inspect the `.ss` files in it
3. do your thing

## Flex Block
Flex block offers you a quick way to group different blocks in a flex `row`. If you are using `Vuetify` or `Bootstrap` or something similar in your frontend stacks, then all you need to do is to set the column sizes in the CMS. Otherwise, you will have to implement the `flexbox` by yourself.

## Questions?
Q: Why don't you...

A: Pull request plz

Q: I need to do such such such... so can you add this feature blah blah blah...

A: This module is meant to be lightweight, so let's keep it that way. I offer the nose pick, but I don't do the pick! (bet you don't want that either!)
