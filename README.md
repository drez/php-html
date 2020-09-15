# A class to write Html in PHP

I hate HTML! I hate putting multiple language is PHP files. So I use a small class that produces HTML and add some shortcut.

So I write HTML like that:

```
$Html = new Html('bootstrap', true);

$Html
    ->jumbotron(null)
        ->addH1("Use HJSON intead of XML for Propel")
        ->addP("This library is part of APIgoat.")
    ->close()
    ->div(null, ['class' => 'card-group'])
        ->div(null, ['class' => 'card'])
            ->div(null, ['class' => 'card-header'])
                ->addSpan("HJSON to Propel XML schema.", ["style" => "display: inline-block;width: 49%;"])
                ->div(null, ["style" => "display: inline-block;width: 50%;text-align: right;"])
                    ->addBut("Convert", ['id' => 'convert', 'type' => 'primary'])
            ->close()
        ->close()
        ->div(null, ['class' => 'card-body'])
            ->addDiv("", ['id' => 'hjson-editor', "style" => "height: 100%; width: 100%"])
        ->close('all', 1)
        ->div(null, ['class' => 'card'])
            ->div(null, ['class' => 'card-header'])
                ->addSpan("HJSON to Propel XML schema.", ["style" => "display: inline-block;width: 49%;"])
                ->div(null, ["style" => "display: inline-block;width: 50%;text-align: right;"])
                ->addBut("Download schema", ['id' => 'download', 'type' => 'primary'])
            ->close()
        ->close()
        ->div(null, ['class' => 'card-body'])
        ->addDiv("", ['id' => 'xml-editor', 'addclass' => 'editor', "style" => "height: 100%; width: 100%"])
    ->close('all');

    $html =  $Html->body(null, ['class' => 'body'])->addContainerFull($Html->getBuffer())->close()->getPage();

```

# Features

* Easy to add shortcut for the most use tag
* Add classes automatically if needed

# Use

    composer require drez/php-html