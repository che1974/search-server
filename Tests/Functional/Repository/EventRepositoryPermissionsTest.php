<?php
/*
 * This file is part of the {Package name}.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Feel free to edit as you please, and have fun.
 *
 * @author Marc Morera <yuhu@mmoreram.com>
 */

namespace Apisearch\Server\Tests\Functional\Repository;

/**
 * Class EventRepositoryPermissionsTest
 */
trait EventRepositoryPermissionsTest
{
    /**
     * Test events list without permissions
     *
     * @expectedException \Apisearch\Exception\ResourceNotAvailableException
     * @dataProvider dataEventsBadPermissions
     */
    public function testEventsBadPermissionsList($appId, $index)
    {
        $this->listEvents(
            null,
            1513470315000000,
            1513470315000000,
            null,
            null,
            $appId,
            $index
        );
    }

    /**
     * Test events stats without permissions
     *
     * @expectedException \Apisearch\Exception\ResourceNotAvailableException
     * @dataProvider dataEventsBadPermissions
     */
    public function testEventsBadPermissionsStats($appId, $index)
    {
        $this->statsEvents(
            1513470315000000,
            1513470315000000,
            $appId,
            $index
        );
    }

    /**
     * Data for some cases
     *
     * @return array
     */
    public function dataEventsBadPermissions() : array
    {
        return [
            [self::$anotherAppId, self::$anotherIndex],
            [self::$anotherInexistentAppId, self::$index],
            [self::$anotherInexistentAppId, self::$anotherIndex],
        ];
    }

    /**
     * Reset all
     */
    public function testResetAfterEventRepositoryPermissionTest()
    {
        $this->resetScenario();
    }
}