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
 * @author PuntMig Technologies
 */

declare(strict_types=1);

namespace Apisearch\Server\Controller;

use Apisearch\Query\Query as QueryModel;
use Apisearch\Repository\HttpRepository;
use Apisearch\Repository\RepositoryReference;
use Apisearch\Exception\InvalidFormatException;
use Apisearch\Exception\InvalidTokenException;
use Apisearch\Server\Domain\Query\Query;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class QueryController.
 */
class QueryController extends ControllerWithBusAndEventRepository
{
    /**
     * @var string
     *
     * Purge Query object from response
     */
    const PURGE_QUERY_FROM_RESPONSE_FIELD = 'incl_query';

    /**
     * Make a query.
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws InvalidFormatException
     * @throws InvalidTokenException
     */
    public function query(Request $request)
    {
        $this->configureEventRepository($request);
        $query = $request->query;

        $plainQuery = $query->get(HttpRepository::QUERY_FIELD, null);
        if (!is_string($plainQuery)) {
            throw InvalidFormatException::queryFormatNotValid($plainQuery);
        }

        $responseAsArray = $this
            ->commandBus
            ->handle(new Query(
                RepositoryReference::create(
                    $query->get(HttpRepository::APP_ID_FIELD),
                    $query->get(HttpRepository::INDEX_FIELD)
                ),
                QueryModel::createFromArray(json_decode($plainQuery, true))
            ))
            ->toArray();

        if ($query->has(self::PURGE_QUERY_FROM_RESPONSE_FIELD)) {
            unset($responseAsArray['query']);
        }

        $response = new JsonResponse(
            $responseAsArray,
            200,
            [
                'Access-Control-Allow-Origin' => '*',
            ]
        );

        return $response;
    }
}
