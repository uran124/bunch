<?php
// app/models/Supply.php

class Supply extends Model
{
    protected string $table = 'supplies';

    public function getAdminList(): array
    {
        $stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY created_at DESC");
        $rows = $stmt->fetchAll();

        return array_map(fn(array $row) => $this->appendDerivedFields($row), $rows);
    }

    public function getNextDeliveryWindow(): array
    {
        $supplies = $this->getAdminList();
        $today = new DateTimeImmutable('today');
        $upcoming = [];

        foreach ($supplies as $supply) {
            $nextDelivery = $supply['next_delivery'] ?? null;
            if (!$nextDelivery) {
                continue;
            }

            try {
                $date = new DateTimeImmutable($nextDelivery);
            } catch (Exception $e) {
                continue;
            }

            $upcoming[] = [
                'date' => $date,
                'supply' => $supply,
            ];
        }

        if (!$upcoming) {
            return [
                'current_date' => null,
                'next_date' => null,
                'current_supply' => null,
            ];
        }

        usort($upcoming, static function (array $left, array $right): int {
            return $left['date']->getTimestamp() <=> $right['date']->getTimestamp();
        });

        $currentIndex = null;
        foreach ($upcoming as $index => $entry) {
            if ($entry['date'] >= $today) {
                $currentIndex = $index;
                break;
            }
        }

        if ($currentIndex === null) {
            $currentIndex = count($upcoming) - 1;
        }

        $current = $upcoming[$currentIndex];
        $next = $upcoming[$currentIndex + 1] ?? null;

        return [
            'current_date' => $current['date']->format('Y-m-d'),
            'next_date' => $next ? $next['date']->format('Y-m-d') : null,
            'current_supply' => $current['supply'],
        ];
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? $this->appendDerivedFields($row) : null;
    }

