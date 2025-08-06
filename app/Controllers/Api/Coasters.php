<?php

namespace App\Controllers\Api;

use App\Schemas\RequestSchemas;
use App\Libraries\RedisData;
use App\Controllers\BaseController;
use App\Libraries\RequestValidator;
use App\Libraries\TimeValidator;
use App\Libraries\ApiLogger;
use App\Libraries\ErrorType;
use App\Libraries\RedisPublisher;
use Throwable;

class Coasters extends BaseController
{
	public function create()
	{
		$data = $this->request->getJSON(true);

		try {
			RequestValidator::validate_input(RequestSchemas::CREATE_QUEUE, $data);
			TimeValidator::assertHourMinuteLessThan($data['godzina_od'], $data['godzina_do'], "godzina_od", "godzina_do");
		} catch (\InvalidArgumentException $e) {
			ApiLogger::getInstance()->logError($e->getMessage(), ErrorType::VALIDATION);
			return $this->failValidationErrors($e->getMessage());
		}

		try {
			$redisData = new RedisData();
			$queueId = $redisData->getNewQueueId();

			$payload = RequestSchemas::castInput($data, RequestSchemas::CREATE_QUEUE);
			$response = $redisData->insert_new_queue($payload, $queueId);
		} catch (\RuntimeException $e) {
			ApiLogger::getInstance()->logError($e->getMessage(), ErrorType::DATABASE);
			return $this->failServerError($e->getMessage());
		} catch (Throwable $e) {
			ApiLogger::getInstance()->logError($e->getMessage(), ErrorType::PHP);
			return $this->failServerError($e->getMessage());
		}

		(new RedisPublisher())->publish('coaster.added', json_encode(['queue_id' => $queueId]));
		return $this->respond($response);
	}

	public function update($coaster_id = null)
	{
		try {
			RequestValidator::validateNumericId($coaster_id, 'ID kolejki');
		} catch (\InvalidArgumentException $e) {
			ApiLogger::getInstance()->logError($e->getMessage(), ErrorType::VALIDATION);
			return $this->failValidationErrors($e->getMessage());
		}

		$data = $this->request->getJSON(true);

		try {
			$redis = new RedisData();

			if (!$redis->existsQueue($coaster_id)) {
				$msg = "Kolejka o ID $coaster_id nie istnieje.";
				ApiLogger::getInstance()->logError($msg, ErrorType::VALIDATION);
				return $this->failNotFound($msg);
			}

			$currentQueue = $redis->getQueue($coaster_id);
			if (!$currentQueue) {
				$msg = "Nie udało się pobrać istniejących danych kolejki.";
				ApiLogger::getInstance()->logError($msg, ErrorType::DATABASE);
				return $this->failServerError($msg);
			}

			$payload = $currentQueue;
			$payload['liczba_personelu'] = (int)$data['liczba_personelu'];
			$payload['liczba_klientow'] = (int)$data['liczba_klientow'];
			$payload['godzina_od'] = $data['godzina_od'];
			$payload['godzina_do'] = $data['godzina_do'];
			$payload['predkosc_wagonu'] = (float)$data['predkosc_wagonu'];

			RequestValidator::validate_input(RequestSchemas::COASTER_UPDATE_FIELDS, $data);
			TimeValidator::assertHourMinuteLessThan($data['godzina_od'], $data['godzina_do'], "godzina_od", "godzina_do");

			$redis->updateQueue($coaster_id, $payload);

			(new RedisPublisher())->publish('coaster.updated', json_encode(['queue_id' => $coaster_id]));
			return $this->respond([
				'message' => "Kolejka $coaster_id zaktualizowana (bez zmiany długości trasy).",
				'data' => $payload
			]);
		} catch (Throwable $e) {
			ApiLogger::getInstance()->logError($e->getMessage(), ErrorType::PHP);
			return $this->failServerError("Błąd: " . $e->getMessage());
		}
	}
}

?>