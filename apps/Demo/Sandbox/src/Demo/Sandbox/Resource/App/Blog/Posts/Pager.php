<?php

namespace Demo\Sandbox\Resource\App\Blog\Posts;

use PDO;
use Demo\Sandbox\Resource\App\Blog\Posts;
use BEAR\Sunday\Annotation\Db;
use BEAR\Sunday\Annotation\Cache;
use BEAR\Sunday\Annotation\DbPager;

/**
 * Pagered posts
 *
 * @Db
 */
class Pager extends Posts
{
    /**
     * Get
     *
     * @param int $id
     *
     * @Cache
     * @DbPager(2)
     */
    public function onGet($id = null)
    {
        $sql = "SELECT id, title, body, created, modified FROM {$this->table}";
        if (is_null($id)) {
            $stmt = $this->db->query($sql);
            $this->body = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $this;
        }
        $sql .= " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue('id', $id);
        $stmt->execute();
        $this->body = $stmt->fetch(PDO::FETCH_ASSOC);

        return $this;
    }
}
