<?php

namespace App\Controllers\Api;

use App\Schemas\RequestSchemas;
use App\Libraries\RequestValidator;
use App\Libraries\RedisData;
use App\Libraries\ApiLogger;
use App\Libraries\ErrorType;
use CodeIgniter\RESTful\ResourceController;
use App\Libraries\RedisPublisher;
use Throwable;

class Wagons extends ResourceController
{
	public function create($coaster_id = null)
	{
		try {
			RequestValidator::validateNumericId($coaster_id, 'ID kolejki');
		} catch (\InvalidArgumentException $e) {
			ApiLogger::getInstance()->logError($e->getMessage(), ErrorType::VALIDATION);
			return $this->failValidationErrors($e->getMessage());
		}

		$data = $this->request->getJSON(true);

		try {
			RequestValidator::validate_input(RequestSchemas::CREATE_WAGON, $data);
			$payload = \App\Schemas\RequestSchemas::castInput($data, RequestSchemas::CREATE_WAGON);

			$redis = new RedisData();

			if (!$redis->existsQueue($coaster_id)) {
				$msg = "Kolejka o ID $coaster_id nie istnieje.";
				ApiLogger::getInstance()->logError($msg, ErrorType::VALIDATION);
				return $this->failNotFound($msg);
			}

			$wagonId = $redis->getNewWagonId($coaster_id);
			$payload['id'] = $wagonId;

			$redis->insertWagon($coaster_id, $payload);

			(new RedisPublisher())->publish('wagon.added', json_encode([
				'queue_id' => $coaster_id
			]));
			return $this->respondCreated([
				'message' => "Wagon '{$payload['id']}' dodany do kolejki $coaster_id.",
				'wagon_data' => $payload
			]);
		} catch (Throwable $e) {
			ApiLogger::getInstance()->logError($e->getMessage(), ErrorType::PHP);
			return $this->failServerError("Błąd: " . $e->getMessage());
		}
	}

	public function delete($coaster_id = null, $wagon_id = null)
	{
		try {
			RequestValidator::validateNumericId($coaster_id, 'ID kolejki');
			RequestValidator::validateNumericId($wagon_id, 'ID wagonu');
		} catch (\InvalidArgumentException $e) {
			ApiLogger::getInstance()->logError($e->getMessage(), ErrorType::VALIDATION);
			return $this->failValidationErrors($e->getMessage());
		}

		try {
			$redis = new RedisData();

			if (!$redis->existsQueue($coaster_id)) {
				$msg = "Kolejka o ID $coaster_id nie istnieje.";
				ApiLogger::getInstance()->logError($msg, ErrorType::VALIDATION);
				return $this->failNotFound($msg);
			}

			if (!$redis->deleteWagon($coaster_id, $wagon_id)) {
				$msg = "W wagonie '$wagon_id' nie znaleziono wpisu.";
				ApiLogger::getInstance()->logError($msg, ErrorType::VALIDATION);
				return $this->failNotFound($msg);
			}

			(new RedisPublisher())->publish('wagon.deleted', json_encode([
				'queue_id' => $coaster_id
			]));
			return $this->respond([
				'message' => "Wagon '$wagon_id' usunięty z kolejki $coaster_id."
			]);
		} catch (Throwable $e) {
			ApiLogger::getInstance()->logError($e->getMessage(), ErrorType::PHP);
			return $this->failServerError("Błąd: " . $e->getMessage());
		}
	}
}

?>