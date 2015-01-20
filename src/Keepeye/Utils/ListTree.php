<?php namespace Keepeye\Utils;
/**
 * 整理无限分类列表的工具
 * User: admin
 * Date: 15-1-9
 * Time: 下午2:06
 */

class ListTree
{
    private $list;

    private $options = array(
        'primaryKey' => 'id',
        'parentKey' => 'parent',
        'levelKey' => 'level',
        'childrenKey' => 'children',
    );

    private $currentLevel = -1;

    /**
     * 载入列表数组
     * @param $list
     * @throws KeepeyeUtilException
     */
    public function __construct($list, $options = array())
    {
        $this->setOptions($options);
        $this->list = $this->formatList($list);
    }

    /**
     * 设置配置项
     * @param $options
     */
    public function setOptions($options)
    {
        foreach ($options as $key => $value) {
            if (isset($this->options[$key])) {
                $this->options[$key] = $value;
            }
        }
    }


    /**
     * 格式化数组
     * @param $list
     * @return array
     * @throws KeepeyeUtilException
     */
    public function formatList($list)
    {
        if (!is_array($list)) {
            if (is_object($list) && method_exists($list,'toArray')) {
                $list = $list->toArray();
            } else {
                throw new KeepeyeUtilException('初始值必须是一个数组或包含toArray方法的对象');
            }
        }

        if (!is_array($list[0])) {
            throw new KeepeyeUtilException('初始值必须是二维数组');
        }

        if (!isset($list[0][$this->options['primaryKey']]) or !isset($list[0][$this->options['parentKey']])) {
            throw new KeepeyeUtilException('数组元素必须包含' . $this->options['primaryKey'] . '和' . $this->options['parentKey'] . '字段');
        }

        $formattedList = array();
        //在list中插入一个id为0的表示根
        $formattedList[0] = array($this->options['levelKey'] => 0, $this->options['childrenKey'] => array());
        //将列表元素一个个格式化
        foreach ($list as $node) {
            $node[$this->options['levelKey']] = 0;
            $node[$this->options['childrenKey']] = array();
            $formattedList[$node[$this->options['primaryKey']]] = $node;
        }
        foreach ($formattedList as $id => &$node) {
            if ($id == 0) continue;
            $formattedList[$node[$this->options['parentKey']]][$this->options['childrenKey']][$node[$this->options['primaryKey']]] = &$node;
        }

        return $formattedList;
    }

    /**
     * 获取格式化后的数组列表
     * @return array
     */
    public function getList()
    {
        return $this->list;
    }

    /**
     * 获取某个节点的一级子节点
     * @param $id
     * @return array
     */
    public function getChildren($id)
    {
        $children = isset($this->list[$id]) ? $this->list[$id][$this->options['childrenKey']] : array();
        foreach ($children as &$child) {
            unset($child[$this->options['childrenKey']]);
        }
        return $children;
    }

    /**
     * 获取某个节点的一级子节点id集合
     * @param $id
     * @return array
     */
    public function getChildrenIds($id)
    {
        return array_keys($this->getChildren($id));
    }

    /**
     * 判断一个节点是否另一个节点的儿子
     * @param int $child 儿子id
     * @param int $parent 父亲id
     * @return bool
     */
    public function isChild($child, $parent)
    {
        $childrenIds = $this->getChildrenIds($parent);
        return in_array($child, $childrenIds);
    }

    /**
     * 获取所有后代节点
     * @param $id 根id
     * @param int $except 需要排除的节点及其后代
     * @return array
     */
    public function getOffspring($id,$except=0)
    {
        $offspring = array();

        if (!isset($this->list[$id])) {
            return $offspring;
        }



        $parent = $this->list[$id];

        if ($children = $parent[$this->options['childrenKey']]) {
            $this->currentLevel++;
            foreach ($children as $cid => $child) {
                if ($except > 0 && $cid == $except) {
                    continue;
                }
                $child[$this->options['levelKey']] = $this->currentLevel;
                unset($child[$this->options['childrenKey']]);
                $offspring[$cid] = $child;
                $offspring = $offspring + $this->getOffspring($cid,$except);
            }
            $this->currentLevel--;
        }

        return $offspring;
    }

    /**
     * 获取指定节点的所有后代节点
     * @param int $id 指定节点id
     * @return array
     */
    public function getOffspringIds($id)
    {
        return array_keys($this->getOffspring($id));
    }

    /**
     * 判断一个节点是否另外一节点的后代
     * @param int $child
     * @param int $ancestor
     * @return bool
     */
    public function isOffspring($child, $ancestor)
    {
        $offspringIds = $this->getOffspringIds($ancestor);
        return in_array($child, $offspringIds);
    }

    /**
     * 获取一个节点的所有祖先节点，按从上到下排序
     * @param $id
     * @return array
     */
    public function getAncestors($id)
    {
        if (!isset($this->list[$id])) {
            return array();
        }
        $current = $this->list[$id];
        $ancestors = array();
        $parentId = $current[$this->options['parentKey']];
        if ($parentId != 0 && isset($this->list[$parentId])) {
            $ancestors[$parentId] = $this->list[$parentId];
            unset($ancestors[$parentId][$this->options['childrenKey']]);
            $ancestors = $ancestors + $this->getAncestors($parentId);
        }
        return array_reverse($ancestors, true);
    }

    public function has($id)
    {
        return isset($this->list[$id]);
    }

}