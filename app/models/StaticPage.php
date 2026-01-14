<?php
// app/models/StaticPage.php

class StaticPage extends Model
{
    protected string $table = 'static_pages';

    public function getAdminList(): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY sort_order ASC, updated_at DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getActiveBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE slug = :slug AND is_active = 1 LIMIT 1"
        );
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getActiveByPlacement(string $placement): array
    {
        $column = $placement === 'footer' ? 'show_in_footer' : ($placement === 'menu' ? 'show_in_menu' : null);
        if ($column === null) {
            return [];
        }

        $stmt = $this->db->prepare(
            "SELECT id, title, slug, sort_order FROM {$this->table} WHERE is_active = 1 AND {$column} = 1 ORDER BY sort_order ASC, title ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function create(array $payload): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table} (title, slug, content, show_in_footer, show_in_menu, is_active, sort_order) VALUES (:title, :slug, :content, :show_in_footer, :show_in_menu, :is_active, :sort_order)"
        );
        $stmt->execute([
            'title' => $payload['title'],
            'slug' => $payload['slug'],
            'content' => $payload['content'],
            'show_in_footer' => $payload['show_in_footer'],
            'show_in_menu' => $payload['show_in_menu'],
            'is_active' => $payload['is_active'],
            'sort_order' => $payload['sort_order'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $payload): void
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table} SET title = :title, slug = :slug, content = :content, show_in_footer = :show_in_footer, show_in_menu = :show_in_menu, is_active = :is_active, sort_order = :sort_order WHERE id = :id"
        );
        $stmt->execute([
            'title' => $payload['title'],
            'slug' => $payload['slug'],
            'content' => $payload['content'],
            'show_in_footer' => $payload['show_in_footer'],
            'show_in_menu' => $payload['show_in_menu'],
            'is_active' => $payload['is_active'],
            'sort_order' => $payload['sort_order'],
            'id' => $id,
        ]);
    }

    public function setActive(int $id, int $active): void
    {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET is_active = :active WHERE id = :id");
        $stmt->execute([
            'active' => $active,
            'id' => $id,
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }
}
