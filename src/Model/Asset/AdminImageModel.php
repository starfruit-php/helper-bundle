<?php

namespace Starfruit\HelperBundle\Model\Asset;

use Pimcore\Model\Asset\Image;
use Pimcore\Model\Element\AdminStyle;

class AdminImageModel extends AdminStyle
{
    protected $element;

    public function __construct($element)
    {
        parent::__construct($element);
        $this->element = $element;
        $this->id = $element->getId();

        if ($element instanceof Image) {
            $this->isSVG = $this->isSVG();
            $this->elementIconClass = null;
            $this->elementIcon = $this->getPreviewPath();
        }
    }

    public function getElementQtipConfig(): ?array
    {
        if ($this->element instanceof Image) {
            $size = $this->getSizeInfo();

            $text = $this->element->getKey();
            $text .= '<br>';
            $text .= $size;
            $text .= '<p><img src="' . $this->getPreviewPath() . '" style="' . $this->getPreviewStyle() . '" /></p>';

            return [
                "title" => "ID: " . $this->id,
                "text" => $text,
            ];
        }

        return parent::getElementQtipConfig();
    }

    private function getPreviewStyle()
    {
        $style = "max-width:150px; max-height:150px";
        if ($this->isSVG) {
            // $style .= ";background-color: black";
        }

        return $style;
    }

    private function getPreviewPath()
    {
        if (!$this->isSVG) {
            $path = '/admin/asset/get-image-thumbnail?id=' . $this->id . '&treepreview=1';
        } else {
            $path = '/admin/asset/get-asset?id=' . $this->id;
        }

        return $path;
    }

    private function getSizeInfo()
    {
        $size = '';
        if ($this->element instanceof Image) {
            try {
                $size = $this->element->getWidth() . ' x ' . $this->element->getHeight() . " px";
            } catch (\Throwable $e) {
            }
        }

        return $size;
    }

    private function isSVG()
    {
        if ($this->element instanceof Image) {
            try {
                $fileExtension = $this->element->getCustomSettings()['embeddedMetaData']['FileTypeExtension'];
            } catch (\Throwable $e) {
                $fileExtension = '';        
            }

            $mimetype = $this->element->getMimeType();

            return $fileExtension == 'svg' || $mimetype == 'image/svg+xml';
        }

        return false;
    }
}