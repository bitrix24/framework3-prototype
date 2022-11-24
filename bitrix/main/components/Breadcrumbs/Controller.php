<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2022 Bitrix
 */

namespace Bitrix\Main\Components\Breadcrumbs;


/**
 * @package    bitrix
 * @subpackage main
 */
class Controller extends \Bitrix\Main\Lib\Controllers\Controller
{
	public function defaultAction()
	{
        $crumbs = [
            [
                'TITLE' => 'Point 1',
                'LINK' => '/point/1'
            ],
            [
                'TITLE' => 'Point 2',
                'LINK' => '/point/2'
            ],
            [
                'TITLE' => 'Point 3',
                'LINK' => '/point/3'
            ],
        ];

        $jsonLd = $this->getJsonLd($crumbs);

		return $this->render('default/template', compact('crumbs', 'jsonLd'));
	}

    protected function getJsonLd($crumbs)
    {
        $jsonLDBreadcrumbList = '';
        $itemSize = count($crumbs);

        for($index = 0; $index < $itemSize; $index++)
        {
            $title = htmlspecialchars($crumbs[$index]["TITLE"]);
            $jsonSeparator = ($index > 0? ',' : '');

            if($crumbs[$index]["LINK"] <> "" && $index != $itemSize-1)
            {
                $jsonLDBreadcrumbList .= $jsonSeparator.'{
                    "@type": "ListItem",
                    "position": '.$index.',
                    "name": "'.$title.'",
                    "item": "'.$crumbs[$index]["LINK"].'"
                }';
            }
        }

       return '
            <script type="application/ld+json">
                {
                  "@context": "https://schema.org",
                  "@type": "BreadcrumbList",
                  "itemListElement": ['.$jsonLDBreadcrumbList.']
                }
            </script>
        ';
    }
}
