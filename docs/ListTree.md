ListTree使用文档
=========

###初始化

- LARAVEL

```
    $list = Category::all()->toArray();//所有分类的二维数组
    if (!empty($list)) {
        $tree = App::make('ListTreeUtil',[$list]);
    }
```  
    

- 通用

```
    $list = Category::all()->toArray();//所有分类的二维数组
    if (!empty($list)) {
        $tree = new \Keepeye\Utils\ListTree($list);
    }
```
- 参数说明
    $list 是一个二维数组，结构如下
    
    ```
    array(
        array('id'=>1,'parent'=>0,'name'=>'分类1'),
        array('id'=>2,'parent'=>0,'name'=>'分类2'),
        array('id'=>3,'parent'=>1,'name'=>'分类1-1'),
        array('id'=>4,'parent'=>2,'name'=>'分类2-1'),
    )
    ```
    $list 不能为空，否则会出异常
    

###方法列表
- TreeList::getChildren($id)
  获取指定分类的儿子分类
- TreeList::getChildrenIds($id)
  获取指定分类的所有儿子的id集合
- TreeList::isChild($child, $parent)
  判断一个分类是否另一个分类的儿子
- TreeList::getOffspring($id,$except=0)
  获取指定分类所有后代，$except可以排除某个分类及其后代，$id＝0表示从根节点开始
- TreeList::getOffspringIds($id,$except=0)
  获取所有后代的id集合，参数同上
- TreeList::isOffspring($child, $ancestor)
  判断一个分类是否另外一个分类的后代
- TreeList::getAncestors($id)
  获取一个分类的祖先线
- TreeList::has($id)
  判断分类树中是否含有指定id的分类

###关于返回值
默认整理后的数组还是二维数组，是按照父子关系排序的，形如：

    分类1
    分类1-1
    分类1-1-1
    分类1-2
    分类2
    ...
其中每个数组单元结构为：

    分类id => array('id'=>分类id,'pid'=>父类id,'name'=>'分类名','children'=>儿子分类二维数组,'level'=>深度级别)

level:这个是自动加上的字段，顶级分类为0，一级分类1，以此类推，可以在模板中显示时，作为缩进参考值
children:包含了该分类的儿子分类，同样是一个二维数组。

###自定义选项
有时候我们的初始数组字段名可能有区别，比如 id，parent字段可能是其他名字，这里提供了自定义的途径。
在初始化的时候了：
    
    $options = array(
        'primaryKey' => 'id',//主键字段
        'parentKey' => 'parent',//父类id字段
        'levelKey' => 'level',//深度
        'childrenKey' => 'children',//儿子
    );
    $tree = new \Keepeye\Utils\ListTree($list,$options);

    
###示例

1.读取顶级分类

    $list = $tree->getChildren(0);

2.在编辑分类的时候，父类select中应该排除自身以及后代分类：

    $list = $tree->getOffspring(0,$id);//$id是当前分类id
    
3.当删除分类的时候，我们需要取得该分类下所有后代分类的id，一并删除

    $ids = $tree->getOffspringIds($id);//$id表示当前分类id
    
    
###总结
大致的使用方法就这些了，不懂的地方建议看看源码，若还有什么疑问或建议可以提issues给我。