    public function createStanding(array $data): int
    {
        $sql = "INSERT INTO {$this->table} (is_standing, photo_url, flower_name, variety, country, packs_total, stems_per_pack, stem_height_cm, stem_weight_g, periodicity, first_delivery_date, planned_delivery_date, actual_delivery_date, allow_small_wholesale, skip_date, packs_reserved) VALUES (:is_standing, :photo_url, :flower_name, :variety, :country, :packs_total, :stems_per_pack, :stem_height_cm, :stem_weight_g, :periodicity, :first_delivery_date, :planned_delivery_date, :actual_delivery_date, :allow_small_wholesale, :skip_date, :packs_reserved)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'is_standing' => 1,
            'photo_url' => $data['photo_url'],
            'flower_name' => $data['flower_name'],
            'variety' => $data['variety'],
            'country' => $data['country'],
            'packs_total' => $data['packs_total'],
            'stems_per_pack' => $data['stems_per_pack'],
            'stem_height_cm' => $data['stem_height_cm'],
            'stem_weight_g' => $data['stem_weight_g'],
            'periodicity' => $data['periodicity'],
            'first_delivery_date' => $data['first_delivery_date'],
            'planned_delivery_date' => $data['planned_delivery_date'],
            'actual_delivery_date' => $data['actual_delivery_date'],
            'allow_small_wholesale' => $data['allow_small_wholesale'],
            'skip_date' => $data['skip_date'],
            'packs_reserved' => $data['packs_reserved'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function createOneTime(array $data): int
    {
        $sql = "INSERT INTO {$this->table} (is_standing, photo_url, flower_name, variety, country, packs_total, stems_per_pack, stem_height_cm, stem_weight_g, periodicity, first_delivery_date, planned_delivery_date, actual_delivery_date, allow_small_wholesale, packs_reserved) VALUES (:is_standing, :photo_url, :flower_name, :variety, :country, :packs_total, :stems_per_pack, :stem_height_cm, :stem_weight_g, :periodicity, :first_delivery_date, :planned_delivery_date, :actual_delivery_date, :allow_small_wholesale, :packs_reserved)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'is_standing' => 0,
            'photo_url' => $data['photo_url'],
            'flower_name' => $data['flower_name'],
            'variety' => $data['variety'],
            'country' => $data['country'],
            'packs_total' => $data['packs_total'],
            'stems_per_pack' => $data['stems_per_pack'],
            'stem_height_cm' => $data['stem_height_cm'],
            'stem_weight_g' => $data['stem_weight_g'],
            'periodicity' => 'single',
            'first_delivery_date' => $data['planned_delivery_date'],
            'planned_delivery_date' => $data['planned_delivery_date'],
            'actual_delivery_date' => $data['actual_delivery_date'],
            'allow_small_wholesale' => $data['allow_small_wholesale'],
            'packs_reserved' => $data['packs_reserved'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function updateStanding(int $id, array $data): void
    {
        $sql = "UPDATE {$this->table} SET photo_url = :photo_url, flower_name = :flower_name, variety = :variety, country = :country, packs_total = :packs_total, stems_per_pack = :stems_per_pack, stem_height_cm = :stem_height_cm, stem_weight_g = :stem_weight_g, periodicity = :periodicity, first_delivery_date = :first_delivery_date, planned_delivery_date = :planned_delivery_date, actual_delivery_date = :actual_delivery_date, allow_small_wholesale = :allow_small_wholesale, skip_date = :skip_date WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'photo_url' => $data['photo_url'],
            'flower_name' => $data['flower_name'],
            'variety' => $data['variety'],
            'country' => $data['country'],
            'packs_total' => $data['packs_total'],
            'stems_per_pack' => $data['stems_per_pack'],
            'stem_height_cm' => $data['stem_height_cm'],
            'stem_weight_g' => $data['stem_weight_g'],
            'periodicity' => $data['periodicity'],
            'first_delivery_date' => $data['first_delivery_date'],
            'planned_delivery_date' => $data['planned_delivery_date'],
            'actual_delivery_date' => $data['actual_delivery_date'],
            'allow_small_wholesale' => $data['allow_small_wholesale'],
            'skip_date' => $data['skip_date'],
            'id' => $id,
        ]);
    }

    public function updateOneTime(int $id, array $data): void
    {
        $sql = "UPDATE {$this->table} SET photo_url = :photo_url, flower_name = :flower_name, variety = :variety, country = :country, packs_total = :packs_total, stems_per_pack = :stems_per_pack, stem_height_cm = :stem_height_cm, stem_weight_g = :stem_weight_g, first_delivery_date = :first_delivery_date, planned_delivery_date = :planned_delivery_date, actual_delivery_date = :actual_delivery_date, allow_small_wholesale = :allow_small_wholesale WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'photo_url' => $data['photo_url'],
            'flower_name' => $data['flower_name'],
            'variety' => $data['variety'],
            'country' => $data['country'],
            'packs_total' => $data['packs_total'],
            'stems_per_pack' => $data['stems_per_pack'],
            'stem_height_cm' => $data['stem_height_cm'],
            'stem_weight_g' => $data['stem_weight_g'],
            'first_delivery_date' => $data['planned_delivery_date'],
            'planned_delivery_date' => $data['planned_delivery_date'],
            'actual_delivery_date' => $data['actual_delivery_date'],
            'allow_small_wholesale' => $data['allow_small_wholesale'],
            'id' => $id,
        ]);
    }

    public function setCardStatus(int $id, string $field, int $status): void
    {
        if (!in_array($field, ['has_product_card', 'has_wholesale_card'], true)) {
            return;
        }

        $sql = "UPDATE {$this->table} SET {$field} = :status WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'status' => $status,
            'id' => $id,
        ]);
    }

    private function appendDerivedFields(array $row): array
    {
        $nextPlannedDate = $this->calculateNextPlannedDate($row);
        $row['next_delivery'] = $row['actual_delivery_date'] ?: $nextPlannedDate;
        $row['packs_available'] = max(0, (int) $row['packs_total'] - (int) $row['packs_reserved']);
        $row['pack_size'] = (int) $row['stems_per_pack'];

        return $row;
    }

    private function calculateNextPlannedDate(array $row): ?string
    {
        if ((int) $row['is_standing'] === 0) {
            return $row['planned_delivery_date'];
        }

        if (empty($row['first_delivery_date'])) {
            return null;
        }

        try {
            $intervalSpec = ($row['periodicity'] === 'biweekly') ? 'P14D' : 'P7D';
            $start = new DateTimeImmutable($row['first_delivery_date']);
            $today = new DateTimeImmutable('today');

            if (!empty($row['skip_date'])) {
                $skip = new DateTimeImmutable($row['skip_date']);
            } else {
                $skip = null;
            }

            while ($start < $today || ($skip && $start->format('Y-m-d') === $skip->format('Y-m-d'))) {
                $start = $start->add(new DateInterval($intervalSpec));
            }

            return $start->format('Y-m-d');
        } catch (Exception $e) {
            error_log('Failed to calculate next planned date: ' . $e->getMessage());
            return $row['planned_delivery_date'] ?? null;
        }
    }
}
