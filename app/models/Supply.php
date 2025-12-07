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
