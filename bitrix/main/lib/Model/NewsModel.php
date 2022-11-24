<?php

namespace Bitrix\Main\Lib\Model;

class NewsModel
{
    public static function getById($id)
    {
        foreach (static::getList() as $article)
        {
            if ($article->id === (int) $id)
            {
                return $article;
            }
        }

        return null;
    }

    public static function getLast($limit = 5)
    {
        $news = static::getList();
        uasort($news, function ($a, $b) {
            return $a->id > $b->id ? -1: 1;
        });

        return array_slice($news, 0, $limit);
    }

    public static function getList()
    {
        return [
            (object) [
                'id' => 1,
                'title' => 'Cows lose their jobs as milk prices drop',
                'category' => '',
            ],
            (object) [
                'id' => 2,
                'title' => 'Man Accused of Killing Lawyer Receives a New Attorney',
                'category' => '',
            ],
            (object) [
                'id' => 3,
                'title' => 'State population to double by 2040, babies to blame',
                'category' => '',
            ],
            (object) [
                'id' => 4,
                'title' => 'Breathing oxygen linked to staying alive',
                'category' => '',
            ],
            (object) [
                'id' => 5,
                'title' => 'Most Earthquake Damage is Caused by Shaking',
                'category' => '',
            ],
            (object) [
                'id' => 6,
                'title' => 'Federal Agents Raid Gun Shop, Find Weapons',
                'category' => '',
            ],
            (object) [
                'id' => 7,
                'title' => 'Safety meeting ends in accident',
                'category' => '',
            ],
            (object) [
                'id' => 8,
                'title' => 'Utah Poison Control Center reminds everyone not to take poison',
                'category' => '',
            ],
            (object) [
                'id' => 9,
                'title' => 'Hospitals resort to hiring doctors',
                'category' => '',
            ],
            (object) [
                'id' => 10,
                'title' => 'Bugs flying around with wings are flying bugs',
                'category' => '',
            ],
        ];
    }
}