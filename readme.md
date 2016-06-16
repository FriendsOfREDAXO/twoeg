twÖg AddOn
============================


Basic usage
-------------

**Set up Twig by instance**

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


---
**Call it statically too:**

    <?php

        // we use twÖg with default caching and template folder
        echo Twoeg::render('index.html', ['headline' => 'some static headline', 'text' => 'translate:twoeg_title']);

        // or even simpler without the echo command:
        Twoeg::out('index.html', ['headline' => 'some static headline', 'text' => 'translate:twoeg_title']);
    ?>


---
**Use rex_i18n::translate or rex_i18n::msg**

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

---
**Use any class methods of Redaxo**

Use rex::getServerName(), rex_clang::getAll() etc. functions in the template,
but instead of using two colons use two underscores - e.g.
rex::getServerName() becomes rex__getServerName():

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

---
**Or even more complex:**

Use rex::getUser()->hasRole('admin') or rex::getUser()->getValue('login')
in your templates by combining a function and a filter:

    <html>
        <body>
            {% if rex__getUser()|hasRole('admin') %}
            <p>
                User login name: {{ rex__getUser()|getValue('login') }}
            </p>
            {% endif %}
        </body>
    </html>

---
Setting template folder
-------------
By default, twÖg searches for templates in

    redaxo/data/addons/twoeg/template

To change that folder globally you can edit the package.yml in the twoeg addon folder and add

    template_folder: 'whatever/path/you/like'

It's also possible to change the template folder when you set up the twÖg instance:

    <?php
        // set up a new Twoeg instance
        $twoeg = new Twoeg([
            'template_folder' => 'whatever/path/you/like'
        ]);
    ?>

---
Caching templates
-------------
By default, twÖg will cache the loaded templates into this cache folder:

    redaxo/data/addons/twoeg/cache

To change that folder globally you can edit the package.yml in the twoeg addon folder and add

    cache_folder: 'whatever/path/you/like'

It's also possible to change the cache folder when you set up the twÖg instance:

    <?php
        // set up a new Twoeg instance
        $twoeg = new Twoeg([
            'cache_folder' => 'whatever/path/you/like'
        ]);
    ?>

To disable caching you have to set the folder to FALSE

    <?php
        // set up a new Twoeg instance
        $twoeg = new Twoeg([
            'cache_folder' => false
        ]);
    ?>

or you can use the Twig_Environment::setCache method through twÖg:

    <?php
        $twoeg = new Twoeg();
        $twoeg->setCache(false);
    ?>

---
Credits
-------
* [Twig template engine](http://twig.sensiolabs.org/) by Sensio Labs
* [twÖg redaxo addon]()
