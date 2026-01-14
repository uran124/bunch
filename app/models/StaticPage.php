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

        $select = $placement === 'footer'
            ? 'id, title, slug, sort_order, footer_column'
            : 'id, title, slug, sort_order';
        $orderBy = $placement === 'footer'
            ? 'footer_column ASC, sort_order ASC, title ASC'
            : 'sort_order ASC, title ASC';
        $stmt = $this->db->prepare(
            "SELECT {$select} FROM {$this->table} WHERE is_active = 1 AND {$column} = 1 ORDER BY {$orderBy}"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function create(array $payload): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table} (title, slug, content, content_format, show_in_footer, show_in_menu, is_active, sort_order, footer_column) VALUES (:title, :slug, :content, :content_format, :show_in_footer, :show_in_menu, :is_active, :sort_order, :footer_column)"
        );
        $stmt->execute([
            'title' => $payload['title'],
            'slug' => $payload['slug'],
            'content' => $payload['content'],
            'content_format' => $payload['content_format'] ?? 'visual',
            'show_in_footer' => $payload['show_in_footer'],
            'show_in_menu' => $payload['show_in_menu'],
            'is_active' => $payload['is_active'],
            'sort_order' => $payload['sort_order'],
            'footer_column' => $payload['footer_column'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $payload): void
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table} SET title = :title, slug = :slug, content = :content, content_format = :content_format, show_in_footer = :show_in_footer, show_in_menu = :show_in_menu, is_active = :is_active, sort_order = :sort_order, footer_column = :footer_column WHERE id = :id"
        );
        $stmt->execute([
            'title' => $payload['title'],
            'slug' => $payload['slug'],
            'content' => $payload['content'],
            'content_format' => $payload['content_format'] ?? 'visual',
            'show_in_footer' => $payload['show_in_footer'],
            'show_in_menu' => $payload['show_in_menu'],
            'is_active' => $payload['is_active'],
            'sort_order' => $payload['sort_order'],
            'footer_column' => $payload['footer_column'],
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
