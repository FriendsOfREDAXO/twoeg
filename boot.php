<?php

rex_extension::register('PACKAGES_INCLUDED', function ($params) {
    
    // set up Twoeg for global use!
    class_alias ('Twoeg\Twoeg' , 'Twoeg');
    
}, rex_extension::EARLY);
