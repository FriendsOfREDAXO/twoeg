twÖg AddOn
============================

This redaxo addon adds [Twig template engine](http://twig.sensiolabs.org) support for Redaxo 5.

Basic usage
-------------

**Set up Twig by instance**

```php
<?php
    // set up a new Twoeg instance
    $twoeg = new Twoeg();

    // you can use any Twig_Environment method on this class now -
    // for example disable cache:
    $twoeg->setCache(false);

    // load template 'index.html' from default templates folder
    $template = $twoeg->loadTemplate('index.html');

    // render the template and submit variables to be placed in the template:
    echo $template->render([
        'headline' => 'Some headline!',
        'text' => 'translate:twoeg_title'
    ]);
?>
```

---
**Call it statically too:**

```php
<?php

    // we use twÖg with default caching and template folder
    echo Twoeg::render('index.html', ['headline' => 'some static headline', 'text' => 'translate:twoeg_title']);

    // or even simpler without the echo command:
    Twoeg::out('index.html', ['headline' => 'some static headline', 'text' => 'translate:twoeg_title']);
?>
```

---
**Use rex_i18n::translate or rex_i18n::msg**
```html
<html>
    <body>
        <p>{{ 'translate:twoeg_title'|translate }}</p>
        <!-- outputs "twÖg - Twig template engine for Redaxo" //-->

        <p>{{ 'twoeg_title'|msg }}</p>
        <!-- outputs "twÖg - Twig template engine for Redaxo" //-->

        <p>{{ 'twoeg_test'|msg('some', 'variable') }}</p>
        <!-- outputs "twÖg - with »some« of »variable«" //-->

    </body>
</html>
```
---
**Use any class methods of Redaxo**

Use rex::getServerName(), rex_clang::getAll() etc. functions in the template,
but instead of using two colons use two underscores - e.g.
rex::getServerName() becomes rex__getServerName():

```html
<html>
    <body>
        <p>
            Server name: {{ rex__getServerName() }}
        </p>
        <!-- outputs "Server name: MY_SERVER_NAME" //-->

        <ul>
        {% for item in rex_clang__getAll() %}
            <li>
                {{ item.id }}: {{ item.code }} ({{ item.name}})
            </li>
        {% endfor %}
        </ul>

        <!-- outputs "1: de (deutsch)" //-->

    </body>
</html>
```
---
**Or even more complex:**

Use rex::getUser()->hasRole('admin') or rex::getUser()->getValue('login')
in your templates by combining a function and a filter:

```html
<html>
    <body>
        {% if rex__getUser()|hasRole('admin') %}
        <p>
            User login name: {{ rex__getUser()|getValue('login') }}
        </p>
        {% endif %}
    </body>
</html>
```
---
Setting template folder
-------------
By default, twÖg searches for templates in

    redaxo/data/addons/twoeg/template

To change that folder globally you can edit the package.yml in the twoeg addon folder and add

    template_folder: 'whatever/path/you/like'

It's also possible to change the template folder when you set up the twÖg instance:

```php
<?php
    // set up a new Twoeg instance
    $twoeg = new Twoeg([
        'template_folder' => 'whatever/path/you/like'
    ]);

    // or statically
    Twoeg::out(
        'index.html', // the template
        ['headline' => 'some static headline', 'text' => 'translate:twoeg_title'], // the variables
        ['template_folder' => 'whatever/path/you/like'] // the options
    );
?>
```
---
Caching templates
-------------
By default, twÖg will cache the loaded templates into this cache folder:

    redaxo/data/addons/twoeg/cache

To change that folder globally you can edit the package.yml in the twoeg addon folder and add

    cache_folder: 'whatever/path/you/like'

It's also possible to change the cache folder when you set up the twÖg instance:

```php
<?php
    // set up a new Twoeg instance
    $twoeg = new Twoeg([
        'cache_folder' => 'whatever/path/you/like'
    ]);

    // or statically
    Twoeg::out(
        'index.html', // the template
        ['headline' => 'some static headline', 'text' => 'translate:twoeg_title'], // the variables
        ['cache_folder' => 'whatever/path/you/like'] // the options
    );
?>
```

To disable caching you have to set the folder to FALSE

```php
<?php
    // set up a new Twoeg instance
    $twoeg = new Twoeg([
        'cache_folder' => false
    ]);

    // or statically
    Twoeg::out(
        'index.html',
        ['headline' => 'some static headline', 'text' => 'translate:twoeg_title'],
        ['cache_folder' => false]
    );
?>
```
or you can use the Twig_Environment::setCache method through twÖg:

```php
<?php
    $twoeg = new Twoeg();
    $twoeg->setCache(false);
?>    
```
---
Filters
-------------
By default, twÖg will use these filters
    
    /* translate : */   {{ 'rex_i18n_string_to_translate'|translate }}
    
    /* msg : */         {{ 'rex_i18n_string_to_translate'|msg('some', 'variables') }}
    
    /* get* : */        {{ SOMEOBJECT|getValue('login') }}
    
    /* has* : */        {{ SOMEOBJECT|hasRole('admin') }}

You can add your own filters by providing a "twig_filter" variable when you set up twÖg:
```php
<?php
    // set up a new Twoeg instance
    $twoeg = new Twoeg([
        'twig_filter' => new \Twig_SimpleFilter('myfilter', function ($string) { return strtolower($string); })
    ]);

    // or statically
    Twoeg::out(
        'index.html',
        ['headline' => 'some static headline', 'text' => 'translate:twoeg_title'],
        ['twig_filter' => new \Twig_SimpleFilter('myfilter', function ($string) { return strtolower($string); })]
    );
?>  
```
---
Functions
-------------
By default, twÖg will try to use any public method of any rex* class that is currently set up,
but make sure you replace the two colons by a double underscore:

    {{ rex_clang__getCurrentId() }}

    {% for item in rex_clang__getAll() %}{{ item.id }}{% endfor %}
    
    // etc.

You can then add a get* or has* filter to the returned value:

    {{ rex__getUser()|getValue('login') }}

You can add your own functions by providing a "twig_function" variable when you set up twÖg:
```php
<?php
    // set up a new Twoeg instance
    $twoeg = new Twoeg([
        'twig_function' => new \Twig_SimpleFunction('myfunction', function () { return 'Hello world'; })
    ]);

    // or statically
    Twoeg::out(
        'index.html',
        ['headline' => 'some static headline', 'text' => 'translate:twoeg_title'],
        ['twig_function' => new \Twig_SimpleFunction('myfunction', function () { return 'Hello world'; })]
    );
?>
```
---
Twig Extension
-------------
You can use any Twig Extension provided by Twig by simply setting a twig_extension variable
when you set up twÖg:
```php
<?php
    // set up a new Twoeg instance
    $twoeg = new Twoeg([
        'twig_extension' => ['intl', date']
    ]);

    // or statically
    Twoeg::out(
        'index.html',
        ['headline' => 'some static headline', 'text' => 'translate:twoeg_title'],
        ['twig_extension' => ['intl', date']]
    );
?>
```
These extensions are available: [Array](http://twig.sensiolabs.org/doc/extensions/array.html), [Date](http://twig.sensiolabs.org/doc/extensions/date.html), [I18n](http://twig.sensiolabs.org/doc/extensions/i18n.html), [Intl](http://twig.sensiolabs.org/doc/extensions/intl.html), [Text](http://twig.sensiolabs.org/doc/extensions/text.html).


---
Credits
-------
* [Twig template engine](http://twig.sensiolabs.org/) by Sensio Labs
* [twÖg redaxo addon](https://github.com/FriendsOfREDAXO/twoeg)
