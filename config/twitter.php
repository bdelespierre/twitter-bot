<?php

return [

    /*
    |--------------------------------------------------------------------------
    | VIP
    |--------------------------------------------------------------------------
    |
    | These are the accounts you don't want to automatically manage, which
    | means the bot will leave them be but will still synchronize them and
    | use them in statistics.
    |
    */

    'vip' => [
        'clientsfh'       , 'SarahCAndersen'  , 'yukaichou'       ,
        'ProductHunt'     , 'iamlosion'       , 'newsycombinator' ,
        'paulg'           , 'verge'           , '_TheFamily'      ,
        'sensiolabs'      , 'elonmusk'        , 'BrianTracy'      ,
        'Medium'          , 'ThePracticalDev' , 'afilina'         ,
        'hackernoon'      , 'IonicFramework'  , 'polymer'         ,
        'reactjs'         , 'MongoDB'         , 'googledevs'      ,
        'Google'          , 'shenanigansen'   , 'Rozasalahshour'  ,
        'jlondiche'       , 'DelespierreB'    , 'matts2cant'      ,
        'newsycombinator' , 'TechCrunch'      ,
    ],

    /*
    |--------------------------------------------------------------------------
    | Emojis
    |--------------------------------------------------------------------------
    |
    | These are the emojis used to decorate tweets.
    */

    'emojis' => ['😎','😃','😊','😍','😺','😻','😄','😆','💯','👍','🔥'],

    /*
    |--------------------------------------------------------------------------
    | Hashtags
    |--------------------------------------------------------------------------
    |
    | These are your interests. When the bot picks article from the sources,
    | it will give more weight to articles talking about these topics. These
    | hashtags will also be used to decorate tweets.
    |
    */

    'hashtags' => [
        'tech'    , 'javascript' , 'php'          , 'startup'     ,
        'ux'      , 'devops'     , 'laravel'      , 'symfony'     ,
        'chatbot' , 'devel'      , 'bitcoin'      , 'blockchain'  ,
        'angular' , 'react'      , 'frontend'     , 'backend'     ,
        'code'    , 'coding'     , 'gamification' , 'programming' ,
        'ai'      , 'node'       , 'nodejs'       , 'firebase'    ,
        'google'  , 'chrome'     , 'android'      , 'webapp'      ,
        'ui'      , 'linux'
    ],

];
