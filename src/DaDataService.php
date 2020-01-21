<?php

namespace Bereza\DaData;

use Bereza\DaData\Dto\AddressDto;
use Curl\Curl;
use ErrorException;
use Exception;

/**
 * Class DadataService
 * @package App\External\Dadata
 */
class DaDataService
{
    /**
     * Адрес REST API для подсказок
     */
    const SUGGESTIONS_API_HOST = "https://suggestions.dadata.ru/suggestions/api/4_1/rs/";
    /**
     * Кол-во попыток для получения данных из Dadata
     */
    const NUMBER_OF_ATTEMPTS = 3;
    /**
     * Кол-во секунд между попытками
     */
    const SECONDS_BETWEEN_ATTEMPTS = 5;
    /** @var string */
    protected $apiToken;
    /** @var string */
    protected $apiSecret;
    
    /**
     * DaDataService constructor.
     * @param  string  $apiToken
     * @param  string  $apiSecret
     */
    public function __construct(string $apiToken, string $apiSecret)
    {
        $this->apiToken = $apiToken;
        $this->apiSecret = $apiSecret;
    }
    
    /**
     * Выполнить запрос к Dadata
     * @param string $apiHost - адрес REST API
     * @param string $url - адрес метода
     * @param array $data - параметры запроса
     * @return array
     * @throws ErrorException
     * @throws Exception
     */
    protected function query(string $apiHost, string $url, array $data): array
    {
        $result = [];
        
        $client = $this->getClient($apiHost);
        //Делаем несколько попыток к Dadata, т.к. она не всегда отвечает
        for ($attempt = 0; $attempt < static::NUMBER_OF_ATTEMPTS; $attempt++) {
            $result = $client->get($url, $data);
        
            //Если ответ не ошибка, то выходим из цикла
            if (!$this->responseIsError($result)) {
                break;
            }
        
            if ($attempt != static::NUMBER_OF_ATTEMPTS - 1) {
                //Ждем N-секунд до выполнения следующей попытки
                sleep(static::SECONDS_BETWEEN_ATTEMPTS);
            }
        }

        return $result;
    }

    /**
     * Dadata вернула ошибку в качестве ответа?
     * @param $result
     * @return bool
     */
    protected function responseIsError($result): bool
    {
        return isset($result['family']) && isset($result['reason']) && isset($result['message']);
    }

    /**
     * Получить подсказки
     * @param string $type - тип (fio, address, party, email, bank)
     * @param array $data - параметры запроса
     * @return array
     * @throws ErrorException
     */
    protected function suggest(string $type, array $data): array
    {
        return $this->query(static::SUGGESTIONS_API_HOST, 'suggest/' . $type, $data);
    }

    /**
     * Получить подсказки для адреса
     * @param string $query - часть адреса
     * @param int $count - кол-во возвращаемых подсказок
     * @return array|AddressDto[]
     * @throws ErrorException
     */
    public function suggestAddress(string $query, int $count = 10): array
    {
        $addresses = [];

        $response = $this->suggest('address', ['query' => $query, 'count' => $count]);
        if ($response && isset($response['suggestions'])) {
            foreach ($response['suggestions'] as $suggestion) {
                $addresses[] = new AddressDto($suggestion);
            }
        }

        return $addresses;
    }

    /**
     * Получить адрес по коду КЛАДР или ФИАС
     * @param string $id - код КЛАДР или ФИАС
     * @return AddressDto|null
     * @throws ErrorException
     */
    public function findAddressById(string $id): ?AddressDto
    {
        $response = $this->query(static::SUGGESTIONS_API_HOST,'findById/address', ['query' => $id]);
        if (!$response) {
            return null;
        }
        
        return isset($response['suggestions'][0]) ? new AddressDto($response['suggestions'][0]) : null;
    }

    /**
     * Пролучить объект для запросов к API
     * @param string $apiHost - адрес REST API
     * @return Curl
     * @throws ErrorException
     */
    protected function getClient(string $apiHost): Curl
    {
        $client = new Curl($apiHost);
        $client->setOpt(CURLOPT_RETURNTRANSFER, true);
        $client->setOpt(CURLOPT_FOLLOWLOCATION, true);
        $client->setHeader('Authorization', "Token " . $this->apiToken);
        $client->setHeader('X-Secret', $this->apiSecret);
        $client->setHeader('Content-Type', 'application/json');
        $client->setHeader('Accept', 'application/json');
        $client->setDefaultJsonDecoder(true);

        return $client;
    }
}
