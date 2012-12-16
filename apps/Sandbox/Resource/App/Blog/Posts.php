<?php
/**
 * @package    Sandbox
 * @subpackage Resource
 */
namespace Sandbox\Resource\App\Blog;

use BEAR\Package\Interceptor\Setter\DbSetter;
use BEAR\Resource\AbstractObject as ResourceObject;
use BEAR\Resource\Link;
use BEAR\Resource\Code;
use PDO;

use BEAR\Sunday\Annotation\Db;
use BEAR\Sunday\Annotation\Time;
use BEAR\Sunday\Annotation\Transactional;
use BEAR\Sunday\Annotation\Cache;
use BEAR\Sunday\Annotation\CacheUpdate;

/**
 * Posts
 *
 * @package    Sandbox
 * @subpackage Resource
 *
 * @Db
 */
class Posts extends ResourceObject
{
    use DbSetter;

    /**
     * @var string
     */
    public $time;

    /**
     * @var string
     */
    protected $table = 'posts';

    /**
     * @var array
     */
    public $links = [
        'page_post' => [Link::HREF => 'page://self/blog/posts/post'],
        'page_item' => [Link::HREF => 'page://self/blog/posts/post{?id}', Link::TEMPLATED => true],
        'page_edit' => [Link::HREF => 'page://self/blog/posts/edit{?id}', Link::TEMPLATED => true],
        'page_delete' => [Link::HREF => 'page://self/blog/posts?_method=delete{&id}', Link::TEMPLATED => true]
    ];

    /**
     * @param int $id
     *
     * @Cache(100)
     */
    public function onGet($id = null)
    {
        $sql = "SELECT id, title, body, created, modified FROM {$this->table}";
        if (is_null($id)) {
            $stmt = $this->db->query($sql);
            $this->body = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $sql .= " WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue('id', $id);
            $stmt->execute();
            $this->body = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return $this;
    }

        /**
         * @param string $title
         * @param string $body
         *
         * @Time
         * @Transactional
         * @CacheUpdate
         */
        public function onPost($title, $body)
        {
            $values = [
                'title' => $title,
                'body' => $body,
                'created' => $this->time
            ];
            $this->db->insert($this->table, $values);
            //
            $lastId = $this->db->lastInsertId('id');
            $this->code = Code::CREATED;
            $this->links['new_post'] = [Link::HREF => "app://self/posts/post?id={$lastId}"];
            $this->links['page_new_post'] = [Link::HREF => "page://self/blog/posts/post?id={$lastId}"];
            return $this;
        }

    /**
     * @param int    $id
     * @param string $title
     * @param string $body
     *
     * @Time
     * @CacheUpdate
     */
    public function onPut($id, $title, $body)
    {
        $values = [
            'title' => $title,
            'body' => $body,
            'created' => $this->time
        ];
        $this->db->update($this->table, $values, ['id' => $id]);
        $this->code = Code::NO_CONTENT;
        return $this;
    }

    /**
     * @param int $id
     *
     * @CacheUpdate
     */
    public function onDelete($id)
    {
        $this->db->delete($this->table, ['id' => $id]);
        $this->code = Code::NO_CONTENT;
        return $this;
    }
}
