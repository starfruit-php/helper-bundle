<?php

namespace Starfruit\HelperBundle\Tool;

use Pimcore\Db;
use Pimcore\Model\Asset;
use Pimcore\Model\Asset\Image;
use Pimcore\Model\Element\Recyclebin;

class AssetTool
{
    // Clean unused assets
    public static function cleanUnusedAssets()
    {
        $query = "
            SELECT `id`
            FROM `assets`
            WHERE `type` != 'folder'
            AND `id` NOT IN (
                SELECT ASSET.`id` as id
                FROM `assets` as ASSET
                INNER JOIN `dependencies` as DEPEN
                WHERE ASSET.`type` != 'folder'
                AND DEPEN.`targettype` = 'asset'
                AND ASSET.`id` = DEPEN.`targetid`
                GROUP BY ASSET.`id`
            )
        ";

        $db = Db::get();
        $data = $db->fetchAllAssociative($query);

        foreach ($data as $item) {
            $asset = Asset::getById($item['id']);

            if ($asset) {
                $asset->delete();

                self::pushToBin($asset);
            }
        }
    }

    public static function pushToBin($asset)
    {
        $currentAdmin = \Pimcore\Tool\Admin::getCurrentUser();
        try {
            $list = $asset::getList(['unpublished' => true]);
            $list->setCondition('`path` LIKE ' . $list->quote($list->escapeLike($asset->getRealFullPath()) . '/%'));
            $children = $list->getTotalCount();

            if ($children <= 100) {
                Recyclebin\Item::create($asset, $currentAdmin);
            }
        } catch (\Throwable $e) {
        }
    }
}
