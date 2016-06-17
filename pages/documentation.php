<?php
$content = '
<section class="rex-page-section">
    <div class="panel panel-info">
        <header class="panel-heading">
            <div class="panel-title">'.$this->i18n('documentation').'</div>
        </header>
        <div class="panel-body">
         '.rex_twoeg_utils::getHtmlFromMDFile('vendor/twig/twig/doc/templates.rst').'
        </div>
    </div>
</section>
';
echo rex_view::content($content);