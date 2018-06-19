<?php

/*
 * This file is part of the Apisearch Server
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Feel free to edit as you please, and have fun.
 *
 * @author Marc Morera <yuhu@mmoreram.com>
 */

declare(strict_types=1);

namespace Apisearch\Plugin\Callbacks\Tests\Functional\Middleware\Query;

use Apisearch\Model\Item;
use Apisearch\Model\ItemUUID;
use Apisearch\Plugin\Callbacks\Tests\Functional\EndpointsFunctionalTest;
use Apisearch\Query\Query;

/**
 * Class CallbacksMiddlewareIndexItemsChangeBeforeTest.
 */
class CallbacksMiddlewareIndexItemsChangeBeforeTest extends EndpointsFunctionalTest
{
    /**
     * Get callbacks configuration.
     *
     * @return array
     */
    protected static function getCallbacksConfiguration(): array
    {
        return [
            'http_client_adapter' => 'http_test',
            'callbacks' => [
                'my_query_callback_before' => [
                    'command' => 'IndexItems',
                    'endpoint' => '/plugin/endpoints/change_items?'.static::getUrlQuery(),
                    'method' => 'GET',
                    'moment' => 'before',
                ],
            ],
        ];
    }

    /**
     * Test something.
     */
    public function testSomething()
    {
        $this->indexItems([
            Item::create(ItemUUID::createByComposedUUID('test~1')),
            Item::create(ItemUUID::createByComposedUUID('test~2')),
        ]);
        $this->assertCount(
            7,
            $this->query(Query::createMatchAll()
                ->filterBy('flag', 'flag', ['1'])
            )->getItems()
        );
    }
}
