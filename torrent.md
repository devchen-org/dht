```
Multi-file Torrent
├─announce
├─announce-list
├─comment
├─comment.utf-8
├─created by
├─creation date
├─encoding
├─info
│ ├─files
│ │ ├─length
│ │ ├─path
│ │ └─path.utf-8
│ ├─name
│ ├─name.utf-8
│ ├─piece length
│ ├─pieces
│ ├─publisher
│ ├─publisher-url
│ ├─publisher-url.utf-8
│ └─publisher.utf-8
└─nodes
    
Single-File Torrent
├─announce
├─announce-list
├─comment
├─comment.utf-8
├─created by
├─creation date
├─encoding
├─info
│ ├─length
│ ├─name
│ ├─name.utf-8
│ ├─piece length
│ ├─pieces
│ ├─publisher
│ ├─publisher-url
│ ├─publisher-url.utf-8
│ └─publisher.utf-8
└─nodes

```
```
announce             Tracker的主服务器
announce-list        Tracker服务器列表
comment              种子文件的注释
created by           创建者,制作软件
creation date        种子文件建立的时间,是从1970年1月1日00:00:00到现在的秒数
encoding             种子文件的默认编码,比如GB2312,Big5,utf-8等
info                 所有关于下载的文件的信息都在这个字段里，它包括多个子字段，而且根据下载的是单个文件还是多个文件，子字段的项目会不同。当种子里包含多个文件时，info字段包括如下子字段：
    length           文件的大小，用byte计算
    path             文件的名字，在下载时不可更改
    path.utf-8       文件名的UTF-8编码，同上
    name             推荐的文件夹名，此项可于下载时更改。
    piece length     每个文件块的大小，用Byte计算
    pieces           文件的特征信息，该字段比较大，实际上是种子内包含所有的文件段的SHA1的校验值的连接，即将所有文件按照piece length的字节大小分成块，每块计算一个SHA1值，然后将这些值连接起来就形成了pieces字段，由于SHA1的校验值为20Byte，所以该字段的大小始终为20的整数倍字节。该字段是Torrent文件中体积最大的部分，可见如果大文件分块很小，会造成Torrent文件体积庞大。
    files            表示文件的名字，大小，该字段包含如下三个子字段(当发布单文件时，files字段是没有的):
        lenghth      文件的大小，用byte计算
        path         文件的名字，在下载时不可更改
        path.utf-8   文件名的UTF-8编码，同上
nodes                这个字段包含一系列ip和相应端口的列表，是用于连接DHT初始node

```
- 说到info就不得不说INFO_HASH，这个值是info字段的HASH值，20个Byte，同样是使用SHA1作为HASH函数。由于info字段是发布的文件信息构成的，所以INFO_HASH在BT协议中是用来识别不同的种子文件的。基本上每个种子文件的INFO_HASH都是不同的(至少现在还没有人发现有SHA的冲突)，所以BT服务器以及客户端都是以这个值来识别不同的种子文件的。
  
  计算的具体范围是从info字段开始(不包含"info"这四个字节)，一直到nodes字段为止(不包含"nodes"这5个字节和nodes前边表示nodes字段长度的"5:"这两个字节)。另外，INFO_HASH值是即时计算的，并不包含在Torrent文件中。
