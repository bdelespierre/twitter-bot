<?php

use Illuminate\Database\Seeder;

class VipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /*
        |----------------------------------------------------------------------
        | Very Important People
        |----------------------------------------------------------------------
        |
        | Never mute, unfollow or block these people no matter if they follow
        | me or not: I want them on my timeline and the bot should not handle
        | them.
        |
        */

        $vip = [
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
        ];

        foreach (App\Models\Twitter\User::whereIn('screen_name', $vip)->get() as $user) {
            if (!$user->isVip()) {
                $user->vip = true;
                $user->save();
            }
        }
    }
}
