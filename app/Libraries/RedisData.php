<?php
namespace App\Libraries;

class RedisData extends RedisLock{
	public function getNewQueueId() {
		return $this->redis->incr("kolejki:id_counter");
	}

	public function insert_new_queue(array $queue_data, int $queueId) {
		$success = $this->redis->set("kolejki:$queueId", json_encode($queue_data));
		if (!$success) {
			throw new \RuntimeException("Nie udało się zapisać danych do Redis.");
		}
		return true;
	}

	public function existsQueue(int $id): bool {
		return $this->redis->exists("kolejki:$id") > 0;
	}

	public function updateQueue(int $id, array $payload): void {
		$success = $this->redis->set("kolejki:$id", json_encode($payload));
		if (!$success) {
			throw new \RuntimeException("Nie udało się zapisać zmian w Redis.");
		}
	}

	public function getQueue(int $queueId): ?array {
		$data = $this->redis->get("kolejki:$queueId");

		if (!$data) {
			return null;
		}

		$decoded = json_decode($data, true);

		if (json_last_error() !== JSON_ERROR_NONE) {
			throw new \RuntimeException("Błąd dekodowania JSON dla kolejki ID $queueId");
		}

		return $decoded;
	}

	public function getNewWagonId(int $queueId): string {
		$id = $this->redis->incr("kolejki:$queueId:wagon_counter");
		return $id;
	}

	public function insertWagon(int $queueId, array $wagonData): bool {
		$id = $wagonData['id'];
		$key = "kolejki:$queueId:wagony";
		unset($wagonData['id']);
		return $this->redis->hSet($key, $id, json_encode($wagonData)) > 0;
	}

	public function deleteWagon(int $queueId, string $wagonId): bool {
		$key = "kolejki:$queueId:wagony";
		return $this->redis->hDel($key, $wagonId) > 0;
	}

}

?>